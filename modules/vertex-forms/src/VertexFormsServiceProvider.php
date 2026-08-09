<?php

namespace Vertex\Forms;

use App\System\Services\EmailService;
use Illuminate\Support\ServiceProvider;
use Vertex\Forms\Console\CleanupFormSubmissions;
use Vertex\Forms\Contracts\CalculatorEngineInterface;
use Vertex\Forms\Contracts\FormRepositoryInterface;
use Vertex\Forms\Repositories\EloquentFormRepository;
use Vertex\Forms\Services\FormAnalyticsService;
use Vertex\Forms\Services\FormCalculatorEngine;
use Vertex\Forms\Services\FormConditionEngine;
use Vertex\Forms\Services\FormImportExportService;
use Vertex\Forms\Services\FormIntegrationService;
use Vertex\Forms\Services\FormService;
use Vertex\Forms\Services\FormSpamProtectionService;
use Vertex\Forms\Services\FormSubmissionRetentionService;

class VertexFormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FormRepositoryInterface::class, EloquentFormRepository::class);
        $this->app->bind(CalculatorEngineInterface::class, FormCalculatorEngine::class);

        $this->app->singleton(FieldTypeRegistry::class, fn () => new FieldTypeRegistry);
        $this->app->singleton(FormCalculatorEngine::class, fn () => new FormCalculatorEngine);
        $this->app->singleton(FormConditionEngine::class, fn () => new FormConditionEngine);
        $this->app->singleton(FormImportExportService::class, fn () => new FormImportExportService);
        $this->app->singleton(FormAnalyticsService::class, fn () => new FormAnalyticsService);
        $this->app->singleton(FormSpamProtectionService::class, fn () => new FormSpamProtectionService);
        $this->app->singleton(FormIntegrationService::class, fn () => new FormIntegrationService);
        $this->app->singleton(FormSubmissionRetentionService::class, fn () => new FormSubmissionRetentionService);

        $this->app->singleton(FormService::class, function ($app) {
            return new FormService(
                $app->make(EmailService::class),
                $app['validator'],
                $app->make(FormCalculatorEngine::class),
                $app->make(FormConditionEngine::class),
                $app->make(FormSpamProtectionService::class),
                $app->make(FormIntegrationService::class),
            );
        });

        $this->mergeConfigFrom(__DIR__.'/../config/forms.php', 'forms');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'forms');
        if ($this->app->runningInConsole()) {
            $this->commands([CleanupFormSubmissions::class]);
        }
        // The host application includes module routes inside its public/admin
        // groups so prefixes, names and middleware remain consistent.

        $this->publishes([
            __DIR__.'/../config/forms.php' => config_path('forms.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/forms'),
        ], 'views');
    }

    public function provides(): array
    {
        return [
            FormService::class,
            FormCalculatorEngine::class,
            FormConditionEngine::class,
            FormImportExportService::class,
            FormAnalyticsService::class,
            FormSpamProtectionService::class,
            FormIntegrationService::class,
            FormSubmissionRetentionService::class,
            FormRepositoryInterface::class,
            CalculatorEngineInterface::class,
        ];
    }
}
