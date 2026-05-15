<?php

namespace App\Security\Login\Providers;

use App\Security\Login\Http\Middleware\HideAdminMiddleware;
use App\Security\Login\Http\Middleware\PasswordExpiryMiddleware;
use App\Security\Login\Http\Middleware\RequireTwoFactorMiddleware;
use App\Security\Login\Http\Middleware\ThrottleLoginMiddleware;
use App\Security\Login\Services\HiddenAdminService;
use App\Security\Login\Services\LoginAttemptService;
use App\Security\Login\Services\SessionManager;
use App\Security\Login\Services\TwoFactorService;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\ServiceProvider;
use PragmaRX\Google2FA\Google2FA;

class LoginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(TwoFactorService::class, function ($app) {
            return new TwoFactorService(new Google2FA());
        });

        $this->app->singleton(LoginAttemptService::class, function ($app) {
            return new LoginAttemptService($app->make(RateLimiter::class));
        });

        $this->app->singleton(SessionManager::class);
        $this->app->singleton(HiddenAdminService::class);

        $router = $this->app['router'];

        $router->aliasMiddleware('login.throttle', ThrottleLoginMiddleware::class);
        $router->aliasMiddleware('login.2fa', RequireTwoFactorMiddleware::class);
        $router->aliasMiddleware('login.password.expiry', PasswordExpiryMiddleware::class);
        $router->aliasMiddleware('login.hide.admin', HideAdminMiddleware::class);

        $this->publishes([
            __DIR__ . '/../../config/security-login.php' => config_path('security-login.php'),
        ], 'vertex-config');
    }
}