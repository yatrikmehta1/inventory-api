<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'name' => $this->name, 'sku' => $this->sku,
            'description' => $this->description, 'base_price' => $this->base_price,
            'is_active' => (bool) $this->is_active,
            'category' => $this->whenLoaded('category', fn () => ['id' => $this->category?->id, 'name' => $this->category?->name]),
            'stock' => $this->when(isset($this->available_stock_total), fn () => (int) $this->available_stock_total),
            'stocks' => $this->whenLoaded('stocks', fn () => $this->stocks->map(fn ($s) => [
                'warehouse_id' => $s->warehouse_id, 'quantity' => $s->quantity,
                'reserved_quantity' => $s->reserved_quantity,
                'available_quantity' => $s->quantity - $s->reserved_quantity,
            ])),
        ];
    }
}
