<?php

namespace App\Providers;

use App\AI\Services\AiDraftService;
use App\AI\Services\AiProviderRegistry;
use App\Builder\Services\PageRenderer;
use App\Content\Services\PageService;
use App\Core\Services\InstallationService;
use App\Core\Services\SettingsService;
use App\Core\Support\RouteRegistrar;
use App\Media\Services\MediaService;
use App\Seo\Services\SeoMetaService;
use App\System\Services\ActivityLogService;
use App\System\Services\CacheService;
use App\System\Services\DatabaseConnectionService;
use App\System\Services\EnvironmentFileService;
use App\System\Services\InstallerRunner;
use App\System\Services\SystemInfoService;
use App\Theme\Services\ThemeManager;
use Illuminate\Support\ServiceProvider;

class VertexServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(AiProviderRegistry::class);
        $this->app->singleton(AiDraftService::class);
        $this->app->singleton(InstallationService::class);
        $this->app->singleton(DatabaseConnectionService::class);
        $this->app->singleton(EnvironmentFileService::class);
        $this->app->singleton(InstallerRunner::class);
        $this->app->singleton(ActivityLogService::class);
        $this->app->singleton(PageService::class);
        $this->app->singleton(SeoMetaService::class);
        $this->app->singleton(PageRenderer::class);
        $this->app->singleton(MediaService::class);
        $this->app->singleton(CacheService::class);
        $this->app->singleton(SystemInfoService::class);
        $this->app->singleton(ThemeManager::class);
    }

    public function boot(RouteRegistrar $routes): void
    {
        require_once app_path('Builder/Config/blocks.php');

        $routes->register();
    }
}
