<?php

namespace App\Vertex\Security\Modules\Hibp;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HibpService
{
    public function occurrenceCount(string $password): int
    {
        $hash = strtoupper(sha1($password));
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);
        $body = Cache::remember("vertex-security:hibp:{$prefix}", now()->addDay(), fn () => Http::accept('text/plain')->withHeaders(['Add-Padding' => 'true'])
            ->timeout((int) config('security.hibp.timeout', 5))
            ->get(rtrim((string) config('security.hibp.endpoint'), '/').'/'.$prefix)
            ->throw()->body()
        );

        foreach (preg_split('/\r\n|\r|\n/', $body) ?: [] as $line) {
            [$candidate, $count] = array_pad(explode(':', trim($line), 2), 2, 0);
            if (hash_equals($suffix, strtoupper($candidate))) {
                return (int) $count;
            }
        }

        return 0;
    }

    public function isCompromised(string $password): bool
    {
        return $this->occurrenceCount($password) >= (int) config('security.hibp.minimum_occurrences', 1);
    }
}
