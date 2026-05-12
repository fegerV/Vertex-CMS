<?php

namespace Vertex\Security\Services;

use Vertex\Security\Support\SecurityModuleRegistry;

class SecurityManager
{
    public function __construct(
        private readonly SecurityModuleRegistry $registry,
    ) {
    }

    public function modules(): array
    {
        return $this->registry->modules();
    }

    public function enabledModules(): array
    {
        return array_filter(
            $this->registry->modules(),
            static fn (array $module): bool => (bool) ($module['enabled'] ?? false)
        );
    }

    public function has(string $module): bool
    {
        return array_key_exists($module, $this->registry->modules());
    }

    public function get(string $module): ?array
    {
        return $this->registry->modules()[$module] ?? null;
    }
}
