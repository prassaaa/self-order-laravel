<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PrintController extends Controller
{
    protected $printService;

    public function __construct(PrintService $printService)
    {
        $this->printService = $printService;
    }

    /**
     * Print customer receipt.
     */
    public function printCustomerReceipt(Order $order): JsonResponse
    {
        try {
            $result = $this->printService->printCustomerReceipt($order);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Customer receipt printed successfully' : 'Failed to print customer receipt',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error printing customer receipt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print kitchen receipt.
     */
    public function printKitchenReceipt(Order $order): JsonResponse
    {
        try {
            $result = $this->printService->printKitchenReceipt($order);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Kitchen receipt printed successfully' : 'Failed to print kitchen receipt',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error printing kitchen receipt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate and download PDF receipt.
     */
    public function downloadPdfReceipt(Order $order)
    {
        try {
            $path = $this->printService->generatePdfReceipt($order);
            
            if (!Storage::exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF receipt not found',
                ], 404);
            }

            return Storage::download($path, 'receipt-' . $order->order_number . '.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating PDF receipt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print all receipts for an order.
     */
    public function printAllReceipts(Order $order): JsonResponse
    {
        try {
            $results = $this->printService->printOrderReceipts($order);
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'message' => 'Print jobs completed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error printing receipts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test printer connection.
     */
    public function testPrinter(Request $request): JsonResponse
    {
        $request->validate([
            'printer_type' => 'required|in:customer_printer,kitchen_printer',
        ]);

        try {
            $result = $this->printService->testPrinter($request->printer_type);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Printer test successful' : 'Printer test failed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing printer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get printer status.
     */
    public function getPrinterStatus(): JsonResponse
    {
        try {
            $status = [
                'customer_printer' => [
                    'connected' => $this->printService->testPrinter('customer_printer'),
                    'type' => 'Customer Receipt Printer',
                ],
                'kitchen_printer' => [
                    'connected' => $this->printService->testPrinter('kitchen_printer'),
                    'type' => 'Kitchen Order Printer',
                ],
            ];

            return response()->json([
                'success' => true,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking printer status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print receipt preview (HTML version).
     */
    public function previewReceipt(Order $order)
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

        return view('receipts.customer', $data);
    }
}
