<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Rate limiters регистрируются в boot, когда все сервисы уже доступны
    }

    public function boot(): void
    {
        $this->registerRateLimiters();
    }

    private function registerRateLimiters(): void
    {
        // Используем facade только после того как все сервисы зарегистрированы
        \Illuminate\Support\Facades\RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute((int) config('vertex.api.rate_limit.public', 60))
                ->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('api-authenticated', function (Request $request) {
            return Limit::perMinute((int) config('vertex.api.rate_limit.authenticated', 120))
                ->by((string) ($request->user()?->id ?: $request->ip()));
        });

        \Illuminate\Support\Facades\RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(5)
                ->by((string) $request->input('email', $request->ip()));
        });
    }
}

