<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для работы с векторными эмбеддингами через Supabase (pgvector)
 * Обеспечивает быстрый семантический поиск на уровне базы данных
 */
class SupabaseVectorService
{
    private string $supabaseUrl;
    private string $supabaseKey;
    private string $apiKey;
    private string $embeddingsApiUrl;
    private int $embeddingDimensions = 1536; // Для text-embedding-ada-002

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url', env('SUPABASE_URL', ''));
        $this->supabaseKey = config('services.supabase.key', env('SUPABASE_KEY', ''));
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->embeddingsApiUrl = 'https://api.openai.com/v1/embeddings';
    }

    /**
     * Проверка доступности Supabase
     */
    public function isAvailable(): bool
    {
        return !empty($this->supabaseUrl) && !empty($this->supabaseKey);
    }

    /**
     * Сгенерировать эмбеддинг для текста через OpenAI API
     */
    public function generateEmbedding(string $text): array
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key не настроен. Используется демо-режим эмбеддингов.');
            return $this->generateMockEmbedding($text);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post($this->embeddingsApiUrl, [
                'model' => 'text-embedding-ada-002',
                'input' => substr($text, 0, 8000),
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
     */
    private function generateMockEmbedding(string $text): array
    {
        $vector = [];
        $hash = md5($text);
        
        for ($i = 0; $i < $this->embeddingDimensions; $i++) {
            $charCode = ord($hash[$i % strlen($hash)]);
            $value = ($charCode / 255) * 2 - 1;
            $vector[] = round($value, 6);
        }

        return $vector;
    }

    /**
     * Найти наиболее релевантные чанки через векторный поиск в Supabase
     * Использует оператор косинусного расстояния (<=>) из pgvector
     */
    public function findRelevantChunks(string $query, int $limit = 5): array
    {
        // Если Supabase не настроен, используем fallback на обычный поиск
        if (!$this->isAvailable()) {
            Log::info('Supabase не настроен. Используем fallback на PHP поиск.');
            return $this->findRelevantChunksFallback($query, $limit);
        }

        $queryVector = $this->generateEmbedding($query);
        $vectorString = '[' . implode(',', $queryVector) . ']';

        try {
            // Выполняем векторный поиск через RPC функцию или прямой SQL запрос
            $response = Http::withHeaders([
                'apikey' => $this->supabaseKey,
                'Authorization' => "Bearer {$this->supabaseKey}",
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])->post("{$this->supabaseUrl}/rest/v1/rpc/search_kb_chunks", [
                'query_embedding' => $vectorString,
                'match_limit' => $limit,
            ]);

            if ($response->successful()) {
                $results = $response->json();
                
                return array_map(function($item) {
                    return [
                        'chunk' => $this->buildChunkModel($item),
                        'similarity' => 1 - ($item['distance'] ?? 0), // Конвертируем расстояние в схожесть
                        'content' => $item['content'],
                    ];
                }, $results);
            }

            Log::error('Ошибка поиска в Supabase: ' . $response->body());
            return $this->findRelevantChunksFallback($query, $limit);

        } catch (\Exception $e) {
            Log::error('Исключение при поиске в Supabase: ' . $e->getMessage());
            return $this->findRelevantChunksFallback($query, $limit);
        }
    }

    /**
     * Fallback метод для поиска без Supabase (перебор в PHP)
     */
    private function findRelevantChunksFallback(string $query, int $limit): array
    {
        $chunks = \App\Models\AiKbChunk::all();
        $queryVector = $this->generateEmbedding($query);
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

        usort($results, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Сохранить чанк с вектором в Supabase
     */
    public function saveChunk(int $documentId, string $content, int $chunkOrder, array $metadata = []): ?int
    {
        $vector = $this->generateEmbedding($content);

        try {
            $response = Http::withHeaders([
                'apikey' => $this->supabaseKey,
                'Authorization' => "Bearer {$this->supabaseKey}",
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])->post("{$this->supabaseUrl}/rest/v1/ai_kb_chunks", [
                'document_id' => $documentId,
                'content' => $content,
                'chunk_order' => $chunkOrder,
                'embedding_vector' => $vector,
                'metadata' => $metadata,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data[0]['id'] ?? null;
            }

            Log::error('Ошибка сохранения чанка в Supabase: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Исключение при сохранении чанка: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Массовая загрузка чанков в Supabase
     */
    public function bulkInsertChunks(array $chunksData): int
    {
        if (!$this->isAvailable() || empty($chunksData)) {
            return 0;
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->supabaseKey,
                'Authorization' => "Bearer {$this->supabaseKey}",
                'Content-Type' => 'application/json',
                'Prefer' => 'return=minimal',
            ])->post("{$this->supabaseUrl}/rest/v1/ai_kb_chunks", $chunksData);

            if ($response->successful()) {
                return count($chunksData);
            }

            Log::error('Ошибка массовой загрузки: ' . $response->body());
            return 0;

        } catch (\Exception $e) {
            Log::error('Исключение при массовой загрузке: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Обновить эмбеддинг для существующего чанка
     */
    public function updateChunkEmbedding(int $chunkId, string $content): bool
    {
        $vector = $this->generateEmbedding($content);

        try {
            $response = Http::withHeaders([
                'apikey' => $this->supabaseKey,
                'Authorization' => "Bearer {$this->supabaseKey}",
                'Content-Type' => 'application/json',
            ])->patch("{$this->supabaseUrl}/rest/v1/ai_kb_chunks?id=eq.{$chunkId}", [
                'content' => $content,
                'embedding_vector' => $vector,
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Исключение при обновлении чанка: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Удалить чанк из Supabase
     */
    public function deleteChunk(int $chunkId): bool
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->supabaseKey,
                'Authorization' => "Bearer {$this->supabaseKey}",
            ])->delete("{$this->supabaseUrl}/rest/v1/ai_kb_chunks?id=eq.{$chunkId}");

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Исключение при удалении чанка: ' . $e->getMessage());
            return false;
        }
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
     * Построить объект чанка из данных API
     */
    private function buildChunkModel(array $data): \App\Models\AiKbChunk
    {
        $chunk = new \App\Models\AiKbChunk();
        $chunk->id = $data['id'];
        $chunk->document_id = $data['document_id'];
        $chunk->content = $data['content'];
        $chunk->chunk_order = $data['chunk_order'];
        $chunk->embedding_vector = is_string($data['embedding_vector']) 
            ? json_decode($data['embedding_vector'], true) 
            : $data['embedding_vector'];
        $chunk->metadata = $data['metadata'] ?? null;
        
        return $chunk;
    }

    /**
     * Получить статистику по чанкам в Supabase
     */
    public function getStats(): array
    {
        if (!$this->isAvailable()) {
            return ['total_chunks' => 0, 'total_documents' => 0];
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->supabaseKey,
                'Authorization' => "Bearer {$this->supabaseKey}",
            ])->get("{$this->supabaseUrl}/rest/v1/ai_kb_chunks?select=count");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'total_chunks' => (int)($data[0]['count'] ?? 0),
                    'total_documents' => \App\Models\AiKbDocument::count(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Ошибка получения статистики: ' . $e->getMessage());
        }

        return ['total_chunks' => 0, 'total_documents' => 0];
    }
}
