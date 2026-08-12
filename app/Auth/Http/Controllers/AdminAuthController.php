<?php

namespace App\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\System\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function __construct(
        protected readonly ActivityLogService $activityLog,
    ) {}

    public function showLogin(): View
    {
        return view('admin.auth.login');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $this->activityLog->record(
                'auth.logout',
                'user',
                $user->id,
                'Admin user signed out.',
                [],
                $request,
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
