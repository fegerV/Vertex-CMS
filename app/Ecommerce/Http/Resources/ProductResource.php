<?php

namespace App\Ecommerce\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => $this->price,
            'compare_price' => $this->compare_price,
            'cost' => $this->cost,
            'quantity' => $this->quantity,
            'track_inventory' => $this->track_inventory,
            'status' => $this->status,
            'published_at' => $this->published_at?->toIso8601String(),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'creator' => when($this->whenLoaded('creator'), fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'media' => when($this->whenLoaded('media'), fn () => MediaResource::collection($this->media)),
        ];
    }
}
