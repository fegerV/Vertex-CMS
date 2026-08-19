<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookLog;
use App\Services\Webhooks\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function __construct(
        public Webhook $webhook,
        public string $event,
        public array $payload
    ) {}

    public function handle(WebhookService $webhookService): void
    {
        $result = $webhookService->sendWebhook($this->webhook, $this->event, $this->payload);

        WebhookLog::create([
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'payload' => $this->payload,
            'status_code' => $result['status_code'] ?? null,
            'response' => $result['body'] ?? $result['error'] ?? null,
            'success' => $result['success'] ?? false,
            'attempt' => $this->attempts(),
            'delivered_at' => ($result['success'] ?? false) ? now() : null,
        ]);

        if (! ($result['success'] ?? false) && $this->attempts() < min($this->tries, $this->webhook->retry_count)) {
            throw new \RuntimeException($result['error'] ?? 'Webhook delivery failed.');
        }
    }

    public function failed(\Throwable $exception): void
    {
        WebhookLog::create([
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'payload' => $this->payload,
            'response' => $exception->getMessage(),
            'success' => false,
            'attempt' => $this->attempts(),
        ]);
    }
}
