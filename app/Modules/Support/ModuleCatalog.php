<?php

namespace App\Modules\Support;

class ModuleCatalog
{
    /**
     * @param  array<string, ModuleManifest>  $manifests
     */
    public function __construct(
        private readonly array $manifests,
    ) {
    }

    /**
     * @return array<string, ModuleManifest>
     */
    public function all(): array
    {
        return $this->manifests;
    }

    /**
     * @return array<string, ModuleManifest>
     */
    public function byTier(string $tier): array
    {
        return array_filter(
            $this->manifests,
            static fn (ModuleManifest $manifest): bool => $manifest->tier() === $tier
        );
    }

    public function find(string $id): ?ModuleManifest
    {
        return $this->manifests[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->manifests[$id]);
    }

    public function providerMap(): array
    {
        $providers = [];

        foreach ($this->manifests as $manifest) {
            $providers[$manifest->id()] = $manifest->providers();
        }

        return $providers;
    }
}
