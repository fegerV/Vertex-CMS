<?php

namespace App\Security\Login\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Security\Login\Rules\TotpCode;
use App\Security\Login\Services\LoginAttemptService;
use App\Security\Login\Services\TwoFactorService;
use App\Security\Login\Support\TwoFactorSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(
        private readonly TwoFactorService $twoFactor,
        private readonly LoginAttemptService $loginAttempt,
    ) {}

    public function show(): View
    {
        return view('admin.auth.two-factor');
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', new TotpCode],
        ]);

        $user = User::query()->find($request->session()->get(TwoFactorSession::USER_ID));
        if (! $user) {
            return redirect()->route('admin.login');
        }

        $secret = $user->two_factor_secret;
        if (! $this->twoFactor->verifyCode($secret, $validated['code'])) {
            $this->loginAttempt->increment($request);

            throw ValidationException::withMessages([
                'code' => __('Invalid 2FA code. :seconds seconds remaining.', [
                    'seconds' => $this->loginAttempt->availableIn($request),
                ]),
            ]);
        }

        $this->loginAttempt->clear($request);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->forget(TwoFactorSession::USER_ID);
        $request->session()->put(TwoFactorSession::VERIFIED, true);

        return redirect()->intended(route('admin.dashboard'));
    }
}
