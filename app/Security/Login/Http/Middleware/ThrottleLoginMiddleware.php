<?php

namespace App\Security\Login\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLoginMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Throttle only for login POST requests
        if ($request->isMethod('post') && $request->route()->getName() === 'admin.login') {
            $throttleKey = 'login-attempts|' . $request->ip() . '|' . strtolower($request->input('email', ''));
            $maxAttempts = 5;
            $decayMinutes = 15;

            if (cache()->has($throttleKey) && cache()->get($throttleKey) >= $maxAttempts) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Too many login attempts. Please try again later.',
                    ], 429);
                }

                return back()->withErrors([
                    'email' => 'Too many login attempts. Please try again later.',
                ]);
            }
        }

        return $next($request);
    }
}