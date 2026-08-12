<?php

namespace App\Vertex\Security\Modules\Cloudflare;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudflareService
{
    public function purgeEverything(): array
    {
        return $this->purge(['purge_everything' => true]);
    }

    public function purgeUrls(array $urls): array
    {
        return $this->purge(['files' => array_values(array_unique($urls))]);
    }

    public function zone(): array
    {
        return $this->request()->get($this->url('zones/'.$this->zoneId()))->throw()->json();
    }

    private function purge(array $payload): array
    {
        return $this->request()->post($this->url('zones/'.$this->zoneId().'/purge_cache'), $payload)->throw()->json();
    }

    private function request(): PendingRequest
    {
        $token = (string) config('security.cloudflare.api_token');
        if ($token === '') {
            throw new RuntimeException('Cloudflare API token is not configured.');
        }

        return Http::withToken($token)->acceptJson()->timeout((int) config('security.cloudflare.timeout', 10));
    }

    private function zoneId(): string
    {
        $zone = (string) config('security.cloudflare.zone_id');
        if ($zone === '') {
            throw new RuntimeException('Cloudflare zone ID is not configured.');
        }

        return $zone;
    }

    private function url(string $path): string
    {
        return rtrim((string) config('security.cloudflare.api_url'), '/').'/'.$path;
    }
}
