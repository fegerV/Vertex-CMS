<?php

namespace App\Modules\Support;

use Illuminate\Filesystem\Filesystem;

class ModuleManifestLoader
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly array $scanPaths = [],
        private readonly array $coreModules = [],
    ) {
    }

    /**
     * @return array<string, ModuleManifest>
     */
    public function loadAll(): array
    {
        $manifests = [];

        foreach ($this->coreModules as $module) {
            $manifest = ModuleManifest::fromArray($module);
            $manifests[$manifest->id()] = $manifest;
        }

        foreach ($this->discoverManifestFiles() as $manifestFile) {
            $decoded = json_decode($this->files->get($manifestFile), true);

            if (! is_array($decoded)) {
                continue;
            }

            $manifest = ModuleManifest::fromArray($decoded, $manifestFile);

            if ($manifest->id() === '') {
                continue;
            }

            $manifests[$manifest->id()] = $manifest;
        }

        ksort($manifests);

        return $manifests;
    }

    /**
     * @return string[]
     */
    public function discoverManifestFiles(): array
    {
        $manifestFiles = [];

        foreach ($this->scanPaths as $path) {
            if (! $this->files->isDirectory($path)) {
                continue;
            }

            foreach ($this->files->directories($path) as $moduleDirectory) {
                $manifestPath = $moduleDirectory . DIRECTORY_SEPARATOR . 'module.json';

                if ($this->files->exists($manifestPath)) {
                    $manifestFiles[] = $manifestPath;
                }
            }
        }

        sort($manifestFiles);

        return $manifestFiles;
    }
}
