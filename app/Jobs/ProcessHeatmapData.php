<?php

namespace App\Jobs;

use App\Models\Analytics\Heatmap;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessHeatmapData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $url,
        public string $type,
        public array $points,
        public int $viewportWidth,
        public int $viewportHeight
    ) {}

    public function handle(AnalyticsService $analyticsService): void
    {
        $analyticsService->recordHeatmapData(
            $this->url,
            $this->type,
            $this->points,
            $this->viewportWidth,
            $this->viewportHeight
        );
    }
}
