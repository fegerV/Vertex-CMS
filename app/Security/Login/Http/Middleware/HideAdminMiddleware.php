<?php

namespace App\Security\Login\Http\Middleware;

use App\Security\Login\Services\HiddenAdminService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Route;

class HideAdminMiddleware
{
    public function __construct(
        private readonly HiddenAdminService $hiddenAdmin,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $currentPath = $request->path();

        // Check if someone is hitting the real /admin path when hidden path is enabled
        if ($this->hiddenAdmin->isHiddenPathEnabled() && $currentPath === 'admin') {
            $this->hiddenAdmin->recordFailedAccessAttempt($request);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Not found.'], 404);
            }

            abort(404);
        }

        return $next($request);
    }
}