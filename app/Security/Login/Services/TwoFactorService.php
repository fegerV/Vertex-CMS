<?php

namespace App\Security\Login\Services;

use App\Models\User;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(
        private readonly Google2FA $google2fa,
    ) {
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQrCodeUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name', 'VertexCMS'),
            $user->email,
            $secret,
        );
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::upper(Str::random(10));
        }
        return $codes;
    }

    public function verifyRecoveryCode(array $storedCodes, string $code): bool
    {
        return in_array(Str::upper($code), array_map('strtoupper', $storedCodes), true);
    }
}