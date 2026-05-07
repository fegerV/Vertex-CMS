<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\File;

class InstallationService
{
    public function isInstalled(): bool
    {
        return (bool) env('VERTEX_INSTALLED', false) || File::exists(config('vertex.install_lock_path'));
    }

    public function requirements(): array
    {
        return [
            'php' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'pdo' => extension_loaded('pdo'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'fileinfo' => extension_loaded('fileinfo'),
            'tokenizer' => extension_loaded('tokenizer'),
            'xml' => extension_loaded('xml'),
            'ctype' => extension_loaded('ctype'),
            'json' => extension_loaded('json'),
            'bcmath' => extension_loaded('bcmath'),
            'gd_or_imagick' => extension_loaded('gd') || extension_loaded('imagick'),
            'zip' => extension_loaded('zip'),
            'curl' => extension_loaded('curl'),
            'storage_writable' => is_writable(storage_path()),
            'bootstrap_cache_writable' => is_writable(base_path('bootstrap/cache')),
            'uploads_writable' => is_writable(public_path('uploads')),
        ];
    }
}

