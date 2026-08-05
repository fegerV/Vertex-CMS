<?php

namespace App\Ecommerce\Http\Controllers;

use App\Ecommerce\Models\Product;
use App\Ecommerce\Services\CartService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartApiController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $sessionId = $request->session()->getId();
        $cartItems = $this->cartService->getCart($sessionId);
        $totals = $this->cartService->getTotals($cartItems);

        return response()->json([
            'items' => $cartItems->load('product'),
            'totals' => $totals,
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:ecommerce_products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $sessionId = $request->session()->getId();

        $cart = $this->cartService->addToCart($product, $validated['quantity'], $sessionId);
        $totals = $this->cartService->getTotals($cart);

        return response()->json([
            'message' => 'Product added to cart.',
            'items' => $cart->load('product'),
            'totals' => $totals,
        ]);
    }

    public function update(Request $request, int $cartItemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $sessionId = $request->session()->getId();
        $cart = $this->cartService->updateQuantity($cartItemId, $validated['quantity'], $sessionId);
        $totals = $this->cartService->getTotals($cart);

        return response()->json([
            'message' => 'Cart updated.',
            'items' => $cart->load('product'),
            'totals' => $totals,
        ]);
    }

    public function remove(Request $request, int $cartItemId): JsonResponse
    {
        $sessionId = $request->session()->getId();
        $cart = $this->cartService->removeFromCart($cartItemId, $sessionId);
        $totals = $this->cartService->getTotals($cart);

        return response()->json([
            'message' => 'Item removed from cart.',
            'items' => $cart->load('product'),
            'totals' => $totals,
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $sessionId = $request->session()->getId();
        $this->cartService->clearCart($sessionId);

        return response()->json([
            'message' => 'Cart cleared.',
            'items' => [],
            'totals' => ['subtotal' => 0, 'tax' => 0, 'discount' => 0, 'total' => 0],
        ]);
    }
}
