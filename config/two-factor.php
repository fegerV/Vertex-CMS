<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication Configuration
    |--------------------------------------------------------------------------
    */
    
    'enabled' => env('TWO_FACTOR_ENABLED', true),
    
    // Название приложения для QR кода
    'app_name' => env('APP_NAME', 'Vertex CMS'),
    
    // Время жизни OTP кода (в секундах)
    'window' => env('TWO_FACTOR_WINDOW', 1),
    
    // Количество кодов восстановления
    'recovery_codes_count' => 8,
    
    // Длина кода восстановления
    'recovery_code_length' => 10,
];
