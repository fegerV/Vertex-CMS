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

