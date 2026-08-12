<?php

return [
    'marketplace' => [
        'catalog_url' => env('VERTEX_MARKETPLACE_URL'),
        'public_key' => env('VERTEX_MARKETPLACE_PUBLIC_KEY'),
        'timeout' => (int) env('VERTEX_MARKETPLACE_TIMEOUT', 10),
    ],
    'localization' => [
        'default' => env('APP_LOCALE', 'ru'),
        'supported' => array_values(array_filter(array_map('trim', explode(',', (string) env('APP_SUPPORTED_LOCALES', 'ru,en'))))),
    ],
    'n8n' => [
        'webhook_url' => env('N8N_WEBHOOK_URL'),
        'secret' => env('N8N_WEBHOOK_SECRET'),
        'timeout' => (int) env('N8N_WEBHOOK_TIMEOUT', 10),
    ],
    'automation' => [
        'max_steps' => (int) env('AUTOMATION_MAX_STEPS', 50),
    ],
    'recommendations' => [
        'decay_days' => (int) env('RECOMMENDATION_DECAY_DAYS', 30),
    ],
    'compliance' => [
        'retention_days' => (int) env('COMPLIANCE_RETENTION_DAYS', 365),
        'required_consents' => ['privacy', 'marketing'],
    ],
];
