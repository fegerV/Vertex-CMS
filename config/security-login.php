<?php

return [
    'login' => [
        // Enable hidden admin path (e.g., /manage instead of /admin)
        'enable_hidden_path' => env('LOGIN_HIDDEN_PATH', false),
        'hidden_path' => env('LOGIN_HIDDEN_PATH_VALUE', 'manage'),

        // Password policy
        'password_expiry_days' => env('LOGIN_PASSWORD_EXPIRY_DAYS', 90),
        'password_min_length' => 12,
        'password_require_change' => true,

        // 2FA settings
        '2fa_enabled' => env('LOGIN_2FA_ENABLED', false),
        '2fa_required_for_roles' => ['super-admin'], // Roles that must use 2FA

        // Session management
        'max_sessions_per_user' => 5,
        'session_timeout_minutes' => 60,

        // Brute force protection
        'max_login_attempts' => 5,
        'lockout_duration_minutes' => 15,

        // Notifications
        'notify_on_new_login' => true,
        'notify_on_password_change' => true,
        'notify_on_2fa_change' => true,
    ],
];