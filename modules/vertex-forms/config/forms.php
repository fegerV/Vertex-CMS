<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Form Theme
    |--------------------------------------------------------------------------
    */
    'default_theme' => env('FORMS_DEFAULT_THEME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Custom CSS for Forms
    |--------------------------------------------------------------------------
    */
    'custom_css' => env('FORMS_CUSTOM_CSS', ''),

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */
    'upload_disk' => env('FORMS_UPLOAD_DISK', 'public'),
    'upload_dir' => env('FORMS_UPLOAD_DIR', 'form-uploads'),

    /*
    |--------------------------------------------------------------------------
    | Honeypot Protection
    |--------------------------------------------------------------------------
    */
    'honeypot_enabled' => env('FORMS_HONEYPOT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA v3 Configuration
    |--------------------------------------------------------------------------
    */
    'recaptcha' => [
        'enabled' => env('RECAPTCHA_ENABLED', false),
        'site_key' => env('RECAPTCHA_SITE_KEY', ''),
        'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
        'threshold' => env('RECAPTCHA_THRESHOLD', 0.5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile Configuration
    |--------------------------------------------------------------------------
    */
    'turnstile' => [
        'enabled' => env('TURNSTILE_ENABLED', false),
        'site_key' => env('TURNSTILE_SITE_KEY', ''),
        'secret_key' => env('TURNSTILE_SECRET_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Notifications
    |--------------------------------------------------------------------------
    */
    'email_from_name' => env('FORMS_EMAIL_FROM_NAME', 'Forms'),
    'email_from_address' => env('FORMS_EMAIL_FROM_ADDRESS', 'noreply@example.com'),

    /*
    |--------------------------------------------------------------------------
    | Submission Limits
    |--------------------------------------------------------------------------
    */
    'max_submissions_per_user' => env('FORMS_MAX_SUBMISSIONS_PER_USER', null),
    'max_submissions_per_ip' => env('FORMS_MAX_SUBMISSIONS_PER_IP', null),
];
