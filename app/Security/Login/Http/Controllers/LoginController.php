<?php

namespace App\Security\Login\Http\Controllers;

use App\Auth\Http\Controllers\AdminAuthController;
use App\Security\Login\Services\LoginAttemptService;
use App\Security\Login\Services\TwoFactorService;
use App\Security\Login\Support\TwoFactorSession;
use App\System\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends AdminAuthController
{
    public function __construct(
        private readonly TwoFactorService $twoFactor,
        private readonly LoginAttemptService $loginAttempt,
        ActivityLogService $activityLog,
    ) {
        parent::__construct($activityLog);
    }

    public function login(Request $request): RedirectResponse|JsonResponse
    {
        // Check if login is locked out
        if ($this->loginAttempt->tooManyAttempts($request)) {
            $seconds = $this->loginAttempt->availableIn($request);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Too many login attempts. Try again in :seconds seconds.',
                    'retry_in' => $seconds,
                ], 429);
            }

            return back()->withErrors([
                'email' => "Too many login attempts. Try again in {$seconds} seconds.",
            ]);
        }

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
            $this->loginAttempt->increment($request);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $this->loginAttempt->clear($request);

        $request->session()->regenerate();

        $user = $request->user();
        $user->forceFill(['last_login_at' => now()])->save();

        // If 2FA is enabled, redirect to verification
        if ($user->two_factor_secret) {
            $request->session()->put([
                TwoFactorSession::USER_ID => $user->getKey(),
                TwoFactorSession::VERIFIED => false,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'requires_2fa' => true,
                    'redirect' => route('admin.2fa.verify'),
                ]);
            }

            return redirect()->route('admin.2fa.verify');
        }

        $request->session()->put(TwoFactorSession::VERIFIED, true);

        $this->activityLog->record(
            'auth.login',
            'user',
            $user->id,
            'Admin user signed in.',
            ['remember' => $remember],
            $request,
        );

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'redirect' => route('admin.dashboard')]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }
}
