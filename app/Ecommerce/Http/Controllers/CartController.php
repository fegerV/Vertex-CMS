<?php

namespace App\Ecommerce\Http\Controllers;

use App\Ecommerce\Services\CartService;
use App\Ecommerce\Services\OrderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();
        $cartItems = $this->cartService->getCart($sessionId);
        $totals = $this->cartService->getTotals($cartItems);

        return view('admin.ecommerce.cart.index', compact('cartItems', 'totals'));
    }

    public function checkout()
    {
        return view('admin.ecommerce.cart.checkout');
    }
}
