<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Page;
use App\Models\Post;
use App\Models\Image;
use App\Models\User;

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
        // Здесь должна быть логика синхронизации с внешним AI сервисом
        // Для примера - эмуляция
        
        Cache::put('ai_usage_last_sync', now(), 3600);
        
        return redirect()->back()->with('success', 'Данные об использовании ИИ успешно синхронизированы!');
    }

    /**
     * Расчет статистики использования за месяц
     */
    private function calculateUsageStats($month)
    {
        // В реальной реализации здесь будут запросы к базе данных или API
        // Эмулируем данные для демонстрации
        
        $stats = [
            // Исследование и анализ
            'keywords_research' => rand(50, 200),
            'topic_research' => rand(20, 80),
            
            // Изображения
            'image_alt' => rand(100, 400),
            
            // Написание контента
            'content_write' => rand(5000, 20000),
            'write_more' => rand(200, 800),
            'article_master' => rand(5, 25),
            'paragraph' => rand(300, 900),
            'paragraph_rewrite' => rand(150, 500),
            'sentence_expander' => rand(200, 600),
            'summarizer' => rand(100, 300),
            'analogy' => rand(50, 150),
            'free_form' => rand(400, 1200),
            
            // Чат и помощь
            'chat' => rand(100, 500),
            'fix_ai' => rand(80, 250),
            'grammar_fix' => rand(150, 450),
            'ai_team' => rand(100, 400),
            
            // SEO мета
            'bulk_meta' => rand(30, 150),
            'seo_title' => rand(100, 400),
            'seo_description' => rand(100, 400),
            'seo_meta' => rand(150, 450),
            'open_graph' => rand(80, 250),
            
            // Блоги и статьи
            'blog_idea' => rand(50, 150),
            'blog_plan' => rand(30, 100),
            'blog_intro' => rand(60, 180),
            'blog_conclusion' => rand(60, 180),
            'post_title' => rand(80, 250),
            
            // Товары
            'product_description' => rand(80, 250),
            'product_pros_cons' => rand(50, 150),
            'product_review' => rand(50, 150),
            
            // Соцсети
            'facebook_post' => rand(50, 150),
            'facebook_comment_reply' => rand(80, 250),
            'tweet' => rand(80, 250),
            'tweet_reply' => rand(80, 250),
            'instagram_caption' => rand(80, 250),
            
            // Email
            'email' => rand(50, 150),
            'email_reply' => rand(50, 150),
            
            // Комментарии и отзывы
            'comment_reply' => rand(100, 300),
            'testimonial' => rand(30, 100),
            
            // Био и компания
            'bio' => rand(20, 80),
            'company_history' => rand(10, 40),
            'job_description' => rand(20, 80),
            
            // FAQ
            'faq' => rand(80, 250),
            
            // Видео и подкасты
            'youtube_script' => rand(10, 40),
            'youtube_description' => rand(50, 150),
            'podcast_plan' => rand(10, 40),
            
            // Рецепты
            'recipe' => rand(20, 80),
            
            // Специальные функции
            'vijdh' => rand(20, 80),
            'irss' => rand(20, 80),
            'par' => rand(20, 80),
            'hero' => rand(20, 80),
            'spin' => rand(50, 150),
            'dpm' => rand(20, 80),
            
            // Ссылки
            'link_opportunities' => rand(50, 150),
            'related_posts' => rand(50, 150),
            'link_suggestions' => rand(80, 250),
        ];

        return $stats;
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
        // Эмуляция данных
        return rand(50, 500);
    }

    private function getUsageTrend($feature, $period)
    {
        // Эмуляция тренда
        return [
            'direction' => rand(0, 1) ? 'up' : 'down',
            'percentage' => rand(5, 30),
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
