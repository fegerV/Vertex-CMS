<?php

namespace App\Core\Http\Middleware;

use App\Core\Services\InstallationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    public function __construct(
        private readonly InstallationService $installation,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->installation->isInstalled() && ! $request->is('install*')) {
            return redirect()->route('install.index');
        }

        return $next($request);
    }
}

