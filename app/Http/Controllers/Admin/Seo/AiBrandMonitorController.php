<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * AI Brand Monitor Controller - DEMO ONLY
 * 
 * WARNING: This controller provides SIMULATED data for demonstration purposes.
 * It does NOT make real API calls to SerpApi, OpenAI, Bing API, or any external service.
 * 
 * To enable real brand monitoring, implement actual API integrations and
 * set AI_FEATURE_BRAND_MONITOR=true in your environment configuration.
 * 
 * @deprecated Demo mode only - not for production use without real API integration
 */
class AiBrandMonitorController extends Controller
{
    /**
     * Display AI monitoring dashboard
     * 
     * NOTE: Returns simulated data when AI_FEATURE_BRAND_MONITOR is false
     */
    public function index()
    {
        // Check if feature is enabled
        $isDemoMode = !config('ai.features.brand_monitor', false);
        
        if ($isDemoMode) {
            Log::info('AI Brand Monitor accessed in demo mode - returning simulated data');
        }
        
        $brandName = config('app.name', 'Vertex CMS');
        $competitors = config('ai.demo.competitors', ['Competitor A', 'Competitor B', 'Competitor C']);
        
        // Get data from cache or generate new simulated data
        $data = Cache::remember(
            'ai_brand_monitor_data', 
            config('ai.fallback.cache_ttl', 3600), 
            function () use ($brandName, $competitors) {
                return $this->generateDemoData($brandName, $competitors);
            }
        );

        return view('admin.seo.ai-monitor.index', compact('data', 'brandName', 'isDemoMode'));
    }

    /**
     * Force refresh data (clears cache)
     */
    public function refresh(Request $request)
    {
        Cache::forget('ai_brand_monitor_data');
        
        $message = config('ai.features.brand_monitor', false) 
            ? 'Данные AI мониторинга обновлены.' 
            : 'Демо-данные AI мониторинга сброшены. Реальные данные недоступны без настройки AI_FEATURE_BRAND_MONITOR.';
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Detailed mentions report
     */
    public function mentions(Request $request)
    {
        $isDemoMode = !config('ai.features.brand_monitor', false);
        $data = Cache::get('ai_brand_monitor_data');
        $mentions = $data['mentions'] ?? [];
        
        // Filter by sentiment
        $filter = $request->get('filter', 'all'); // all, positive, negative, neutral
        if ($filter !== 'all') {
            $mentions = array_filter($mentions, fn($m) => $m['sentiment'] === $filter);
        }

        return view('admin.seo.ai-monitor.mentions', compact('mentions', 'filter', 'isDemoMode'));
    }

    /**
     * Citation Audit - Source analysis
     */
    public function sources()
    {
        $isDemoMode = !config('ai.features.brand_monitor', false);
        $data = Cache::get('ai_brand_monitor_data');
        $sources = $data['citations'] ?? [];
        return view('admin.seo.ai-monitor.sources', compact('sources', 'isDemoMode'));
    }

    /**
     * Competitor comparison
     */
    public function competitors()
    {
        $isDemoMode = !config('ai.features.brand_monitor', false);
        $data = Cache::get('ai_brand_monitor_data');
        $comparison = $data['competitor_analysis'] ?? [];
        return view('admin.seo.ai-monitor.competitors', compact('comparison', 'isDemoMode'));
    }

    /**
     * AI Opportunities - Recommendations generation
     */
    public function opportunities()
    {
        $isDemoMode = !config('ai.features.brand_monitor', false);
        $data = Cache::get('ai_brand_monitor_data');
        $opportunities = $data['opportunities'] ?? [];
        return view('admin.seo.ai-monitor.opportunities', compact('opportunities', 'isDemoMode'));
    }

    /**
     * Generate DEMO data (simulated AI API responses)
     * 
     * In production with real API integration, this method should:
     * - Call SerpApi or DataForSEO for search results
     * - Use OpenAI API for sentiment analysis
     * - Query Bing API for web mentions
     * - Aggregate real competitor data
     * 
     * @param string $brandName Brand name to monitor
     * @param array $competitors List of competitor names
     * @return array Simulated monitoring data
     */
    private function generateDemoData($brandName, $competitors)
    {
        $today = now();
        
        // 1. Mentions and Sentiment (SIMULATED)
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
                'source_url' => 'https://example.com/blog/best-cms-2024',
                'demo' => true,
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
                'source_url' => null,
                'demo' => true,
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
                'source_url' => 'https://security-blog.example.com/cve-2023',
                'demo' => true,
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
                'source_url' => 'https://ecommerce-guide.com/fast-setup',
                'demo' => true,
            ],
        ];

        // 2. Citation Statistics (SIMULATED)
        $citations = [
            ['page_title' => 'Документация: Установка', 'url' => '/docs/install', 'mentions_count' => 45, 'authority_score' => 92],
            ['page_title' => 'Блог: Тренды E-commerce 2024', 'url' => '/blog/ecommerce-trends', 'mentions_count' => 28, 'authority_score' => 85],
            ['page_title' => 'Страница: Преимущества', 'url' => '/features', 'mentions_count' => 15, 'authority_score' => 70],
            ['page_title' => 'Кейс: Магазин одежды', 'url' => '/cases/clothing-store', 'mentions_count' => 8, 'authority_score' => 60],
        ];

        // 3. Competitor Comparison (SIMULATED)
        $comparison = [
            ['name' => $brandName, 'visibility_score' => 78, 'mention_volume' => 120, 'sentiment_ratio' => 0.75],
            ['name' => $competitors[0], 'visibility_score' => 92, 'mention_volume' => 450, 'sentiment_ratio' => 0.65],
            ['name' => $competitors[1], 'visibility_score' => 65, 'mention_volume' => 80, 'sentiment_ratio' => 0.80],
            ['name' => $competitors[2], 'visibility_score' => 45, 'mention_volume' => 30, 'sentiment_ratio' => 0.50],
        ];

        // 4. Opportunities (SIMULATED)
        $opportunities = [
            [
                'type' => 'content_gap',
                'title' => 'Отсутствие руководства по миграции',
                'description' => 'ИИ часто рекомендуют конкурентов при запросе "миграция с WordPress", так как у нас нет гайда.',
                'potential_traffic' => '+15%',
                'action' => 'Создать статью "Миграция на Vertex за 1 час"',
                'priority' => 'high',
            ],
            [
                'type' => 'schema_missing',
                'title' => 'Недостаток микроразметки FAQ',
                'description' => 'Страницы с вопросами не попадают в сниппеты AI Overviews.',
                'potential_traffic' => '+8%',
                'action' => 'Добавить Schema.org FAQPage на страницу поддержки',
                'priority' => 'medium',
            ],
            [
                'type' => 'authority_building',
                'title' => 'Низкая цитируемость технических статей',
                'description' => 'Технические блоги конкурентов цитируются в 3 раза чаще.',
                'potential_traffic' => '+20%',
                'action' => 'Опубликовать исследование производительности ядра',
                'priority' => 'high',
            ],
        ];

        // Aggregated stats
        $stats = [
            'total_mentions' => count($mentions) + 115, // Emulate larger volume
            'positive_percent' => round((count(array_filter($mentions, fn($m) => $m['sentiment'] === 'positive')) / count($mentions)) * 100),
            'sources_count' => count(array_filter($mentions, fn($m) => $m['is_source'])),
            'visibility_trend' => '+12%', // Growth per month
            'is_demo' => true,
        ];

        return [
            'mentions' => $mentions,
            'citations' => $citations,
            'competitor_analysis' => $comparison,
            'opportunities' => $opportunities,
            'stats' => $stats,
            'last_updated' => now(),
            'is_demo_mode' => true,
        ];
    }
}
