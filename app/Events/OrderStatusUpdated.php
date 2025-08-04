<?php

namespace App\Events;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    public string $previousStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, string $previousStatus = null)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus ?? $order->getOriginal('status');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders'),
            new PrivateChannel('kitchen'),
            new PrivateChannel('admin-dashboard'),
            new Channel("customer-order.{$this->order->id}"), // Public channel for customer
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order' => new OrderResource($this->order->load(['orderItems.menu', 'payments'])),
            'previous_status' => $this->previousStatus,
            'current_status' => $this->order->status,
            'message' => "Order status updated to {$this->order->status}",
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }
}
