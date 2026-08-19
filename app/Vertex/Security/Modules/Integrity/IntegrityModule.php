<?php

namespace App\Vertex\Security\Modules\Integrity;

use Illuminate\Contracts\Foundation\Application;

class IntegrityModule
{
    public function register(Application $app): void
    {
        $app->singleton(IntegrityService::class, fn () => new IntegrityService());
        $app->singleton(self::class, fn () => $this);
    }
}
