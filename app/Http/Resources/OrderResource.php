<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'table_number' => $this->table_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'total_amount' => $this->total_amount,
            'formatted_total' => $this->formatted_total,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),

            // Calculated fields
            'items_count' => $this->when(
                $this->relationLoaded('orderItems'),
                $this->orderItems->count()
            ),
            'total_paid' => $this->when(
                $this->relationLoaded('payments'),
                $this->payments->where('status', 'completed')->sum('amount')
            ),
            'remaining_amount' => $this->when(
                $this->relationLoaded('payments'),
                max(0, $this->total_amount - $this->payments->where('status', 'completed')->sum('amount'))
            ),

            // Status helpers
            'is_paid' => $this->isPaid(),
            'can_be_cancelled' => $this->canBeCancelled(),

            // Time tracking
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at_human' => $this->updated_at->diffForHumans(),
        ];
    }
}
