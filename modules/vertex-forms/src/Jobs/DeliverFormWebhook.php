<?php

namespace Vertex\Forms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;
use Vertex\Forms\Models\FormWebhookDelivery;

class DeliverFormWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public FormWebhookDelivery $delivery)
    {
        $this->onQueue('notifications');
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $delivery = $this->delivery->fresh();
        if ($delivery === null || $delivery->status === 'delivered') {
            return;
        }

        $attempt = max(1, $this->attempts());
        $timestamp = now()->timestamp;
        $body = json_encode([
            'event' => 'form.submitted',
            'timestamp' => $timestamp,
            'data' => $delivery->payload,
        ], JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $body, (string) $delivery->secret);

        $delivery->update(['status' => 'processing', 'attempts' => $attempt]);

        try {
            $response = Http::withHeaders(array_merge([
                'Content-Type' => 'application/json',
                'X-Vertex-Event' => 'form.submitted',
                'X-Vertex-Timestamp' => (string) $timestamp,
                'X-Vertex-Signature' => $signature,
            ], $delivery->headers ?? []))
                ->timeout((int) config('forms.webhook_timeout', 10))
                ->withBody($body, 'application/json')
                ->post($delivery->url);
        } catch (ConnectionException $exception) {
            $delivery->update([
                'status' => 'retrying',
                'response' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $delivery->update([
            'status_code' => $response->status(),
            'response' => mb_substr($response->body(), 0, 10000),
        ]);

        if (! $response->successful()) {
            $delivery->update(['status' => 'retrying']);
            throw new RuntimeException("Webhook returned HTTP {$response->status()}");
        }

        $delivery->update(['status' => 'delivered', 'delivered_at' => now()]);
    }

    public function failed(Throwable $exception): void
    {
        $this->delivery->fresh()?->update([
            'status' => 'failed',
            'failed_at' => now(),
            'response' => mb_substr($exception->getMessage(), 0, 10000),
        ]);
    }
}
