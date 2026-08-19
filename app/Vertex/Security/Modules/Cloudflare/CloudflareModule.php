<?php

namespace App\Vertex\Security\Modules\Cloudflare;

use Illuminate\Contracts\Foundation\Application;

class CloudflareModule
{
    public function register(Application $app): void
    {
        $app->singleton(CloudflareService::class);
        $app->singleton(CloudflareRequest::class);
        $app->singleton(self::class, fn () => $this);
    }
}
