<?php

namespace App\Marketplace\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MarketplaceCatalog
{
    public function modules(): array
    {
        return $this->fetch('modules');
    }

    public function themes(): array
    {
        return $this->fetch('themes');
    }

    public function verifyPackage(string $contents, string $signature): bool
    {
        $key = (string) config('platform-modules.marketplace.public_key');
        if ($key === '' || ! function_exists('sodium_crypto_sign_verify_detached')) {
            return false;
        }
        $decodedKey = base64_decode($key, true);
        $decodedSignature = base64_decode($signature, true);

        return $decodedKey !== false && $decodedSignature !== false
            && sodium_crypto_sign_verify_detached($decodedSignature, $contents, $decodedKey);
    }

    private function fetch(string $type): array
    {
        $url = rtrim((string) config('platform-modules.marketplace.catalog_url'), '/');
        if ($url === '' || ! str_starts_with($url, 'https://')) {
            throw new RuntimeException('A HTTPS marketplace catalog URL is required.');
        }
        $payload = Http::acceptJson()->timeout((int) config('platform-modules.marketplace.timeout', 10))->get("{$url}/{$type}")->throw()->json();

        return collect($payload['data'] ?? [])->filter(fn ($item) => is_array($item) && isset($item['id'], $item['version'], $item['download_url']))->values()->all();
    }
}
