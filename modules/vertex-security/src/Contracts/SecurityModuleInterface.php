<?php

namespace Vertex\Security\Contracts;

interface SecurityModuleInterface
{
    public function key(): string;

    public function enabled(): bool;

    public function health(): array;

    public function settings(): array;
}
