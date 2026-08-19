<?php

namespace App\Vertex\Security\Modules\Waf;

use Illuminate\Contracts\Foundation\Application;

class WafModule
{
    public function register(Application $app): void
    {
        $app->singleton(WafService::class);
        $app->singleton(self::class, fn () => $this);
    }
}
