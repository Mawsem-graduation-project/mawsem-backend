<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
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
            'products' => new ProductResource($this->whenLoaded('product')),
            'current_stock' => $this->current_stock,
            'minimum_stock' => $this->minimum_stock,
            'shop' => $this->whenLoaded('shop'),
        ];
    }
}
