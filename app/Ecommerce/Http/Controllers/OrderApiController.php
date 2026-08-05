<?php

namespace App\Ecommerce\Http\Controllers;

use App\Ecommerce\Models\Order;
use App\Ecommerce\Services\OrderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderApiController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
    }

    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'shipping_address' => 'required|array',
            'shipping_address.street' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.country' => 'required|string',
            'billing_address' => 'nullable|array',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $sessionId = $request->session()->getId();

        $order = $this->orderService->createFromCart($validated, $user, $sessionId);

        return response()->json([
            'message' => 'Order created successfully.',
            'order' => $order->load('items'),
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['items.product', 'user']);

        return response()->json([
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ]);

        $updatedOrder = $this->orderService->updateStatus($order, $validated['status'], $request->user());

        return response()->json([
            'message' => 'Order status updated.',
            'order' => $updatedOrder,
        ]);
    }

    public function cancel(Order $order, Request $request): JsonResponse
    {
        $cancelledOrder = $this->orderService->cancel($order, $request->user());

        return response()->json([
            'message' => 'Order cancelled.',
            'order' => $cancelledOrder,
        ]);
    }
}
