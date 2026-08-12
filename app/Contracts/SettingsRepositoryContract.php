<?php

namespace App\Contracts;

interface SettingsRepositoryContract
{
    public function all(): array;

    public function get(string $key, mixed $default = null): mixed;

    public function setMany(array $values): void;

    public function forgetCache(): void;
}
