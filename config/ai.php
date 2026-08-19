<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configure global AI settings, fallbacks, and feature toggles for all
    | AI-powered services in the application.
    |
    */

    // Enable/disable all AI features globally
    'enabled' => (bool) env('AI_ENABLED', false),

    // Default AI provider (openai, anthropic, custom)
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),

    // Default model for text generation
    'default_model' => env('AI_DEFAULT_MODEL', 'gpt-4o-mini'),

    // Maximum tokens for generation
    'max_tokens' => (int) env('AI_MAX_TOKENS', 1000),

    // Temperature for generation (0.0 - 2.0)
    'temperature' => (float) env('AI_TEMPERATURE', 0.7),

    /*
    |--------------------------------------------------------------------------
    | API Keys Configuration
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
    ],

    'custom' => [
        'api_key' => env('CUSTOM_AI_API_KEY'),
        'base_url' => env('CUSTOM_AI_BASE_URL'),
        'model' => env('CUSTOM_AI_MODEL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vector Storage Configuration (for RAG)
    |--------------------------------------------------------------------------
    */

    'vector_storage' => [
        // Driver: database, supabase, pinecone, qdrant
        'driver' => env('VECTOR_STORAGE_DRIVER', 'database'),

        // Database configuration for PgVector
        'database' => [
            'connection' => env('DB_CONNECTION', 'pgsql'),
            'table' => 'ai_kb_chunks',
            'vector_column' => 'embedding_vector',
            'dimensions' => (int) env('VECTOR_DIMENSIONS', 1536), // Ada-002 = 1536
        ],

        // Supabase configuration
        'supabase' => [
            'url' => env('SUPABASE_URL'),
            'key' => env('SUPABASE_KEY'),
            'table' => env('SUPABASE_VECTOR_TABLE', 'documents'),
            'query_function' => env('SUPABASE_QUERY_FUNCTION', 'match_documents'),
        ],

        // Pinecone configuration
        'pinecone' => [
            'api_key' => env('PINECONE_API_KEY'),
            'environment' => env('PINECONE_ENVIRONMENT'),
            'index' => env('PINECONE_INDEX'),
        ],

        // Qdrant configuration
        'qdrant' => [
            'url' => env('QDRANT_URL'),
            'api_key' => env('QDRANT_API_KEY'),
            'collection' => env('QDRANT_COLLECTION', 'knowledge_base'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    | Control which AI features are available to users
    */

    'features' => [
        'brand_monitor' => (bool) env('AI_FEATURE_BRAND_MONITOR', false),
        'chat_bot' => (bool) env('AI_FEATURE_CHAT_BOT', true),
        'content_generation' => (bool) env('AI_FEATURE_CONTENT_GENERATION', true),
        'image_analysis' => (bool) env('AI_FEATURE_IMAGE_ANALYSIS', true),
        'rag_chat' => (bool) env('AI_FEATURE_RAG_CHAT', true),
        'semantic_search' => (bool) env('AI_FEATURE_SEMANTIC_SEARCH', true),
        'site_wizard' => (bool) env('AI_FEATURE_SITE_WIZARD', true),
        'draft_service' => (bool) env('AI_FEATURE_DRAFT_SERVICE', true),
        'document_processing' => (bool) env('AI_FEATURE_DOCUMENT_PROCESSING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    | What to do when AI service is unavailable
    */

    'fallback' => [
        // Use stub responses when AI is unavailable
        'use_stubs' => (bool) env('AI_FALLBACK_USE_STUBS', false),

        // Return cached responses if available
        'use_cache' => (bool) env('AI_FALLBACK_USE_CACHE', true),

        // Cache TTL in seconds
        'cache_ttl' => (int) env('AI_FALLBACK_CACHE_TTL', 3600),

        // Log warnings when falling back
        'log_warnings' => (bool) env('AI_FALLBACK_LOG_WARNINGS', true),

        // Notify admin on repeated failures
        'notify_on_failure' => (bool) env('AI_FALLBACK_NOTIFY_ON_FAILURE', false),
        'notify_threshold' => (int) env('AI_FALLBACK_NOTIFY_THRESHOLD', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limit' => [
        'enabled' => (bool) env('AI_RATE_LIMIT_ENABLED', true),
        'max_requests_per_minute' => (int) env('AI_RATE_LIMIT_PER_MINUTE', 60),
        'max_requests_per_hour' => (int) env('AI_RATE_LIMIT_PER_HOUR', 1000),
        'max_tokens_per_day' => (int) env('AI_RATE_LIMIT_TOKENS_PER_DAY', 100000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging & Monitoring
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'log_requests' => (bool) env('AI_LOG_REQUESTS', false),
        'log_responses' => (bool) env('AI_LOG_RESPONSES', false),
        'log_errors' => (bool) env('AI_LOG_ERRORS', true),
        'log_usage' => (bool) env('AI_LOG_USAGE', true),
        'store_prompts' => (bool) env('AI_STORE_PROMPTS', false),
        'store_responses' => (bool) env('AI_STORE_RESPONSES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunk Processing Settings (for RAG)
    |--------------------------------------------------------------------------
    */

    'chunk_size' => (int) env('AI_CHUNK_SIZE', 500),
    'chunk_overlap' => (int) env('AI_CHUNK_OVERLAP', 50),

    /*
    |--------------------------------------------------------------------------
    | Brand Voice & Content Settings
    |--------------------------------------------------------------------------
    */

    'brand_voice' => env('AI_BRAND_VOICE', ''),
    'content_language' => env('AI_CONTENT_LANGUAGE', 'ru'),

    /*
    |--------------------------------------------------------------------------
    | Demo Mode Configuration
    |--------------------------------------------------------------------------
    | When enabled, shows demo data instead of making real API calls
    */

    'demo_mode' => [
        'enabled' => (bool) env('AI_DEMO_MODE', false),
        'show_disclaimer' => (bool) env('AI_DEMO_SHOW_DISCLAIMER', true),
    ],
];
