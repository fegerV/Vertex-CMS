<?php

namespace App\Services\AI;

use App\Models\AiKbDocument;
use App\Models\AiKbChunk;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для обработки документов
 * Отвечает за извлечение текста и разбиение на чанки
 */
class DocumentProcessorService
{
    private int $chunkSize;
    private int $chunkOverlap;

    public function __construct()
    {
        // Настройки по умолчанию
        $this->chunkSize = config('ai.chunk_size', 500);
        $this->chunkOverlap = config('ai.chunk_overlap', 50);
    }

    /**
     * Обработать документ: разбить на чанки и сохранить
     * 
     * @param AiKbDocument $document Документ для обработки
     * @return int Количество созданных чанков
     */
    public function processDocument(AiKbDocument $document): int
    {
        // Удаляем старые чанки документа
        AiKbChunk::where('document_id', $document->id)->delete();

        // Разбиваем контент на чанки
        $chunks = $this->splitIntoChunks($document->content);

        // Сохраняем чанки
        foreach ($chunks as $index => $chunkContent) {
            AiKbChunk::create([
                'document_id' => $document->id,
                'chunk_index' => $index,
                'content' => $chunkContent,
                'token_count' => str_word_count($chunkContent),
            ]);
        }

        // Помечаем документ как обработанный
        $document->update(['is_processed' => true]);

        Log::info("Document {$document->id} processed: " . count($chunks) . " chunks created");

        return count($chunks);
    }

    /**
     * Извлечь текст из файла
     * 
     * @param string $filePath Путь к файлу
     * @return string Извлеченный текст
     * @throws \Exception Если файл не поддерживается или ошибка чтения
     */
    public function extractTextFromFile(string $filePath): string
    {
        // Критическое исправление безопасности: проверка пути на выход за пределы разрешенной директории
        $realPath = realpath($filePath);
        if ($realPath === false) {
            throw new \Exception("File not found: {$filePath}");
        }
        
        // Проверяем что файл находится в разрешенной директории (storage или uploads)
        $allowedPrefixes = [
            realpath(storage_path()),
            realpath(base_path('uploads')),
            realpath(sys_get_temp_dir()),
        ];
        
        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if ($prefix !== false && strpos($realPath, $prefix) === 0) {
                $isAllowed = true;
                break;
            }
        }
        
        if (!$isAllowed) {
            throw new \Exception("Access denied: file is outside allowed directories");
        }
        
        $filePath = $realPath;
        
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = mime_content_type($filePath);

        switch ($extension) {
            case 'txt':
                return $this->extractFromTxt($filePath);
            
            case 'pdf':
                return $this->extractFromPdf($filePath);
            
            case 'doc':
            case 'docx':
                return $this->extractFromDocx($filePath);
            
            case 'md':
            case 'markdown':
                return $this->extractFromTxt($filePath);
            
            case 'html':
            case 'htm':
                return $this->extractFromHtml($filePath);
            
            default:
                // Пробуем прочитать как текст
                return $this->extractFromTxt($filePath);
        }
    }

    /**
     * Извлечь текст из TXT файла
     */
    private function extractFromTxt(string $filePath): string
    {
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            throw new \Exception("Failed to read text file: {$filePath}");
        }

        return trim($content);
    }

    /**
     * Извлечь текст из PDF файла
     * Примечание: требует установленного pdftotext или подобной библиотеки
     */
    private function extractFromPdf(string $filePath): string
    {
        // Проверяем наличие pdftotext
        if (exec('which pdftotext')) {
            // Критическое исправление безопасности: экранирование аргументов командной строки
            // Используем escapeshellarg для полной защиты от инъекций команд
            $escapedFilePath = escapeshellarg($filePath);
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_text_');
            $escapedTempFile = escapeshellarg($tempFile);
            
            // Выполняем команду только с экранированными аргументами
            exec("pdftotext {$escapedFilePath} {$escapedTempFile} 2>/dev/null", $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempFile)) {
                $text = file_get_contents($tempFile);
                unlink($tempFile);
                return trim($text);
            }
        }

        // Fallback: пробуем прочитать как бинарный и извлечь текст
        $content = @file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception("Failed to read PDF file content");
        }
        // Простая эвристика для извлечения текста из PDF
        $text = preg_replace('/[^\x20-\x7E\x0A\x0D]/u', '', $content);
        return trim($text);
    }

    /**
     * Извлечь текст из DOCX файла
     */
    private function extractFromDocx(string $filePath): string
    {
        // DOCX - это ZIP архив с XML файлами
        $zip = new \ZipArchive();
        
        if ($zip->open($filePath) !== true) {
            throw new \Exception("Failed to open DOCX file: {$filePath}");
        }

        // Извлекаем content.xml из архива
        $content = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($content === false) {
            throw new \Exception("Failed to extract document.xml from DOCX");
        }

        // Извлекаем текст из XML
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }

    /**
     * Извлечь текст из HTML файла
     */
    private function extractFromHtml(string $filePath): string
    {
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            throw new \Exception("Failed to read HTML file: {$filePath}");
        }

        // Удаляем HTML теги
        $text = strip_tags($content);
        
        // Нормализуем пробелы
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }

    /**
     * Разбить текст на чанки
     * Использует стратегию перекрывающихся сегментов
     * 
     * @param string $text Текст для разбиения
     * @return array Массив чанков
     */
    private function splitIntoChunks(string $text): array
    {
        $chunks = [];
        $text = trim($text);
        
        if (empty($text)) {
            return $chunks;
        }

        // Сначала пробуем разбить по абзацам
        $paragraphs = preg_split('/\n\s*\n/', $text);
        
        $currentChunk = '';
        
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            
            if (empty($paragraph)) {
                continue;
            }

            // Если абзац больше chunkSize, разбиваем его на предложения
            if (strlen($paragraph) > $this->chunkSize) {
                // Сохраняем текущий чанк если он есть
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                    $currentChunk = '';
                }

                // Разбиваем большой абзац на предложения
                $sentences = preg_split('/(?<=[.!?])\s+/', $paragraph);
                $sentenceChunk = '';

                foreach ($sentences as $sentence) {
                    if (strlen($sentenceChunk . ' ' . $sentence) > $this->chunkSize) {
                        if (!empty($sentenceChunk)) {
                            $chunks[] = trim($sentenceChunk);
                        }
                        $sentenceChunk = $sentence;
                    } else {
                        $sentenceChunk .= ' ' . $sentence;
                    }
                }

                if (!empty($sentenceChunk)) {
                    $chunks[] = trim($sentenceChunk);
                }

                continue;
            }

            // Добавляем абзац к текущему чанку
            if (strlen($currentChunk . "\n\n" . $paragraph) <= $this->chunkSize) {
                $currentChunk .= "\n\n" . $paragraph;
            } else {
                // Сохраняем текущий чанк и начинаем новый
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                }
                $currentChunk = $paragraph;
            }
        }

        // Сохраняем последний чанк
        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        // Применяем overlap между чанками
        if (count($chunks) > 1 && $this->chunkOverlap > 0) {
            $chunks = $this->applyOverlap($chunks);
        }

        return $chunks;
    }

    /**
     * Применить перекрытие между чанками
     */
    private function applyOverlap(array $chunks): array
    {
        $result = [];
        
        for ($i = 0; $i < count($chunks); $i++) {
            $overlapContent = '';
            
            // Добавляем конец предыдущего чанка
            if ($i > 0) {
                $prevChunk = $chunks[$i - 1];
                $overlapStart = max(0, strlen($prevChunk) - $this->chunkOverlap);
                $overlapContent = substr($prevChunk, $overlapStart) . "\n\n";
            }
            
            $result[] = $overlapContent . $chunks[$i];
        }

        return $result;
    }

    /**
     * Переобработать все документы
     * 
     * @return int Количество переобработанных документов
     */
    public function reprocessAllDocuments(): int
    {
        $documents = AiKbDocument::all();
        $count = 0;

        foreach ($documents as $document) {
            $this->processDocument($document);
            $count++;
        }

        return $count;
    }
}
