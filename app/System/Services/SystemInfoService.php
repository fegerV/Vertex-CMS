<?php

namespace App\System\Services;

use App\Models\Module;
use Illuminate\Support\Facades\DB;

class SystemInfoService
{
    public function get(): array
    {
        return [
            'vertex_version' => config('vertex.version'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database_type' => config('database.default'),
            'database_version' => $this->databaseVersion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? php_sapi_name(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'storage_writable' => is_writable(storage_path()),
            'cache_writable' => is_writable(storage_path('framework/cache')) || is_writable(storage_path('framework')),
            'uploads_writable' => is_writable(public_path('uploads')),
            'installed_modules' => $this->installedModules(),
        ];
    }

    private function databaseVersion(): ?string
    {
        try {
            $result = DB::selectOne('select version() as version');

            return $result?->version;
        } catch (\Throwable) {
            return null;
        }
    }

    private function installedModules(): array
    {
        try {
            return Module::query()
                ->orderBy('name')
                ->get(['name', 'slug', 'version', 'status'])
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }
}

