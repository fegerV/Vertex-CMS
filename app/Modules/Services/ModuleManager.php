<?php

namespace App\Modules\Services;

use App\Modules\Support\ModuleCatalog;
use App\Modules\Support\ModuleManifest;

class ModuleManager
{
    public function __construct(
        private readonly ModuleCatalog $catalog,
    ) {
    }

    public function catalog(): ModuleCatalog
    {
        return $this->catalog;
    }

    public function core(): array
    {
        return $this->catalog->byTier(ModuleManifest::TIER_CORE);
    }

    public function builtin(): array
    {
        return $this->catalog->byTier(ModuleManifest::TIER_BUILTIN);
    }

    public function marketplace(): array
    {
        return $this->catalog->byTier(ModuleManifest::TIER_MARKETPLACE);
    }

    public function providerMap(): array
    {
        return $this->catalog->providerMap();
    }
}
