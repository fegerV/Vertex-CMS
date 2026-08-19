<?php

namespace App\Security\Login\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SessionManager
{
    public function getUserSessions(User $user): Collection
    {
        return collect(session()->get('user_sessions', []))
            ->where('user_id', $user->id);
    }

    public function createSession(User $user): string
    {
        $sessionId = Str::random(40);
        $sessions = session()->get('user_sessions', []);
        $sessions[$sessionId] = [
            'user_id' => $user->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()->toIso8601String(),
            'last_activity' => now()->toIso8601String(),
        ];
        session()->put('user_sessions', $sessions);

        return $sessionId;
    }

    public function revokeSession(User $user, string $sessionId): void
    {
        $sessions = session()->get('user_sessions', []);
        unset($sessions[$sessionId]);
        session()->put('user_sessions', $sessions);
    }

    public function revokeOtherSessions(User $user): void
    {
        $currentSessionId = session()->getId();
        $sessions = session()->get('user_sessions', []);
        foreach ($sessions as $id => $session) {
            if ($session['user_id'] === $user->id && $id !== $currentSessionId) {
                unset($sessions[$id]);
            }
        }
        session()->put('user_sessions', $sessions);
    }
}