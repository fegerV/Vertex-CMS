<?php

namespace App\Security\Login\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TotpCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^\d{6}$/', (string) $value)) {
            $fail('The :attribute must be a 6-digit code.');
        }
    }
}