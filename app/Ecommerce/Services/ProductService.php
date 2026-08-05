<?php

namespace App\Ecommerce\Services;

use App\Models\User;
use App\System\Services\ActivityLogService;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public const STATUSES = ['draft', 'active', 'archived'];

    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function create(array $payload, User $user): \App\Ecommerce\Models\Product
    {
        $payload = $this->preparePayload($payload);

        $product = \App\Ecommerce\Models\Product::query()->create([
            ...$this->productAttributes($payload),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->activityLog->record('products.create', 'product', $product->id, "Product \"{$product->name}\" created.");

        return $product;
    }

    public function update(\App\Ecommerce\Models\Product $product, array $payload, User $user): \App\Ecommerce\Models\Product
    {
        $payload = $this->preparePayload($payload, $product);

        $product->forceFill([
            ...$this->productAttributes($payload),
            'updated_by' => $user->id,
        ])->save();

        $this->activityLog->record('products.edit', 'product', $product->id, "Product \"{$product->name}\" updated.");

        return $product;
    }

    public function delete(\App\Ecommerce\Models\Product $product, User $user): void
    {
        $product->forceFill(['updated_by' => $user->id])->save();
        $product->delete();

        $this->activityLog->record('products.delete', 'product', $product->id, "Product \"{$product->name}\" deleted.");
    }

    private function preparePayload(array $payload, ?\App\Ecommerce\Models\Product $product = null): array
    {
        $status = $payload['status'] ?? 'draft';
        $sku = trim($payload['sku'] ?? '');
        
        if ($sku !== '' && $this->skuExists($sku, $product?->id)) {
            throw ValidationException::withMessages([
                'sku' => 'SKU already exists.',
            ]);
        }

        return [
            'name' => $payload['name'],
            'slug' => $payload['slug'] ?? \Illuminate\Support\Str::slug($payload['name']),
            'sku' => $sku ?: null,
            'description' => $payload['description'] ?? null,
            'price' => (float) ($payload['price'] ?? 0),
            'compare_price' => (float) ($payload['compare_price'] ?? 0),
            'cost' => (float) ($payload['cost'] ?? 0),
            'quantity' => (int) ($payload['quantity'] ?? 0),
            'track_inventory' => (bool) ($payload['track_inventory'] ?? false),
            'status' => $status,
            'published_at' => $status === 'active' ? ($product?->published_at ?? now()) : null,
            'meta_title' => $payload['meta_title'] ?? null,
            'meta_description' => $payload['meta_description'] ?? null,
            'meta_keywords' => $payload['meta_keywords'] ?? null,
        ];
    }

    private function skuExists(string $sku, ?int $ignoreId = null): bool
    {
        return \App\Ecommerce\Models\Product::query()
            ->where('sku', $sku)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    private function productAttributes(array $payload): array
    {
        return Arr::only($payload, [
            'name',
            'slug',
            'sku',
            'description',
            'price',
            'compare_price',
            'cost',
            'quantity',
            'track_inventory',
            'status',
            'published_at',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ]);
    }
}
