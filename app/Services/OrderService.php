<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Events\NewOrderCreated;
use App\Events\OrderStatusUpdated;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new order.
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // Create the order
            $order = Order::create([
                'table_number' => $data['table_number'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Add order items
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $menu = Menu::findOrFail($item['menu_id']);

                // Check if menu is available
                if (!$menu->is_available) {
                    throw new \Exception("Menu '{$menu->name}' is not available");
                }

                $subtotal = $menu->price * $item['quantity'];
                $totalAmount += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'quantity' => $item['quantity'],
                    'price' => $menu->price,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            // Dispatch new order event
            event(new NewOrderCreated($order->load('orderItems.menu')));

            return $order;
        });
    }

    /**
     * Update an existing order.
     */
    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            // Check if order can be updated
            if (!in_array($order->status, ['pending', 'confirmed'])) {
                throw new \Exception('Order cannot be updated in current status');
            }

            // Update order details
            $order->update([
                'table_number' => $data['table_number'] ?? $order->table_number,
                'customer_name' => $data['customer_name'] ?? $order->customer_name,
                'customer_phone' => $data['customer_phone'] ?? $order->customer_phone,
                'notes' => $data['notes'] ?? $order->notes,
            ]);

            // Update order items if provided
            if (isset($data['items'])) {
                // Delete existing items
                $order->orderItems()->delete();

                // Add new items
                $totalAmount = 0;
                foreach ($data['items'] as $item) {
                    $menu = Menu::findOrFail($item['menu_id']);

                    if (!$menu->is_available) {
                        throw new \Exception("Menu '{$menu->name}' is not available");
                    }

                    $subtotal = $menu->price * $item['quantity'];
                    $totalAmount += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'menu_id' => $menu->id,
                        'quantity' => $item['quantity'],
                        'price' => $menu->price,
                        'subtotal' => $subtotal,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }

                // Update order total
                $order->update(['total_amount' => $totalAmount]);
            }

            return $order->fresh();
        });
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Order $order, string $status): Order
    {
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready'],
            'ready' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (!in_array($status, $validTransitions[$order->status] ?? [])) {
            throw new \Exception("Invalid status transition from {$order->status} to {$status}");
        }

        $order->update(['status' => $status]);

        // Dispatch order status updated event
        event(new OrderStatusUpdated($order->load('orderItems.menu')));

        return $order;
    }

    /**
     * Cancel an order.
     */
    public function cancelOrder(Order $order): Order
    {
        if (!$order->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled');
        }

        return DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);

            // If there are any payments, mark them as refunded
            $order->payments()->where('status', 'completed')->update([
                'status' => 'refunded'
            ]);

            return $order;
        });
    }

    /**
     * Get order statistics.
     */
    public function getOrderStatistics(string $startDate = null, string $endDate = null): array
    {
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
            'total_revenue' => $query->where('payment_status', 'paid')->sum('total_amount'),
            'average_order_value' => $query->where('payment_status', 'paid')->avg('total_amount'),
        ];
    }
}
