<?php

namespace Tests\Feature\Ecommerce;

use App\Ecommerce\Models\Product;
use App\Ecommerce\Services\CartService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_product_can_be_added_and_totals_are_calculated(): void
    {
        $product = $this->product(['price' => 1000, 'quantity' => 10]);

        $cart = app(CartService::class)->addToCart($product, 2, 'cart-session');

        $this->assertDatabaseHas('ecommerce_cart_items', [
            'session_id' => 'cart-session',
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->assertSame(2000.0, app(CartService::class)->getTotals($cart)['total']);
    }

    public function test_cart_rejects_quantity_above_tracked_stock(): void
    {
        $product = $this->product(['quantity' => 2]);

        $this->expectException(ValidationException::class);

        app(CartService::class)->addToCart($product, 3, 'cart-session');
    }

    public function test_unpublished_product_cannot_be_added(): void
    {
        $product = $this->product(['status' => 'draft', 'published_at' => null]);

        $this->expectException(ValidationException::class);

        app(CartService::class)->addToCart($product, 1, 'cart-session');
    }

    private function product(array $attributes = []): Product
    {
        $user = User::query()->create([
            'name' => 'Store owner',
            'email' => uniqid().'@example.test',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        return Product::query()->create(array_merge([
            'name' => 'Test product',
            'slug' => 'test-product-'.uniqid(),
            'price' => 100,
            'quantity' => 5,
            'track_inventory' => true,
            'status' => 'active',
            'published_at' => now(),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ], $attributes));
    }
}
