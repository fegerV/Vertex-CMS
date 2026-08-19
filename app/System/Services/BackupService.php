<?php

namespace App\System\Services;

use App\System\Support\BackupHookRegistry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupService
{
    protected string $disk;

    protected string $backupPath;

    public function __construct(private readonly BackupHookRegistry $hooks)
    {
        $this->disk = config('vertex.backup.schedule.storage', config('filesystems.default', 'local'));
        $this->backupPath = 'backups';
    }

    public function createDatabaseBackup(): string
    {
        return $this->runWithHooks('database', fn (): string => $this->performDatabaseBackup());
    }

    private function performDatabaseBackup(): string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $filename = "db_{$connection}_".date('Y-m-d_His').'.sql';
        $filepath = storage_path("app/{$this->backupPath}/{$filename}");

        $command = match ($config['driver']) {
            'pgsql' => sprintf(
                'PGPASSWORD=%s pg_dump -h %s -U %s %s > %s',
                escapeshellarg($config['password'] ?? ''),
                escapeshellarg($config['host'] ?? 'localhost'),
                escapeshellarg($config['username'] ?? ''),
                escapeshellarg($config['database']),
                escapeshellarg($filepath)
            ),
            'mysql' => sprintf(
                'mysqldump -h %s -u %s %s %s > %s',
                escapeshellarg($config['host'] ?? 'localhost'),
                escapeshellarg($config['username'] ?? ''),
                empty($config['password']) ? '' : sprintf('-p%s', escapeshellarg($config['password'])),
                escapeshellarg($config['database']),
                escapeshellarg($filepath)
            ),
            'sqlite' => sprintf(
                'cp %s %s',
                escapeshellarg(database_path($config['database'])),
                escapeshellarg($filepath)
            ),
            default => throw new \Exception("Unsupported database driver: {$config['driver']}"),
        };

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \Exception("Database backup failed with exit code {$exitCode}");
        }

        Storage::disk($this->disk)->put(
            "{$this->backupPath}/{$filename}",
            file_get_contents($filepath)
        );

        unlink($filepath);

        Log::info("Database backup created: {$filename}");

        return $filename;
    }

    public function createFilesBackup(): string
    {
        return $this->runWithHooks('files', fn (): string => $this->performFilesBackup());
    }

    private function performFilesBackup(): string
    {
        $filename = 'files_'.date('Y-m-d_His').'.zip';
        $filepath = storage_path("app/{$this->backupPath}/{$filename}");

        $zip = new \ZipArchive;
        if ($zip->open($filepath, \ZipArchive::CREATE) !== true) {
            throw new \Exception('Cannot create ZIP archive');
        }

        $directoriesToBackup = [
            public_path('uploads'),
            storage_path('app'),
        ];

        foreach ($directoriesToBackup as $dir) {
            if (is_dir($dir)) {
                $this->addDirToZip($zip, $dir, basename($dir));
            }
        }

        $zip->close();

        Storage::disk($this->disk)->put(
            "{$this->backupPath}/{$filename}",
            file_get_contents($filepath)
        );

        unlink($filepath);

        Log::info("Files backup created: {$filename}");

        return $filename;
    }

    private function runWithHooks(string $type, callable $operation): string
    {
        $context = ['disk' => $this->disk, 'started_at' => now()->toISOString()];
        $this->hooks->before($type, $context);

        try {
            $filename = $operation();
            $this->hooks->after($type, $filename, $context);

            return $filename;
        } catch (Throwable $exception) {
            $this->hooks->failed($type, $exception, $context);
            throw $exception;
        }
    }

    protected function addDirToZip(\ZipArchive $zip, string $dir, string $prefix = ''): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (! $file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $prefix.'/'.substr($filePath, strlen($dir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    public function restoreDatabase(string $backupFile): void
    {
        $content = Storage::disk($this->disk)->get("{$this->backupPath}/{$backupFile}");
        $tempFile = tempnam(sys_get_temp_dir(), 'db_restore_');
        file_put_contents($tempFile, $content);

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        // FIX C01: Use environment variable for password instead of command line argument
        // to prevent shell injection attacks
        $command = match ($config['driver']) {
            'pgsql' => sprintf(
                'PGPASSWORD=%s psql -h %s -U %s %s < %s',
                escapeshellarg($config['password'] ?? ''),
                escapeshellarg($config['host'] ?? 'localhost'),
                escapeshellarg($config['username'] ?? ''),
                escapeshellarg($config['database']),
                escapeshellarg($tempFile)
            ),
            'mysql' => $this->createMysqlRestoreCommand($config, $tempFile),
            default => throw new \Exception("Unsupported database driver: {$config['driver']}"),
        };

        exec($command, $output, $exitCode);
        unlink($tempFile);

        if ($exitCode !== 0) {
            throw new \Exception("Database restore failed with exit code {$exitCode}");
        }

        Log::info("Database restored from: {$backupFile}");
    }

    /**
     * Create secure MySQL restore command using option file instead of command line
     * to prevent SQL injection via password parameter
     */
    private function createMysqlRestoreCommand(array $config, string $tempFile): string
    {
        $host = escapeshellarg($config['host'] ?? 'localhost');
        $user = escapeshellarg($config['username'] ?? '');
        $database = escapeshellarg($config['database']);
        $file = escapeshellarg($tempFile);
        
        // Create temporary option file to avoid passing password via command line
        $optionFile = tempnam(sys_get_temp_dir(), 'mysql_option_');
        $password = $config['password'] ?? '';
        
        // Write credentials to option file with restricted permissions
        $optionContent = "[client]\n";
        if (!empty($password)) {
            $optionContent .= "password={$password}\n";
        }
        file_put_contents($optionFile, $optionContent);
        chmod($optionFile, 0600);
        
        $command = sprintf(
            'mysql --defaults-extra-file=%s -h %s -u %s %s < %s',
            escapeshellarg($optionFile),
            $host,
            $user,
            $database,
            $file
        );
        
        // Clean up option file after command execution (will be done after exec)
        // We need to delete it manually since exec doesn't support cleanup callbacks
        register_shutdown_function(function() use ($optionFile) {
            if (file_exists($optionFile)) {
                unlink($optionFile);
            }
        });
        
        return $command;
    }

    public function listBackups(): array
    {
        $files = Storage::disk($this->disk)->files($this->backupPath);

        return collect($files)->map(function ($file) {
            return [
                'name' => basename($file),
                'size' => Storage::disk($this->disk)->size($file),
                'created_at' => Storage::disk($this->disk)->lastModified($file),
                'type' => str_starts_with(basename($file), 'db_') ? 'database' : 'files',
            ];
        })->sortByDesc('created_at')->values()->toArray();
    }

    public function deleteBackup(string $filename): void
    {
        Storage::disk($this->disk)->delete("{$this->backupPath}/{$filename}");
        Log::info("Backup deleted: {$filename}");
    }

    public function cleanupOldBackups(int $daysToKeep = 30): int
    {
        $deleted = 0;
        $cutoff = now()->subDays($daysToKeep)->timestamp;

        foreach ($this->listBackups() as $backup) {
            if ($backup['created_at'] < $cutoff) {
                $this->deleteBackup($backup['name']);
                $deleted++;
            }
        }

        return $deleted;
    }
}
