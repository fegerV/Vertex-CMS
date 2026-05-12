<?php

namespace Vertex\Security\Support;

class SecurityModuleRegistry
{
    public function __construct(
        private readonly array $modules = [],
    ) {
    }

    public function modules(): array
    {
        return $this->modules;
    }

    public function keys(): array
    {
        return array_keys($this->modules);
    }
}
