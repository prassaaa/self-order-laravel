<?php

namespace App\Repositories;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class OrderRepository
{
    /**
     * Get all orders with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Order::with(['orderItems.menu', 'payments']);

        $this->applyFilters($query, $filters);

        return $query->latest()->get();
    }

    /**
     * Get paginated orders with filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::with(['orderItems.menu', 'payments']);

        $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find order by ID.
     */
    public function findById(int $id): ?Order
    {
        return Order::with(['orderItems.menu', 'payments'])->find($id);
    }

    /**
     * Find order by ID or fail.
     */
    public function findByIdOrFail(int $id): Order
    {
        return Order::with(['orderItems.menu', 'payments'])->findOrFail($id);
    }

    /**
     * Find order by order number.
     */
    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::with(['orderItems.menu', 'payments'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    /**
     * Get orders by status.
     */
    public function getByStatus(string $status, array $filters = []): Collection
    {
        $filters['status'] = $status;
        return $this->getAll($filters);
    }

    /**
     * Get orders by payment status.
     */
    public function getByPaymentStatus(string $paymentStatus, array $filters = []): Collection
    {
        $filters['payment_status'] = $paymentStatus;
        return $this->getAll($filters);
    }

    /**
     * Get orders by date range.
     */
    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $filters['start_date'] = $startDate;
        $filters['end_date'] = $endDate;
        return $this->getAll($filters);
    }

    /**
     * Get today's orders.
     */
    public function getTodayOrders(array $filters = []): Collection
    {
        $filters['start_date'] = Carbon::today()->toDateString();
        $filters['end_date'] = Carbon::today()->toDateString();
        return $this->getAll($filters);
    }

    /**
     * Get pending orders.
     */
    public function getPendingOrders(): Collection
    {
        return $this->getByStatus('pending');
    }

    /**
     * Get orders ready for pickup.
     */
    public function getReadyOrders(): Collection
    {
        return $this->getByStatus('ready');
    }

    /**
     * Get recent orders.
     */
    public function getRecent(int $limit = 10): Collection
    {
        return Order::with(['orderItems.menu', 'payments'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new order.
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update an order.
     */
    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order->fresh(['orderItems.menu', 'payments']);
    }

    /**
     * Delete an order.
     */
    public function delete(Order $order): bool
    {
        return $order->delete();
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);
        return $order->fresh(['orderItems.menu', 'payments']);
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(Order $order, string $paymentStatus): Order
    {
        $order->update(['payment_status' => $paymentStatus]);
        return $order->fresh(['orderItems.menu', 'payments']);
    }

    /**
     * Get order statistics.
     */
    public function getStatistics(string $startDate = null, string $endDate = null): array
    {
        $cacheKey = "order_statistics_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $query = Order::query();

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            return [
                'total_orders' => $query->count(),
                'pending_orders' => $query->where('status', 'pending')->count(),
                'confirmed_orders' => $query->where('status', 'confirmed')->count(),
                'preparing_orders' => $query->where('status', 'preparing')->count(),
                'ready_orders' => $query->where('status', 'ready')->count(),
                'completed_orders' => $query->where('status', 'completed')->count(),
                'cancelled_orders' => $query->where('status', 'cancelled')->count(),
                'paid_orders' => $query->where('payment_status', 'paid')->count(),
                'pending_payment_orders' => $query->where('payment_status', 'pending')->count(),
                'total_revenue' => $query->where('payment_status', 'paid')->sum('total_amount'),
                'average_order_value' => $query->where('payment_status', 'paid')->avg('total_amount'),
            ];
        });
    }

    /**
     * Get orders count by status.
     */
    public function getCountByStatus(): array
    {
        return Cache::remember('orders_count_by_status', 300, function () {
            return Order::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        });
    }

    /**
     * Get daily order counts for a date range.
     */
    public function getDailyOrderCounts(string $startDate, string $endDate): Collection
    {
        return Order::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['table_number'])) {
            $query->where('table_number', $filters['table_number']);
        }

        if (isset($filters['customer_name'])) {
            $query->where('customer_name', 'like', '%' . $filters['customer_name'] . '%');
        }

        if (isset($filters['order_number'])) {
            $query->where('order_number', 'like', '%' . $filters['order_number'] . '%');
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('customer_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('customer_phone', 'like', '%' . $filters['search'] . '%');
            });
        }
    }

    /**
     * Clear order-related cache.
     */
    public function clearCache(): void
    {
        Cache::flush(); // For simplicity, we'll flush all cache
    }
}
