<?php

namespace App\Providers;

use App\Content\Services\PageService;
use App\Core\Services\InstallationService;
use App\Core\Services\SettingsService;
use App\Core\Support\RouteRegistrar;
use App\System\Services\ActivityLogService;
use App\System\Services\DatabaseConnectionService;
use App\System\Services\EnvironmentFileService;
use App\System\Services\InstallerRunner;
use Illuminate\Support\ServiceProvider;

class VertexServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(InstallationService::class);
        $this->app->singleton(DatabaseConnectionService::class);
        $this->app->singleton(EnvironmentFileService::class);
        $this->app->singleton(InstallerRunner::class);
        $this->app->singleton(ActivityLogService::class);
        $this->app->singleton(PageService::class);
    }

    public function boot(RouteRegistrar $routes): void
    {
        $routes->register();
    }
}
