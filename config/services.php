
<?php

return [

    // OpenAI for AI services
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    // Supabase for vector storage and RAG
    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'key' => env('SUPABASE_KEY'),
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
