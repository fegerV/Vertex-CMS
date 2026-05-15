<?php

namespace App\Vertex\Security\Modules\GeoIp;

use Illuminate\Contracts\Foundation\Application;

class GeoIpModule
{
    public function register(Application $app): void
    {
        $app->singleton(self::class, fn () => $this);
    }
}
