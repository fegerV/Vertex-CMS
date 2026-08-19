
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Services Configuration
    |--------------------------------------------------------------------------
    */

    // OpenAI for AI services
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],

    // Anthropic (Claude) - alternative AI provider
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
    ],

    // Custom AI provider
    'custom_ai' => [
        'api_key' => env('CUSTOM_AI_API_KEY'),
        'base_url' => env('CUSTOM_AI_BASE_URL'),
        'model' => env('CUSTOM_AI_MODEL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vector Storage Configuration
    |--------------------------------------------------------------------------
    */

    // Supabase for vector storage and RAG
    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'key' => env('SUPABASE_KEY'),
    ],

    // Pinecone for vector search
    'pinecone' => [
        'api_key' => env('PINECONE_API_KEY'),
        'environment' => env('PINECONE_ENVIRONMENT'),
        'index' => env('PINECONE_INDEX'),
    ],

    // Qdrant for vector search
    'qdrant' => [
        'url' => env('QDRANT_URL'),
        'api_key' => env('QDRANT_API_KEY'),
        'collection' => env('QDRANT_COLLECTION', 'knowledge_base'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Other External Services
    |--------------------------------------------------------------------------
    */

    'aws' => [
        'access_key_id' => env('AWS_ACCESS_KEY_ID'),
        'secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),
        'session_token' => env('AWS_SESSION_TOKEN'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Telegram for notifications
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

    // Slack for notifications
    'slack' => [
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
    ],

];
