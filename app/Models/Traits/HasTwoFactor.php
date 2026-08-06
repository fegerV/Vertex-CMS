<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

trait HasTwoFactor
{
    /**
     * Включить 2FA для пользователя
     */
    public function enableTwoFactorAuthentication(): array
    {
        $this->two_factor_enabled = true;
        
        // Генерация секретного ключа
        $secret = Str::random(32);
        $this->two_factor_secret = encrypt($secret);
        
        $this->save();

        // Генерация QR кода
        $google2fa = new Google2FA();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $secret
        );

        return [
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl
        ];
    }

    /**
     * Отключить 2FA
     */
    public function disableTwoFactorAuthentication(): void
    {
        $this->two_factor_enabled = false;
        $this->two_factor_secret = null;
        $this->two_factor_recovery_codes = null;
        $this->save();
    }

    /**
     * Проверка OTP кода
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        if (!$this->two_factor_enabled || !$this->two_factor_secret) {
            return false;
        }

        $google2fa = new Google2FA();
        $secret = decrypt($this->two_factor_secret);

        return $google2fa->verifyKey($secret, $code);
    }

    /**
     * Генерация кодов восстановления
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10) . '-' . Str::random(4);
        }

        $this->two_factor_recovery_codes = encrypt(json_encode($codes));
        $this->save();

        return $codes;
    }
}
