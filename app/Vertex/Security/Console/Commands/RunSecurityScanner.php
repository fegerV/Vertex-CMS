<?php

namespace App\Vertex\Security\Console\Commands;

use App\System\Services\ActivityLogService;
use App\Vertex\Security\Modules\Scanner\ScannerService;
use Illuminate\Console\Command;

class RunSecurityScanner extends Command
{
    protected $signature = 'security:scanner:run {--force : Run even if the scanner module is disabled}';

    protected $description = 'Run the background security scanner and persist the latest report';

    public function handle(ScannerService $scanner, ActivityLogService $activityLog): int
    {
        if (! config('security.modules.scanner', false) && ! $this->option('force')) {
            $this->warn('Scanner module is disabled in configuration.');

            return self::SUCCESS;
        }

        $status = $scanner->runScan();

        $activityLog->record(
            'security.scanner.scan',
            'security_scanner',
            null,
            'Background security scanner completed.',
            [
                'status' => $status['status'] ?? 'unknown',
                'scanned_files' => $status['scanned_files'] ?? 0,
                'issues_total' => $status['counts']['total'] ?? 0,
                'danger' => $status['counts']['danger'] ?? 0,
                'warning' => $status['counts']['warning'] ?? 0,
            ]
        );

        $this->info('Scanner completed.');
        $this->line('Status: '.($status['status'] ?? 'unknown'));
        $this->line('Scanned files: '.($status['scanned_files'] ?? 0));
        $this->line('Findings: '.($status['counts']['total'] ?? 0));

        return self::SUCCESS;
    }
}
