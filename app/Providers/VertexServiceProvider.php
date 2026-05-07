<?php

namespace App\Providers;

use App\Core\Services\InstallationService;
use App\Core\Services\SettingsService;
use App\Core\Support\RouteRegistrar;
use Illuminate\Support\ServiceProvider;

class VertexServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(InstallationService::class);
    }

    public function boot(RouteRegistrar $routes): void
    {
        $routes->register();
    }
}

