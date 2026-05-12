<?php

namespace Vertex\Security;

use Illuminate\Support\ServiceProvider;
use Vertex\Security\Contracts\SecurityModuleInterface;
use Vertex\Security\Services\SecurityManager;
use Vertex\Security\Support\SecurityModuleRegistry;

class VertexSecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/security.php', 'security');

        $this->app->singleton(SecurityModuleRegistry::class, function () {
            return new SecurityModuleRegistry(config('security.modules', []));
        });

        $this->app->singleton(SecurityManager::class, function ($app) {
            return new SecurityManager(
                $app->make(SecurityModuleRegistry::class)
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'security');
    }

    public function provides(): array
    {
        return [
            SecurityManager::class,
            SecurityModuleRegistry::class,
            SecurityModuleInterface::class,
        ];
    }
}
