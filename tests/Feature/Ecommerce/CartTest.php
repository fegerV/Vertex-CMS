<?php

namespace Tests\Feature\Ecommerce;

use App\Models\User;
use App\Modules\Ecommerce\Models\Product;
use App\Modules\Ecommerce\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 1000,
            'stock' => 10
        ]);
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 2
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);
    }

    public function test_user_cannot_add_more_than_stock(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 15 // More than stock (10)
            ]);

        $response->assertStatus(422);
    }

    public function test_user_can_update_cart_quantity(): void
    {
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($this->user)
            ->putJson('/api/cart/update', [
                'product_id' => $this->product->id,
                'quantity' => 5
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5
        ]);
    }

    public function test_user_can_clear_cart(): void
    {
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 3
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/cart/clear');

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->user->id
        ]);
    }
}
