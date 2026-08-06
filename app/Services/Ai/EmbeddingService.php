<?php

namespace App\Services\Ai;

use App\Models\AiKbChunk;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для работы с векторными эмбеддингами и поиска
 * В продакшене лучше использовать внешние сервисы (OpenAI Embeddings, Pinecone, Qdrant)
 */
class EmbeddingService
{
    /**
     * API ключ для генерации эмбеддингов (OpenAI или совместимый)
     */
    private string $apiKey;
    
    /**
     * URL API для генерации эмбеддингов
     */
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->apiUrl = 'https://api.openai.com/v1/embeddings';
    }

    /**
     * Сгенерировать эмбеддинг для текста
     * Возвращает массив чисел (вектор)
     */
    public function generateEmbedding(string $text): array
    {
        // Если API ключ не настроен, используем заглушку (хэш-вектор для демонстрации)
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key не настроен. Используется демо-режим эмбеддингов.');
            return $this->generateMockEmbedding($text);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'text-embedding-ada-002',
                'input' => substr($text, 0, 8000), // Лимит токенов
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'][0]['embedding'];
            }

            Log::error('Ошибка генерации эмбеддинга: ' . $response->body());
            return $this->generateMockEmbedding($text);

        } catch (\Exception $e) {
            Log::error('Исключение при генерации эмбеддинга: ' . $e->getMessage());
            return $this->generateMockEmbedding($text);
        }
    }

    /**
     * Демо-эмбеддинг (псевдо-вектор на основе хэша)
     * НЕ подходит для реального семантического поиска, только для тестов!
     */
    private function generateMockEmbedding(string $text): array
    {
        // Генерируем псевдо-вектор размерностью 1536 (как у ada-002)
        $vector = [];
        $hash = md5($text);
        
        for ($i = 0; $i < 1536; $i++) {
            // Псевдо-случайное число от -1 до 1 на основе хэша
            $charCode = ord($hash[$i % strlen($hash)]);
            $value = ($charCode / 255) * 2 - 1;
            $vector[] = round($value, 6);
        }

        return $vector;
    }

    /**
     * Найти наиболее релевантные чанки по запросу
     * Использует косинусное сходство
     */
    public function findRelevantChunks(string $query, int $limit = 5): array
    {
        $queryVector = $this->generateEmbedding($query);
        
        // В реальном проекте здесь должен быть поиск по векторной БД (pgvector, Pinecone)
        // Для примера делаем простой перебор с вычислением косинусного сходства в PHP
        
        $chunks = AiKbChunk::all();
        $results = [];

        foreach ($chunks as $chunk) {
            $chunkVector = $chunk->embedding_vector;
            
            if (!$chunkVector) {
                continue;
            }

            $similarity = $this->cosineSimilarity($queryVector, $chunkVector);
            
            $results[] = [
                'chunk' => $chunk,
                'similarity' => $similarity,
                'content' => $chunk->content,
            ];
        }

        // Сортируем по убыванию схожести
        usort($results, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Вычислить косинусное сходство между двумя векторами
     */
    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        $length = min(count($vectorA), count($vectorB));

        for ($i = 0; $i < $length; $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $magnitudeA += $vectorA[$i] ** 2;
            $magnitudeB += $vectorB[$i] ** 2;
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Массово обновить эмбеддинги для всех необработанных чанков
     */
    public function processAllChunks(): int
    {
        $processed = 0;
        $chunks = AiKbChunk::whereNull('embedding_vector')->orWhere('embedding_vector', '')->get();

        foreach ($chunks as $chunk) {
            $vector = $this->generateEmbedding($chunk->content);
            $chunk->update(['embedding_vector' => $vector]);
            $processed++;
            
            // Небольшая задержка чтобы не превысить лимиты API
            if (!empty($this->apiKey)) {
                usleep(100000); // 100ms
            }
        }

        Log::info("Обновлено эмбеддингов: {$processed}");
        return $processed;
    }
}
