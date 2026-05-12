<?php

namespace App\Core\Support;

class SettingCatalog
{
    public static function groups(): array
    {
        return [
            'site' => [
                'label' => 'Основные',
                'description' => 'Базовые параметры сайта и публичного брендинга.',
                'fields' => [
                    'site.name' => ['label' => 'Название сайта', 'type' => 'string', 'input' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'site.url' => ['label' => 'URL сайта', 'type' => 'string', 'input' => 'url', 'rules' => ['required', 'url', 'max:500']],
                    'site.description' => ['label' => 'Описание сайта', 'type' => 'string', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
                    'site.logo' => ['label' => 'Media ID логотипа', 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                    'site.favicon' => ['label' => 'Media ID favicon', 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                    'site.locale' => ['label' => 'Локаль сайта', 'type' => 'string', 'input' => 'text', 'rules' => ['required', 'string', 'max:20']],
                    'site.admin_locale' => ['label' => 'Язык админ-панели', 'type' => 'string', 'input' => 'select', 'options' => ['ru' => 'Русский', 'en' => 'English'], 'rules' => ['required', 'string', 'max:5']],
                    'site.timezone' => ['label' => 'Часовой пояс', 'type' => 'string', 'input' => 'text', 'rules' => ['required', 'timezone']],
                ],
            ],
            'seo' => [
                'label' => 'SEO',
                'description' => 'Глобальные SEO-настройки и поведение индексации.',
                'fields' => [
                    'seo.default_title' => ['label' => 'SEO title по умолчанию', 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'seo.default_description' => ['label' => 'SEO description по умолчанию', 'type' => 'string', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:500']],
                    'seo.robots_enabled' => ['label' => 'Разрешить robots meta', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'seo.sitemap_enabled' => ['label' => 'Включить sitemap.xml', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'seo.organization_name' => ['label' => 'Название организации', 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'seo.organization_logo' => ['label' => 'Media ID логотипа организации', 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                    'seo.robots_txt' => ['label' => 'Содержимое robots.txt', 'type' => 'string', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:5000']],
                ],
            ],
            'api' => [
                'label' => 'API и Mobile',
                'description' => 'Параметры публичного API для мобильных приложений и внешних клиентов.',
                'fields' => [
                    'api.public_enabled' => ['label' => 'Включить публичный API', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'api.mobile_enabled' => ['label' => 'Включить mobile API', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'api.version' => ['label' => 'Версия API', 'type' => 'string', 'input' => 'text', 'rules' => ['required', 'string', 'max:20']],
                    'api.rate_limit_public' => ['label' => 'Public API rate limit', 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer', 'min:1']],
                    'api.rate_limit_mobile' => ['label' => 'Mobile API rate limit', 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer', 'min:1']],
                ],
            ],
            'ai' => [
                'label' => 'AI',
                'description' => 'Провайдеры нейросетей, ключи и базовое поведение AI-помощника.',
                'fields' => [
                    'ai.enabled' => ['label' => 'Включить AI-модуль', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'ai.default_provider' => ['label' => 'Провайдер по умолчанию', 'type' => 'string', 'input' => 'select', 'options' => ['openai' => 'OpenAI', 'anthropic' => 'Anthropic', 'custom' => 'Custom'], 'rules' => ['nullable', 'string', 'max:50']],
                    'ai.default_model' => ['label' => 'Модель по умолчанию', 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
                    'ai.monthly_budget' => ['label' => 'Месячный бюджет', 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    'ai.store_prompts' => ['label' => 'Хранить prompts', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'ai.store_responses' => ['label' => 'Хранить ответы', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'ai.allow_editor_use' => ['label' => 'Разрешить Editor использовать AI', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'ai.content_language' => ['label' => 'Язык контента', 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:20']],
                    'ai.brand_voice' => ['label' => 'Brand voice', 'type' => 'string', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
                    'ai.openai_api_key' => ['label' => 'OpenAI API key', 'type' => 'encrypted', 'input' => 'password', 'secret' => true, 'rules' => ['nullable', 'string', 'max:255']],
                    'ai.anthropic_api_key' => ['label' => 'Anthropic API key', 'type' => 'encrypted', 'input' => 'password', 'secret' => true, 'rules' => ['nullable', 'string', 'max:255']],
                    'ai.custom_api_base' => ['label' => 'Custom API base URL', 'type' => 'string', 'input' => 'url', 'rules' => ['nullable', 'url', 'max:500']],
                    'ai.custom_api_key' => ['label' => 'Custom API key', 'type' => 'encrypted', 'input' => 'password', 'secret' => true, 'rules' => ['nullable', 'string', 'max:255']],
                ],
            ],
            'pwa' => [
                'label' => 'PWA',
                'description' => 'Подготовка сайта к установке как progressive web app.',
                'fields' => [
                    'pwa.enabled' => ['label' => 'Включить PWA', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'pwa.name' => ['label' => 'PWA name', 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'pwa.short_name' => ['label' => 'PWA short name', 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:50']],
                    'pwa.theme_color' => ['label' => 'Theme color', 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:20']],
                    'pwa.background_color' => ['label' => 'Background color', 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:20']],
                    'pwa.display' => ['label' => 'Display mode', 'type' => 'string', 'input' => 'select', 'options' => ['standalone' => 'standalone', 'browser' => 'browser', 'minimal-ui' => 'minimal-ui'], 'rules' => ['nullable', 'string', 'max:30']],
                    'pwa.start_url' => ['label' => 'Start URL', 'type' => 'string', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'pwa.offline_page_id' => ['label' => 'Offline page ID', 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                    'pwa.icon_192' => ['label' => 'Media ID icon 192', 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                    'pwa.icon_512' => ['label' => 'Media ID icon 512', 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer']],
                ],
            ],
            'cache' => [
                'label' => 'Кеш',
                'description' => 'Поведение кеша и базовые performance-параметры.',
                'fields' => [
                    'cache.enabled' => ['label' => 'Включить кеш', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                    'cache.driver' => ['label' => 'Драйвер кеша', 'type' => 'string', 'input' => 'select', 'options' => ['file' => 'file', 'database' => 'database'], 'rules' => ['required', 'string', 'max:30']],
                    'cache.ttl' => ['label' => 'TTL в секундах', 'type' => 'integer', 'input' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    'cache.html_minify' => ['label' => 'Минификация HTML', 'type' => 'boolean', 'input' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                ],
            ],
            'maintenance' => [
                'label' => 'Режим обслуживания',
                'description' => 'Настройка страницы режима обслуживания и его поведения.',
                'fields' => [
                    'maintenance.enabled' => [
                        'label' => 'Включить режим обслуживания',
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'maintenance.theme' => [
                        'label' => 'Тема оформления',
                        'type' => 'string',
                        'input' => 'select',
                        'options' => [
                            'minimal' => 'Minimal (Минимализм)',
                            'dark' => 'Dark (Тёмная)',
                            'light' => 'Light (Светлая)',
                            'gradient' => 'Gradient (Градиент)',
                            'geometric' => 'Geometric (Геометрия)',
                            'nature' => 'Nature (Природа)',
                            'city' => 'City (Город)',
                            'abstract' => 'Abstract (Абстракция)',
                            'retro' => 'Retro (Ретро)',
                            'tech' => 'Tech (Технологии)',
                        ],
                        'rules' => ['required', 'string', 'max:50'],
                    ],
                    'maintenance.background_image' => [
                        'label' => 'Media ID фонового изображения',
                        'type' => 'integer',
                        'input' => 'number',
                        'rules' => ['nullable', 'integer'],
                    ],
                    'maintenance.background_blur' => [
                        'label' => 'Включить размытие фона',
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'maintenance.logo' => [
                        'label' => 'Media ID логотипа',
                        'type' => 'integer',
                        'input' => 'number',
                        'rules' => ['nullable', 'integer'],
                    ],
                    'maintenance.primary_color' => [
                        'label' => 'Основной цвет',
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:20'],
                    ],
                    'maintenance.secondary_color' => [
                        'label' => 'Вторичный цвет',
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:20'],
                    ],
                    'maintenance.title' => [
                        'label' => 'Заголовок',
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:255'],
                    ],
                    'maintenance.slogan' => [
                        'label' => 'Слоган',
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:500'],
                    ],
                    'maintenance.text' => [
                        'label' => 'Описание',
                        'type' => 'string',
                        'input' => 'textarea',
                        'rules' => ['nullable', 'string', 'max:2000'],
                    ],
                    'maintenance.show_login_form' => [
                        'label' => 'Показывать форму входа',
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                    ],
                    'maintenance.google_analytics_id' => [
                        'label' => 'Google Analytics ID',
                        'type' => 'string',
                        'input' => 'text',
                        'rules' => ['nullable', 'string', 'max:50'],
                    ],
                    'maintenance.excluded_pages' => [
                        'label' => 'Исключить страницы из обслуживания',
                        'type' => 'json',
                        'input' => 'textarea',
                        'rules' => ['nullable', 'string', 'json'],
                        'description' => 'Массив путей в JSON формате, например: ["admin*", "api/*"]. Поддерживает wildcards.',
                    ],
                    'maintenance.cache_compatibility' => [
                        'label' => 'Режим совместимости с кэшем',
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
                        'description' => 'Добавляет заголовки для совместимости с плагинами кэширования',
                    ],
                    'maintenance.http_status_code' => [
                        'label' => 'HTTP статус код',
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
                        'label' => 'Разрешенные IP (по одному на строке)',
                        'type' => 'string',
                        'input' => 'textarea',
                        'rules' => ['nullable', 'string'],
                        'description' => 'Эти IP обойдут режим обслуживания',
                    ],
                    'maintenance.bypass_for_admins' => [
                        'label' => 'Обход для администраторов',
                        'type' => 'boolean',
                        'input' => 'checkbox',
                        'rules' => ['nullable', 'boolean'],
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

