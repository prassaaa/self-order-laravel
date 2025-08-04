<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'menu_id' => $this->menu_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'subtotal' => $this->subtotal,
            'formatted_subtotal' => $this->formatted_subtotal,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'menu' => new MenuResource($this->whenLoaded('menu')),
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
