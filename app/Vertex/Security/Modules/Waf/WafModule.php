<?php

namespace App\Vertex\Security\Modules\Waf;

use Illuminate\Contracts\Foundation\Application;

class WafModule
{
    public function register(Application $app): void
    {
        $app->singleton(self::class, fn () => $this);
    }
}
