<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AiImagesController extends Controller
{
    /**
     * Отображение страницы AI генерации Alt-текстов
     */
    public function index()
    {
        $pages = Page::select('id', 'title', 'slug', 'content')
            ->latest()
            ->take(50)
            ->get();

        // Анализируем изображения без alt
        $imagesWithoutAlt = [];
        
        foreach ($pages as $page) {
            $images = $this->extractImages($page->content);
            $missingAlt = array_filter($images, fn($img) => empty($img['alt']));
            
            if (!empty($missingAlt)) {
                $imagesWithoutAlt[] = [
                    'page' => $page,
                    'images' => array_values($missingAlt),
                    'count' => count($missingAlt),
                ];
            }
        }

        return view('admin.seo.ai-images.index', compact('pages', 'imagesWithoutAlt'));
    }

    /**
     * Генерация Alt для конкретного изображения
     */
    public function generate(Request $request)
    {
        $request->validate([
            'image_url' => 'required|url',
            'page_content' => 'nullable|string',
            'context' => 'nullable|string',
        ]);

        $imageUrl = $request->image_url;
        $context = $request->context ?? $request->page_content ?? '';
        
        // Генерируем описание через AI
        $altText = $this->generateAltText($imageUrl, $context);

        return response()->json([
            'success' => true,
            'alt_text' => $altText,
        ]);
    }

    /**
     * Массовая генерация Alt для всех изображений на странице
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'page_id' => 'required|exists:pages,id',
        ]);

        $page = Page::findOrFail($request->page_id);
        $images = $this->extractImages($page->content);
        $updated = 0;
        $results = [];

        foreach ($images as $image) {
            if (empty($image['alt'])) {
                $altText = $this->generateAltText($image['src'], $page->content);
                
                if (!empty($altText)) {
                    $results[] = [
                        'src' => $image['src'],
                        'alt' => $altText,
                    ];
                    $updated++;
                }
            }
        }

        // Обновляем контент страницы с новыми alt
        if ($updated > 0) {
            $newContent = $this->updateAltInContent($page->content, $results);
            $page->update(['content' => $newContent]);
        }

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'results' => $results,
        ]);
    }

    /**
     * Извлечение изображений из контента
     */
    private function extractImages($content)
    {
        $pattern = '/<img[^>]*src="([^"]*)"[^>]*(?:alt="([^"]*)")?[^>]*>/i';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $images = [];
        foreach ($matches as $match) {
            $images[] = [
                'src' => $match[1],
                'alt' => $match[2] ?? '',
            ];
        }

        // Если alt не найден в первом проходе, пробуем другой паттерн
        if (empty($images)) {
            $pattern = '/<img[^>]*src="([^"]*)"[^>]*>/i';
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                // Пытаемся найти alt отдельно
                $fullTag = $match[0];
                preg_match('/alt="([^"]*)"/i', $fullTag, $altMatch);
                
                $images[] = [
                    'src' => $match[1],
                    'alt' => $altMatch[1] ?? '',
                ];
            }
        }

        return $images;
    }

    /**
     * Генерация Alt текста через AI
     */
    private function generateAltText($imageUrl, $context = '')
    {
        // В реальной реализации здесь будет вызов AI API (OpenAI Vision, etc.)
        // Для демонстрации используем эмуляцию
        
        $filename = pathinfo($imageUrl, PATHINFO_FILENAME);
        $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
        
        // Очищаем имя файла от служебных символов
        $cleanName = preg_replace('/[-_]/', ' ', $filename);
        $cleanName = ucwords($cleanName);
        
        // Ключевые слова из контекста
        $keywords = $this->extractKeywords($context, 5);
        
        // Формируем осмысленное описание
        $description = !empty($keywords) 
            ? "{$cleanName} - " . implode(', ', $keywords)
            : $cleanName;
        
        // Ограничиваем длину (рекомендуется до 125 символов)
        if (mb_strlen($description) > 125) {
            $description = mb_substr($description, 0, 122) . '...';
        }

        return $description;
    }

    /**
     * Извлечение ключевых слов из контекста
     */
    private function extractKeywords($text, $limit = 5)
    {
        // Удаляем HTML теги
        $text = strip_tags($text);
        
        // Приводим к нижнему регистру
        $text = mb_strtolower($text);
        
        // Разбиваем на слова
        preg_match_all('/[\p{L}]+/u', $text, $matches);
        $words = $matches[0];
        
        // Стоп-слова (русские и английские)
        $stopWords = [
            'и', 'в', 'во', 'не', 'что', 'он', 'на', 'я', 'с', 'со', 'как', 'а', 'то', 'все', 'она', 'так', 'его', 'но', 'да', 'ты', 'к', 'у', 'же', 'вы', 'за', 'бы', 'по', 'только', 'ее', 'мне', 'было', 'вот', 'от', 'меня', 'еще', 'нет', 'о', 'из', 'ему', 'теперь', 'когда', 'даже', 'ну', 'вдруг', 'ли', 'если', 'уже', 'или', 'ни', 'быть', 'был', 'него', 'до', 'вас', 'нибудь', 'опять', 'уж', 'вам', 'ведь', 'там', 'потом', 'себя', 'ничего', 'ей', 'может', 'они', 'тут', 'где', 'есть', 'надо', 'ней', 'для', 'мы', 'тебя', 'их', 'чем', 'была', 'сам', 'чтоб', 'без', 'будто', 'how', 'the', 'and', 'is', 'in', 'to', 'of', 'a', 'for', 'on', 'with', 'at', 'by', 'from', 'as', 'or', 'an', 'be', 'this', 'that', 'it', 'has', 'are', 'was', 'were', 'been', 'have', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can',
        ];
        
        // Фильтруем стоп-слова и короткие слова
        $filtered = array_filter($words, function($word) use ($stopWords) {
            return mb_strlen($word) > 3 && !in_array($word, $stopWords);
        });
        
        // Считаем частоту
        $frequency = array_count_values($filtered);
        arsort($frequency);
        
        // Возвращаем топ ключевых слов
        return array_slice(array_keys($frequency), 0, $limit);
    }

    /**
     * Обновление Alt атрибутов в контенте
     */
    private function updateAltInContent($content, $results)
    {
        foreach ($results as $result) {
            $src = preg_quote($result['src'], '/');
            $alt = addslashes($result['alt']);
            
            // Паттерн для изображения без alt или с пустым alt
            $pattern = "/<img([^>]*)src=\"{$src}\"([^>]*)(?:alt=\"[^\"]*\")?([^>]*)>/i";
            
            $replacement = "<img\$1src=\"{$result['src']}\"\$2alt=\"{$alt}\"\$3>";
            
            $content = preg_replace($pattern, $replacement, $content, 1);
        }
        
        return $content;
    }
}
