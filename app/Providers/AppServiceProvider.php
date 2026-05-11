<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute((int) config('vertex.api.rate_limit.public', 60))
                ->by($request->ip());
        });

        RateLimiter::for('api-authenticated', function (Request $request) {
            return Limit::perMinute((int) config('vertex.api.rate_limit.authenticated', 120))
                ->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(5)
                ->by((string) $request->input('email', $request->ip()));
        });
    }
}

