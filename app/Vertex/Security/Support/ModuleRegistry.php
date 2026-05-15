<?php

namespace App\Vertex\Security\Support;

class ModuleRegistry
{
    public function coreEnabled(): bool
    {
        return (bool) config('security.core', true);
    }

    public function isEnabled(string $module): bool
    {
        return (bool) config("security.modules.{$module}", false);
    }

    /**
     * @return array<string, bool>
     */
    public function all(): array
    {
        return collect(config('security.modules', []))
            ->map(fn (mixed $enabled): bool => (bool) $enabled)
            ->all();
    }

    /**
     * @return array<string, bool>
     */
    public function enabled(): array
    {
        return collect($this->all())
            ->filter()
            ->all();
    }
}
