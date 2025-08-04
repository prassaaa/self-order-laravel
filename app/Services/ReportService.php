<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get sales report grouped by period.
     */
    public function getSalesReport(string $startDate, string $endDate, string $groupBy = 'day'): array
    {
        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $orders = Order::select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as average_order_value')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'period' => $groupBy,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'data' => $orders,
            'summary' => [
                'total_orders' => $orders->sum('total_orders'),
                'total_revenue' => $orders->sum('total_revenue'),
                'average_order_value' => $orders->avg('average_order_value'),
            ]
        ];
    }

    /**
     * Get popular items report.
     */
    public function getPopularItemsReport(string $startDate = null, string $endDate = null, int $limit = 10): array
    {
        $query = Menu::select(
                'menus.id',
                'menus.name',
                'menus.price',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
            )
            ->join('order_items', 'menus.id', '=', 'order_items.menu_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('categories', 'menus.category_id', '=', 'categories.id')
            ->where('orders.status', '!=', 'cancelled');

        if ($startDate) {
            $query->whereDate('orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('orders.created_at', '<=', $endDate);
        }

        $items = $query->groupBy('menus.id', 'menus.name', 'menus.price', 'categories.name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => $limit,
            'data' => $items,
            'summary' => [
                'total_items_sold' => $items->sum('total_quantity'),
                'total_revenue' => $items->sum('total_revenue'),
            ]
        ];
    }

    /**
     * Get revenue report with breakdown.
     */
    public function getRevenueReport(string $startDate, string $endDate): array
    {
        // Total revenue
        $totalRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        // Revenue by payment method
        $revenueByMethod = Payment::select(
                'method',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('method')
            ->get();

        // Revenue by category
        $revenueByCategory = DB::table('categories')
            ->select(
                'categories.name',
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('SUM(order_items.quantity) as total_quantity')
            )
            ->join('menus', 'categories.id', '=', 'menus.category_id')
            ->join('order_items', 'menus.id', '=', 'order_items.menu_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Daily revenue trend
        $dailyRevenue = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_revenue' => $totalRevenue,
            'revenue_by_method' => $revenueByMethod,
            'revenue_by_category' => $revenueByCategory,
            'daily_revenue' => $dailyRevenue,
            'summary' => [
                'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', '!=', 'cancelled')->count(),
                'paid_orders' => Order::whereBetween('created_at', [$startDate, $endDate])
                    ->where('payment_status', 'paid')->count(),
                'average_order_value' => Order::whereBetween('created_at', [$startDate, $endDate])
                    ->where('payment_status', 'paid')->avg('total_amount'),
            ]
        ];
    }

    /**
     * Get dashboard analytics.
     */
    public function getDashboardAnalytics(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'today' => [
                'orders' => Order::whereDate('created_at', $today)->count(),
                'revenue' => Order::whereDate('created_at', $today)
                    ->where('payment_status', 'paid')->sum('total_amount'),
                'pending_orders' => Order::whereDate('created_at', $today)
                    ->where('status', 'pending')->count(),
            ],
            'yesterday' => [
                'orders' => Order::whereDate('created_at', $yesterday)->count(),
                'revenue' => Order::whereDate('created_at', $yesterday)
                    ->where('payment_status', 'paid')->sum('total_amount'),
            ],
            'this_month' => [
                'orders' => Order::where('created_at', '>=', $thisMonth)->count(),
                'revenue' => Order::where('created_at', '>=', $thisMonth)
                    ->where('payment_status', 'paid')->sum('total_amount'),
            ],
            'last_month' => [
                'orders' => Order::whereBetween('created_at', [$lastMonth, $thisMonth])->count(),
                'revenue' => Order::whereBetween('created_at', [$lastMonth, $thisMonth])
                    ->where('payment_status', 'paid')->sum('total_amount'),
            ],
            'popular_items_today' => $this->getPopularItemsReport($today->toDateString(), $today->toDateString(), 5),
            'recent_orders' => Order::with(['orderItems.menu'])
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Export report to file.
     */
    public function exportReport(string $type, string $format, string $startDate, string $endDate): string
    {
        $data = match($type) {
            'sales' => $this->getSalesReport($startDate, $endDate),
            'popular_items' => $this->getPopularItemsReport($startDate, $endDate),
            'revenue' => $this->getRevenueReport($startDate, $endDate),
            default => throw new \Exception('Invalid report type'),
        };

        $filename = "{$type}_report_{$startDate}_to_{$endDate}.{$format}";
        $filePath = "reports/{$filename}";

        if ($format === 'pdf') {
            return $this->exportToPdf($data, $type, $filePath);
        } elseif ($format === 'excel') {
            return $this->exportToExcel($data, $type, $filePath);
        }

        throw new \Exception('Invalid export format');
    }

    /**
     * Export data to PDF.
     */
    protected function exportToPdf(array $data, string $type, string $filePath): string
    {
        // This would use dompdf to generate PDF
        // For now, we'll create a simple implementation
        $pdf = app('dompdf.wrapper');
        $html = view("reports.{$type}", compact('data'))->render();
        $pdf->loadHTML($html);
        
        $fullPath = storage_path('app/public/' . $filePath);
        $pdf->save($fullPath);

        return $filePath;
    }

    /**
     * Export data to Excel.
     */
    protected function exportToExcel(array $data, string $type, string $filePath): string
    {
        // This would use Laravel Excel package
        // For now, we'll create a CSV file
        $fullPath = storage_path('app/public/' . $filePath);
        $handle = fopen($fullPath, 'w');

        // Write CSV headers and data based on report type
        if ($type === 'sales') {
            fputcsv($handle, ['Period', 'Total Orders', 'Total Revenue', 'Average Order Value']);
            foreach ($data['data'] as $row) {
                fputcsv($handle, [$row->period, $row->total_orders, $row->total_revenue, $row->average_order_value]);
            }
        }

        fclose($handle);
        return $filePath;
    }
}
