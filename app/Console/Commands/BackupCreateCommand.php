<?php

namespace App\Console\Commands;

use App\System\Services\BackupService;
use Illuminate\Console\Command;

class BackupCreateCommand extends Command
{
    protected $signature = 'backup:create {--database} {--files}';
    protected $description = 'Создать резервную копию базы данных и/или файлов';

    public function handle(BackupService $backupService): int
    {
        $createDatabase = $this->option('database') || !$this->option('files');
        $createFiles = $this->option('files') || !$this->option('database');

        if ($createDatabase) {
            try {
                $this->info('Создание резервной копии базы данных...');
                $dbFile = $backupService->createDatabaseBackup();
                $this->line("✓ База данных сохранена: {$dbFile}");
            } catch (\Exception $e) {
                $this->error("Ошибка создания бэкапа БД: {$e->getMessage()}");
                return 1;
            }
        }

        if ($createFiles) {
            try {
                $this->info('Создание резервной копии файлов...');
                $filesFile = $backupService->createFilesBackup();
                $this->line("✓ Файлы сохранены: {$filesFile}");
            } catch (\Exception $e) {
                $this->error("Ошибка создания бэкапа файлов: {$e->getMessage()}");
                return 1;
            }
        }

        $this->newLine();
        $this->info('Резервное копирование завершено успешно!');

        return 0;
    }
}
