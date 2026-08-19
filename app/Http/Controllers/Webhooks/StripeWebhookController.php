<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Ecommerce\Models\Order;
use App\Ecommerce\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controller for handling inbound Stripe webhooks
 * 
 * SECURITY: This endpoint verifies webhook signatures from Stripe
 * before processing any payment status changes.
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Handle Stripe webhook events
     * 
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        
        if (!$signature) {
            Log::warning('Stripe webhook received without signature');
            return response()->json(['error' => 'Missing signature'], 400);
        }

        $webhookSecret = config('services.stripe.webhook_secret');
        
        if (!$webhookSecret) {
            Log::error('Stripe webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        // FIX C03: Verify webhook signature from Stripe
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::warning('Invalid Stripe webhook payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
                'signature' => substr($signature, 0, 20) . '...'
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Process the event based on type
        $data = $event['data']['object'];
        
        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($data);
                break;
                
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($data);
                break;
                
            case 'charge.refunded':
                $this->handleChargeRefunded($data);
                break;
                
            default:
                Log::info('Unhandled Stripe event type', ['type' => $event['type']]);
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentSucceeded(array $data): void
    {
        $orderId = $data['metadata']['order_id'] ?? null;
        $transactionId = $data['id'] ?? null;
        
        if (!$orderId || !$transactionId) {
            Log::warning('Stripe payment succeeded without order_id or transaction_id');
            return;
        }

        $order = Order::find($orderId);
        
        if (!$order) {
            Log::error('Stripe webhook for non-existent order', ['order_id' => $orderId]);
            return;
        }

        // FIX C03: Verify amount matches order total to prevent price manipulation
        $stripeAmount = $data['amount'] / 100; // Stripe uses cents
        $orderTotal = $order->total;
        
        if (abs($stripeAmount - $orderTotal) > 0.01) {
            Log::critical('Stripe payment amount mismatch', [
                'order_id' => $orderId,
                'expected' => $orderTotal,
                'received' => $stripeAmount,
            ]);
            return;
        }

        try {
            $this->orderService->updatePaymentStatus(
                $order,
                'paid',
                $transactionId,
                verifiedSignature: 'stripe_webhook_verified'
            );
            
            Log::info('Order marked as paid via verified Stripe webhook', [
                'order_id' => $orderId,
                'transaction_id' => $transactionId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update payment status', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailed(array $data): void
    {
        $orderId = $data['metadata']['order_id'] ?? null;
        $transactionId = $data['id'] ?? null;
        
        if (!$orderId) {
            Log::warning('Stripe payment failed without order_id');
            return;
        }

        $order = Order::find($orderId);
        
        if (!$order) {
            return;
        }

        try {
            $this->orderService->updatePaymentStatus(
                $order,
                'failed',
                $transactionId,
                verifiedSignature: 'stripe_webhook_verified'
            );
            
            Log::info('Order marked as failed via verified Stripe webhook', [
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update payment status', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle refunded charge
     */
    private function handleChargeRefunded(array $data): void
    {
        Log::info('Stripe charge refunded', ['charge_id' => $data['id'] ?? null]);
    }
}
