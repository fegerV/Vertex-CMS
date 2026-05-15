<?php

namespace App\Security\Login\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PasswordExpiryMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        // Skip if user is not active
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        // Skip middleware for password change routes
        if ($request->is('admin/password/*') || $request->routeIs('admin.password.*')) {
            return $next($request);
        }

        // Check if password is expired (90 days by default)
        $expiryDays = config('security.login.password_expiry_days', 90);
        if ($expiryDays > 0 && $user->password_changed_at) {
            $lastChange = \Carbon\Carbon::parse($user->password_changed_at);
            if ($lastChange->addDays($expiryDays)->isPast()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Password expired. Please reset your password.',
                        'requires_password_reset' => true,
                    ], 403);
                }

                return redirect()->route('admin.password.expired');
            }
        }

        return $next($request);
    }
}