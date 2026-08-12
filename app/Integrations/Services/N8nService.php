<?php

namespace App\Integrations\Services;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class N8nService
{
    public function trigger(string $workflow, array $payload, ?string $idempotencyKey = null): array
    {
        $url = (string) config('platform-modules.n8n.webhook_url');
        if (! str_starts_with($url, 'https://')) {
            throw new InvalidArgumentException('n8n webhook must use HTTPS.');
        }
        $timestamp = now()->timestamp;
        $body = ['workflow' => $workflow, 'timestamp' => $timestamp, 'data' => $payload];
        $json = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $secret = (string) config('platform-modules.n8n.secret');
        $response = Http::withHeaders([
            'X-Vertex-Signature' => hash_hmac('sha256', $json, $secret),
            'X-Vertex-Timestamp' => $timestamp,
            'Idempotency-Key' => $idempotencyKey ?: hash('sha256', $workflow.$json),
        ])->withBody($json, 'application/json')->timeout((int) config('platform-modules.n8n.timeout', 10))->post($url)->throw();

        return $response->json() ?? ['accepted' => true];
    }
}
