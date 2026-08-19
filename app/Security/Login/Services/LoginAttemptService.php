<?php

namespace App\Security\Login\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginAttemptService
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_MINUTES = 15;

    public function __construct(
        private readonly RateLimiter $limiter,
    ) {
    }

    public function tooManyAttempts(Request $request): bool
    {
        return $this->limiter->tooManyAttempts(
            $this->throttleKey($request),
            self::MAX_ATTEMPTS
        );
    }

    public function increment(Request $request): void
    {
        $this->limiter->hit(
            $this->throttleKey($request),
            self::DECAY_MINUTES * 60
        );
    }

    public function clear(Request $request): void
    {
        $this->limiter->clear($this->throttleKey($request));
    }

    public function availableIn(Request $request): int
    {
        return $this->limiter->availableIn($this->throttleKey($request));
    }

    private function throttleKey(Request $request): string
    {
        return 'login-attempts|' . $request->ip() . '|' . Str::lower($request->input('email', ''));
    }
}