<?php

namespace App\Services\AI;

use App\Models\AiKbChunk;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с эмбеддингами (векторными представлениями)
 * Отвечает за генерацию эмбеддингов и векторный поиск
 */
class EmbeddingService
{
    private string $apiKey;
    private string $embeddingApiUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->embeddingApiUrl = 'https://api.openai.com/v1/embeddings';
        $this->model = 'text-embedding-ada-002';
    }

    /**
     * Проверка доступности сервиса эмбеддингов
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Сгенерировать эмбеддинг для текста
     * 
     * @param string $text Текст для векторизации
     * @return array Векторное представление (массив float)
     */
    public function generateEmbedding(string $text): array
    {
        if (!$this->isAvailable()) {
            Log::warning('Embedding service unavailable: OpenAI API key not configured');
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post($this->embeddingApiUrl, [
                'model' => $this->model,
                'input' => $text,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'][0]['embedding'] ?? [];
            }

            Log::error('Embedding API error: ' . $response->body());
            return [];

        } catch (\Exception $e) {
            Log::error('Embedding generation exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Сгенерировать эмбеддинги для нескольких текстов
     * 
     * @param array $texts Массив текстов
     * @return array Массив эмбеддингов
     */
    public function generateEmbeddings(array $texts): array
    {
        if (!$this->isAvailable()) {
            Log::warning('Embedding service unavailable: OpenAI API key not configured');
            return [];
        }

        // OpenAI поддерживает батчи до 2048 текстов
        $batchSize = 100;
        $allEmbeddings = [];

        foreach (array_chunk($texts, $batchSize) as $batch) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])->post($this->embeddingApiUrl, [
                    'model' => $this->model,
                    'input' => $batch,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    foreach ($data['data'] as $item) {
                        $allEmbeddings[] = $item['embedding'];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Batch embedding error: ' . $e->getMessage());
            }
        }

        return $allEmbeddings;
    }

    /**
     * Найти релевантные чанки по запросу
     * Использует косинусное сходство для поиска
     * 
     * @param string $query Поисковый запрос
     * @param int $limit Максимальное количество результатов
     * @return array Массив найденных чанков с相似度
     */
    public function findRelevantChunks(string $query, int $limit = 5): array
    {
        // Генерируем эмбеддинг для запроса
        $queryEmbedding = $this->generateEmbedding($query);
        
        if (empty($queryEmbedding)) {
            Log::warning('Cannot find relevant chunks: query embedding is empty');
            return [];
        }

        // Получаем все чанки из базы
        $chunks = AiKbChunk::with('document')
            ->whereNotNull('embedding')
            ->get();

        if ($chunks->isEmpty()) {
            return [];
        }

        // Вычисляем косинусное сходство для каждого чанка
        $similarities = [];
        foreach ($chunks as $chunk) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $chunk->embedding);
            $similarities[] = [
                'chunk' => $chunk,
                'similarity' => $similarity,
                'content' => $chunk->content,
            ];
        }

        // Сортируем по убыванию相似度
        usort($similarities, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        // Возвращаем топ-N результатов
        return array_slice($similarities, 0, $limit);
    }

    /**
     * Вычислить косинусное сходство между двумя векторами
     * 
     * @param array $vector1 Первый вектор
     * @param array $vector2 Второй вектор
     * @return float Косинусное сходство (от -1 до 1)
     */
    private function cosineSimilarity(array $vector1, array $vector2): float
    {
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        $length = min(count($vector1), count($vector2));
        
        for ($i = 0; $i < $length; $i++) {
            $dotProduct += $vector1[$i] * $vector2[$i];
            $norm1 += $vector1[$i] * $vector1[$i];
            $norm2 += $vector2[$i] * $vector2[$i];
        }

        if ($norm1 == 0 || $norm2 == 0) {
            return 0;
        }

        return $dotProduct / (sqrt($norm1) * sqrt($norm2));
    }

    /**
     * Обработать все необработанные чанки
     * Генерирует эмбеддинги для чанков без векторного представления
     * 
     * @return int Количество обработанных чанков
     */
    public function processAllChunks(): int
    {
        $chunks = AiKbChunk::whereNull('embedding')->get();
        $count = 0;

        foreach ($chunks as $chunk) {
            $embedding = $this->generateEmbedding($chunk->content);
            
            if (!empty($embedding)) {
                $chunk->embedding = $embedding;
                $chunk->save();
                $count++;
            }
        }

        return $count;
    }

    /**
     * Перегенерировать эмбеддинг для конкретного чанка
     * 
     * @param int $chunkId ID чанка
     * @return bool Успешность операции
     */
    public function regenerateChunkEmbedding(int $chunkId): bool
    {
        $chunk = AiKbChunk::find($chunkId);
        
        if (!$chunk) {
            return false;
        }

        $embedding = $this->generateEmbedding($chunk->content);
        
        if (!empty($embedding)) {
            $chunk->embedding = $embedding;
            $chunk->save();
            return true;
        }

        return false;
    }
}
