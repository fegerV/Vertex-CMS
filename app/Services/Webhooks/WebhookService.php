<?php

namespace App\Services\Webhooks;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WebhookService
{
    public function createWebhook(array $data)
    {
        return \App\Models\Webhook::create([
            'name' => $data['name'],
            'url' => $data['url'],
            'events' => $data['events'] ?? [],
            'secret' => Str::random(32),
            'is_active' => $data['is_active'] ?? true,
            'headers' => $data['headers'] ?? [],
            'retry_count' => $data['retry_count'] ?? 3,
            'timeout' => $data['timeout'] ?? 30,
        ]);
    }

    public function triggerWebhook(string $event, array $payload)
    {
        $webhooks = \App\Models\Webhook::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            dispatch(new \App\Jobs\ProcessWebhook($webhook, $event, $payload));
        }

        return count($webhooks);
    }

    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    public function sendWebhook(\App\Models\Webhook $webhook, string $event, array $payload): array
    {
        $timestamp = Carbon::now()->timestamp;
        $signature = hash_hmac('sha256', json_encode($payload) . $timestamp, $webhook->secret);

        $headers = array_merge([
            'Content-Type' => 'application/json',
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => $timestamp,
            'X-Webhook-Event' => $event,
        ], $webhook->headers ?? []);

        try {
            $response = Http::withHeaders($headers)
                ->timeout($webhook->timeout)
                ->post($webhook->url, [
                    'event' => $event,
                    'timestamp' => $timestamp,
                    'data' => $payload,
                ]);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'body' => $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getAvailableEvents(): array
    {
        return [
            'order.created' => 'Заказ создан',
            'order.updated' => 'Заказ обновлен',
            'order.completed' => 'Заказ завершен',
            'order.cancelled' => 'Заказ отменен',
            'product.created' => 'Товар создан',
            'product.updated' => 'Товар обновлен',
            'product.deleted' => 'Товар удален',
            'user.registered' => 'Пользователь зарегистрирован',
            'user.updated' => 'Пользователь обновлен',
            'payment.success' => 'Оплата успешна',
            'payment.failed' => 'Оплата не удалась',
        ];
    }
}
