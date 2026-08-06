<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Отображение главной страницы справки
     */
    public function index()
    {
        $sections = [
            'dashboard' => [
                'title' => 'SEO Дашборд',
                'icon' => 'bi-speedometer2',
                'description' => 'Общий обзор здоровья вашего сайта и ключевых метрик SEO.',
                'topics' => [
                    ['name' => 'Понимание SEO Score', 'anchor' => 'seo-score'],
                    ['name' => 'Интерпретация графиков', 'anchor' => 'charts'],
                    ['name' => 'Работа с виджетами', 'anchor' => 'widgets'],
                ]
            ],
            'analysis' => [
                'title' => 'Анализ контента',
                'icon' => 'bi-file-text',
                'description' => 'Глубокий аудит отдельных страниц и постов для улучшения их видимости.',
                'topics' => [
                    ['name' => 'Как работает анализ', 'anchor' => 'how-analysis-works'],
                    ['name' => 'Оптимизация мета-тегов', 'anchor' => 'meta-tags'],
                    ['name' => 'Работа с заголовками', 'anchor' => 'headings'],
                    ['name' => 'Плотность ключевых слов', 'anchor' => 'keyword-density'],
                ]
            ],
            'bulk-editor' => [
                'title' => 'Массовое редактирование',
                'icon' => 'bi-table',
                'description' => 'Быстрое изменение SEO-данных для множества страниц одновременно.',
                'topics' => [
                    ['name' => 'Фильтрация и поиск', 'anchor' => 'filtering'],
                    ['name' => 'Пакетное обновление', 'anchor' => 'bulk-update'],
                    ['name' => 'Экспорт данных', 'anchor' => 'export'],
                ]
            ],
            'redirects' => [
                'title' => 'Менеджер редиректов',
                'icon' => 'bi-arrow-left-right',
                'description' => 'Управление переадресациями и мониторинг ошибок 404.',
                'topics' => [
                    ['name' => 'Типы редиректов (301 vs 302)', 'anchor' => 'redirect-types'],
                    ['name' => 'Мониторинг 404', 'anchor' => '404-monitor'],
                    ['name' => 'Импорт из логов', 'anchor' => 'import-logs'],
                    ['name' => 'Регулярные выражения', 'anchor' => 'regex'],
                ]
            ],
            'social-media' => [
                'title' => 'Социальные сети (Open Graph)',
                'icon' => 'bi-share',
                'description' => 'Настройка отображения ссылок в социальных сетях и мессенджерах.',
                'topics' => [
                    ['name' => 'Что такое Open Graph', 'anchor' => 'what-is-og'],
                    ['name' => 'Twitter Cards', 'anchor' => 'twitter-cards'],
                    ['name' => 'Загрузка изображений', 'anchor' => 'og-images'],
                    ['name' => 'Предпросмотр', 'anchor' => 'preview'],
                ]
            ],
            'semantics' => [
                'title' => 'Семантическое ядро',
                'icon' => 'bi-key',
                'description' => 'Управление ключевыми словами и их распределение по сайту.',
                'topics' => [
                    ['name' => 'Сбор семантики', 'anchor' => 'keyword-research'],
                    ['name' => 'Кластеризация', 'anchor' => 'clustering'],
                    ['name' => 'Привязка к страницам', 'anchor' => 'page-mapping'],
                ]
            ],
            'internal-links' => [
                'title' => 'Внутренние ссылки',
                'icon' => 'bi-link-45deg',
                'description' => 'Анализ структуры ссылок и автоматическая перелинковка.',
                'topics' => [
                    ['name' => 'Граф связей', 'anchor' => 'link-graph'],
                    ['name' => 'Сиротские страницы', 'anchor' => 'orphan-pages'],
                    ['name' => 'Авто-линки', 'anchor' => 'auto-links'],
                ]
            ],
            'ai-assistant' => [
                'title' => 'AI Ассистент',
                'icon' => 'bi-robot',
                'description' => 'Генерация контента и мета-тегов с помощью искусственного интеллекта.',
                'topics' => [
                    ['name' => 'Генерация мета-тегов', 'anchor' => 'ai-meta'],
                    ['name' => 'Написание контента', 'anchor' => 'ai-content'],
                    ['name' => 'Работа с промптами', 'anchor' => 'prompts'],
                    ['name' => 'Лимиты использования', 'anchor' => 'limits'],
                ]
            ],
            'schema' => [
                'title' => 'Конструктор Schema.org',
                'icon' => 'bi-code-square',
                'description' => 'Создание микроразметки для расширенных сниппетов в поиске.',
                'topics' => [
                    ['name' => 'Типы схем', 'anchor' => 'schema-types'],
                    ['name' => 'JSON-LD формат', 'anchor' => 'json-ld'],
                    ['name' => 'Тестирование разметки', 'anchor' => 'testing'],
                ]
            ],
            'indexnow' => [
                'title' => 'Мгновенная индексация',
                'icon' => 'bi-lightning-charge',
                'description' => 'Автоматическая отправка URL в поисковые системы через IndexNow.',
                'topics' => [
                    ['name' => 'Настройка API ключа', 'anchor' => 'api-key'],
                    ['name' => 'Автоматическая отправка', 'anchor' => 'auto-submit'],
                    ['name' => 'Ручная отправка', 'anchor' => 'manual-submit'],
                ]
            ],
            'keyword-maps' => [
                'title' => 'Карты ключевых слов',
                'icon' => 'bi-map',
                'description' => 'Автоматическое создание внутренних ссылок по ключевым фразам.',
                'topics' => [
                    ['name' => 'Создание правил', 'anchor' => 'creating-rules'],
                    ['name' => 'AI варианты фраз', 'anchor' => 'ai-variations'],
                    ['name' => 'Настройки применения', 'anchor' => 'rule-settings'],
                ]
            ],
            'images' => [
                'title' => 'Анализатор изображений',
                'icon' => 'bi-image',
                'description' => 'Оптимизация картинок: ALT теги, сжатие, lazy load.',
                'topics' => [
                    ['name' => 'Оптимизация ALT', 'anchor' => 'alt-optimization'],
                    ['name' => 'Сжатие WebP', 'anchor' => 'webp-compression'],
                    ['name' => 'Lazy Load', 'anchor' => 'lazy-load'],
                ]
            ],
            'tools' => [
                'title' => 'Инструменты и отладка',
                'icon' => 'bi-tools',
                'description' => 'Утилиты для очистки кэша, сброса данных и восстановления.',
                'topics' => [
                    ['name' => 'Очистка данных', 'anchor' => 'cleanup'],
                    ['name' => 'Перестройка индексов', 'anchor' => 'rebuild-index'],
                    ['name' => 'Восстановление таблиц', 'anchor' => 'restore-tables'],
                ]
            ],
        ];

        return view('admin.seo.help.index', compact('sections'));
    }

    /**
     * Просмотр конкретной статьи справки
     */
    public function show($section, $topic = null)
    {
        $content = $this->getHelpContent($section, $topic);
        
        if (!$content) {
            abort(404, 'Статья справки не найдена');
        }

        return view('admin.seo.help.article', compact('content', 'section', 'topic'));
    }

    /**
     * Поиск по справке
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $results = [];

        if (strlen($query) >= 3) {
            // Здесь будет логика поиска по контенту справки
            // Пока возвращаем заглушку
            $results = [
                ['title' => 'Как настроить редиректы?', 'section' => 'redirects', 'excerpt' => 'Пошаговое руководство по созданию 301 и 302 редиректов...'],
                ['title' => 'Оптимизация изображений', 'section' => 'images', 'excerpt' => 'Как сжимать изображения и добавлять ALT теги автоматически...'],
            ];
        }

        return view('admin.seo.help.search', compact('results', 'query'));
    }

    /**
     * Получение контента статьи
     */
    private function getHelpContent($section, $topic)
    {
        $files = [
            'dashboard' => [
                'seo-score' => 'help_dashboard_seo_score.md',
                'charts' => 'help_dashboard_charts.md',
                'widgets' => 'help_dashboard_widgets.md',
            ],
            'analysis' => [
                'how-analysis-works' => 'help_analysis_how.md',
                'meta-tags' => 'help_analysis_meta.md',
                'headings' => 'help_analysis_headings.md',
                'keyword-density' => 'help_analysis_density.md',
            ],
            'redirects' => [
                'redirect-types' => 'help_redirects_types.md',
                '404-monitor' => 'help_redirects_404.md',
                'import-logs' => 'help_redirects_import.md',
                'regex' => 'help_redirects_regex.md',
            ],
            'social-media' => [
                'what-is-og' => 'help_social_og.md',
                'twitter-cards' => 'help_social_twitter.md',
                'og-images' => 'help_social_images.md',
                'preview' => 'help_social_preview.md',
            ],
            'ai-assistant' => [
                'ai-meta' => 'help_ai_meta.md',
                'ai-content' => 'help_ai_content.md',
                'prompts' => 'help_ai_prompts.md',
                'limits' => 'help_ai_limits.md',
            ],
            'schema' => [
                'schema-types' => 'help_schema_types.md',
                'json-ld' => 'help_schema_jsonld.md',
                'testing' => 'help_schema_testing.md',
            ],
            'indexnow' => [
                'api-key' => 'help_indexnow_api.md',
                'auto-submit' => 'help_indexnow_auto.md',
                'manual-submit' => 'help_indexnow_manual.md',
            ],
            'keyword-maps' => [
                'creating-rules' => 'help_keywords_rules.md',
                'ai-variations' => 'help_keywords_ai.md',
                'rule-settings' => 'help_keywords_settings.md',
            ],
            'images' => [
                'alt-optimization' => 'help_images_alt.md',
                'webp-compression' => 'help_images_webp.md',
                'lazy-load' => 'help_images_lazy.md',
            ],
            'tools' => [
                'cleanup' => 'help_tools_cleanup.md',
                'rebuild-index' => 'help_tools_rebuild.md',
                'restore-tables' => 'help_tools_restore.md',
            ],
        ];

        if (!isset($files[$section][$topic])) {
            return null;
        }

        $filename = $files[$section][$topic];
        $path = resource_path('views/admin/seo/help/articles/' . $filename);

        if (!file_exists($path)) {
            return $this->generateDefaultContent($section, $topic);
        }

        return [
            'title' => $this->formatTitle($topic),
            'section' => $section,
            'content' => file_get_contents($path),
            'updated_at' => date('d.m.Y', filemtime($path)),
        ];
    }

    /**
     * Генерация контента по умолчанию, если файл не найден
     */
    private function generateDefaultContent($section, $topic)
    {
        $titles = [
            'dashboard' => 'Дашборд',
            'analysis' => 'Анализ контента',
            'redirects' => 'Редиректы',
            'social-media' => 'Социальные сети',
            'ai-assistant' => 'AI Ассистент',
            'schema' => 'Schema.org',
            'indexnow' => 'Мгновенная индексация',
            'keyword-maps' => 'Карты ключевых слов',
            'images' => 'Изображения',
            'tools' => 'Инструменты',
        ];

        $sectionTitle = $titles[$section] ?? $section;
        $topicTitle = $this->formatTitle($topic);

        return [
            'title' => "$topicTitle ($sectionTitle)",
            'section' => $section,
            'content' => "# $topicTitle\n\nРаздел находится в разработке. Подробная документация появится в ближайшее время.\n\nВы можете обратиться в службу поддержки для получения консультации по этому вопросу.",
            'updated_at' => date('d.m.Y'),
        ];
    }

    /**
     * Форматирование заголовка из slug
     */
    private function formatTitle($slug)
    {
        return ucwords(str_replace('-', ' ', $slug));
    }
}
