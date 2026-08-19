<?php

namespace App\Ecommerce\Services;

use App\Ecommerce\Models\CartItem;
use App\Ecommerce\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getCart(?string $sessionId = null): Collection
    {
        if ($sessionId === null) {
            return collect(session()->get('cart', []));
        }

        return collect(CartItem::query()
            ->where('session_id', $sessionId)
            ->with('product')
            ->get());
    }

    public function addToCart(Product $product, int $quantity = 1, ?string $sessionId = null): Collection
    {
        if ($quantity < 1 || $product->status !== 'active' || $product->published_at === null || $product->published_at->isFuture()) {
            throw ValidationException::withMessages(['product_id' => 'This product is not available.']);
        }

        if ($sessionId === null) {
            $sessionId = session()->getId();
        }

        $cartItem = CartItem::query()
            ->where('session_id', $sessionId)
            ->where('product_id', $product->id)
            ->first();

        $newQuantity = ($cartItem?->quantity ?? 0) + $quantity;
        $this->ensureStockIsAvailable($product, $newQuantity);

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            CartItem::query()->create([
                'session_id' => $sessionId,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        return $this->getCart($sessionId);
    }

    public function updateQuantity(int $cartItemId, int $quantity, ?string $sessionId = null): Collection
    {
        if ($sessionId === null) {
            $sessionId = session()->getId();
        }

        $cartItem = CartItem::query()
            ->where('id', $cartItemId)
            ->where('session_id', $sessionId)
            ->firstOrFail();

        if ($quantity <= 0) {
            $cartItem->delete();
        } else {
            $this->ensureStockIsAvailable($cartItem->product, $quantity);
            $cartItem->update(['quantity' => $quantity]);
        }

        return $this->getCart($sessionId);
    }

    public function removeFromCart(int $cartItemId, ?string $sessionId = null): Collection
    {
        if ($sessionId === null) {
            $sessionId = session()->getId();
        }

        CartItem::query()
            ->where('id', $cartItemId)
            ->where('session_id', $sessionId)
            ->delete();

        return $this->getCart($sessionId);
    }

    public function clearCart(?string $sessionId = null): void
    {
        if ($sessionId === null) {
            $sessionId = session()->getId();
        }

        CartItem::query()
            ->where('session_id', $sessionId)
            ->delete();
    }

    public function getTotals(Collection $cartItems): array
    {
        $subtotal = 0;
        $tax = 0;
        $discount = 0;

        foreach ($cartItems as $item) {
            $price = $item->product?->price ?? 0;
            $subtotal += $price * $item->quantity;
        }

        $taxRate = config('ecommerce.tax_rate', 0);
        $tax = $subtotal * ($taxRate / 100);

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $subtotal + $tax - $discount,
        ];
    }

    private function ensureStockIsAvailable(Product $product, int $quantity): void
    {
        if ($product->track_inventory && $quantity > $product->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity is not available.',
            ]);
        }
    }
}
