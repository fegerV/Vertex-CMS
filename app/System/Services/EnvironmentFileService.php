<?php

namespace App\System\Services;

use Illuminate\Support\Facades\File;

class EnvironmentFileService
{
    public function write(array $values): void
    {
        $path = base_path('.env');

        if (! File::exists($path)) {
            File::copy(base_path('.env.example'), $path);
        }

        $contents = File::get($path);

        foreach ($values as $key => $value) {
            $contents = $this->replaceValue($contents, $key, $this->formatValue($value));
        }

        File::put($path, $contents);
    }

    private function replaceValue(string $contents, string $key, string $value): string
    {
        $pattern = "/^{$key}=.*$/m";
        $line = "{$key}={$value}";

        if (preg_match($pattern, $contents) === 1) {
            return preg_replace($pattern, $line, $contents) ?? $contents;
        }

        return rtrim($contents).PHP_EOL.$line.PHP_EOL;
    }

    private function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"|\'/', $value) === 1) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }
}

