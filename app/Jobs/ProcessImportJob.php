<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(
        public string $filePath,
        public string $type,
        public ?int $userId = null,
    ) {}

    public function handle(): void
    {
        if (!Storage::exists($this->filePath)) {
            throw new \Exception("File not found: {$this->filePath}");
        }

        $content = Storage::get($this->filePath);

        match ($this->type) {
            'csv' => $this->processCsv($content),
            'xml' => $this->processXml($content),
            'json' => $this->processJson($content),
            default => throw new \Exception("Unsupported type: {$this->type}"),
        };

        Storage::delete($this->filePath);
    }

    protected function processCsv(string $content): void
    {
        $lines = explode("\n", $content);
        $headers = str_getcsv(array_shift($lines));

        foreach ($lines as $line) {
            $row = str_getcsv($line);
            $data = array_combine($headers, $row);
            // Process each row
        }
    }

    protected function processXml(string $content): void
    {
        $xml = simplexml_load_string($content);
        // Process XML data
    }

    protected function processJson(string $content): void
    {
        $data = json_decode($content, true);
        // Process JSON data
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error("Import failed for {$this->filePath}: " . $exception->getMessage());
        
        if ($this->userId) {
            // Notify user about failure
        }
    }
}
