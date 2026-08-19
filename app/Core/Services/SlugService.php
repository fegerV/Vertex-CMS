<?php

namespace App\Core\Services;

use Illuminate\Support\Str;

class SlugService
{
    public function make(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->slug('-')
            ->lower()
            ->value();
    }
}

