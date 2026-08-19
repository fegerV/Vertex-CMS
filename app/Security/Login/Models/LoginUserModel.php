<?php

namespace App\Security\Login\Models;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Extended User model properties for 2FA and session management.
 * These should be added to the existing User model's $fillable, $casts, and migration.
 */
final class LoginUserModel
{
    public static function getFillable(): array
    {
        return [
            'two_factor_secret',
            'two_factor_recovery_codes',
            'password_changed_at',
            'last_login_at',
            'last_login_ip',
            'last_login_user_agent',
        ];
    }

    public static function getCasts(): array
    {
        return [
            'two_factor_secret' => 'encrypted:array',
            'two_factor_recovery_codes' => 'encrypted:array',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public static function getDefaults(): array
    {
        return [
            'two_factor_recovery_codes' => [],
            'password_changed_at' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'last_login_user_agent' => null,
        ];
    }

    /**
     * Enable 2FA for a user.
     */
    public static function enableTwoFactor(User $user, string $secret, array $recoveryCodes): bool
    {
        return $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();
    }

    /**
     * Disable 2FA for a user.
     */
    public static function disableTwoFactor(User $user): bool
    {
        return $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => [],
        ])->save();
    }

    /**
     * Mark a recovery code as used.
     */
    public static function useRecoveryCode(User $user, string $code): void
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $index = array_search(Str::upper($code), array_map('strtoupper', $codes), true);

        if ($index !== false) {
            unset($codes[$index]);
            $user->forceFill([
                'two_factor_recovery_codes' => array_values($codes),
            ])->save();
        }
    }

    /**
     * Record login metadata on user.
     */
    public static function recordLogin(User $user, Request $request): void
    {
        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => $request->userAgent(),
        ])->save();
    }
}