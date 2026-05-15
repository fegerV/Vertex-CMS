<?php

namespace App\Vertex\Security\Middleware;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class BasicRateLimiter
{
    public function handle(Request $request, Closure $next): Response
    {
        [$maxAttempts, $decaySeconds] = $this->resolveLimit();
        $key = $this->requestKey($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Too many requests. Please slow down.',
                    'retry_after' => $retryAfter,
                ], 429);
            }

            abort(429, 'Too many requests. Please slow down.');
        }

        RateLimiter::hit($key, $decaySeconds);

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) max(0, $maxAttempts - RateLimiter::attempts($key)));

        return $response;
    }

    /**
     * @return array{0:int,1:int}
     */
    private function resolveLimit(): array
    {
        $driver = Cache::getDefaultDriver();
        $fallbackDrivers = ['file', 'database'];
        $limit = in_array($driver, $fallbackDrivers, true)
            ? (string) config('security.rate_limiter.fallback_limit', '30/min')
            : (string) config('security.rate_limiter.limit', '60/min');

        [$amount, $unit] = array_pad(explode('/', strtolower($limit), 2), 2, 'min');

        $maxAttempts = max(1, (int) $amount);
        $decaySeconds = match ($unit) {
            'second', 'seconds', 'sec' => 1,
            'hour', 'hours', 'hr' => 3600,
            default => 60,
        };

        return [$maxAttempts, $decaySeconds];
    }

    private function requestKey(Request $request): string
    {
        $prefix = (string) config('security.rate_limiter.key_prefix', 'vertex-security:core');
        $actor = $request->user()?->getAuthIdentifier() ?: $request->ip();

        return $prefix.'|'.$actor;
    }
}
