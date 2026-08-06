<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AiBrandMonitorController extends Controller
{
    /**
     * Отображение дашборда AI мониторинга
     */
    public function index()
    {
        // В реальной версии здесь были бы запросы к API (SerpApi, DataForSEO, или прямые запросы к LLM)
        // Для демонстрации генерируем реалистичные данные на основе настроек сайта
        
        $brandName = config('app.name', 'Vertex CMS');
        $competitors = ['Competitor A', 'Competitor B', 'Competitor C'];
        
        // Получаем данные из кэша или генерируем новые
        $data = Cache::remember('ai_brand_monitor_data', 3600, function () use ($brandName, $competitors) {
            return $this->generateAiAnalysisData($brandName, $competitors);
        });

        return view('admin.seo.ai-monitor.index', compact('data', 'brandName'));
    }

    /**
     * Принудительное обновление данных
     */
    public function refresh(Request $request)
    {
        Cache::forget('ai_brand_monitor_data');
        return redirect()->back()->with('success', 'Данные AI мониторинга обновлены.');
    }

    /**
     * Детальный отчет по упоминаниям
     */
    public function mentions(Request $request)
    {
        $data = Cache::get('ai_brand_monitor_data');
        $mentions = $data['mentions'] ?? [];
        
        // Фильтрация
        $filter = $request->get('filter', 'all'); // all, positive, negative, neutral
        if ($filter !== 'all') {
            $mentions = array_filter($mentions, fn($m) => $m['sentiment'] === $filter);
        }

        return view('admin.seo.ai-monitor.mentions', compact('mentions', 'filter'));
    }

    /**
     * Анализ источников (Citation Audit)
     */
    public function sources()
    {
        $data = Cache::get('ai_brand_monitor_data');
        $sources = $data['citations'] ?? [];
        return view('admin.seo.ai-monitor.sources', compact('sources'));
    }

    /**
     * Сравнение с конкурентами
     */
    public function competitors()
    {
        $data = Cache::get('ai_brand_monitor_data');
        $comparison = $data['competitor_analysis'] ?? [];
        return view('admin.seo.ai-monitor.competitors', compact('comparison'));
    }

    /**
     * Генерация рекомендаций (AI Opportunities)
     */
    public function opportunities()
    {
        $data = Cache::get('ai_brand_monitor_data');
        $opportunities = $data['opportunities'] ?? [];
        return view('admin.seo.ai-monitor.opportunities', compact('opportunities'));
    }

    /**
     * Генератор тестовых данных (Симуляция работы AI API)
     * В продакшене здесь будут вызовы к OpenAI API / SerpApi / Bing API
     */
    private function generateAiAnalysisData($brandName, $competitors)
    {
        $today = now();
        
        // 1. Упоминания и Тональность
        $mentions = [
            [
                'id' => 1,
                'date' => $today->subDays(1)->toDateString(),
                'query' => "Лучшая CMS для малого бизнеса",
                'model' => 'ChatGPT-4o',
                'snippet' => "... среди популярных решений выделяется {$brandName}, которая предлагает отличный баланс между простотой и функциональностью...",
                'sentiment' => 'positive',
                'url' => route('admin.dashboard'),
                'is_source' => true,
                'source_url' => 'https://example.com/blog/best-cms-2024'
            ],
            [
                'id' => 2,
                'date' => $today->subDays(3)->toDateString(),
                'query' => "Альтернативы WordPress",
                'model' => 'Claude 3.5',
                'snippet' => "... если вам нужно что-то более легкое, попробуйте {$brandName}. Однако некоторые пользователи отмечают нехватку плагинов...",
                'sentiment' => 'neutral',
                'url' => route('admin.dashboard'),
                'is_source' => false,
                'source_url' => null
            ],
            [
                'id' => 3,
                'date' => $today->subDays(5)->toDateString(),
                'query' => "Платформы с уязвимостями безопасности",
                'model' => 'Perplexity',
                'snippet' => "... в прошлом году были сообщения о проблемах в {$brandName}, но команда быстро выпустила патч...",
                'sentiment' => 'negative',
                'url' => route('admin.dashboard'),
                'is_source' => true,
                'source_url' => 'https://security-blog.example.com/cve-2023'
            ],
            [
                'id' => 4,
                'date' => $today->subDays(2)->toDateString(),
                'query' => "Как создать интернет магазин быстро",
                'model' => 'Gemini Pro',
                'snippet' => "... используйте {$brandName} для быстрого старта. Встроенные модули SEO и оплаты экономят время...",
                'sentiment' => 'positive',
                'url' => route('admin.dashboard'),
                'is_source' => true,
                'source_url' => 'https://ecommerce-guide.com/fast-setup'
            ],
        ];

        // 2. Статистика цитирования
        $citations = [
            ['page_title' => 'Документация: Установка', 'url' => '/docs/install', 'mentions_count' => 45, 'authority_score' => 92],
            ['page_title' => 'Блог: Тренды E-commerce 2024', 'url' => '/blog/ecommerce-trends', 'mentions_count' => 28, 'authority_score' => 85],
            ['page_title' => 'Страница: Преимущества', 'url' => '/features', 'mentions_count' => 15, 'authority_score' => 70],
            ['page_title' => 'Кейс: Магазин одежды', 'url' => '/cases/clothing-store', 'mentions_count' => 8, 'authority_score' => 60],
        ];

        // 3. Сравнение с конкурентами
        $comparison = [
            ['name' => $brandName, 'visibility_score' => 78, 'mention_volume' => 120, 'sentiment_ratio' => 0.75],
            ['name' => $competitors[0], 'visibility_score' => 92, 'mention_volume' => 450, 'sentiment_ratio' => 0.65],
            ['name' => $competitors[1], 'visibility_score' => 65, 'mention_volume' => 80, 'sentiment_ratio' => 0.80],
            ['name' => $competitors[2], 'visibility_score' => 45, 'mention_volume' => 30, 'sentiment_ratio' => 0.50],
        ];

        // 4. Возможности (Opportunities)
        $opportunities = [
            [
                'type' => 'content_gap',
                'title' => 'Отсутствие руководства по миграции',
                'description' => 'ИИ часто рекомендуют конкурентов при запросе "миграция с WordPress", так как у нас нет гайда.',
                'potential_traffic' => '+15%',
                'action' => 'Создать статью "Миграция на Vertex за 1 час"',
                'priority' => 'high'
            ],
            [
                'type' => 'schema_missing',
                'title' => 'Недостаток микроразметки FAQ',
                'description' => 'Страницы с вопросами не попадают в сниппеты AI Overviews.',
                'potential_traffic' => '+8%',
                'action' => 'Добавить Schema.org FAQPage на страницу поддержки',
                'priority' => 'medium'
            ],
            [
                'type' => 'authority_building',
                'title' => 'Низкая цитируемость технических статей',
                'description' => 'Технические блоги конкурентов цитируются в 3 раза чаще.',
                'potential_traffic' => '+20%',
                'action' => 'Опубликовать исследование производительности ядра',
                'priority' => 'high'
            ]
        ];

        // Агрегированная статистика
        $stats = [
            'total_mentions' => count($mentions) + 115, // эмуляция большего объема
            'positive_percent' => round((count(array_filter($mentions, fn($m) => $m['sentiment'] === 'positive')) / count($mentions)) * 100),
            'sources_count' => count(array_filter($mentions, fn($m) => $m['is_source'])),
            'visibility_trend' => '+12%', // рост за месяц
        ];

        return [
            'mentions' => $mentions,
            'citations' => $citations,
            'competitor_analysis' => $comparison,
            'opportunities' => $opportunities,
            'stats' => $stats,
            'last_updated' => now()
        ];
    }
}
