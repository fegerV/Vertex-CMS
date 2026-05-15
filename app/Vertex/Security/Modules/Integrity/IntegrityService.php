<?php

namespace App\Vertex\Security\Modules\Integrity;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IntegrityService
{
    public function getStatus(): array
    {
        if (! config('security.modules.integrity', false)) {
            return [
                'enabled' => false,
                'status' => 'disabled',
                'summary' => 'Модуль контроля целостности отключен в конфигурации.',
                'baseline_exists' => false,
                'tracked_files' => 0,
                'changed_count' => 0,
                'added_count' => 0,
                'removed_count' => 0,
                'last_scanned_at' => null,
                'baseline_created_at' => null,
                'recent_changes' => [],
            ];
        }

        $baseline = $this->readJson($this->baselinePath());
        $report = $this->readJson($this->reportPath());

        if ($baseline === null) {
            return [
                'enabled' => true,
                'status' => 'not_initialized',
                'summary' => 'Baseline еще не инициализирован. Сначала сохраните эталонное состояние файлов.',
                'baseline_exists' => false,
                'tracked_files' => 0,
                'changed_count' => 0,
                'added_count' => 0,
                'removed_count' => 0,
                'last_scanned_at' => Arr::get($report, 'scanned_at'),
                'baseline_created_at' => null,
                'recent_changes' => [],
            ];
        }

        $trackedFiles = count(Arr::get($baseline, 'files', []));
        $changedCount = count(Arr::get($report, 'changed', []));
        $addedCount = count(Arr::get($report, 'added', []));
        $removedCount = count(Arr::get($report, 'removed', []));
        $hasDrift = $changedCount > 0 || $addedCount > 0 || $removedCount > 0;

        return [
            'enabled' => true,
            'status' => $report === null
                ? 'baseline_ready'
                : ($hasDrift ? 'drift_detected' : 'clean'),
            'summary' => $report === null
                ? 'Baseline сохранен, но сканирование еще не запускалось.'
                : ($hasDrift ? 'Обнаружены изменения относительно сохраненного baseline.' : 'Отклонений от baseline не найдено.'),
            'baseline_exists' => true,
            'tracked_files' => $trackedFiles,
            'changed_count' => $changedCount,
            'added_count' => $addedCount,
            'removed_count' => $removedCount,
            'last_scanned_at' => Arr::get($report, 'scanned_at'),
            'baseline_created_at' => Arr::get($baseline, 'generated_at'),
            'recent_changes' => array_slice(array_merge(
                array_map(fn (array $item) => ['type' => 'changed'] + $item, Arr::get($report, 'changed', [])),
                array_map(fn (array $item) => ['type' => 'added'] + $item, Arr::get($report, 'added', [])),
                array_map(fn (array $item) => ['type' => 'removed'] + $item, Arr::get($report, 'removed', [])),
            ), 0, 10),
        ];
    }

    public function initializeBaseline(): array
    {
        $snapshot = $this->collectSnapshot();
        $baseline = [
            'generated_at' => now()->toIso8601String(),
            'files' => $snapshot,
        ];

        $this->writeJson($this->baselinePath(), $baseline);

        $report = [
            'scanned_at' => now()->toIso8601String(),
            'baseline_created_at' => $baseline['generated_at'],
            'tracked_files' => count($snapshot),
            'changed' => [],
            'added' => [],
            'removed' => [],
        ];

        $this->writeJson($this->reportPath(), $report);

        return $this->getStatus();
    }

    public function runScan(): array
    {
        $baseline = $this->readJson($this->baselinePath());
        if ($baseline === null) {
            return $this->getStatus();
        }

        $current = $this->collectSnapshot();
        $baselineFiles = Arr::get($baseline, 'files', []);

        $changed = [];
        $added = [];
        $removed = [];

        foreach ($current as $path => $meta) {
            if (! array_key_exists($path, $baselineFiles)) {
                $added[] = ['path' => $path, 'size' => $meta['size']];
                continue;
            }

            $original = $baselineFiles[$path];
            if (($original['hash'] ?? null) !== ($meta['hash'] ?? null)) {
                $changed[] = [
                    'path' => $path,
                    'size' => $meta['size'],
                    'modified_at' => $meta['modified_at'],
                ];
            }
        }

        foreach ($baselineFiles as $path => $meta) {
            if (! array_key_exists($path, $current)) {
                $removed[] = ['path' => $path, 'size' => $meta['size'] ?? 0];
            }
        }

        $report = [
            'scanned_at' => now()->toIso8601String(),
            'baseline_created_at' => Arr::get($baseline, 'generated_at'),
            'tracked_files' => count($current),
            'changed' => $changed,
            'added' => $added,
            'removed' => $removed,
        ];

        $this->writeJson($this->reportPath(), $report);

        return $this->getStatus();
    }

    /**
     * @return array<string, array{hash:string,size:int,modified_at:string}>
     */
    private function collectSnapshot(): array
    {
        $snapshot = [];
        $maxBytes = max(1, (int) config('security.integrity.max_file_size_kb', 5120)) * 1024;

        foreach (config('security.integrity.tracked_paths', []) as $path) {
            $absolutePath = $this->resolvePath($path);

            if (! file_exists($absolutePath)) {
                continue;
            }

            if (is_file($absolutePath)) {
                $item = $this->snapshotFile($absolutePath, $maxBytes);
                if ($item !== null) {
                    $snapshot[$this->normalizeRelativePath($absolutePath)] = $item;
                }
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolutePath, \FilesystemIterator::SKIP_DOTS)
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $realPath = $file->getPathname();
                $relative = $this->normalizeRelativePath($realPath);

                if ($this->shouldExclude($relative)) {
                    continue;
                }

                $item = $this->snapshotFile($realPath, $maxBytes);
                if ($item !== null) {
                    $snapshot[$relative] = $item;
                }
            }
        }

        ksort($snapshot);

        return $snapshot;
    }

    private function snapshotFile(string $absolutePath, int $maxBytes): ?array
    {
        if (! is_readable($absolutePath)) {
            return null;
        }

        $size = filesize($absolutePath);
        if ($size === false || $size > $maxBytes) {
            return null;
        }

        $hash = hash_file('sha256', $absolutePath);
        if ($hash === false) {
            return null;
        }

        return [
            'hash' => $hash,
            'size' => (int) $size,
            'modified_at' => date(DATE_ATOM, filemtime($absolutePath) ?: time()),
        ];
    }

    private function shouldExclude(string $relativePath): bool
    {
        $normalized = str_replace('\\', '/', ltrim($relativePath, '/'));

        foreach (config('security.integrity.excluded_paths', []) as $excluded) {
            $excluded = trim(str_replace('\\', '/', (string) $excluded), '/');
            if ($excluded !== '' && ($normalized === $excluded || Str::startsWith($normalized, $excluded.'/'))) {
                return true;
            }
        }

        return false;
    }

    private function resolvePath(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return base_path($path);
    }

    private function normalizeRelativePath(string $absolutePath): string
    {
        $base = str_replace('\\', '/', base_path());
        $path = str_replace('\\', '/', $absolutePath);

        return ltrim(Str::after($path, $base), '/');
    }

    private function isAbsolutePath(string $path): bool
    {
        return Str::startsWith($path, ['/']) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
    }

    private function baselinePath(): string
    {
        return (string) config('security.integrity.baseline_path');
    }

    private function reportPath(): string
    {
        return (string) config('security.integrity.report_path');
    }

    private function readJson(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function writeJson(string $path, array $payload): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
