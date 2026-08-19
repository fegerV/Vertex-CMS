<?php

namespace App\Modules\Support;

class ModuleManifest
{
    public const TIER_CORE = 'core';
    public const TIER_BUILTIN = 'builtin';
    public const TIER_MARKETPLACE = 'marketplace';

    public const ACTIVATION_ALWAYS_ON = 'always_on';
    public const ACTIVATION_ADMIN_TOGGLE = 'admin_toggle';
    public const ACTIVATION_INSTALL_THEN_TOGGLE = 'install_then_toggle';

    public function __construct(
        private readonly array $attributes,
        private readonly ?string $sourcePath = null,
    ) {
    }

    public static function fromArray(array $attributes, ?string $sourcePath = null): self
    {
        $module = $attributes['module'] ?? $attributes;

        return new self($module, $sourcePath);
    }

    public function all(): array
    {
        return $this->attributes;
    }

    public function sourcePath(): ?string
    {
        return $this->sourcePath;
    }

    public function id(): string
    {
        return (string) ($this->attributes['id'] ?? '');
    }

    public function name(): string
    {
        return (string) ($this->attributes['name'] ?? $this->id());
    }

    public function tier(): string
    {
        return (string) ($this->attributes['tier'] ?? self::TIER_BUILTIN);
    }

    public function activation(): string
    {
        return (string) ($this->attributes['activation'] ?? self::ACTIVATION_ADMIN_TOGGLE);
    }

    public function version(): ?string
    {
        $version = $this->attributes['version'] ?? null;

        return $version !== null ? (string) $version : null;
    }

    public function description(): ?string
    {
        $description = $this->attributes['description'] ?? null;

        return $description !== null ? (string) $description : null;
    }

    public function providers(): array
    {
        return array_values(array_filter((array) ($this->attributes['providers'] ?? []), 'is_string'));
    }

    public function dependencies(): array
    {
        return (array) ($this->attributes['dependencies'] ?? []);
    }

    public function permissions(): array
    {
        return array_values(array_filter((array) ($this->attributes['permissions'] ?? []), 'is_string'));
    }

    public function routes(): array
    {
        return (array) ($this->attributes['routes'] ?? []);
    }

    public function assets(): array
    {
        return (array) ($this->attributes['assets'] ?? []);
    }

    public function ui(): array
    {
        return (array) ($this->attributes['ui'] ?? []);
    }

    public function isCore(): bool
    {
        return $this->tier() === self::TIER_CORE;
    }

    public function isBuiltin(): bool
    {
        return $this->tier() === self::TIER_BUILTIN;
    }

    public function isMarketplace(): bool
    {
        return $this->tier() === self::TIER_MARKETPLACE;
    }
}
