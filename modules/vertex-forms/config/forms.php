<?php

return [

    /*
     * Global visual defaults.
     * Individual forms can set settings.theme to one of these values or
     * use "inherit" to follow the site-wide default from forms.default_theme.
     */
    'default_theme' => env('FORMS_DEFAULT_THEME', 'default'),
    'theme_presets' => [
        'inherit' => 'Inherit global theme',
        'default' => 'Default',
        'minimal' => 'Minimal',
        'modern' => 'Modern',
        'rounded' => 'Rounded',
        'dark' => 'Dark',
        'transparent' => 'Transparent',
    ],

    /*
     * Default sender email for form notifications.
     */
    'default_from_email' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
    'default_from_name' => env('MAIL_FROM_NAME', 'VertexCMS'),

    /*
     * Global limits for all forms (0 = unlimited).
     */
    'max_entries_global' => env('FORMS_MAX_ENTRIES_GLOBAL', 0),
    'daily_limit_per_ip_global' => env('FORMS_DAILY_LIMIT_PER_IP', 0),

    /*
     * Anti-spam settings.
     */
    'honeypot_enabled' => env('FORMS_HONEYPOT_ENABLED', true),
    'honeypot_field_name' => env('FORMS_HONEYPOT_FIELD', 'vertex_honeypot'),

    'recaptcha_enabled' => env('FORMS_RECAPTCHA_ENABLED', false),
    'recaptcha_version' => env('FORMS_RECAPTCHA_VERSION', 'v2'),
    'recaptcha_site_key' => env('RECAPTCHA_SITE_KEY'),
    'recaptcha_secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'recaptcha_min_score' => env('FORMS_RECAPTCHA_MIN_SCORE', 0.5),

    /*
     * Turnstile (Cloudflare) alternative.
     */
    'turnstile_enabled' => env('FORMS_TURNSTILE_ENABLED', false),
    'turnstile_site_key' => env('TURNSTILE_SITE_KEY'),
    'turnstile_secret_key' => env('TURNSTILE_SECRET_KEY'),

    /*
     * File upload settings.
     */
    'max_file_size' => env('FORMS_MAX_FILE_SIZE', 5 * 1024 * 1024), // 5 MB
    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],
    'upload_dir' => env('FORMS_UPLOAD_DIR', 'form-uploads'),
    'upload_disk' => env('FORMS_UPLOAD_DISK', 'local'),

    /*
     * Analytics & logging.
     */
    'log_form_views' => env('FORMS_LOG_VIEWS', true),
    'log_view_details' => env('FORMS_LOG_VIEW_DETAILS', false),
    'analytics_retention_days' => env('FORMS_ANALYTICS_RETENTION_DAYS', 90),

    /*
     * Import/Export.
     */
    'allow_import_export' => env('FORMS_ALLOW_IMPORT_EXPORT', true),

    /*
     * Notifications.
     */
    'notify_admin_emails' => env('FORMS_NOTIFY_ADMIN_EMAILS', []),
    'auto_response_enabled' => env('FORMS_AUTO_RESPONSE_ENABLED', true),

    /*
     * Calculator defaults.
     */
    'currency' => env('FORMS_CURRENCY', '₽'),
    'currency_position' => env('FORMS_CURRENCY_POSITION', 'before'),
    'thousand_separator' => env('FORMS_THOUSAND_SEPARATOR', ' '),
    'decimal_separator' => env('FORMS_DECIMAL_SEPARATOR', '.'),

    /*
     * Security.
     */
    'max_submissions_per_minute' => env('FORMS_MAX_SUBMISSIONS_PER_MINUTE', 10),
    'require_login_for_view' => env('FORMS_REQUIRE_LOGIN_VIEW', false),
    'require_login_for_submit' => env('FORMS_REQUIRE_LOGIN_SUBMIT', false),

    /*
     * Versioning.
     */
    'auto_snapshot_on_save' => env('FORMS_AUTO_SNAPSHOT', true),
    'max_snapshots_per_form' => env('FORMS_MAX_SNAPSHOTS', 50),

];
