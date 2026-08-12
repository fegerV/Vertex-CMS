<?php

namespace App\Services\Ai;

use App\Models\AiKbChunk;
use App\Models\AiKbDocument;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для обработки документов и разбиения на чанки
 */
class DocumentProcessorService
{
    /**
     * Максимальный размер чанка в символах
     */
    private int $maxChunkSize = 500;

    /**
     * Перекрытие между чанками (символы)
     */
    private int $chunkOverlap = 50;

    /**
     * Обработать документ и разбить на чанки
     */
    public function processDocument(AiKbDocument $document): int
    {
        // Очищаем старые чанки
        $document->chunks()->delete();

        $content = strip_tags($document->content);
        $content = preg_replace('/\s+/', ' ', $content); // Нормализация пробелов

        $chunks = $this->splitIntoChunks($content);

        foreach ($chunks as $index => $chunkContent) {
            AiKbChunk::create([
                'document_id' => $document->id,
                'content' => trim($chunkContent),
                'chunk_order' => $index,
                'metadata' => [
                    'title' => $document->title,
                    'category' => $document->category?->name,
                ],
            ]);
        }

        $document->update(['is_processed' => true]);

        Log::info("Докукт '{$document->title}' обработан. Создано чанков: ".count($chunks));

        return count($chunks);
    }

    /**
     * Разбить текст на чанки с перекрытием
     */
    private function splitIntoChunks(string $text): array
    {
        $chunks = [];
        $start = 0;
        $length = strlen($text);

        while ($start < $length) {
            // Определяем конец текущего чанка
            $end = min($start + $this->maxChunkSize, $length);

            // Если это не последний чанк, пытаемся разбить по слову или предложению
            if ($end < $length) {
                // Ищем ближайший пробел или точку
                $breakPoint = $this->findBreakPoint($text, $end);
                if ($breakPoint > $start) {
                    $end = $breakPoint;
                }
            }

            $chunk = substr($text, $start, $end - $start);

            if (trim($chunk)) {
                $chunks[] = $chunk;
            }

            // Следующий чанк начинается с учетом перекрытия
            $start = $end - $this->chunkOverlap;

            // Защита от бесконечного цикла
            if ($start >= $length) {
                break;
            }
        }

        return $chunks;
    }

    /**
     * Найти удобную точку разрыва (пробел, точка, новая строка)
     */
    private function findBreakPoint(string $text, int $position): int
    {
        // Ищем точку разрыва в пределах 100 символов до позиции
        $searchStart = max(0, $position - 100);
        $searchText = substr($text, $searchStart, $position - $searchStart);

        // Приоритеты разрыва: новая строка, точка, пробел
        $patterns = ["\n", '. ', ' '];

        foreach ($patterns as $pattern) {
            $lastPos = strrpos($searchText, $pattern);
            if ($lastPos !== false) {
                return $searchStart + $lastPos + strlen($pattern);
            }
        }

        return $position;
    }

    /**
     * Извлечь текст из файла (PDF, DOCX, TXT)
     */
    public function extractTextFromFile(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'txt':
                return file_get_contents($filePath);

            case 'pdf':
                return $this->extractFromPdf($filePath);

            case 'docx':
                return $this->extractFromDocx($filePath);

            default:
                throw new \Exception("Неподдерживаемый формат файла: {$extension}");
        }
    }

    /**
     * Извлечение текста из PDF (требует установки pdftotext или библиотеки)
     */
    private function extractFromPdf(string $filePath): string
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            throw new \RuntimeException("PDF файл недоступен: {$filePath}");
        }

        $binary = trim((string) shell_exec('command -v pdftotext 2>/dev/null'));
        if ($binary === '') {
            throw new \RuntimeException('Для извлечения PDF установите утилиту pdftotext.');
        }

        $command = escapeshellarg($binary).' -enc UTF-8 '.escapeshellarg($filePath).' - 2>&1';
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException('Не удалось извлечь текст из PDF: '.implode("\n", $output));
        }

        return trim(implode("\n", $output));
    }

    /**
     * Извлечение текста из DOCX
     */
    private function extractFromDocx(string $filePath): string
    {
        // В продакшене использовать PhpOffice\PhpWord
        // Для примера упрощенное извлечение (DOCX это ZIP архив)
        $content = '';
        $zip = new \ZipArchive;

        if ($zip->open($filePath) === true) {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml !== false) {
                $xml = str_replace(['</w:p>', '</w:tr>', '<w:tab/>'], ["\n", "\n", "\t"], $xml);
                $content = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
            }
            $zip->close();
        }

        if ($content === '') {
            throw new \RuntimeException("Не удалось извлечь текст из DOCX: {$filePath}");
        }

        return trim($content);
    }
}
