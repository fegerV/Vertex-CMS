<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Exception;

class UpdateService
{
    /**
     * Проверка доступности обновлений
     */
    public function checkForUpdates(): array
    {
        $currentVersion = config('vertex.version', '1.0.0');
        
        try {
            $response = Http::timeout(10)->get(config('vertex.update_server', 'https://updates.vertexcms.com'), [
                'version' => $currentVersion,
                'license_key' => config('vertex.license_key'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'available' => version_compare($data['version'], $currentVersion, '>'),
                    'current_version' => $currentVersion,
                    'latest_version' => $data['version'],
                    'changelog' => $data['changelog'] ?? '',
                    'download_url' => $data['download_url'] ?? null,
                    'critical' => $data['critical'] ?? false,
                ];
            }
        } catch (Exception $e) {
            \Log::error('Update check failed: ' . $e->getMessage());

            return [
                'available' => false,
                'current_version' => $currentVersion,
                'latest_version' => null,
                'status' => 'unavailable',
                'error' => 'Update server is unreachable.',
            ];
        }

        return [
            'available' => false,
            'current_version' => $currentVersion,
            'latest_version' => null,
            'status' => 'unavailable',
            'error' => 'Update server returned an invalid response.',
        ];
    }

    /**
     * Загрузка обновления
     */
    public function downloadUpdate(string $downloadUrl, ?string $expectedChecksum = null): string
    {
        $tempPath = storage_path('app/updates/latest.zip');
        
        $response = Http::timeout(300)->get($downloadUrl);
        
        if (!$response->successful()) {
            throw new Exception('Failed to download update package');
        }

        File::ensureDirectoryExists(storage_path('app/updates'));
        File::put($tempPath, $response->body());

        $expectedChecksum = $expectedChecksum ?: config('vertex.update_checksum');
        if (empty($expectedChecksum)) {
            throw new Exception('Update checksum is required before applying downloaded packages');
        }

        $this->verifyChecksum($tempPath, $expectedChecksum);

        return $tempPath;
    }

    /**
     * Применение обновления
     */
    public function applyUpdate(string $packagePath): array
    {
        $extractPath = storage_path('app/updates/extracted');
        $backupPath = storage_path('app/backups/pre-update-' . time());
        
        try {
            // 1. Создание бэкапа перед обновлением
            $this->createBackup($backupPath);

            // 2. Распаковка архива
            $this->extractPackage($packagePath, $extractPath);

            // 3. Копирование файлов с сохранением конфигурации
            $this->copyFiles($extractPath, base_path());

            // 4. Очистка кэша
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');

            // 5. Запуск миграций
            Artisan::call('migrate', ['--force' => true]);

            // 6. Запуск сидеров (если нужно)
            // Artisan::call('db:seed', ['--class' => 'UpdateSeeder']);

            // 7. Оптимизация
            if (app()->environment('production')) {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
            }

            // Обновление версии в конфиге
            $newVersion = $this->getVersionFromPackage($extractPath);
            $this->updateVersionConfig($newVersion);

            // Очистка временных файлов
            File::deleteDirectory($extractPath);
            File::delete($packagePath);

            return [
                'success' => true,
                'message' => 'Update successfully applied',
                'version' => $newVersion,
            ];

        } catch (Exception $e) {
            // Откат при ошибке
            $this->rollback($backupPath);
            
            return [
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
                'rolled_back' => true,
            ];
        }
    }

    /**
     * Создание бэкапа перед обновлением
     */
    private function createBackup(string $backupPath): void
    {
        File::ensureDirectoryExists($backupPath);
        
        if (File::exists(base_path('config'))) {
            File::copyDirectory(base_path('config'), $backupPath.'/config');
        }

        if (File::exists(base_path('.env'))) {
            File::copy(base_path('.env'), $backupPath.'/.env');
        }

        if (config('database.default') === 'sqlite') {
            $database = database_path('database.sqlite');
            if (File::exists($database)) {
                File::copy($database, $backupPath.'/database.sqlite');
            }
        }
    }

    /**
     * Распаковка архива
     */
    private function extractPackage(string $packagePath, string $extractPath): void
    {
        if (File::exists($extractPath)) {
            File::deleteDirectory($extractPath);
        }
        
        File::ensureDirectoryExists($extractPath);

        $zip = new \ZipArchive;
        if ($zip->open($packagePath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            throw new Exception('Failed to extract update package');
        }
    }

    /**
     * Копирование файлов с исключением критических
     */
    private function copyFiles(string $source, string $destination): void
    {
        $excludePatterns = [
            '.env',
            'config/*.php', // Не перезаписываем конфиги полностью
            'storage/*',
            'bootstrap/cache/*',
        ];

        $files = File::allFiles($source);
        
        foreach ($files as $file) {
            $relativePath = str_replace($source, '', $file->getPathname());
            
            // Пропускаем исключенные файлы
            if ($this->shouldExclude($relativePath, $excludePatterns)) {
                continue;
            }

            $targetPath = $destination . $relativePath;
            File::ensureDirectoryExists(dirname($targetPath));
            File::copy($file->getPathname(), $targetPath);
        }
    }

    /**
     * Проверка на исключение файла
     */
    private function shouldExclude(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (fnmatch('*' . trim($pattern, '*'), $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Проверка контрольной суммы архива обновления.
     */
    private function verifyChecksum(string $packagePath, string $expectedChecksum): void
    {
        $actualChecksum = hash_file('sha256', $packagePath);

        if (! hash_equals(strtolower($expectedChecksum), strtolower($actualChecksum))) {
            throw new Exception('Update package checksum mismatch');
        }
    }

    /**
     * Откат изменений
     */
    private function rollback(string $backupPath): void
    {
        if (!File::exists($backupPath)) {
            return;
        }

        // Восстановление конфигов
        $configBackup = $backupPath . '/config_backup';
        if (File::exists($configBackup)) {
            File::copyDirectory($configBackup, base_path('config'));
        }

        \Log::error('Update rolled back. Check backup at: ' . $backupPath);
    }

    /**
     * Получение версии из пакета
     */
    private function getVersionFromPackage(string $extractPath): string
    {
        $versionFile = $extractPath . '/VERSION';
        if (File::exists($versionFile)) {
            return trim(File::get($versionFile));
        }
        return 'unknown';
    }

    /**
     * Обновление версии в конфиге
     */
    private function updateVersionConfig(string $version): void
    {
        $configPath = config_path('vertex.php');
        if (File::exists($configPath)) {
            $content = File::get($configPath);
            $content = preg_replace(
                "/'version' => '[^']+'/",
                "'version' => '$version'",
                $content
            );
            File::put($configPath, $content);
        }
    }
}
