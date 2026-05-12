<?php

namespace Vertex\Forms;

use Illuminate\Support\ServiceProvider;
use Vertex\Forms\Services\FormService;
use Vertex\Forms\Services\FormCalculatorEngine;
use Vertex\Forms\Services\FormConditionEngine;
use Vertex\Forms\Services\FormImportExportService;
use Vertex\Forms\Services\FormAnalyticsService;
use Vertex\Forms\FieldTypeRegistry;
use Vertex\Forms\Contracts\FormRepositoryInterface;
use Vertex\Forms\Contracts\CalculatorEngineInterface;
use Vertex\Forms\Repositories\EloquentFormRepository;

class VertexFormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind contracts to implementations
        $this->app->bind(FormRepositoryInterface::class, EloquentFormRepository::class);
        $this->app->bind(CalculatorEngineInterface::class, FormCalculatorEngine::class);

        // Register core services
        $this->app->singleton(FieldTypeRegistry::class, fn() => new FieldTypeRegistry());

        $this->app->singleton(FormCalculatorEngine::class, fn($app) => new FormCalculatorEngine());
        $this->app->singleton(FormConditionEngine::class, fn($app) => new FormConditionEngine());
        $this->app->singleton(FormImportExportService::class, fn($app) => new FormImportExportService());
        $this->app->singleton(FormAnalyticsService::class, fn($app) => new FormAnalyticsService());

        // FormService requires EmailService, Validator, CalculatorEngine, ConditionEngine
        $this->app->singleton(FormService::class, function ($app) {
            return new FormService(
                $app->make(\App\System\Services\EmailService::class), // from core
                $app['validator'],
                $app->make(FormCalculatorEngine::class),
                $app->make(FormConditionEngine::class)
            );
        });

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../config/forms.php', 'forms');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'forms');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

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
            FormRepositoryInterface::class,
            CalculatorEngineInterface::class,
        ];
    }
}

