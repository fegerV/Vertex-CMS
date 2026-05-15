<?php

namespace App\Vertex\Security\Modules\Hibp;

use Illuminate\Contracts\Foundation\Application;

class HibpModule
{
    public function register(Application $app): void
    {
        $app->singleton(self::class, fn () => $this);
    }
}
