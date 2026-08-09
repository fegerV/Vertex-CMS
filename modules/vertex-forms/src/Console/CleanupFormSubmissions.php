<?php

namespace Vertex\Forms\Console;

use Illuminate\Console\Command;
use Vertex\Forms\Services\FormSubmissionRetentionService;

class CleanupFormSubmissions extends Command
{
    protected $signature = 'forms:cleanup-submissions';

    protected $description = 'Delete expired form submissions and their private uploads';

    public function handle(FormSubmissionRetentionService $retention): int
    {
        $deleted = $retention->cleanup();
        $this->info("Deleted {$deleted} expired form submissions.");

        return self::SUCCESS;
    }
}
