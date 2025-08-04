<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Events\PaymentProcessed;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Create a new payment.
     */
    public function createPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $order = Order::findOrFail($data['order_id']);

            // Validate payment amount
            $remainingAmount = $this->getRemainingAmount($order);
            if ($data['amount'] > $remainingAmount) {
                throw new \Exception('Payment amount exceeds remaining balance');
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $data['amount'],
                'method' => $data['method'],
                'status' => $data['status'] ?? 'pending',
                'processed_by' => auth()->id(),
                'transaction_id' => $data['transaction_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update order payment status if fully paid
            $this->updateOrderPaymentStatus($order);

            return $payment;
        });
    }

    /**
     * Update an existing payment.
     */
    public function updatePayment(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            // Don't allow updating completed payments
            if ($payment->status === 'completed') {
                throw new \Exception('Cannot update completed payment');
            }

            $payment->update([
                'amount' => $data['amount'] ?? $payment->amount,
                'method' => $data['method'] ?? $payment->method,
                'status' => $data['status'] ?? $payment->status,
                'transaction_id' => $data['transaction_id'] ?? $payment->transaction_id,
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            // Update order payment status
            $this->updateOrderPaymentStatus($payment->order);

            return $payment;
        });
    }

    /**
     * Process payment for an order.
     */
    public function processOrderPayment(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data) {
            // Check if order can accept payments
            if ($order->status === 'cancelled') {
                throw new \Exception('Cannot process payment for cancelled order');
            }

            // Calculate remaining amount
            $remainingAmount = $this->getRemainingAmount($order);
            $paymentAmount = $data['amount'] ?? $remainingAmount;

            if ($paymentAmount > $remainingAmount) {
                throw new \Exception('Payment amount exceeds remaining balance');
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $paymentAmount,
                'method' => $data['method'],
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'transaction_id' => $data['transaction_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'processed_at' => now(),
            ]);

            // Update order payment status
            $this->updateOrderPaymentStatus($order);

            // Dispatch payment processed event
            event(new PaymentProcessed($payment->load('order')));

            return $payment;
        });
    }

    /**
     * Refund a payment.
     */
    public function refundPayment(Payment $payment, array $data = []): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            if ($payment->status !== 'completed') {
                throw new \Exception('Can only refund completed payments');
            }

            $payment->update([
                'status' => 'refunded',
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            // Update order payment status
            $this->updateOrderPaymentStatus($payment->order);

            return $payment;
        });
    }

    /**
     * Get remaining amount for an order.
     */
    public function getRemainingAmount(Order $order): float
    {
        $totalPaid = $order->payments()
            ->whereIn('status', ['completed'])
            ->sum('amount');

        return max(0, $order->total_amount - $totalPaid);
    }

    /**
     * Update order payment status based on payments.
     */
    protected function updateOrderPaymentStatus(Order $order): void
    {
        $remainingAmount = $this->getRemainingAmount($order);

        if ($remainingAmount <= 0) {
            $order->update(['payment_status' => 'paid']);
        } else {
            $totalPaid = $order->payments()
                ->whereIn('status', ['completed'])
                ->sum('amount');

            if ($totalPaid > 0) {
                $order->update(['payment_status' => 'partial']);
            } else {
                $order->update(['payment_status' => 'pending']);
            }
        }
    }

    /**
     * Get payment statistics.
     */
    public function getPaymentStatistics(string $startDate = null, string $endDate = null): array
    {
        $query = Payment::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $completedPayments = $query->where('status', 'completed');

        return [
            'total_payments' => $query->count(),
            'completed_payments' => $completedPayments->count(),
            'total_amount' => $completedPayments->sum('amount'),
            'cash_payments' => $completedPayments->where('method', 'cash')->sum('amount'),
            'qris_payments' => $completedPayments->where('method', 'qris')->sum('amount'),
            'bank_transfer_payments' => $completedPayments->where('method', 'bank_transfer')->sum('amount'),
            'e_wallet_payments' => $completedPayments->where('method', 'e_wallet')->sum('amount'),
            'average_payment_amount' => $completedPayments->avg('amount'),
        ];
    }

    /**
     * Validate payment method.
     */
    public function validatePaymentMethod(string $method): bool
    {
        $allowedMethods = ['cash', 'qris', 'bank_transfer', 'e_wallet'];
        return in_array($method, $allowedMethods);
    }

    /**
     * Process digital payment (QRIS, Bank Transfer, E-Wallet).
     */
    public function processDigitalPayment(array $data): array
    {
        // This would integrate with actual payment gateways
        // For now, we'll simulate the process

        $method = $data['method'];
        $amount = $data['amount'];

        // Simulate payment processing
        $transactionId = 'TXN-' . time() . '-' . strtoupper(substr($method, 0, 3));

        // In real implementation, this would call payment gateway APIs
        $success = true; // Simulate success

        return [
            'success' => $success,
            'transaction_id' => $transactionId,
            'message' => $success ? 'Payment processed successfully' : 'Payment failed',
        ];
    }
}
