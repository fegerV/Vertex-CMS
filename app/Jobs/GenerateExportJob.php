<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(
        public string $entityType,
        public array $filters = [],
        public ?int $userId = null,
        public string $format = 'csv',
    ) {}

    public function handle(): void
    {
        $data = $this->fetchData();

        $content = match ($this->format) {
            'csv' => $this->generateCsv($data),
            'json' => json_encode($data, JSON_PRETTY_PRINT),
            'xml' => $this->generateXml($data),
            default => throw new \Exception("Unsupported format: {$this->format}"),
        };

        $filename = "exports/{$this->entityType}_export_" . date('Y-m-d_His') . ".{$this->format}";
        Storage::put($filename, $content);

        if ($this->userId) {
            // Notify user about completed export with download link
        }
    }

    protected function fetchData(): array
    {
        return match ($this->entityType) {
            'pages' => $this->fetchPages(),
            'users' => $this->fetchUsers(),
            'media' => $this->fetchMedia(),
            default => [],
        };
    }

    protected function fetchPages(): array
    {
        return \App\Models\Page::query()
            ->when(!empty($this->filters['status']), fn ($q) => $q->where('status', $this->filters['status']))
            ->get()
            ->toArray();
    }

    protected function fetchUsers(): array
    {
        return \App\Models\User::query()->get()->toArray();
    }

    protected function fetchMedia(): array
    {
        return \App\Models\Media::query()->get()->toArray();
    }

    protected function generateCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        fputcsv($output, array_keys(reset($data)));

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    protected function generateXml(array $data): string
    {
        $xml = new \SimpleXMLElement('<root/>');
        foreach ($data as $item) {
            $node = $xml->addChild('item');
            foreach ($item as $key => $value) {
                $node->addChild($key, htmlspecialchars((string) $value));
            }
        }
        return $xml->asXML();
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error("Export failed for {$this->entityType}: " . $exception->getMessage());
    }
}
