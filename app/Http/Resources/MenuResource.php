<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
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
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'is_available' => $this->is_available,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'category' => new CategoryResource($this->whenLoaded('category')),

            // Conditional fields for admin/kasir
            'order_items_count' => $this->when(
                $request->user()?->isStaff() && isset($this->order_items_count),
                $this->order_items_count
            ),
            'total_ordered' => $this->when(
                $request->user()?->isStaff() && isset($this->total_ordered),
                $this->total_ordered
            ),
        ];
    }
}
