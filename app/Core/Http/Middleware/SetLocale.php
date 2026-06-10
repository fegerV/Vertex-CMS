<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! in_array($locale, ['en', 'ru'])) {
            $locale = Session::get('locale', config_value('site.locale', config('app.locale')));
        }

        if (in_array($locale, ['en', 'ru'])) {
            App::setLocale($locale);
            
            if ($request->hasSession()) {
                Session::put('locale', $locale);
            }
        }

        return $next($request);
    }
}
