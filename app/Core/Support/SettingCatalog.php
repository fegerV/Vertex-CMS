<?php

namespace App\Core\Support;

class SettingCatalog
{
    public static function groups(): array
    {
        return [
            'site' => [
                'label' => __('settings.groups.site'),
                'description' => __('settings.groups.site_desc'),
                'fields' => [
                    'site.name' => ['label' => __('settings.fields.site_name'), 'type' => 'string', 'input' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'site.url' => ['label' => __('settings.fields.site_url'), 'type' => 'string', 'input' => 'url', 'rules' => ['required', 'url', 'max:500']],
                    'site.description' => ['label' => __('settings.fields.site_description'), 'type' => 'string', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
                    'site.logo' => ['label' => __('settings.fields.site_logo'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                    'site.favicon' => ['label' => __('settings.fields.site_favicon'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                    'site.locale' => [
                        'label' => __('settings.fields.site_locale'), 
                        'type' => 'string', 
                        'input' => 'select', 
                        'options' => ['ru' => 'Русский', 'en' => 'English'],
                        'rules' => ['required', 'string', 'in:ru,en']
                    ],
                    'site.admin_locale' => ['label' => __('settings.fields.site_admin_locale'), 'type' => 'string', 'input' => 'select', 'options' => ['ru' => 'Русский', 'en' => 'English'], 'rules' => ['required', 'string', 'max:5']],
                    'site.timezone' => ['label' => __('settings.fields.site_timezone'), 'type' => 'string', 'input' => 'text', 'rules' => ['required', 'timezone']],
                ],
            ],
            'seo' => [
                'label' => __('settings.groups.seo'),
                'description' => __('settings.groups.seo_desc'),
                'fields' => [
                    'seo.default_title' => ['label' => __('settings.fields.seo_default_title'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'seo.default_description' => ['label' => __('settings.fields.seo_default_description'), 'type' => 'string', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:500']],
                    'seo.robots_enabled' => ['label' => __('settings.fields.seo_robots_enabled'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'seo.sitemap_enabled' => ['label' => __('settings.fields.seo_sitemap_enabled'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'seo.organization_name' => ['label' => __('settings.fields.seo_organization_name'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'seo.organization_logo' => ['label' => __('settings.fields.seo_organization_logo'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                    'seo.robots_txt' => ['label' => __('settings.fields.seo_robots_txt'), 'type' => 'string', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:5000']],
                ],
            ],
            'api' => [
                'label' => __('settings.groups.api'),
                'description' => __('settings.groups.api_desc'),
                'fields' => [
                    'api.public_enabled' => ['label' => __('settings.fields.api_public_enabled'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'api.mobile_enabled' => ['label' => __('settings.fields.api_mobile_enabled'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'api.version' => ['label' => __('settings.fields.api_version'), 'type' => 'string', 'input' => 'text', 'rules' => ['required', 'string', 'max:20']],
                    'api.rate_limit_public' => ['label' => __('settings.fields.api_rate_limit_public'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer', 'min:1']],
                    'api.rate_limit_mobile' => ['label' => __('settings.fields.api_rate_limit_mobile'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer', 'min:1']],
                ],
            ],
            'ai' => [
                'label' => __('settings.groups.ai'),
                'description' => __('settings.groups.ai_desc'),
                'fields' => [
                    'ai.enabled' => ['label' => __('settings.fields.ai_enabled'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'ai.default_provider' => ['label' => __('settings.fields.ai_default_provider'), 'type' => 'string', 'input' => 'select', 'options' => ['openai' => 'OpenAI', 'anthropic' => 'Anthropic', 'custom' => 'Custom'], 'rules' => ['nullable', 'string', 'max:50']],
                    'ai.default_model' => ['label' => __('settings.fields.ai_default_model'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
                    'ai.monthly_budget' => ['label' => __('settings.fields.ai_monthly_budget'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    'ai.store_prompts' => ['label' => __('settings.fields.ai_store_prompts'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'ai.store_responses' => ['label' => __('settings.fields.ai_store_responses'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'ai.allow_editor_use' => ['label' => __('settings.fields.ai_allow_editor_use'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'ai.content_language' => ['label' => __('settings.fields.ai_content_language'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:20']],
                    'ai.brand_voice' => ['label' => __('settings.fields.ai_brand_voice'), 'type' => 'string', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
                    'ai.openai_api_key' => ['label' => __('settings.fields.ai_openai_api_key'), 'type' => 'encrypted', 'input' => 'password', 'secret' => true, 'rules' => ['nullable', 'string', 'max:255']],
                    'ai.anthropic_api_key' => ['label' => __('settings.fields.ai_anthropic_api_key'), 'type' => 'encrypted', 'input' => 'password', 'secret' => true, 'rules' => ['nullable', 'string', 'max:255']],
                    'ai.custom_api_base' => ['label' => __('settings.fields.ai_custom_api_base'), 'type' => 'string', 'input' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
                    'ai.custom_api_key' => ['label' => __('settings.fields.ai_custom_api_key'), 'type' => 'encrypted', 'input' => 'password', 'secret' => true, 'rules' => ['nullable', 'string', 'max:255']],
                ],
            ],
            'pwa' => [
                'label' => __('settings.groups.pwa'),
                'description' => __('settings.groups.pwa_desc'),
                'fields' => [
                    'pwa.enabled' => ['label' => __('settings.fields.pwa_enabled'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'pwa.name' => ['label' => __('settings.fields.pwa_name'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'pwa.short_name' => ['label' => __('settings.fields.pwa_short_name'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:50']],
                    'pwa.theme_color' => ['label' => __('settings.fields.pwa_theme_color'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:20']],
                    'pwa.background_color' => ['label' => __('settings.fields.pwa_background_color'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:20']],
                    'pwa.display' => ['label' => __('settings.fields.pwa_display'), 'type' => 'string', 'input' => 'select', 'options' => ['standalone' => 'standalone', 'browser' => 'browser', 'minimal-ui' => 'minimal-ui'], 'rules' => ['nullable', 'string', 'max:30']],
                    'pwa.start_url' => ['label' => __('settings.fields.pwa_start_url'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'pwa.offline_page_id' => ['label' => __('settings.fields.pwa_offline_page_id'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                    'pwa.icon_192' => ['label' => __('settings.fields.pwa_icon_192'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                    'pwa.icon_512' => ['label' => __('settings.fields.pwa_icon_512'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                ],
            ],
            'cache' => [
                'label' => __('settings.groups.cache'),
                'description' => __('settings.groups.cache_desc'),
                'fields' => [
                    'cache.enabled' => ['label' => __('settings.fields.cache_enabled'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'cache.driver' => ['label' => __('settings.fields.cache_driver'), 'type' => 'string', 'input' => 'select', 'options' => ['file' => 'file', 'database' => 'database'], 'rules' => ['required', 'string', 'max:30']],
                    'cache.ttl' => ['label' => __('settings.fields.cache_ttl'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    'cache.html_minify' => ['label' => __('settings.fields.cache_html_minify'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                ],
            ],
            'maintenance' => [
                'label' => __('settings.groups.maintenance'),
                'description' => __('settings.groups.maintenance_desc'),
                'fields' => [
                    'maintenance.enabled' => [
                        'label' => __('settings.fields.maintenance_enabled'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'maintenance.theme' => [
                        'label' => __('settings.fields.maintenance_theme'),
                        'type' => 'string',
                        'input' => 'select',
                        'options' => [
                            'minimal' => 'Minimal',
                            'dark' => 'Dark',
                            'light' => 'Light',
                            'gradient' => 'Gradient',
                            'geometric' => 'Geometric',
                            'nature' => 'Nature',
                            'city' => 'City',
                            'abstract' => 'Abstract',
                            'retro' => 'Retro',
                            'tech' => 'Tech',
                        ],
                        'rules' => ['required', 'string', 'max:50'],
                    ],
                    'maintenance.background_image' => [
                        'label' => __('settings.fields.maintenance_background_image'),
                        'type' => 'integer',
                        'input' => 'number',
                        'rules' => ['nullable', 'integer'],
                    ],
                    'maintenance.background_blur' => [
                        'label' => __('settings.fields.maintenance_background_blur'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'maintenance.logo' => [
                        'label' => __('settings.fields.maintenance_logo'),
                        'type' => 'integer',
                        'input' => 'number',
                        'rules' => ['nullable', 'integer'],
                    ],
                    'maintenance.primary_color' => [
                        'label' => __('settings.fields.maintenance_primary_color'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:20'],
                    ],
                    'maintenance.secondary_color' => [
                        'label' => __('settings.fields.maintenance_secondary_color'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:20'],
                    ],
                    'maintenance.title' => [
                        'label' => __('settings.fields.maintenance_title'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:255'],
                    ],
                    'maintenance.slogan' => [
                        'label' => __('settings.fields.maintenance_slogan'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:500'],
                    ],
                    'maintenance.text' => [
                        'label' => __('settings.fields.maintenance_text'),
                        'type' => 'string',
                        'input' => 'textarea',
                        'rules' => ['nullable', 'string', 'max:2000'],
                    ],
                    'maintenance.show_login_form' => [
                        'label' => __('settings.fields.maintenance_show_login_form'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'maintenance.google_analytics_id' => [
                        'label' => __('settings.fields.maintenance_google_analytics_id'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:50'],
                    ],
                    'maintenance.excluded_pages' => [
                        'label' => __('settings.fields.maintenance_excluded_pages'),
                        'type' => 'json',
                        'input' => 'textarea',
                        'rules' => ['nullable', 'string', 'json'],
                        'description' => __('settings.fields.maintenance_excluded_pages_desc'),
                    ],
                    'maintenance.cache_compatibility' => [
                        'label' => __('settings.fields.maintenance_cache_compatibility'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                        'description' => __('settings.fields.maintenance_cache_compatibility_desc'),
                    ],
                    'maintenance.http_status_code' => [
                        'label' => __('settings.fields.maintenance_http_status_code'),
                        'type' => 'string',
                        'input' => 'select',
                        'options' => [
                            '503' => '503 Service Unavailable',
                            '200' => '200 OK',
                            '302' => '302 Found',
                        ],
                        'rules' => ['required', 'string', 'in:503,200,302'],
                    ],
                    'maintenance.allowed_ips' => [
                        'label' => __('settings.fields.maintenance_allowed_ips'),
                        'type' => 'string',
                        'input' => 'textarea',
                        'rules' => ['nullable', 'string'],
                        'description' => __('settings.fields.maintenance_allowed_ips_desc'),
                    ],
                    'maintenance.bypass_for_admins' => [
                        'label' => __('settings.fields.maintenance_bypass_for_admins'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                ],
            ],
            'mail' => [
                'label' => __('settings.groups.mail'),
                'description' => __('settings.groups.mail_desc'),
                'fields' => [
                    'mail.driver' => [
                        'label' => __('settings.fields.mail_driver'),
                        'type' => 'string',
                        'input' => 'select',
                        'options' => [
                            'smtp' => 'SMTP',
                            'sendmail' => 'Sendmail',
                            'mailgun' => 'Mailgun',
                            'ses' => 'Amazon SES',
                            'postmark' => 'Postmark',
                            'log' => 'Log (отладка)',
                        ],
                        'rules' => ['required', 'string', 'max:50'],
                    ],
                    'mail.host' => ['label' => __('settings.fields.mail_host'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'mail.port' => ['label' => __('settings.fields.mail_port'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:10']],
                    'mail.encryption' => ['label' => __('settings.fields.mail_encryption'), 'type' => 'string', 'input' => 'select', 'options' => ['' => 'Нет', 'tls' => 'TLS', 'ssl' => 'SSL'], 'rules' => ['nullable', 'string', 'max:10']],
                    'mail.username' => ['label' => __('settings.fields.mail_username'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'mail.password' => ['label' => __('settings.fields.mail_password'), 'type' => 'string', 'input' => 'password', 'rules' => ['nullable', 'string', 'max:255'], 'secret' => true],
                    'mail.from_address' => ['label' => __('settings.fields.mail_from_address'), 'type' => 'string', 'input' => 'email', 'rules' => ['nullable', 'email', 'max:255']],
                    'mail.from_name' => ['label' => __('settings.fields.mail_from_name'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'mail.reply_to_address' => ['label' => __('settings.fields.mail_reply_to_address'), 'type' => 'string', 'input' => 'email', 'rules' => ['nullable', 'email', 'max:255']],
                    'mail.reply_to_name' => ['label' => __('settings.fields.mail_reply_to_name'), 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'mail.test_recipient' => ['label' => __('settings.fields.mail_test_recipient'), 'type' => 'string', 'input' => 'email', 'rules' => ['nullable', 'email', 'max:255']],
                    'mail.queue_enabled' => ['label' => __('settings.fields.mail_queue_enabled'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'mail.retry_attempts' => ['label' => __('settings.fields.mail_retry_attempts'), 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer', 'min:1', 'max:10']],
                    'mail.log_all' => ['label' => __('settings.fields.mail_log_all'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'mail.attach_assets' => ['label' => __('settings.fields.mail_attach_assets'), 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                ],
            ],
            'telegram' => [
                'label' => __('settings.groups.telegram'),
                'description' => __('settings.groups.telegram_desc'),
                'fields' => [
                    'telegram.enabled' => [
                        'label' => __('settings.fields.telegram_enabled'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'telegram.username' => [
                        'label' => __('settings.fields.telegram_username'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:255'],
                    ],
                    'telegram.bot_token' => [
                        'label' => __('settings.fields.telegram_bot_token'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                        'secret' => true,
                    ],
                    'telegram.chat_id' => [
                        'label' => __('settings.fields.telegram_chat_id'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                    ],
                    'telegram.widget_style' => [
                        'label' => __('settings.fields.telegram_widget_style'),
                        'type' => 'string',
                        'input' => 'select',
                        'options' => [
                            'button' => 'Button',
                            'badge' => 'Badge',
                            'floating' => 'Floating',
                            'inline' => 'Inline',
                        ],
                        'rules' => ['required', 'string', 'max:20'],
                    ],
                    'telegram.widget_position' => [
                        'label' => __('settings.fields.telegram_widget_position'),
                        'type' => 'string',
                        'input' => 'select',
                        'options' => [
                            'bottom-right' => 'Bottom Right',
                            'bottom-left' => 'Bottom Left',
                            'bottom-center' => 'Bottom Center',
                        ],
                        'rules' => ['nullable', 'string', 'max:20'],
                    ],
                    'telegram.greeting' => [
                        'label' => __('settings.fields.telegram_greeting'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:255'],
                    ],
                    'telegram.color' => [
                        'label' => __('settings.fields.telegram_color'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:20'],
                    ],
                    'telegram.show_online_status' => [
                        'label' => __('settings.fields.telegram_show_online_status'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'telegram.message_prefill' => [
                        'label' => __('settings.fields.telegram_message_prefill'),
                        'type' => 'string',
                        'input' => 'textarea',
                        'rules' => ['nullable', 'string', 'max:500'],
                    ],
                ],
            ],
            'forms' => [
                'label' => __('settings.groups.forms'),
                'description' => __('settings.groups.forms_desc'),
                'fields' => [
                    'forms.default_theme' => [
                        'label' => __('settings.fields.forms_default_theme'),
                        'type' => 'string',
                        'input' => 'select',
                        'options' => [
                            'default' => 'Default',
                            'minimal' => 'Minimal',
                            'modern' => 'Modern',
                            'rounded' => 'Rounded',
                        ],
                        'rules' => ['nullable', 'string', 'max:50'],
                    ],
                    'forms.require_email_verification' => [
                        'label' => __('settings.fields.forms_require_email_verification'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'forms.honeypot_enabled' => [
                        'label' => __('settings.fields.forms_honeypot_enabled'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'forms.recaptcha_enabled' => [
                        'label' => __('settings.fields.forms_recaptcha_enabled'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'forms.recaptcha_site_key' => [
                        'label' => __('settings.fields.forms_recaptcha_site_key'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:255'],
                    ],
                    'forms.recaptcha_secret_key' => [
                        'label' => __('settings.fields.forms_recaptcha_secret_key'),
                        'type' => 'string',
                        'input' => 'password',
                        'rules' => ['nullable', 'string', 'max:255'],
                        'secret' => true,
                    ],
                    'forms.recaptcha_version' => [
                        'label' => __('settings.fields.forms_recaptcha_version'),
                        'type' => 'string',
                        'input' => 'select',
                        'options' => [
                            'v2' => 'reCAPTCHA v2',
                            'v3' => 'reCAPTCHA v3',
                        ],
                        'rules' => ['nullable', 'string', 'max:10'],
                    ],
                    'forms.notify_admin' => [
                        'label' => __('settings.fields.forms_notify_admin'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'forms.admin_emails' => [
                        'label' => __('settings.fields.forms_admin_emails'),
                        'type' => 'string',
                        'input' => 'textarea',
                        'rules' => ['nullable', 'string'],
                    ],
                    'forms.notify_user' => [
                        'label' => __('settings.fields.forms_notify_user'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'forms.user_email_template' => [
                        'label' => __('settings.fields.forms_user_email_template'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                    ],
                    'forms.autoresponder_subject' => [
                        'label' => __('settings.fields.forms_autoresponder_subject'),
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:255'],
                    ],
                    'forms.autoresponder_body' => [
                        'label' => __('settings.fields.forms_autoresponder_body'),
                        'type' => 'string',
                        'input' => 'textarea',
                        'rules' => ['nullable', 'string'],
                    ],
                    'forms.allow_duplicate' => [
                        'label' => __('settings.fields.forms_allow_duplicate'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'forms.max_entries' => [
                        'label' => __('settings.fields.forms_max_entries'),
                        'type' => 'integer',
                        'input' => 'number',
                        'rules' => ['nullable', 'integer', 'min:0'],
                    ],
                    'forms.daily_limit_per_ip' => [
                        'label' => __('settings.fields.forms_daily_limit_per_ip'),
                        'type' => 'integer',
                        'input' => 'number',
                        'rules' => ['nullable', 'integer', 'min:0'],
                    ],
                    'forms.show_entry_count' => [
                        'label' => __('settings.fields.forms_show_entry_count'),
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'forms.custom_css' => [
                        'label' => __('settings.fields.forms_custom_css'),
                        'type' => 'string',
                        'input' => 'textarea',
                        'rules' => ['nullable', 'string'],
                    ],
                ],
            ],
        ];
    }

    public static function definitions(): array
    {
        return collect(self::groups())
            ->pluck('fields')
            ->collapse()
            ->all();
    }

    public static function definition(string $key): ?array
    {
        return self::definitions()[$key] ?? null;
    }

    public static function publicSiteKeys(): array
    {
        return [
            'site.name',
            'site.url',
            'site.description',
            'site.locale',
            'seo.default_title',
            'seo.default_description',
            'api.public_enabled',
            'api.mobile_enabled',
            'api.version',
            'pwa.enabled',
            'pwa.name',
            'pwa.short_name',
            'pwa.theme_color',
            'pwa.background_color',
            'pwa.display',
            'pwa.start_url',
        ];
    }

    public static function secretKeys(): array
    {
        return collect(self::definitions())
            ->filter(fn (array $definition) => (bool) ($definition['secret'] ?? false))
            ->keys()
            ->all();
    }
}
