<?php

namespace App\Ecommerce\Services\PaymentProviders;

use App\Ecommerce\Services\PaymentProviderInterface;
use Illuminate\Support\Facades\Http;

/**
 * Stripe Payment Provider (stub implementation)
 */
class StripeProvider implements PaymentProviderInterface
{
    protected string $apiKey;
    protected string $webhookSecret;

    public function __construct()
    {
        $this->apiKey = config('services.stripe.api_key', '');
        $this->webhookSecret = config('services.stripe.webhook_secret', '');
    }

    public function getName(): string
    {
        return 'stripe';
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function initiatePayment(array $orderData): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'error' => 'Stripe is not configured',
            ];
        }

        try {
            // Create Payment Intent via Stripe API
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => (int) ($orderData['amount'] * 100), // Convert to cents
                'currency' => $orderData['currency'] ?? 'usd',
                'payment_method_types[]' => 'card',
                'metadata' => [
                    'order_id' => $orderData['order_id'],
                    'customer_email' => $orderData['customer_email'] ?? '',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'provider' => 'stripe',
                    'payment_intent' => $data['id'],
                    'client_secret' => $data['client_secret'],
                    'amount' => $data['amount'] / 100,
                    'currency' => $data['currency'],
                    'status' => $data['status'],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Payment initiation failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(string $paymentId): array
    {
        if (!$this->isAvailable()) {
            return ['success' => false, 'error' => 'Stripe is not configured'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("https://api.stripe.com/v1/payment_intents/{$paymentId}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'provider' => 'stripe',
                    'payment_intent' => $data['id'],
                    'status' => $data['status'],
                    'amount' => $data['amount'] / 100,
                    'currency' => $data['currency'],
                    'paid' => $data['paid'] ?? false,
                ];
            }

            return [
                'success' => false,
                'error' => 'Payment verification failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function processWebhook(array $payload): array
    {
        // Stub implementation - in production, verify webhook signature
        $eventType = $payload['type'] ?? '';

        return [
            'success' => true,
            'event_type' => $eventType,
            'data' => $payload['data'] ?? [],
        ];
    }

    public function refund(string $paymentId, float $amount): array
    {
        if (!$this->isAvailable()) {
            return ['success' => false, 'error' => 'Stripe is not configured'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->post('https://api.stripe.com/v1/refunds', [
                'payment_intent' => $paymentId,
                'amount' => (int) ($amount * 100),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'refund_id' => $data['id'],
                    'amount' => $data['amount'] / 100,
                    'status' => $data['status'],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Refund failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
