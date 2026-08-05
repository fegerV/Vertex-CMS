<?php

namespace App\Ecommerce\Services;

use App\Ecommerce\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    public function getCart(?string $sessionId = null): Collection
    {
        if ($sessionId === null) {
            return collect(session()->get('cart', []));
        }

        return collect(\App\Ecommerce\Models\CartItem::query()
            ->where('session_id', $sessionId)
            ->with('product')
            ->get());
    }

    public function addToCart(Product $product, int $quantity = 1, ?string $sessionId = null): Collection
    {
        if ($sessionId === null) {
            $sessionId = session()->getId();
        }

        $cartItem = \App\Ecommerce\Models\CartItem::query()
            ->where('session_id', $sessionId)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            \App\Ecommerce\Models\CartItem::query()->create([
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

        $cartItem = \App\Ecommerce\Models\CartItem::query()
            ->where('id', $cartItemId)
            ->where('session_id', $sessionId)
            ->firstOrFail();

        if ($quantity <= 0) {
            $cartItem->delete();
        } else {
            $cartItem->update(['quantity' => $quantity]);
        }

        return $this->getCart($sessionId);
    }

    public function removeFromCart(int $cartItemId, ?string $sessionId = null): Collection
    {
        if ($sessionId === null) {
            $sessionId = session()->getId();
        }

        \App\Ecommerce\Models\CartItem::query()
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

        \App\Ecommerce\Models\CartItem::query()
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
}
