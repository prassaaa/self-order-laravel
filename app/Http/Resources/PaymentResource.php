<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order_id' => $this->order_id,
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'method' => $this->method,
            'method_label' => $this->method_label,
            'status' => $this->status,
            'transaction_id' => $this->transaction_id,
            'notes' => $this->notes,
            'processed_at' => $this->processed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'order' => new OrderResource($this->whenLoaded('order')),
            'processed_by_user' => $this->when(
                $this->relationLoaded('processedBy') && $this->processedBy,
                [
                    'id' => $this->processedBy?->id,
                    'name' => $this->processedBy?->name,
                    'email' => $this->processedBy?->email,
                ]
            ),

            // Status helpers
            'is_completed' => $this->isCompleted(),

            // Time tracking
            'processed_at_human' => $this->when(
                $this->processed_at,
                $this->processed_at?->diffForHumans()
            ),
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }
}
