<?php

namespace App\Vertex\Security;

use App\Vertex\Security\Console\Commands\RunSecurityScanner;
use App\Vertex\Security\Middleware\BasicRateLimiter;
use App\Vertex\Security\Middleware\SecureHeaders;
use App\Vertex\Security\Middleware\SessionGuard;
use App\Vertex\Security\Modules\Alerts\AlertsModule;
use App\Vertex\Security\Modules\Cloudflare\CloudflareModule;
use App\Vertex\Security\Modules\GeoIp\GeoIpMiddleware;
use App\Vertex\Security\Modules\GeoIp\GeoIpModule;
use App\Vertex\Security\Modules\Hibp\HibpModule;
use App\Vertex\Security\Modules\Integrity\IntegrityModule;
use App\Vertex\Security\Modules\Scanner\ScannerModule;
use App\Vertex\Security\Modules\Waf\WafMiddleware;
use App\Vertex\Security\Modules\Waf\WafModule;
use App\Vertex\Security\Support\ModuleRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class, fn () => new ModuleRegistry);
    }

    public function boot(): void
    {
        $this->configureFallbackDrivers();
        $this->registerCoreRateLimiter();
        $this->registerPasswordRules();
        $this->registerConsoleTools();
        $this->registerCoreMiddleware();
        $this->registerOptionalModules();
    }

    private function registerConsoleTools(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RunSecurityScanner::class,
            ]);
        }

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            if (! config('security.modules.scanner', false)) {
                return;
            }

            $event = $schedule->command('security:scanner:run')->withoutOverlapping();

            match ((string) config('security.scanner.schedule', 'hourly')) {
                'daily' => $event->dailyAt('03:00'),
                'twice-daily' => $event->twiceDaily(3, 15),
                default => $event->hourly(),
            };
        });
    }

    private function registerCoreMiddleware(): void
    {
        $register = function (object $kernel): void {
            if (! method_exists($kernel, 'pushMiddleware')) {
                return;
            }

            $reflection = new \ReflectionObject($kernel);
            $property = $reflection->getProperty('middleware');
            $property->setAccessible(true);
            $current = $property->getValue($kernel);

            foreach ([SecureHeaders::class, SessionGuard::class, BasicRateLimiter::class] as $middleware) {
                if (in_array($middleware, $current, true)) {
                    continue;
                }

                $kernel->pushMiddleware($middleware);
            }
        };

        if ($this->app->resolved(HttpKernelContract::class)) {
            $register($this->app->make(HttpKernelContract::class));
        }

        $this->app->afterResolving(HttpKernelContract::class, $register);
    }

    private function registerCoreRateLimiter(): void
    {
        RateLimiter::for('vertex-security-core', function (Request $request) {
            [$amount, $unit] = array_pad(
                explode('/', (string) config('security.rate_limiter.limit', '60/min'), 2),
                2,
                'min'
            );

            return match (strtolower($unit)) {
                'hour', 'hours', 'hr' => Limit::perHour((int) $amount)->by((string) ($request->user()?->getAuthIdentifier() ?: $request->ip())),
                default => Limit::perMinute((int) $amount)->by((string) ($request->user()?->getAuthIdentifier() ?: $request->ip())),
            };
        });
    }

    private function registerPasswordRules(): void
    {
        $minLength = max(8, (int) config('security.password_policy.min_length', 12));

        Password::defaults(function () use ($minLength) {
            $rule = Password::min($minLength);

            if (config('security.password_policy.require_mixed_case', false)) {
                $rule = $rule->mixedCase();
            }

            if (config('security.password_policy.require_numbers', true)) {
                $rule = $rule->numbers();
            }

            if (config('security.password_policy.require_symbols', false)) {
                $rule = $rule->symbols();
            }

            if (config('security.password_policy.uncompromised', false)) {
                $rule = $rule->uncompromised();
            }

            return $rule;
        });
    }

    private function configureFallbackDrivers(): void
    {
        $defaultCacheDriver = config('cache.default');
        if (! config("cache.stores.{$defaultCacheDriver}")) {
            config(['cache.default' => config('security.fallback.cache_driver', 'file')]);
        }

        $defaultQueueDriver = config('queue.default');
        if (! config("queue.connections.{$defaultQueueDriver}")) {
            config(['queue.default' => config('security.fallback.queue_driver', 'database')]);
        }
    }

    private function registerOptionalModules(): void
    {
        $modules = [
            'waf' => WafModule::class,
            'geoip' => GeoIpModule::class,
            'integrity' => IntegrityModule::class,
            'hibp' => HibpModule::class,
            'cloudflare' => CloudflareModule::class,
            'scanner' => ScannerModule::class,
            'alerts' => AlertsModule::class,
        ];

        foreach ($modules as $key => $moduleClass) {
            if (! config("security.modules.{$key}", false)) {
                continue;
            }

            /** @var object{register:callable(Application):void} $module */
            $module = $this->app->make($moduleClass);
            $module->register($this->app);
        }

        $middleware = array_filter([
            config('security.modules.waf', false) ? WafMiddleware::class : null,
            config('security.modules.geoip', false) ? GeoIpMiddleware::class : null,
        ]);

        $register = function (object $kernel) use ($middleware): void {
            if (! method_exists($kernel, 'prependMiddleware')) {
                return;
            }
            foreach (array_reverse($middleware) as $item) {
                $kernel->prependMiddleware($item);
            }
        };

        if ($this->app->resolved(HttpKernelContract::class)) {
            $register($this->app->make(HttpKernelContract::class));
        }
        $this->app->afterResolving(HttpKernelContract::class, $register);
    }
}
