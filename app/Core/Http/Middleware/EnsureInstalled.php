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
        if (! $this->installation->isInstalled() && ! $this->isAllowedBeforeInstall($request)) {
            return redirect()->route('install.index');
        }

        return $next($request);
    }

    private function isAllowedBeforeInstall(Request $request): bool
    {
        return $request->is('install*')
            || $request->is('up')
            || $request->is('build/*')
            || $request->is('storage/*')
            || $request->is('uploads/*')
            || $request->is('favicon.ico');
    }
}
