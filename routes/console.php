<?php

use App\System\Services\BackupService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('vertex:about', function (): void {
    $this->comment('VertexCMS MVP v0.1 skeleton is installed.');
});

Schedule::call(function () {
    $backupService = app(BackupService::class);
    
    try {
        $dbFile = $backupService->createDatabaseBackup();
        $this->info("Database backup created: {$dbFile}");
    } catch (\Exception $e) {
        $this->error("Database backup failed: {$e->getMessage()}");
    }
    
    try {
        $filesFile = $backupService->createFilesBackup();
        $this->info("Files backup created: {$filesFile}");
    } catch (\Exception $e) {
        $this->error("Files backup failed: {$e->getMessage()}");
    }
    
    $deleted = $backupService->cleanupOldBackups(30);
    if ($deleted > 0) {
        $this->info("Cleaned up {$deleted} old backups.");
    }
})->name('vertex:daily-backup')->daily()->at('03:00')->withoutOverlapping();

Schedule::command('forms:cleanup-submissions')
    ->daily()
    ->at('02:30')
    ->withoutOverlapping();
