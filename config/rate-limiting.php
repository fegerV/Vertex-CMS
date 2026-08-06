<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */
    
    'enabled' => env('RATE_LIMIT_ENABLED', true),
    
    'limiters' => [
        'api' => [
            'max_attempts' => env('RATE_LIMIT_API_MAX', 60),
            'decay_minutes' => env('RATE_LIMIT_API_DECAY', 1),
        ],
        
        'admin' => [
            'max_attempts' => env('RATE_LIMIT_ADMIN_MAX', 30),
            'decay_minutes' => env('RATE_LIMIT_ADMIN_DECAY', 1),
        ],
        
        'login' => [
            'max_attempts' => env('RATE_LIMIT_LOGIN_MAX', 5),
            'decay_minutes' => env('RATE_LIMIT_LOGIN_DECAY', 1),
        ],
        
        'password-reset' => [
            'max_attempts' => env('RATE_LIMIT_PASSWORD_RESET_MAX', 3),
            'decay_minutes' => env('RATE_LIMIT_PASSWORD_RESET_DECAY', 60),
        ],
    ],
];
