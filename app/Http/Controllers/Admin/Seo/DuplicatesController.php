<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuplicatesController extends Controller
{
    /**
     * Отображение страницы поиска дубликатов
     */
    public function index()
    {
        $duplicates = session('duplicates', []);
        $lastScanAt = session('last_scan_at', null);
        
        return view('admin.seo.duplicates.index', compact('duplicates', 'lastScanAt'));
    }

    /**
     * Сканирование на дубликаты контента
     */
    public function scan(Request $request)
    {
        $minSimilarity = $request->input('similarity', 85); // Процент схожести
        
        $pages = Page::whereNotNull('content')
            ->select('id', 'title', 'slug', 'content', 'meta_description')
            ->get();

        $duplicates = [
            'by_content' => [],
            'by_title' => [],
            'by_meta_description' => [],
        ];

        // Поиск дубликатов по контенту
        $duplicates['by_content'] = $this->findContentDuplicates($pages, $minSimilarity);
        
        // Поиск дубликатов по заголовкам
        $duplicates['by_title'] = $this->findExactDuplicates($pages, 'title');
        
        // Поиск дубликатов по meta description
        $duplicates['by_meta_description'] = $this->findExactDuplicates($pages, 'meta_description');

        // Сохраняем в сессию для отображения
        session([
            'duplicates' => $duplicates,
            'last_scan_at' => now(),
        ]);

        $totalDuplicates = count($duplicates['by_content']) + 
                          count($duplicates['by_title']) + 
                          count($duplicates['by_meta_description']);

        return redirect()->back()
            ->with('success', "Найдено {$totalDuplicates} групп дубликатов контента.");
    }

    /**
     * Поиск дубликатов по контенту с использованием similarity
     */
    private function findContentDuplicates($pages, $minSimilarity)
    {
        $duplicates = [];
        $processed = [];

        foreach ($pages as $page1) {
            if (in_array($page1->id, $processed)) {
                continue;
            }

            $similarPages = [$page1];
            
            foreach ($pages as $page2) {
                if ($page1->id === $page2->id || in_array($page2->id, $processed)) {
                    continue;
                }

                $similarity = $this->calculateSimilarity($page1->content, $page2->content);
                
                if ($similarity >= $minSimilarity) {
                    $similarPages[] = $page2;
                    $processed[] = $page2->id;
                }
            }

            if (count($similarPages) > 1) {
                $duplicates[] = [
                    'type' => 'content',
                    'similarity' => round($similarity ?? 0),
                    'pages' => $similarPages,
                ];
                $processed[] = $page1->id;
            }
        }

        return $duplicates;
    }

    /**
     * Поиск точных дубликатов по полю
     */
    private function findExactDuplicates($pages, $field)
    {
        $duplicates = [];
        $grouped = $pages->groupBy($field)->filter(function($group) {
            return $group->count() > 1 && !empty($group->first()->$field);
        });

        foreach ($grouped as $value => $group) {
            $duplicates[] = [
                'type' => $field,
                'value' => mb_substr($value, 0, 100) . (mb_strlen($value) > 100 ? '...' : ''),
                'pages' => $group->values()->toArray(),
            ];
        }

        return $duplicates;
    }

    /**
     * Расчет схожести текстов (упрощенный алгоритм)
     */
    private function calculateSimilarity($text1, $text2)
    {
        if (empty($text1) || empty($text2)) {
            return 0;
        }

        // Очистка текста
        $text1 = $this->normalizeText($text1);
        $text2 = $this->normalizeText($text2);

        // Используем similar_text для расчета процента схожести
        $percent = 0;
        similar_text($text1, $text2, $percent);

        return $percent;
    }

    /**
     * Нормализация текста
     */
    private function normalizeText($text)
    {
        // Удаляем HTML теги
        $text = strip_tags($text);
        
        // Приводим к нижнему регистру
        $text = mb_strtolower($text);
        
        // Удаляем лишние пробелы
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Удаляем пунктуацию
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        
        return trim($text);
    }
}
