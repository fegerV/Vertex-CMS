<?php

namespace App\Providers;

use App\AI\Services\AiDraftService;
use App\AI\Services\AiProviderRegistry;
use App\Builder\Services\PageRenderer;
use App\Content\Services\PageService;
use App\Core\Services\InstallationService;
use App\Core\Services\SettingsService;
use App\Core\Support\RouteRegistrar;
use App\Modules\Services\ModuleManager;
use App\Modules\Support\ModuleCatalog;
use App\Modules\Support\ModuleManifestLoader;
use App\Media\Services\MediaService;
use App\Seo\Services\SeoContentAnalysisService;
use App\Seo\Services\RedirectResolver;
use App\Seo\Services\SeoAuditService;
use App\Seo\Services\SeoMetaService;
use App\System\Console\Commands\ProcessEmailQueue;
use App\System\Services\ActivityLogService;
use App\System\Services\CacheService;
use App\System\Services\DatabaseConnectionService;
use App\System\Services\EmailService;
use App\System\Services\EnvironmentFileService;
use App\System\Services\InstallerRunner;
use App\System\Services\MaintenanceService;
use App\System\Services\SystemInfoService;
use App\System\Services\TelegramWidgetService;
use App\Theme\Services\ThemeManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class VertexServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register the 'files' binding required by Laravel core aliases
        $this->app->singleton('files', function ($app) {
            return new Filesystem();
        });

        $this->app->singleton(SettingsService::class);
        $this->app->singleton(AiProviderRegistry::class);
        $this->app->singleton(AiDraftService::class);
        $this->app->singleton(InstallationService::class);
        $this->app->singleton(DatabaseConnectionService::class);
        $this->app->singleton(EnvironmentFileService::class);
        $this->app->singleton(InstallerRunner::class);
        $this->app->singleton(ActivityLogService::class);
        $this->app->singleton(EmailService::class);
        $this->app->singleton(TelegramWidgetService::class);
        $this->app->singleton(MaintenanceService::class);
        $this->app->singleton(PageService::class);
        $this->app->singleton(SeoMetaService::class);
        $this->app->singleton(SeoContentAnalysisService::class);
        $this->app->singleton(SeoAuditService::class);
        $this->app->singleton(RedirectResolver::class);
        $this->app->singleton(PageRenderer::class);
        $this->app->singleton(MediaService::class);
        $this->app->singleton(CacheService::class);
        $this->app->singleton(SystemInfoService::class);
        $this->app->singleton(ThemeManager::class);
        $this->app->singleton(ModuleManifestLoader::class, function ($app) {
            return new ModuleManifestLoader(
                $app->make(Filesystem::class),
                config('modules.scan_paths', []),
                config('modules.core_modules', [])
            );
        });
        $this->app->singleton(ModuleCatalog::class, function ($app) {
            return new ModuleCatalog(
                $app->make(ModuleManifestLoader::class)->loadAll()
            );
        });
        $this->app->singleton(ModuleManager::class);
    }

    public function boot(RouteRegistrar $routes): void
    {
        require_once app_path('Builder/Config/blocks.php');

        $routes->register();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessEmailQueue::class,
            ]);
        }
    }
}
