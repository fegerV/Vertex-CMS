<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Sentry\Laravel\Integration;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;

class SentryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! class_exists(SentrySdk::class)) {
            return;
        }

        $this->app->bind(HubInterface::class, function ($app) {
            return SentrySdk::getCurrentHub();
        });
    }

    public function boot(): void
    {
        if (empty(config('sentry.dsn')) || ! function_exists('Sentry\\init')) {
            return;
        }

        \Sentry\init([
            'dsn' => config('sentry.dsn'),
            'breadcrumbs' => config('sentry.breadcrumbs', []),
            'traces_sample_rate' => config('sentry.tracing.enabled', true) ? 1.0 : 0,
            'send_default_pii' => config('sentry.send_default_pii', false),
            'max_breadcrumbs' => config('sentry.max_breadcrumbs', 50),
            'environment' => config('sentry.environment', env('APP_ENV')),
            'release' => config('sentry.release', '0.1.0'),
            'integrations' => [
                new Integration,
            ],
        ]);

        $this->registerErrorHandling();
    }

    protected function registerErrorHandling(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $this->app->error(function (\Throwable $exception) {
            \Sentry\captureException($exception);
        });
    }
}
