<?php

namespace App\Services\AI;

use App\Models\AiKbChunk;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Service for working with embeddings (vector representations)
 * Responsible for embedding generation and vector search
 * 
 * REQUIRES:
 * - OPENAI_API_KEY configured in environment
 * - Vector storage (PgVector/Supabase/Pinecone) for production use
 */
class EmbeddingService
{
    private string $apiKey;
    private string $embeddingApiUrl;
    private string $model;
    private bool $isEnabled;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->embeddingApiUrl = 'https://api.openai.com/v1/embeddings';
        $this->model = 'text-embedding-ada-002';
        $this->isEnabled = config('ai.enabled', false) && config('ai.features.content_generation', true);
    }

    /**
     * Check if embedding service is available and configured
     */
    public function isAvailable(): bool
    {
        return $this->isEnabled && !empty($this->apiKey);
    }

    /**
     * Generate embedding for text
     * 
     * @param string $text Text to vectorize
     * @return array Vector representation (array of floats)
     * @throws RuntimeException If service is unavailable or API call fails
     */
    public function generateEmbedding(string $text): array
    {
        if (!$this->isAvailable()) {
            $reason = empty($this->apiKey) ? 'OpenAI API key not configured' : 'AI service is disabled';
            
            if (config('ai.fallback.log_warnings', true)) {
                Log::warning('Embedding service unavailable: ' . $reason);
            }
            
            throw new RuntimeException(
                "Embedding service unavailable: {$reason}. " .
                "Set OPENAI_API_KEY in environment and ensure AI_ENABLED=true."
            );
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
                $embedding = $data['data'][0]['embedding'] ?? [];
                
                if (empty($embedding)) {
                    throw new RuntimeException('Empty embedding received from API');
                }
                
                return $embedding;
            }

            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'Unknown API error';
            
            Log::error('Embedding API error: ' . $errorMessage, [
                'status' => $response->status(),
                'response' => $errorData,
            ]);
            
            throw new RuntimeException("Embedding API error ({$response->status()}): {$errorMessage}");

        } catch (\Exception $e) {
            if ($e instanceof RuntimeException) {
                throw $e;
            }
            
            Log::error('Embedding generation exception: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new RuntimeException(
                "Failed to generate embedding: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Generate embeddings for multiple texts
     * 
     * @param array $texts Array of texts
     * @return array Array of embeddings
     * @throws RuntimeException If service is unavailable
     */
    public function generateEmbeddings(array $texts): array
    {
        if (!$this->isAvailable()) {
            $reason = empty($this->apiKey) ? 'OpenAI API key not configured' : 'AI service is disabled';
            
            if (config('ai.fallback.log_warnings', true)) {
                Log::warning('Embedding service unavailable: ' . $reason);
            }
            
            throw new RuntimeException(
                "Embedding service unavailable: {$reason}. " .
                "Set OPENAI_API_KEY in environment and ensure AI_ENABLED=true."
            );
        }

        // OpenAI supports batches up to 2048 texts
        $batchSize = 100;
        $allEmbeddings = [];
        $failedBatches = 0;

        foreach (array_chunk($texts, $batchSize) as $index => $batch) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post($this->embeddingApiUrl, [
                    'model' => $this->model,
                    'input' => $batch,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    foreach ($data['data'] as $item) {
                        $allEmbeddings[] = $item['embedding'];
                    }
                } else {
                    $failedBatches++;
                    Log::error("Embedding batch {$index} failed: " . $response->status());
                }
            } catch (\Exception $e) {
                $failedBatches++;
                Log::error("Batch embedding error (batch {$index}): " . $e->getMessage());
            }
        }

        if ($failedBatches > 0 && config('ai.fallback.log_warnings', true)) {
            Log::warning("Embedding generation completed with {$failedBatches} failed batches");
        }

        return $allEmbeddings;
    }

    /**
     * Find relevant chunks by query
     * Uses cosine similarity for search
     * 
     * NOTE: This implementation loads ALL chunks into memory - inefficient for production!
     * For production, use PgVector with database-level similarity search or dedicated vector DB.
     * 
     * @param string $query Search query
     * @param int $limit Maximum number of results
     * @return array Array of found chunks with similarity scores
     * @throws RuntimeException If service is unavailable
     */
    public function findRelevantChunks(string $query, int $limit = 5): array
    {
        // Generate embedding for query
        $queryEmbedding = $this->generateEmbedding($query);
        
        if (empty($queryEmbedding)) {
            Log::warning('Cannot find relevant chunks: query embedding is empty');
            return [];
        }

        // Get all chunks from database
        // WARNING: This is inefficient for large datasets!
        // Use PgVector extension for database-level similarity search in production
        $chunks = AiKbChunk::with('document')
            ->whereNotNull('embedding_vector')
            ->get();

        if ($chunks->isEmpty()) {
            Log::info('No chunks with embeddings found in database');
            return [];
        }

        // Calculate cosine similarity for each chunk
        $similarities = [];
        foreach ($chunks as $chunk) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $chunk->embedding_vector);
            $similarities[] = [
                'chunk' => $chunk,
                'similarity' => $similarity,
                'content' => $chunk->content,
            ];
        }

        // Sort by similarity descending
        usort($similarities, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        // Return top-N results
        return array_slice($similarities, 0, $limit);
    }

    /**
     * Calculate cosine similarity between two vectors
     * 
     * @param array $vector1 First vector
     * @param array $vector2 Second vector
     * @return float Cosine similarity (from -1 to 1)
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
     * Process all unprocessed chunks
     * Generates embeddings for chunks without vector representation
     * 
     * @return int Number of processed chunks
     * @throws RuntimeException If service is unavailable
     */
    public function processAllChunks(): int
    {
        if (!$this->isAvailable()) {
            throw new RuntimeException('Embedding service unavailable');
        }

        $chunks = AiKbChunk::whereNull('embedding_vector')->get();
        $count = 0;
        $failed = 0;

        foreach ($chunks as $chunk) {
            try {
                $embedding = $this->generateEmbedding($chunk->content);
                
                if (!empty($embedding)) {
                    $chunk->embedding_vector = $embedding;
                    $chunk->save();
                    $count++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error("Failed to process chunk {$chunk->id}: " . $e->getMessage());
            }
        }

        if ($failed > 0 && config('ai.fallback.log_warnings', true)) {
            Log::warning("Chunk processing completed: {$count} successful, {$failed} failed");
        }

        return $count;
    }

    /**
     * Regenerate embedding for specific chunk
     * 
     * @param int $chunkId Chunk ID
     * @return bool Success status
     * @throws RuntimeException If service is unavailable
     */
    public function regenerateChunkEmbedding(int $chunkId): bool
    {
        if (!$this->isAvailable()) {
            throw new RuntimeException('Embedding service unavailable');
        }

        $chunk = AiKbChunk::find($chunkId);
        
        if (!$chunk) {
            return false;
        }

        $embedding = $this->generateEmbedding($chunk->content);
        
        if (!empty($embedding)) {
            $chunk->embedding_vector = $embedding;
            $chunk->save();
            return true;
        }

        return false;
    }

    /**
     * Get service statistics
     */
    public function getStatistics(): array
    {
        $totalChunks = AiKbChunk::count();
        $chunksWithEmbeddings = AiKbChunk::whereNotNull('embedding_vector')->count();
        
        return [
            'service_available' => $this->isAvailable(),
            'api_key_configured' => !empty($this->apiKey),
            'total_chunks' => $totalChunks,
            'chunks_with_embeddings' => $chunksWithEmbeddings,
            'chunks_without_embeddings' => $totalChunks - $chunksWithEmbeddings,
            'embedding_coverage' => $totalChunks > 0 ? round(($chunksWithEmbeddings / $totalChunks) * 100, 2) : 0,
            'model' => $this->model,
            'dimensions' => 1536, // Ada-002 dimensions
        ];
    }
}
