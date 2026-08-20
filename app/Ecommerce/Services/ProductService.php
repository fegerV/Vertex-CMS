<?php

namespace App\Ecommerce\Services;

use App\Models\User;
use App\System\Services\ActivityLogService;
use App\Services\Webhooks\WebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;
use App\Ecommerce\Models\Product;

class ProductService
{
    public const STATUSES = ['draft', 'active', 'archived'];

    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly WebhookService $webhooks,
    ) {
    }

    public function create(array $payload, User $user): Product
    {
        $payload = $this->preparePayload($payload);

        $product = Product::query()->create([
            ...$this->productAttributes($payload),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->activityLog->record('products.create', 'product', $product->id, "Product \"{$product->name}\" created.");
        DB::afterCommit(fn () => $this->webhooks->triggerWebhook('product.created', $product->toArray()));

        return $product;
    }

    public function update(Product $product, array $payload, User $user): Product
    {
        $payload = $this->preparePayload($payload, $product);

        $product->forceFill([
            ...$this->productAttributes($payload),
            'updated_by' => $user->id,
        ])->save();

        $this->activityLog->record('products.edit', 'product', $product->id, "Product \"{$product->name}\" updated.");
        DB::afterCommit(fn () => $this->webhooks->triggerWebhook('product.updated', $product->fresh()->toArray()));

        return $product;
    }

    public function delete(Product $product, User $user): void
    {
        $product->forceFill(['updated_by' => $user->id])->save();
        $payload = $product->toArray();
        $product->delete();
        DB::afterCommit(fn () => $this->webhooks->triggerWebhook('product.deleted', $payload));

        $this->activityLog->record('products.delete', 'product', $product->id, "Product \"{$product->name}\" deleted.");
    }

    /**
     * Get public products for catalog
     */
    public function getPublicProducts(array $filters = []): Collection
    {
        $query = Product::query()
            ->where('status', 'active')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        $sortField = in_array($filters['sort'] ?? '', ['name', 'price', 'created_at']) ? $filters['sort'] : 'created_at';
        $sortOrder = strtolower($filters['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        
        $query->orderBy($sortField, $sortOrder);

        return $query->with('media')->get();
    }

    /**
     * Get single public product by slug
     */
    public function getPublicProductBySlug(string $slug): ?Product
    {
        return Product::query()
            ->where('status', 'active')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('slug', $slug)
            ->with('media')
            ->first();
    }

    /**
     * Get related products
     */
    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        return Product::query()
            ->where('status', 'active')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereKeyNot($product->id)
            ->limit($limit)
            ->get();
    }

    /**
     * Get product categories (placeholder for taxonomy integration)
     */
    public function getCategories(): Collection
    {
        // Placeholder - integrate with taxonomy system
        return collect([]);
    }

    /**
     * Get available filters (placeholder for dynamic filters)
     */
    public function getAvailableFilters(): array
    {
        return [
            'price_range' => [
                'min' => Product::query()->min('price') ?? 0,
                'max' => Product::query()->max('price') ?? 1000,
            ],
        ];
    }

    private function preparePayload(array $payload, ?Product $product = null): array
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
        return Product::query()
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
