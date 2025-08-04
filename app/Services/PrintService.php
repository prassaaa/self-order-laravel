<?php

namespace App\Services;

use App\Models\Order;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PrintService
{
    protected $printerConfig;

    public function __construct()
    {
        $this->printerConfig = config('printing', [
            'customer_printer' => [
                'type' => 'network',
                'host' => '192.168.1.100',
                'port' => 9100,
            ],
            'kitchen_printer' => [
                'type' => 'network', 
                'host' => '192.168.1.101',
                'port' => 9100,
            ],
        ]);
    }

    /**
     * Print customer receipt.
     */
    public function printCustomerReceipt(Order $order): bool
    {
        try {
            $connector = $this->getConnector('customer_printer');
            if (!$connector) {
                return false;
            }

            $printer = new Printer($connector);
            
            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text(config('app.name', 'UMKM Restaurant') . "\n");
            $printer->setTextSize(1, 1);
            $printer->text("Customer Receipt\n");
            $printer->text(str_repeat("-", 32) . "\n");
            
            // Order details
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Order: " . $order->order_number . "\n");
            $printer->text("Date: " . $order->created_at->format('d/m/Y H:i') . "\n");
            if ($order->table_number) {
                $printer->text("Table: " . $order->table_number . "\n");
            }
            if ($order->customer_name) {
                $printer->text("Customer: " . $order->customer_name . "\n");
            }
            $printer->text(str_repeat("-", 32) . "\n");
            
            // Order items
            foreach ($order->orderItems as $item) {
                $printer->text($item->menu->name . "\n");
                $printer->text(sprintf("  %dx %s = %s\n", 
                    $item->quantity,
                    number_format($item->price, 0, ',', '.'),
                    number_format($item->subtotal, 0, ',', '.')
                ));
                if ($item->notes) {
                    $printer->text("  Note: " . $item->notes . "\n");
                }
            }
            
            $printer->text(str_repeat("-", 32) . "\n");
            
            // Total
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->setTextSize(2, 1);
            $printer->text("TOTAL: Rp " . number_format($order->total_amount, 0, ',', '.') . "\n");
            $printer->setTextSize(1, 1);
            
            // Footer
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(str_repeat("-", 32) . "\n");
            $printer->text("Thank you for your order!\n");
            $printer->text("Please keep this receipt\n");
            $printer->feed(3);
            $printer->cut();
            $printer->close();
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to print customer receipt: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Print kitchen receipt.
     */
    public function printKitchenReceipt(Order $order): bool
    {
        try {
            $connector = $this->getConnector('kitchen_printer');
            if (!$connector) {
                return false;
            }

            $printer = new Printer($connector);
            
            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("KITCHEN ORDER\n");
            $printer->setTextSize(1, 1);
            $printer->text(str_repeat("=", 32) . "\n");
            
            // Order details
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setTextSize(2, 1);
            $printer->text("Order: " . $order->order_number . "\n");
            $printer->setTextSize(1, 1);
            $printer->text("Time: " . $order->created_at->format('H:i') . "\n");
            if ($order->table_number) {
                $printer->text("Table: " . $order->table_number . "\n");
            }
            if ($order->customer_name) {
                $printer->text("Customer: " . $order->customer_name . "\n");
            }
            $printer->text(str_repeat("=", 32) . "\n");
            
            // Order items
            foreach ($order->orderItems as $item) {
                $printer->setTextSize(1, 2);
                $printer->text(sprintf("%dx %s\n", $item->quantity, $item->menu->name));
                $printer->setTextSize(1, 1);
                if ($item->notes) {
                    $printer->text("*** " . strtoupper($item->notes) . " ***\n");
                }
                $printer->text("\n");
            }
            
            // Special instructions
            if ($order->notes) {
                $printer->text(str_repeat("-", 32) . "\n");
                $printer->text("SPECIAL INSTRUCTIONS:\n");
                $printer->setTextSize(1, 2);
                $printer->text(strtoupper($order->notes) . "\n");
                $printer->setTextSize(1, 1);
            }
            
            $printer->feed(3);
            $printer->cut();
            $printer->close();
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to print kitchen receipt: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate PDF receipt.
     */
    public function generatePdfReceipt(Order $order): string
    {
        $data = [
            'order' => $order,
            'company' => [
                'name' => config('app.name', 'UMKM Restaurant'),
                'address' => 'Jl. Contoh No. 123, Jakarta',
                'phone' => '021-12345678',
                'email' => 'info@umkmrestaurant.com',
            ],
        ];

        $pdf = Pdf::loadView('receipts.customer', $data);
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'receipt-' . $order->order_number . '.pdf';
        $path = 'receipts/' . $filename;
        
        Storage::put($path, $pdf->output());
        
        return $path;
    }

    /**
     * Get printer connector based on configuration.
     */
    protected function getConnector(string $printerType)
    {
        $config = $this->printerConfig[$printerType] ?? null;
        
        if (!$config) {
            return null;
        }

        try {
            switch ($config['type']) {
                case 'network':
                    return new NetworkPrintConnector($config['host'], $config['port']);
                case 'file':
                    return new FilePrintConnector($config['path']);
                default:
                    return null;
            }
        } catch (\Exception $e) {
            Log::error("Failed to connect to printer {$printerType}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Test printer connection.
     */
    public function testPrinter(string $printerType): bool
    {
        try {
            $connector = $this->getConnector($printerType);
            if (!$connector) {
                return false;
            }

            $printer = new Printer($connector);
            $printer->text("Test print - " . date('Y-m-d H:i:s') . "\n");
            $printer->feed(2);
            $printer->cut();
            $printer->close();
            
            return true;
        } catch (\Exception $e) {
            Log::error("Printer test failed for {$printerType}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Print both customer and kitchen receipts.
     */
    public function printOrderReceipts(Order $order): array
    {
        $results = [
            'customer' => $this->printCustomerReceipt($order),
            'kitchen' => $this->printKitchenReceipt($order),
            'pdf' => null,
        ];

        try {
            $results['pdf'] = $this->generatePdfReceipt($order);
        } catch (\Exception $e) {
            Log::error('Failed to generate PDF receipt: ' . $e->getMessage());
        }

        return $results;
    }
}
