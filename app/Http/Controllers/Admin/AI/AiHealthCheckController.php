<?php

namespace App\Http\Controllers\Admin\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\EmbeddingService;
use App\Services\AI\RagChatService;
use App\Services\AI\ContentGenerationService;
use App\Services\AI\ImageAnalysisService;
use App\Services\AI\SmartSearchService;
use App\Services\AI\ChatBotService;
use App\Services\AI\DocumentProcessorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Health Check Controller
 * 
 * Provides endpoints for monitoring AI service status and availability.
 * Use this to verify that all AI services are properly configured and operational.
 */
class AiHealthCheckController extends Controller
{
    public function __construct(
        private readonly EmbeddingService $embeddingService,
        private readonly RagChatService $ragChatService,
        private readonly ContentGenerationService $contentGenerationService,
        private readonly ImageAnalysisService $imageAnalysisService,
        private readonly SmartSearchService $smartSearchService,
        private readonly ChatBotService $chatBotService,
        private readonly DocumentProcessorService $documentProcessorService,
    ) {}

    /**
     * Get comprehensive health status of all AI services
     */
    public function index(): JsonResponse
    {
        $isGloballyEnabled = config('ai.enabled', false);
        
        return response()->json([
            'status' => $isGloballyEnabled ? 'operational' : 'disabled',
            'timestamp' => now()->toIso8601String(),
            'global_settings' => [
                'enabled' => $isGloballyEnabled,
                'default_provider' => config('ai.default_provider', 'openai'),
                'default_model' => config('ai.default_model', 'gpt-4o-mini'),
                'demo_mode' => config('ai.demo_mode.enabled', false),
            ],
            'features' => $this->checkFeatures(),
            'services' => $this->checkServices(),
            'configuration' => $this->checkConfiguration(),
            'vector_storage' => $this->checkVectorStorage(),
        ]);
    }

    /**
     * Check individual service health
     */
    public function checkService(string $service): JsonResponse
    {
        $checkers = [
            'embedding' => fn() => $this->checkEmbeddingService(),
            'rag_chat' => fn() => $this->checkRagChatService(),
            'content_generation' => fn() => $this->checkContentGenerationService(),
            'image_analysis' => fn() => $this->checkImageAnalysisService(),
            'smart_search' => fn() => $this->checkSmartSearchService(),
            'chat_bot' => fn() => $this->checkChatBotService(),
            'document_processor' => fn() => $this->checkDocumentProcessorService(),
        ];

        if (!isset($checkers[$service])) {
            return response()->json([
                'error' => 'Unknown service',
                'available_services' => array_keys($checkers),
            ], 404);
        }

        return response()->json($checkers[$service]());
    }

    /**
     * Check all feature toggles
     */
    private function checkFeatures(): array
    {
        $features = config('ai.features', []);
        $results = [];

        foreach ($features as $feature => $enabled) {
            $results[$feature] = [
                'enabled' => (bool) $enabled,
                'config_path' => "ai.features.{$feature}",
            ];
        }

        return $results;
    }

    /**
     * Check all AI services status
     */
    private function checkServices(): array
    {
        return [
            'embedding' => $this->checkEmbeddingService(),
            'rag_chat' => $this->checkRagChatService(),
            'content_generation' => $this->checkContentGenerationService(),
            'image_analysis' => $this->checkImageAnalysisService(),
            'smart_search' => $this->checkSmartSearchService(),
            'chat_bot' => $this->checkChatBotService(),
            'document_processor' => $this->checkDocumentProcessorService(),
        ];
    }

    /**
     * Check Embedding Service
     */
    private function checkEmbeddingService(): array
    {
        $apiKey = config('services.openai.api_key');
        $isAvailable = !empty($apiKey);

        return [
            'status' => $isAvailable ? 'healthy' : 'unavailable',
            'api_key_configured' => $isAvailable,
            'model' => 'text-embedding-ada-002',
            'can_generate_embeddings' => $isAvailable,
            'last_check' => now()->toIso8601String(),
        ];
    }

    /**
     * Check RAG Chat Service
     */
    private function checkRagChatService(): array
    {
        $apiKey = config('services.openai.api_key');
        $isAvailable = !empty($apiKey);
        
        // Check if knowledge base has chunks with embeddings
        $chunkCount = \App\Models\AiKbChunk::whereNotNull('embedding_vector')->count();

        return [
            'status' => $isAvailable ? 'healthy' : 'unavailable',
            'api_key_configured' => $isAvailable,
            'llm_model' => 'gpt-3.5-turbo',
            'knowledge_base_chunks' => $chunkCount,
            'ready_for_queries' => $isAvailable && $chunkCount > 0,
            'last_check' => now()->toIso8601String(),
        ];
    }

    /**
     * Check Content Generation Service
     */
    private function checkContentGenerationService(): array
    {
        $apiKey = config('services.openai.api_key');
        $isAvailable = !empty($apiKey);

        return [
            'status' => $isAvailable ? 'healthy' : 'unavailable',
            'api_key_configured' => $isAvailable,
            'default_model' => 'gpt-3.5-turbo',
            'can_generate_text' => $isAvailable,
            'last_check' => now()->toIso8601String(),
        ];
    }

    /**
     * Check Image Analysis Service
     */
    private function checkImageAnalysisService(): array
    {
        $apiKey = config('services.openai.api_key');
        $isAvailable = !empty($apiKey);

        return [
            'status' => $isAvailable ? 'healthy' : 'unavailable',
            'api_key_configured' => $isAvailable,
            'vision_model' => 'gpt-4-vision-preview',
            'can_analyze_images' => $isAvailable,
            'last_check' => now()->toIso8601String(),
        ];
    }

    /**
     * Check Smart Search Service
     */
    private function checkSmartSearchService(): array
    {
        $apiKey = config('services.openai.api_key');
        $hasApiKey = !empty($apiKey);
        
        // Semantic search requires API key for query expansion
        $semanticSearchAvailable = $hasApiKey;

        return [
            'status' => 'healthy',
            'api_key_configured' => $hasApiKey,
            'semantic_search_available' => $semanticSearchAvailable,
            'fallback_search_available' => true,
            'searchable_models' => SmartSearchService::SEARCHABLE_MODELS,
            'last_check' => now()->toIso8601String(),
        ];
    }

    /**
     * Check Chat Bot Service
     */
    private function checkChatBotService(): array
    {
        $apiKey = config('services.openai.api_key');
        $hasApiKey = !empty($apiKey);

        return [
            'status' => $hasApiKey ? 'healthy' : 'degraded',
            'api_key_configured' => $hasApiKey,
            'faq_lookup_available' => true,
            'ai_responses_available' => $hasApiKey,
            'contact_info_configured' => !empty(config('vertex.contacts.phone')),
            'last_check' => now()->toIso8601String(),
        ];
    }

    /**
     * Check Document Processor Service
     */
    private function checkDocumentProcessorService(): array
    {
        $pdftotextAvailable = (bool) exec('which pdftotext');

        return [
            'status' => 'healthy',
            'pdftotext_installed' => $pdftotextAvailable,
            'pdf_extraction_quality' => $pdftotextAvailable ? 'high' : 'low',
            'docx_extraction_available' => class_exists('ZipArchive'),
            'txt_extraction_available' => true,
            'html_extraction_available' => true,
            'last_check' => now()->toIso8601String(),
        ];
    }

    /**
     * Check configuration completeness
     */
    private function checkConfiguration(): array
    {
        return [
            'ai_enabled' => config('ai.enabled', false),
            'openai_api_key' => !empty(config('services.openai.api_key')) ? 'configured' : 'missing',
            'anthropic_api_key' => !empty(config('ai.anthropic.api_key')) ? 'configured' : 'missing',
            'custom_ai_configured' => !empty(config('ai.custom.base_url')) ? 'yes' : 'no',
            'fallback_enabled' => config('ai.fallback.use_cache', false),
            'rate_limiting_enabled' => config('ai.rate_limit.enabled', false),
            'logging_enabled' => config('ai.logging.log_errors', true),
        ];
    }

    /**
     * Check vector storage configuration
     */
    private function checkVectorStorage(): array
    {
        $driver = config('ai.vector_storage.driver', 'database');
        
        $storageConfig = match ($driver) {
            'database' => [
                'driver' => 'database',
                'connection' => config('ai.vector_storage.database.connection'),
                'table' => config('ai.vector_storage.database.table'),
                'dimensions' => config('ai.vector_storage.database.dimensions', 1536),
                'status' => 'configured',
            ],
            'supabase' => [
                'driver' => 'supabase',
                'url_configured' => !empty(config('ai.vector_storage.supabase.url')),
                'key_configured' => !empty(config('ai.vector_storage.supabase.key')),
                'table' => config('ai.vector_storage.supabase.table'),
                'status' => !empty(config('ai.vector_storage.supabase.url')) ? 'configured' : 'missing_config',
            ],
            'pinecone' => [
                'driver' => 'pinecone',
                'api_key_configured' => !empty(config('ai.vector_storage.pinecone.api_key')),
                'index' => config('ai.vector_storage.pinecone.index'),
                'status' => !empty(config('ai.vector_storage.pinecone.api_key')) ? 'configured' : 'missing_config',
            ],
            'qdrant' => [
                'driver' => 'qdrant',
                'url_configured' => !empty(config('ai.vector_storage.qdrant.url')),
                'collection' => config('ai.vector_storage.qdrant.collection'),
                'status' => !empty(config('ai.vector_storage.qdrant.url')) ? 'configured' : 'missing_config',
            ],
            default => [
                'driver' => $driver,
                'status' => 'unknown_driver',
            ],
        };

        return $storageConfig;
    }

    /**
     * Test API connectivity (optional - use with caution in production)
     */
    public function testApiConnectivity(): JsonResponse
    {
        if (!config('ai.enabled', false)) {
            return response()->json(['error' => 'AI is disabled'], 403);
        }

        $apiKey = config('services.openai.api_key');
        
        if (empty($apiKey)) {
            return response()->json(['error' => 'API key not configured'], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->get('https://api.openai.com/v1/models');

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'API connectivity verified',
                    'models_available' => count($response.json()['data'] ?? []),
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'API request failed',
                'http_status' => $response->status(),
            ], 500);

        } catch (\Exception $e) {
            Log::error('AI API connectivity test failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
