<?php

namespace App\Ecommerce\Services;

use App\Ecommerce\Models\Order;
use App\Ecommerce\Models\OrderItem;
use App\Models\User;
use App\System\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function createFromCart(array $checkoutData, ?User $user = null, ?string $sessionId = null): Order
    {
        return DB::transaction(function () use ($checkoutData, $user, $sessionId) {
            $cartService = app(CartService::class);
            $cartItems = $cartService->getCart($sessionId);

            if ($cartItems->isEmpty()) {
                throw new \RuntimeException('Cart is empty.');
            }

            $totals = $cartService->getTotals($cartItems);

            $order = Order::query()->create([
                'user_id' => $user?->id,
                'session_id' => $sessionId,
                'status' => 'pending',
                'customer_email' => $checkoutData['email'] ?? ($user?->email ?? null),
                'customer_name' => $checkoutData['name'] ?? ($user?->name ?? 'Guest'),
                'shipping_address_json' => $checkoutData['shipping_address'] ?? [],
                'billing_address_json' => $checkoutData['billing_address'] ?? $checkoutData['shipping_address'] ?? [],
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'discount' => $totals['discount'],
                'total' => $totals['total'],
                'payment_status' => 'pending',
                'payment_method' => $checkoutData['payment_method'] ?? null,
                'notes' => $checkoutData['notes'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? 'Unknown',
                    'sku' => $item->product?->sku ?? null,
                    'quantity' => $item->quantity,
                    'price' => $item->product?->price ?? 0,
                    'total' => ($item->product?->price ?? 0) * $item->quantity,
                ]);

                if ($item->product?->track_inventory) {
                    $item->product->decrement('quantity', $item->quantity);
                }
            }

            $cartService->clearCart($sessionId);

            $this->activityLog->record('orders.create', 'order', $order->id, "Order #{$order->id} created.");

            return $order;
        });
    }

    public function updateStatus(Order $order, string $status, ?User $user = null): Order
    {
        if (! in_array($status, self::STATUSES)) {
            throw new \InvalidArgumentException("Invalid order status: {$status}");
        }

        $order->update(['status' => $status]);

        $this->activityLog->record(
            'orders.status_change',
            'order',
            $order->id,
            "Order #{$order->id} status changed to {$status}.",
            $user?->id
        );

        return $order->fresh();
    }

    public function updatePaymentStatus(Order $order, string $paymentStatus, ?string $transactionId = null): Order
    {
        $order->update([
            'payment_status' => $paymentStatus,
            'payment_transaction_id' => $transactionId,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
        ]);

        $this->activityLog->record('orders.payment', 'order', $order->id, "Order #{$order->id} payment status: {$paymentStatus}.");

        return $order->fresh();
    }

    public function cancel(Order $order, ?User $user = null): Order
    {
        return $this->updateStatus($order, 'cancelled', $user);
    }

    public function refund(Order $order, ?User $user = null): Order
    {
        return $this->updateStatus($order, 'refunded', $user);
    }
}
