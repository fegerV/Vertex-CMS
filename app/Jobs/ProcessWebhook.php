<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Services\Webhooks\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Webhook $webhook,
        public string $event,
        public array $payload
    ) {}

    public function handle(WebhookService $webhookService): void
    {
        $result = $webhookService->sendWebhook($this->webhook, $this->event, $this->payload);

        \App\Models\WebhookLog::create([
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'payload' => $this->payload,
            'status_code' => $result['status_code'] ?? null,
            'response' => $result['body'] ?? $result['error'] ?? null,
            'success' => $result['success'] ?? false,
            'attempt' => $this->attempts(),
            'delivered_at' => ($result['success'] ?? false) ? now() : null,
        ]);

        if (!($result['success'] ?? false) && $this->attempts() < $this->webhook->retry_count) {
            $this->release(60 * $this->attempts());
        }
    }

    public function failed(\Throwable $exception): void
    {
        \App\Models\WebhookLog::create([
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'payload' => $this->payload,
            'response' => $exception->getMessage(),
            'success' => false,
            'attempt' => $this->attempts(),
        ]);
    }
}
