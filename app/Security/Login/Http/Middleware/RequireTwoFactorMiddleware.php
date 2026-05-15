<?php

namespace App\Security\Login\Http\Middleware;

use App\Security\Login\Services\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorMiddleware
{
    public function __construct(
        private readonly TwoFactorService $twoFactor,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->two_factor_secret) {
            return $next($request);
        }

        if (! $request->session()->get('2fa:verified')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => '2FA verification required.',
                    'requires_2fa' => true,
                ], 403);
            }

            $request->session()->put('2fa:user', $user);

            return redirect()->route('admin.2fa.verify');
        }

        return $next($request);
    }
}