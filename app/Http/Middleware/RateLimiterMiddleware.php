<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimiterMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $limiter = 'api'): Response
    {
        if (RateLimiter::tooManyAttempts($this->resolveSignature($request, $limiter), $this->decayMinutes())) {
            return response()->json([
                'message' => 'Слишком много запросов. Попробуйте позже.',
                'retry_after' => RateLimiter::availableIn($this->resolveSignature($request, $limiter))
            ], 429);
        }

        RateLimiter::hit($this->resolveSignature($request, $limiter), $this->decayMinutes());

        return $next($request);
    }

    protected function resolveSignature(Request $request, string $limiter): string
    {
        return sha1($request->ip() . '|' . $limiter);
    }

    protected function decayMinutes(): int
    {
        return 1;
    }
}
