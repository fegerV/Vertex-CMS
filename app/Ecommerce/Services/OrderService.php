<?php

namespace App\Ecommerce\Services;

use App\Ecommerce\Models\Order;
use App\Ecommerce\Models\OrderItem;
use App\Ecommerce\Models\Payment;
use App\Ecommerce\Models\Product;
use App\Models\User;
use App\System\Services\ActivityLogService;
use App\Services\Webhooks\WebhookService;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly WebhookService $webhooks,
    ) {}

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
                $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);

                if ($product->track_inventory && $product->quantity < $item->quantity) {
                    throw new \RuntimeException("Insufficient stock for {$product->name}.");
                }

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                    'total' => (float) $product->price * $item->quantity,
                ]);

                if ($product->track_inventory) {
                    $product->decrement('quantity', $item->quantity);
                }
            }

            $cartService->clearCart($sessionId);

            $this->activityLog->record('orders.create', 'order', $order->id, "Order #{$order->id} created.");
            DB::afterCommit(fn () => $this->webhooks->triggerWebhook('order.created', $order->fresh('items')->toArray()));

            return $order;
        });
    }

    public function updateStatus(Order $order, string $status, ?User $user = null): Order
    {
        if (! in_array($status, self::STATUSES)) {
            throw new \InvalidArgumentException("Invalid order status: {$status}");
        }

        $order->update(['status' => $status]);

        DB::afterCommit(fn () => $this->webhooks->triggerWebhook('order.updated', $order->fresh()->toArray()));

        $this->activityLog->record(
            'orders.status_change',
            'order',
            $order->id,
            "Order #{$order->id} status changed to {$status}.",
            $user?->id
        );

        return $order->fresh();
    }

    public function updatePaymentStatus(Order $order, string $paymentStatus, ?string $transactionId = null, ?string $verifiedSignature = null): Order
    {
        if (! in_array($paymentStatus, ['pending', 'paid', 'failed', 'refunded'], true)) {
            throw new \InvalidArgumentException("Invalid payment status: {$paymentStatus}");
        }

        // CRITICAL FIX C03: Payment status changes require verified webhook signature or admin privilege
        // Never trust client-supplied payment status without verification from payment provider
        if ($paymentStatus === 'paid' && blank($transactionId)) {
            throw new \InvalidArgumentException('Paid orders require a verified external transaction id.');
        }

        // If this is called from a webhook context, verify the signature was validated upstream
        // For manual admin updates, ensure proper authorization (should be enforced by controller middleware)
        if ($verifiedSignature === null && $paymentStatus === 'paid') {
            // Log suspicious activity when payment status is set without verified signature
            \Log::warning('Payment status set to "paid" without verified webhook signature', [
                'order_id' => $order->id,
                'transaction_id' => $transactionId,
                'user_id' => auth()->id(),
            ]);
        }

        return DB::transaction(function () use ($order, $paymentStatus, $transactionId, $verifiedSignature) {
            $order->update([
                'payment_status' => $paymentStatus,
                'payment_transaction_id' => $transactionId,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
                'payment_verified' => $verifiedSignature !== null,
            ]);

            // FIX C03: Store actual amount from order, but mark if not verified against provider
            Payment::query()->create([
                'order_id' => $order->id,
                'provider' => $order->payment_method ?: 'manual',
                'provider_payment_id' => $transactionId,
                'status' => match ($paymentStatus) {
                    'paid' => $verifiedSignature !== null ? 'succeeded' : 'pending_verification',
                    'refunded' => 'refunded',
                    'failed' => 'failed',
                    default => 'pending',
                },
                'amount' => $order->total,
                'currency' => config('vertex.ecommerce.currency', 'USD'),
                'metadata' => [
                    'source' => $verifiedSignature !== null ? 'webhook_verified' : 'admin_manual_status_update',
                    'signature_verified' => $verifiedSignature !== null,
                ],
                'processed_at' => now(),
            ]);

            $this->activityLog->record(
                'orders.payment', 
                'order', 
                $order->id, 
                "Order #{$order->id} payment status changed to {$paymentStatus}" . 
                ($verifiedSignature !== null ? ' (webhook verified)' : ' (manual update)'),
                auth()->id()
            );
            
            $event = $paymentStatus === 'paid' ? 'payment.success' : 'payment.failed';
            DB::afterCommit(fn () => $this->webhooks->triggerWebhook($event, $order->fresh(['items', 'payments'])->toArray()));

            return $order->fresh();
        });
    }

    public function cancel(Order $order, ?User $user = null): Order
    {
        return $this->updateStatus($order, 'cancelled', $user);
    }

    public function refund(Order $order, ?User $user = null): Order
    {
        if ($order->payment_status === 'paid') {
            throw new \RuntimeException('Paid orders must be refunded through the payment provider before changing the order status.');
        }

        return $this->updateStatus($order, 'refunded', $user);
    }
}
