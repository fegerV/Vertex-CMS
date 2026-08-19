<?php

namespace App\Security\Login\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HiddenAdminService
{
    private const ADMIN_PATH_KEY = 'security.hidden_admin_path';

    public function getAdminPath(): string
    {
        return config('security.login.hidden_path', 'manage');
    }

    public function isHiddenPathEnabled(): bool
    {
        return config('security.login.enable_hidden_path', false);
    }

    public function validatePath(string $path): bool
    {
        if (! $this->isHiddenPathEnabled()) {
            return $path === 'admin';
        }

        return $path === $this->getAdminPath();
    }

    public function recordFailedAccessAttempt(Request $request): void
    {
        $key = 'hidden_admin_failures|' . $request->ip();
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes(5));

        // Alert on brute force against hidden path
        if ($attempts >= 20) {
            activity()->withProperties(['ip' => $request->ip()])
                ->log('hidden_admin_brute_force');
        }
    }
}