<?php

namespace App\Console\Commands;

use App\System\Services\BackupService;
use Illuminate\Console\Command;

class BackupRestoreCommand extends Command
{
    protected $signature = 'backup:restore {file?} {--database} {--files}';
    protected $description = 'Восстановить базу данных и/или файлы из резервной копии';

    public function handle(BackupService $backupService): int
    {
        $file = $this->argument('file');

        if (!$file) {
            $backups = $backupService->listBackups();

            if (empty($backups)) {
                $this->error('Резервные копии не найдены.');
                return 1;
            }

            $this->table(['Имя', 'Тип', 'Размер', 'Дата создания'], array_map(function ($backup) {
                return [
                    $backup['name'],
                    $backup['type'],
                    $this->formatSize($backup['size']),
                    date('Y-m-d H:i:s', $backup['created_at']),
                ];
            }, $backups));

            $file = $this->choice('Выберите файл для восстановления', array_column($backups, 'name'));
        }

        $restoreDatabase = $this->option('database') || !$this->option('files');
        $restoreFiles = $this->option('files') || !$this->option('database');

        $backupType = str_starts_with($file, 'db_') ? 'database' : 'files';

        if ($restoreDatabase && $backupType === 'database') {
            if (!$this->confirm('Вы уверены, что хотите восстановить базу данных? Это перезапишет текущие данные.')) {
                return 0;
            }

            try {
                $this->info('Восстановление базы данных...');
                $backupService->restoreDatabase($file);
                $this->line("✓ База данных восстановлена из: {$file}");
            } catch (\Exception $e) {
                $this->error("Ошибка восстановления БД: {$e->getMessage()}");
                return 1;
            }
        } elseif ($restoreDatabase && $backupType === 'files') {
            $this->warn('Выбранный файл содержит резервную копию файлов, а не базы данных.');
            $restoreDatabase = false;
        }

        if ($restoreFiles && $backupType === 'files') {
            if (!$this->confirm('Вы уверены, что хотите восстановить файлы? Это может перезаписать текущие файлы.')) {
                return 0;
            }

            try {
                $this->info('Восстановление файлов...');
                $this->warn('Восстановление файлов требует ручной распаковки архива.');
                $this->line("Архив доступен в хранилище: {$file}");
            } catch (\Exception $e) {
                $this->error("Ошибка восстановления файлов: {$e->getMessage()}");
                return 1;
            }
        } elseif ($restoreFiles && $backupType === 'database') {
            $this->warn('Выбранный файл содержит резервную копию базы данных, а не файлов.');
            $restoreFiles = false;
        }

        $this->newLine();
        $this->info('Восстановление завершено!');

        return 0;
    }

    protected function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
