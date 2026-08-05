<?php

namespace App\Ecommerce\Http\Controllers;

use App\Ecommerce\Models\Order;
use App\Ecommerce\Services\OrderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Order::class);

        $query = Order::query()->with(['user', 'items']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($qi) use ($search) {
                        $qi->where('product_name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.ecommerce.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        $order->load(['user', 'items.product']);

        return view('admin.ecommerce.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        Gate::authorize('update', $order);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ]);

        $order = $this->orderService->updateStatus($order, $validated['status'], $request->user());

        return redirect()->back()->with('success', 'Order status updated.');
    }

    public function updatePayment(Request $request, Order $order)
    {
        Gate::authorize('update', $order);

        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $order = $this->orderService->updatePaymentStatus(
            $order,
            $validated['payment_status'],
            $validated['transaction_id'] ?? null
        );

        return redirect()->back()->with('success', 'Payment status updated.');
    }

    public function cancel(Order $order, Request $request)
    {
        Gate::authorize('update', $order);

        $this->orderService->cancel($order, $request->user());

        return redirect()->back()->with('success', 'Order cancelled.');
    }

    public function refund(Order $order, Request $request)
    {
        Gate::authorize('update', $order);

        $this->orderService->refund($order, $request->user());

        return redirect()->back()->with('success', 'Order refunded.');
    }
}
