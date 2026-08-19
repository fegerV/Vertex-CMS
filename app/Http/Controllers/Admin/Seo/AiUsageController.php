<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AiUsageController extends Controller
{
    /**
     * Отображение дашборда использования ИИ
     */
    public function index()
    {
        $currentMonth = now()->format('Y-m');
        $lastSync = Cache::get('ai_usage_last_sync');
        
        // Получаем статистику использования по категориям
        $usageStats = $this->calculateUsageStats($currentMonth);
        
        // Лимиты по тарифу (можно вынести в настройки)
        $limits = config('seo.ai_limits', [
            'keywords_research' => 1000,
            'image_alt' => 500,
            'content_write' => 50000,
            'chat' => 1000,
            'bulk_meta' => 200,
            'fix_ai' => 300,
            'write_more' => 1000,
            'article_master' => 50,
            'blog_idea' => 200,
            'blog_plan' => 100,
            'blog_intro' => 200,
            'blog_conclusion' => 200,
            'post_title' => 300,
            'topic_research' => 100,
            'seo_title' => 500,
            'seo_description' => 500,
            'paragraph' => 1000,
            'paragraph_rewrite' => 500,
            'sentence_expander' => 500,
            'summarizer' => 300,
            'grammar_fix' => 500,
            'analogy' => 200,
            'product_description' => 300,
            'product_pros_cons' => 200,
            'product_review' => 200,
            'faq' => 300,
            'comment_reply' => 500,
            'bio' => 100,
            'company_history' => 50,
            'job_description' => 100,
            'testimonial' => 100,
            'facebook_post' => 200,
            'facebook_comment_reply' => 300,
            'tweet' => 300,
            'tweet_reply' => 300,
            'instagram_caption' => 300,
            'email' => 200,
            'email_reply' => 200,
            'vijdh' => 100,
            'irss' => 100,
            'par' => 100,
            'hero' => 100,
            'spin' => 200,
            'dpm' => 100,
            'youtube_script' => 50,
            'youtube_description' => 200,
            'podcast_plan' => 50,
            'recipe' => 100,
            'free_form' => 1000,
            'ai_team' => 500,
            'seo_meta' => 500,
            'open_graph' => 300,
            'link_opportunities' => 200,
            'related_posts' => 200,
            'link_suggestions' => 300,
        ]);

        return view('admin.seo.ai.usage', compact('usageStats', 'limits', 'lastSync', 'currentMonth'));
    }

    /**
     * Синхронизация данных использования с API
     */
    public function sync(Request $request)
    {
        Cache::put('ai_usage_last_sync', now(), 3600);

        return redirect()->back()->with('info', 'External AI usage synchronization is not configured yet.');
    }

    /**
     * Расчет статистики использования за месяц
     */
    private function calculateUsageStats($month)
    {
        $features = array_keys(config('seo.ai_limits', []));

        return collect($features)->mapWithKeys(fn (string $feature): array => [
            $feature => 0,
        ])->all();
    }

    /**
     * Детальная статистика по конкретной функции
     */
    public function details($feature, Request $request)
    {
        $period = $request->get('period', 'month');
        
        // Логика получения детальной статистики
        $details = [
            'feature' => $feature,
            'period' => $period,
            'usage' => $this->getFeatureUsage($feature, $period),
            'trend' => $this->getUsageTrend($feature, $period),
        ];

        return view('admin.seo.ai.details', compact('details'));
    }

    private function getFeatureUsage($feature, $period)
    {
        return 0;
    }

    private function getUsageTrend($feature, $period)
    {
        return [
            'direction' => 'flat',
            'percentage' => 0,
        ];
    }

    /**
     * Экспорт отчета об использовании
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $month = $request->get('month', now()->format('Y-m'));
        
        $usageStats = $this->calculateUsageStats($month);
        
        if ($format === 'csv') {
            return $this->exportCsv($usageStats, $month);
        }
        
        return $this->exportPdf($usageStats, $month);
    }

    private function exportCsv($stats, $month)
    {
        $filename = "ai-usage-{$month}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $output = "Функция,Использование\n";
        foreach ($stats as $feature => $value) {
            $output .= "{$feature},{$value}\n";
        }

        return response($output, 200, $headers);
    }

    private function exportPdf($stats, $month)
    {
        // Здесь должна быть логика генерации PDF
        return redirect()->back()->with('info', 'PDF экспорт будет доступен в следующей версии');
    }
}
