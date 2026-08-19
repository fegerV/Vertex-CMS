<?php

namespace App\Vertex\Security\Modules\Alerts;

use Illuminate\Contracts\Foundation\Application;

class AlertsModule
{
    public function register(Application $app): void
    {
        $app->singleton(AlertsService::class, fn (Application $app) => new AlertsService(
            $app->make(\App\System\Services\SystemInfoService::class),
            $app->make(\App\Vertex\Security\Modules\Integrity\IntegrityService::class),
            $app->make(\App\Vertex\Security\Modules\Scanner\ScannerService::class),
        ));
        $app->singleton(self::class, fn () => $this);
    }
}
