<?php

namespace App\Vertex\Security\Modules\Scanner;

use App\Models\Media;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ScannerService
{
    public function getStatus(): array
    {
        if (! config('security.modules.scanner', false)) {
            return [
                'enabled' => false,
                'status' => 'disabled',
                'summary' => 'Фоновый Scanner отключен в конфигурации.',
                'is_stale' => false,
                'last_scanned_at' => null,
                'scanned_files' => 0,
                'counts' => [
                    'total' => 0,
                    'danger' => 0,
                    'warning' => 0,
                ],
                'findings' => [],
            ];
        }

        $report = $this->readJson($this->reportPath());
        if ($report === null) {
            return [
                'enabled' => true,
                'status' => 'not_scanned',
                'summary' => 'Scanner еще не запускался. Нет ни одного сохраненного отчета.',
                'is_stale' => false,
                'last_scanned_at' => null,
                'scanned_files' => 0,
                'counts' => [
                    'total' => 0,
                    'danger' => 0,
                    'warning' => 0,
                ],
                'findings' => [],
            ];
        }

        $lastScannedAt = Arr::get($report, 'scanned_at');
        $findings = Arr::get($report, 'findings', []);
        $isStale = $this->isStale($lastScannedAt);
        $hasIssues = count($findings) > 0;

        return [
            'enabled' => true,
            'status' => $hasIssues ? 'issues_detected' : 'clean',
            'summary' => $hasIssues
                ? 'Scanner нашел подозрительные файлы или аномалии в uploads/media.'
                : ($isStale ? 'Последний отчет Scanner устарел и требует повторного прогона.' : 'Подозрительных файлов по последнему отчету не найдено.'),
            'is_stale' => $isStale,
            'last_scanned_at' => $lastScannedAt,
            'scanned_files' => (int) Arr::get($report, 'scanned_files', 0),
            'counts' => [
                'total' => count($findings),
                'danger' => (int) Arr::get($report, 'counts.danger', 0),
                'warning' => (int) Arr::get($report, 'counts.warning', 0),
            ],
            'findings' => array_slice($findings, 0, 12),
        ];
    }

    public function runScan(): array
    {
        $findings = [];
        $scannedFiles = 0;

        foreach ((array) config('security.scanner.paths', []) as $path) {
            $absolutePath = $this->resolvePath((string) $path);

            if (! is_dir($absolutePath)) {
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

                $scannedFiles++;
                $realPath = $file->getPathname();
                $relativePath = $this->normalizeRelativePath($realPath);
                $filename = strtolower($file->getFilename());
                $extension = strtolower((string) pathinfo($realPath, PATHINFO_EXTENSION));

                if ($this->shouldIgnoreFilename($filename)) {
                    continue;
                }

                if (in_array($extension, $this->executableExtensions(), true)) {
                    $findings[] = $this->makeFinding(
                        'executable-upload',
                        'danger',
                        $relativePath,
                        'В публичной директории найден исполняемый файл.',
                        ['extension' => $extension]
                    );
                    continue;
                }

                if ($extension !== '' && ! in_array($extension, $this->allowedExtensions(), true)) {
                    $findings[] = $this->makeFinding(
                        'unexpected-extension',
                        'warning',
                        $relativePath,
                        'В uploads найден файл с нетипичным расширением вне списка разрешенных.',
                        ['extension' => $extension]
                    );
                }

                if ($extension === 'svg') {
                    $finding = $this->scanSvgFile($realPath, $relativePath);
                    if ($finding !== null) {
                        $findings[] = $finding;
                    }
                }
            }
        }

        foreach ($this->missingMediaFiles() as $finding) {
            $findings[] = $finding;
        }

        $counts = [
            'danger' => collect($findings)->where('severity', 'danger')->count(),
            'warning' => collect($findings)->where('severity', 'warning')->count(),
        ];

        $report = [
            'scanned_at' => now()->toIso8601String(),
            'scanned_files' => $scannedFiles,
            'counts' => $counts,
            'findings' => $findings,
        ];

        $this->writeJson($this->reportPath(), $report);

        return $this->getStatus();
    }

    private function scanSvgFile(string $absolutePath, string $relativePath): ?array
    {
        $size = @filesize($absolutePath);
        $maxBytes = max(1, (int) config('security.scanner.max_file_size_kb', 2048)) * 1024;

        if ($size === false || $size > $maxBytes || ! is_readable($absolutePath)) {
            return null;
        }

        $contents = strtolower((string) file_get_contents($absolutePath));

        foreach ((array) config('security.scanner.suspicious_svg_tokens', []) as $token) {
            $needle = strtolower((string) $token);
            if ($needle !== '' && str_contains($contents, $needle)) {
                return $this->makeFinding(
                    'suspicious-svg',
                    'danger',
                    $relativePath,
                    'SVG содержит потенциально опасный токен.',
                    ['token' => $needle]
                );
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function missingMediaFiles(): array
    {
        try {
            return Media::query()
                ->get(['id', 'path', 'original_filename'])
                ->filter(function (Media $media): bool {
                    return filled($media->path) && ! is_file(public_path($media->path));
                })
                ->map(fn (Media $media): array => $this->makeFinding(
                    'missing-media-file',
                    'warning',
                    (string) $media->path,
                    'Файл присутствует в медиатеке, но отсутствует на диске.',
                    [
                        'media_id' => (string) $media->id,
                        'original_filename' => (string) $media->original_filename,
                    ]
                ))
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, string>  $meta
     * @return array<string, mixed>
     */
    private function makeFinding(string $type, string $severity, string $path, string $message, array $meta = []): array
    {
        return [
            'type' => $type,
            'severity' => $severity,
            'path' => $path,
            'message' => $message,
            'meta' => $meta,
        ];
    }

    private function isStale(?string $value): bool
    {
        if (! $value) {
            return false;
        }

        return now()->subHours((int) config('security.scanner.stale_after_hours', 24))
            ->gt(\Illuminate\Support\Carbon::parse($value));
    }

    /**
     * @return array<int, string>
     */
    private function allowedExtensions(): array
    {
        return array_map('strtolower', (array) config('vertex.uploads.allowed_extensions', []));
    }

    /**
     * @return array<int, string>
     */
    private function executableExtensions(): array
    {
        return array_map('strtolower', (array) config('security.scanner.executable_extensions', []));
    }

    private function shouldIgnoreFilename(string $filename): bool
    {
        return in_array($filename, array_map('strtolower', (array) config('security.scanner.ignored_filenames', [])), true);
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

    private function reportPath(): string
    {
        return (string) config('security.scanner.report_path');
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

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
