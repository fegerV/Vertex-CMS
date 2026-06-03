<?php

namespace App\Seo\Services;

use App\Models\Redirect;
use Illuminate\Http\Request;

class RedirectResolver
{
    public function resolve(Request $request): ?Redirect
    {
        if (! $request->isMethodCacheable()) {
            return null;
        }

        if ($request->is('admin') || $request->is('admin/*') || $request->is('api/*') || $request->is('install') || $request->is('install/*') || $request->is('up')) {
            return null;
        }

        $path = '/'.ltrim($request->getPathInfo(), '/');
        $path = $path === '//' ? '/' : $path;
        $absolute = rtrim($request->getSchemeAndHttpHost(), '/').$path;

        $redirect = Redirect::query()
            ->where('enabled', true)
            ->whereIn('from_url', [$path, $absolute])
            ->first();

        if (! $redirect) {
            return null;
        }

        if (in_array($redirect->to_url, [$path, $absolute], true)) {
            return null;
        }

        return $redirect;
    }
}
