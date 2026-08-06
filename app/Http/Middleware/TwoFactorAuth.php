<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuth
{
    /**
     * Handle an incoming request.
     * Требует 2FA для доступа к админке
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверка включен ли 2FA у пользователя
        if ($request->user() && $request->user()->two_factor_enabled) {
            // Если сессия не подтверждена 2FA
            if (!$request->session()->get('2fa_verified')) {
                // Если это не запрос на верификацию 2FA
                if (!$request->routeIs('admin.2fa.*')) {
                    return redirect()->route('admin.2fa.verify');
                }
            }
        }

        return $next($request);
    }
}
