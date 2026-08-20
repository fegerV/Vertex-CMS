<?php

namespace App\Ecommerce\Services;

/**
 * Payment Provider Interface
 */
interface PaymentProviderInterface
{
    /**
     * Get provider name
     */
    public function getName(): string;

    /**
     * Check if provider is configured and available
     */
    public function isAvailable(): bool;

    /**
     * Initiate payment
     * 
     * @param array $orderData Order information
     * @return array Payment initiation result with redirect URL or payment intent
     */
    public function initiatePayment(array $orderData): array;

    /**
     * Verify payment status
     * 
     * @param string $paymentId Payment identifier from provider
     * @return array Payment verification result
     */
    public function verifyPayment(string $paymentId): array;

    /**
     * Process webhook notification from payment provider
     * 
     * @param array $payload Webhook payload
     * @return array Processing result
     */
    public function processWebhook(array $payload): array;

    /**
     * Refund a payment
     * 
     * @param string $paymentId Payment identifier
     * @param float $amount Refund amount
     * @return array Refund result
     */
    public function refund(string $paymentId, float $amount): array;
}
