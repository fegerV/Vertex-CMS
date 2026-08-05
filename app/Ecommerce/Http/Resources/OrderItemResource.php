<?php

namespace App\Ecommerce\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total' => $this->total,
            'product' => when($this->whenLoaded('product'), fn () => new ProductResource($this->product)),
        ];
    }
}
