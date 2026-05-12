<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to admin routes
        if ($request->is('admin*')) {
            $locale = config_value('site.admin_locale', 'ru');
            App::setLocale($locale);
        }

        return $next($request);
    }
}
