<?php

namespace App\Auth\Http\Controllers;

use App\Auth\Http\Resources\AuthUserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use App\System\Services\ActivityLogService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->where('status', 'active')
            ->with('roles.permissions')
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }
        $user->forceFill(['last_login_at' => now()])->save();
        $abilities = $user->apiAbilities();
        $deviceName = trim((string) ($credentials['device_name'] ?? 'mobile-client')) ?: 'mobile-client';
        $token = $user->createToken($deviceName, $abilities);

        $this->activityLog->record(
            'api.auth.login',
            'user',
            $user->id,
            'API bearer token issued.',
            ['mode' => 'sanctum', 'device_name' => $deviceName, 'abilities' => $abilities],
            $request,
        );

        return ApiResponse::success([
            'user' => AuthUserResource::make($user->load('roles.permissions'))->resolve($request),
            'auth' => [
                'mode' => 'bearer',
                'guard' => 'sanctum',
                'token_type' => 'Bearer',
                'access_token' => $token->plainTextToken,
                'abilities' => $abilities,
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
                'API bearer token revoked.',
                ['mode' => 'sanctum'],
                $request,
            );
        }

        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success([
            'logged_out' => true,
        ]);
    }
}
