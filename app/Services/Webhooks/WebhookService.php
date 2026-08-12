<?php

namespace App\Services\Webhooks;

use App\Jobs\ProcessWebhook;
use App\Models\Webhook;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookService
{
    public function createWebhook(array $data)
    {
        $this->assertSafeUrl($data['url']);

        return Webhook::create([
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
        $webhooks = Webhook::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            dispatch(new ProcessWebhook($webhook, $event, $payload));
        }

        return count($webhooks);
    }

    public function verifySignature(string $payload, string $signature, string $secret, int|string|null $timestamp = null): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload.($timestamp ?? ''), $secret);

        return hash_equals($expectedSignature, $signature);
    }

    public function sendWebhook(Webhook $webhook, string $event, array $payload): array
    {
        $timestamp = Carbon::now()->timestamp;
        $this->assertSafeUrl($webhook->url);
        $body = [
            'event' => $event,
            'timestamp' => $timestamp,
            'data' => $payload,
        ];
        $encodedBody = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $encodedBody.$timestamp, $webhook->secret);

        // System headers deliberately win over user-supplied headers.
        $headers = array_merge($webhook->headers ?? [], [
            'Content-Type' => 'application/json',
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => $timestamp,
            'X-Webhook-Event' => $event,
        ]);

        try {
            $response = Http::withHeaders($headers)
                ->timeout($webhook->timeout)
                ->withBody($encodedBody, 'application/json')
                ->post($webhook->url);

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

    private function assertSafeUrl(string $url): void
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https' || empty($parts['host'])) {
            throw new \InvalidArgumentException('Webhook URL must be an absolute HTTPS URL.');
        }

        $host = strtolower(rtrim($parts['host'], '.'));
        if ($host === 'localhost' || str_ends_with($host, '.localhost')) {
            throw new \InvalidArgumentException('Webhook URL cannot target a local address.');
        }

        $addresses = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : array_values(array_unique(array_merge(
                gethostbynamel($host) ?: [],
                array_column(dns_get_record($host, DNS_AAAA), 'ipv6'),
            )));

        if ($addresses === []) {
            throw new \InvalidArgumentException('Webhook host could not be resolved.');
        }

        foreach ($addresses as $address) {
            if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new \InvalidArgumentException('Webhook URL cannot target private or reserved networks.');
            }
        }
    }
}
