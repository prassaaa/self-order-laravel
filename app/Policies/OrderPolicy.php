<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Staff can view all orders, customers can view their own orders
        return $user?->isStaff() ?? false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Order $order): bool
    {
        // Staff can view any order
        if ($user?->isStaff()) {
            return true;
        }

        // For now, allow public access to orders (customer interface)
        // In production, you might want to implement order ownership
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Anyone can create orders (customer interface)
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Order $order): bool
    {
        // Only staff can update orders
        if (!$user?->isStaff()) {
            return false;
        }

        // Cannot update completed or cancelled orders
        return !in_array($order->status, ['completed', 'cancelled']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Order $order): bool
    {
        // Only staff can cancel orders
        if (!$user?->isStaff()) {
            return false;
        }

        // Can only cancel orders that can be cancelled
        return $order->canBeCancelled();
    }

    /**
     * Determine whether the user can update order status.
     */
    public function updateStatus(?User $user, Order $order): bool
    {
        // Only staff can update order status
        return $user?->isStaff() ?? false;
    }

    /**
     * Determine whether the user can process payments for the order.
     */
    public function processPayment(?User $user, Order $order): bool
    {
        // Only staff can process payments
        if (!$user?->isStaff()) {
            return false;
        }

        // Cannot process payment for cancelled orders
        return $order->status !== 'cancelled';
    }

    /**
     * Determine whether the user can view order payments.
     */
    public function viewPayments(?User $user, Order $order): bool
    {
        // Staff can view all payments
        if ($user?->isStaff()) {
            return true;
        }

        // For now, allow public access (customer can see their payment status)
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        // Only admin can restore orders
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        // Only admin can permanently delete orders
        return $user->hasRole('admin');
    }
}
