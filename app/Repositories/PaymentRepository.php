<?php

namespace App\Repositories;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PaymentRepository
{
    /**
     * Get all payments with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Payment::with(['order', 'processedBy']);

        $this->applyFilters($query, $filters);

        return $query->latest()->get();
    }

    /**
     * Get paginated payments with filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::with(['order', 'processedBy']);

        $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find payment by ID.
     */
    public function findById(int $id): ?Payment
    {
        return Payment::with(['order', 'processedBy'])->find($id);
    }

    /**
     * Find payment by ID or fail.
     */
    public function findByIdOrFail(int $id): Payment
    {
        return Payment::with(['order', 'processedBy'])->findOrFail($id);
    }

    /**
     * Find payment by transaction ID.
     */
    public function findByTransactionId(string $transactionId): ?Payment
    {
        return Payment::with(['order', 'processedBy'])
            ->where('transaction_id', $transactionId)
            ->first();
    }

    /**
     * Get payments by order.
     */
    public function getByOrder(int $orderId): Collection
    {
        return Payment::with('processedBy')
            ->where('order_id', $orderId)
            ->latest()
            ->get();
    }

    /**
     * Get payments by method.
     */
    public function getByMethod(string $method, array $filters = []): Collection
    {
        $filters['method'] = $method;
        return $this->getAll($filters);
    }

    /**
     * Get payments by status.
     */
    public function getByStatus(string $status, array $filters = []): Collection
    {
        $filters['status'] = $status;
        return $this->getAll($filters);
    }

    /**
     * Get completed payments.
     */
    public function getCompleted(array $filters = []): Collection
    {
        return $this->getByStatus('completed', $filters);
    }

    /**
     * Get pending payments.
     */
    public function getPending(array $filters = []): Collection
    {
        return $this->getByStatus('pending', $filters);
    }

    /**
     * Get payments by date range.
     */
    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $filters['start_date'] = $startDate;
        $filters['end_date'] = $endDate;
        return $this->getAll($filters);
    }

    /**
     * Get today's payments.
     */
    public function getTodayPayments(array $filters = []): Collection
    {
        $filters['start_date'] = Carbon::today()->toDateString();
        $filters['end_date'] = Carbon::today()->toDateString();
        return $this->getAll($filters);
    }

    /**
     * Get payments processed by user.
     */
    public function getByProcessedBy(int $userId, array $filters = []): Collection
    {
        $filters['processed_by'] = $userId;
        return $this->getAll($filters);
    }

    /**
     * Create a new payment.
     */
    public function create(array $data): Payment
    {
        $payment = Payment::create($data);
        $this->clearCache();
        return $payment->load(['order', 'processedBy']);
    }

    /**
     * Update a payment.
     */
    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);
        $this->clearCache();
        return $payment->fresh(['order', 'processedBy']);
    }

    /**
     * Delete a payment.
     */
    public function delete(Payment $payment): bool
    {
        $result = $payment->delete();
        $this->clearCache();
        return $result;
    }

    /**
     * Get payment statistics.
     */
    public function getStatistics(string $startDate = null, string $endDate = null): array
    {
        $cacheKey = "payment_statistics_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $query = Payment::query();

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            $completedQuery = clone $query;
            $completedQuery->where('status', 'completed');

            return [
                'total_payments' => $query->count(),
                'completed_payments' => $completedQuery->count(),
                'pending_payments' => $query->where('status', 'pending')->count(),
                'failed_payments' => $query->where('status', 'failed')->count(),
                'refunded_payments' => $query->where('status', 'refunded')->count(),
                'total_amount' => $completedQuery->sum('amount'),
                'cash_amount' => $completedQuery->where('method', 'cash')->sum('amount'),
                'qris_amount' => $completedQuery->where('method', 'qris')->sum('amount'),
                'bank_transfer_amount' => $completedQuery->where('method', 'bank_transfer')->sum('amount'),
                'e_wallet_amount' => $completedQuery->where('method', 'e_wallet')->sum('amount'),
                'average_payment_amount' => $completedQuery->avg('amount'),
            ];
        });
    }

    /**
     * Get payment method breakdown.
     */
    public function getMethodBreakdown(string $startDate = null, string $endDate = null): array
    {
        $cacheKey = "payment_method_breakdown_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $query = Payment::where('status', 'completed');

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            return $query->selectRaw('method, COUNT(*) as count, SUM(amount) as total_amount')
                ->groupBy('method')
                ->get()
                ->keyBy('method')
                ->toArray();
        });
    }

    /**
     * Get daily payment totals.
     */
    public function getDailyTotals(string $startDate, string $endDate): Collection
    {
        return Payment::selectRaw('DATE(created_at) as date, SUM(amount) as total_amount, COUNT(*) as count')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get transaction history for an order.
     */
    public function getTransactionHistory(int $orderId): Collection
    {
        return Payment::with('processedBy')
            ->where('order_id', $orderId)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['processed_by'])) {
            $query->where('processed_by', $filters['processed_by']);
        }

        if (isset($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        if (isset($filters['transaction_id'])) {
            $query->where('transaction_id', 'like', '%' . $filters['transaction_id'] . '%');
        }

        if (isset($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if (isset($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }
    }

    /**
     * Clear payment-related cache.
     */
    public function clearCache(): void
    {
        Cache::flush(); // For simplicity, we'll flush all cache
    }
}
