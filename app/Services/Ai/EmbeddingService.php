<?php

namespace App\Services\Ai;

use App\Models\AiKbChunk;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с векторными эмбеддингами и поиска
 * Поддерживает работу через Supabase (pgvector) или локальный поиск в PHP
 */
class EmbeddingService
{
    private SupabaseVectorService $supabaseService;
    
    /**
     * API ключ для генерации эмбеддингов (OpenAI или совместимый)
     */
    private string $apiKey;
    
    /**
     * URL API для генерации эмбеддингов
     */
    private string $apiUrl;

    public function __construct(SupabaseVectorService $supabaseService)
    {
        $this->supabaseService = $supabaseService;
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->apiUrl = 'https://api.openai.com/v1/embeddings';
    }

    /**
     * Сгенерировать эмбеддинг для текста
     * Возвращает массив чисел (вектор)
     */
    public function generateEmbedding(string $text): array
    {
        // Используем Supabase сервис для генерации эмбеддинга
        return $this->supabaseService->generateEmbedding($text);
    }

    /**
     * Найти наиболее релевантные чанки по запросу
     * Использует косинусное сходство
     * Приоритет отдается векторному поиску через Supabase (если настроен)
     */
    public function findRelevantChunks(string $query, int $limit = 5): array
    {
        // Используем Supabase для векторного поиска (быстрее и эффективнее)
        return $this->supabaseService->findRelevantChunks($query, $limit);
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
