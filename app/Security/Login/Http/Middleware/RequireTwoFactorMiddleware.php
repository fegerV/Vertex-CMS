<?php

namespace App\Security\Login\Http\Middleware;

use App\Security\Login\Support\TwoFactorSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->two_factor_secret) {
            return $next($request);
        }

        if (! $request->session()->boolean(TwoFactorSession::VERIFIED)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => '2FA verification required.',
                    'requires_2fa' => true,
                ], 403);
            }

            $request->session()->put(TwoFactorSession::USER_ID, $user->getKey());

            if ($request->routeIs('admin.2fa.*')) {
                return $next($request);
            }

            return redirect()->route('admin.2fa.verify');
        }

        return $next($request);
    }
}
