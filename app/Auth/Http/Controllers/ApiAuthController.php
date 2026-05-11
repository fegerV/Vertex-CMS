<?php

namespace App\Auth\Http\Controllers;

use App\Auth\Http\Resources\AuthUserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use App\System\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'status' => 'active',
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();
        $user->forceFill(['last_login_at' => now()])->save();

        $this->activityLog->record(
            'api.auth.login',
            'user',
            $user->id,
            'API user signed in.',
            ['remember' => $remember, 'mode' => 'session'],
            $request,
        );

        return ApiResponse::success([
            'user' => AuthUserResource::make($user->load('roles.permissions'))->resolve($request),
            'auth' => [
                'mode' => 'session',
                'guard' => 'web',
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            AuthUserResource::make($request->user()->load('roles.permissions'))->resolve($request)
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $this->activityLog->record(
                'api.auth.logout',
                'user',
                $user->id,
                'API user signed out.',
                ['mode' => 'session'],
                $request,
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ApiResponse::success([
            'logged_out' => true,
        ]);
    }
}
