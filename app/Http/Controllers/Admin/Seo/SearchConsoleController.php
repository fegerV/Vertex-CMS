<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SearchConsoleController extends Controller
{
    /**
     * Отображение панели Search Console
     */
    public function index()
    {
        $isConnected = $this->isConnected();
        $queries = [];
        $errors = [];
        $stats = [
            'total_clicks' => 0,
            'total_impressions' => 0,
            'avg_position' => 0,
            'ctr' => 0,
        ];

        if ($isConnected) {
            $queries = Cache::remember('gsc_queries', 3600, function () {
                return $this->fetchQueriesFromApi();
            });
            
            $errors = Cache::remember('gsc_errors', 3600, function () {
                return $this->fetchErrorsFromApi();
            });

            $stats = $this->calculateStats($queries);
        }

        return view('admin.seo.search-console.index', compact('isConnected', 'queries', 'errors', 'stats'));
    }

    /**
     * Подключение к Search Console
     */
    public function connect()
    {
        // В реальной реализации здесь будет OAuth flow
        // Для демонстрации сохраняем тестовые данные
        
        $config = [
            'property_url' => config('app.url'),
            'access_token' => 'demo_token_' . uniqid(),
            'connected_at' => now(),
        ];

        // Сохраняем в настройки (в реальности нужно хранить securely)
        $settings = get_option('seo_settings', []);
        $settings['search_console'] = $config;
        update_option('seo_settings', $settings);

        return redirect()->route('admin.seo.search-console')
            ->with('success', 'Google Search Console успешно подключен!');
    }

    /**
     * Получение запросов из Search Console
     */
    public function fetchQueries()
    {
        if (!$this->isConnected()) {
            return response()->json(['error' => 'Not connected'], 403);
        }

        Cache::forget('gsc_queries');
        $queries = $this->fetchQueriesFromApi();
        
        return response()->json([
            'success' => true,
            'queries' => $queries,
        ]);
    }

    /**
     * Получение ошибок индексации
     */
    public function fetchErrors()
    {
        if (!$this->isConnected()) {
            return response()->json(['error' => 'Not connected'], 403);
        }

        Cache::forget('gsc_errors');
        $errors = $this->fetchErrorsFromApi();
        
        return response()->json([
            'success' => true,
            'errors' => $errors,
        ]);
    }

    /**
     * Проверка подключения
     */
    private function isConnected()
    {
        $settings = get_option('seo_settings', []);
        return isset($settings['search_console']['access_token']);
    }

    /**
     * Получение запросов из API (демо-режим)
     */
    private function fetchQueriesFromApi()
    {
        $settings = get_option('seo_settings', []);
        
        // В реальной реализации: Http::withToken($token)->get(...)
        
        // Демо-данные для примера
        return collect([
            ['query' => 'купить товар онлайн', 'clicks' => 150, 'impressions' => 2500, 'position' => 3.2, 'ctr' => 6.0],
            ['query' => 'лучший магазин', 'clicks' => 89, 'impressions' => 1800, 'position' => 5.1, 'ctr' => 4.9],
            ['query' => 'отзывы о продукте', 'clicks' => 67, 'impressions' => 1200, 'position' => 4.8, 'ctr' => 5.6],
            ['query' => 'цена услуги', 'clicks' => 45, 'impressions' => 900, 'position' => 7.2, 'ctr' => 5.0],
            ['query' => 'как выбрать', 'clicks' => 34, 'impressions' => 800, 'position' => 8.5, 'ctr' => 4.3],
        ])->sortByDesc('clicks')->values()->toArray();
    }

    /**
     * Получение ошибок из API (демо-режим)
     */
    private function fetchErrorsFromApi()
    {
        // Демо-данные для примера
        return collect([
            ['type' => '404', 'url' => '/old-page-1', 'count' => 15, 'last_crawled' => now()->subDays(2)],
            ['type' => 'soft_404', 'url' => '/empty-category', 'count' => 8, 'last_crawled' => now()->subDays(1)],
            ['type' => 'server_error', 'url' => '/api/broken', 'count' => 3, 'last_crawled' => now()->subHours(5)],
            ['type' => 'redirect_error', 'url' => '/loop-redirect', 'count' => 2, 'last_crawled' => now()->subDays(3)],
        ])->sortByDesc('count')->values()->toArray();
    }

    /**
     * Расчет статистики
     */
    private function calculateStats($queries)
    {
        $collection = collect($queries);
        
        return [
            'total_clicks' => $collection->sum('clicks'),
            'total_impressions' => $collection->sum('impressions'),
            'avg_position' => round($collection->avg('position') ?? 0, 2),
            'ctr' => round($collection->avg('ctr') ?? 0, 2),
        ];
    }
}
