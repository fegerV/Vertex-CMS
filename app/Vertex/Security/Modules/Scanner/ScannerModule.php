<?php

namespace App\Vertex\Security\Modules\Scanner;

use Illuminate\Contracts\Foundation\Application;

class ScannerModule
{
    public function register(Application $app): void
    {
        $app->singleton(ScannerService::class, fn () => new ScannerService());
        $app->singleton(self::class, fn () => $this);
    }
}
