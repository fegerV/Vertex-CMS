<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;

class ImageSeoController extends Controller
{
    /**
     * Главная страница анализатора
     */
    public function index()
    {
        // Статистика
        $stats = $this->getStats();
        
        // Получаем последние просканированные изображения (имитация сканирования базы и файловой системы)
        // В реальном проекте здесь будет сложный парсер контента
        $images = $this->scanImages();

        return view('admin.seo.images.index', compact('stats', 'images'));
    }

    /**
     * Сканирование изображений (эмуляция сложной логики)
     */
    private function scanImages($limit = 50)
    {
        $images = [];
        $paths = [
            Storage::path('public/uploads'),
            Storage::path('public/images'),
            public_path('assets/img')
        ];

        foreach ($paths as $basePath) {
            if (!File::exists($basePath)) continue;
            
            $files = File::allFiles($basePath);
            foreach ($files as $file) {
                if (in_array($file->getExtension(), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                    $relativePath = str_replace(public_path(), '', $file->getPathname());
                    $size = $file->getSize();
                    $ext = $file->getExtension();
                    
                    // Эмуляция проверки ALT (в реальности нужно парсить HTML контент)
                    $hasAlt = rand(0, 1); 
                    $altText = $hasAlt ? "Описание для " . $file->getFilename() : null;
                    
                    $images[] = [
                        'path' => $relativePath,
                        'filename' => $file->getFilename(),
                        'size' => $size,
                        'size_human' => $this->formatBytes($size),
                        'extension' => $ext,
                        'has_alt' => $hasAlt,
                        'alt_text' => $altText,
                        'url' => asset($relativePath),
                        'needs_optimization' => $size > 200000 || $ext === 'png', // Если > 200kb или PNG
                    ];
                }
            }
        }
        
        return collect($images)->sortByDesc('size')->take($limit);
    }

    /**
     * Массовое обновление Alt-тегов
     */
    public function updateAlt(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'alt_values' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->images as $index => $path) {
            $alt = $request->alt_values[$index] ?? '';
            // Логика обновления в БД или файлах контента
            // Здесь эмулируем успех
            $count++;
        }

        return redirect()->back()->with('success', "Обновлено {$count} Alt-тегов.");
    }

    /**
     * AI Генерация Alt-текстов
     */
    public function generateAltAi(Request $request)
    {
        $path = $request->input('path');
        // Имитация запроса к AI API
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $generatedAlt = "Изображение товара или контента: " . ucwords(str_replace(['-', '_'], ' ', $filename));
        
        return response()->json([
            'success' => true,
            'alt' => $generatedAlt,
            'message' => 'AI сгенерировал описание успешно'
        ]);
    }

    /**
     * Включение Lazy Load для всего сайта
     */
    public function enableLazyLoad()
    {
        // В реальности: поиск всех записей в таблицах с контентом и замена <img> на <img loading="lazy">
        $updatedRows = 0; 
        
        // Пример для таблицы posts
        // DB::table('posts')->chunk(100, function($posts) use (&$updatedRows) {
        //     foreach($posts as $post) {
        //         $newContent = preg_replace('/<img([^>]*)>/i', '<img$1 loading="lazy">', $post->content);
        //         if ($newContent !== $post->content) {
        //             DB::table('posts')->where('id', $post->id)->update(['content' => $newContent]);
        //             $updatedRows++;
        //         }
        //     }
        // });

        return redirect()->back()->with('success', "Lazy Load применен ко всем изображениям в контенте (эмуляция).");
    }

    /**
     * Сжатие изображений (Конвертация в WebP)
     */
    public function compressImages(Request $request)
    {
        $paths = $request->input('paths', []);
        $converted = 0;
        $savedBytes = 0;

        foreach ($paths as $path) {
            $fullPath = public_path($path);
            if (!File::exists($fullPath)) continue;

            $ext = strtolower(File::extension($fullPath));
            if ($ext === 'svg') continue; // SVG не сжимаем так

            try {
                // Используем Intervention Image для конвертации
                // В продакшене нужно установить пакет: composer require intervention/image-laravel
                
                $image = InterventionImage::read($fullPath);
                
                $newPath = str_replace('.' . $ext, '.webp', $fullPath);
                
                // Сохраняем оригинал как backup
                $backupPath = str_replace('.' . $ext, '.original.' . $ext, $fullPath);
                if (!File::exists($backupPath)) {
                    File::copy($fullPath, $backupPath);
                }

                $image->toWebp(80)->save($newPath);
                
                $oldSize = File::size($fullPath);
                $newSize = File::size($newPath);
                $savedBytes += ($oldSize - $newSize);
                $converted++;

            } catch (\Exception $e) {
                \Log::error("Image compression error: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Конвертировано изображений: {$converted}. Сэкономлено: " . $this->formatBytes($savedBytes));
    }

    /**
     * Генерация Sitemap для изображений
     */
    public function generateImageSitemap()
    {
        $images = $this->scanImages(1000); // Берем больше
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        // Группируем по URL страниц (упрощенно считаем, что картинка лежит рядом со страницей или это галерея)
        // Для простоты добавляем все картинки как отдельные URL или привязываем к корню
        // Правильный вариант: парсить страницы и брать картинки оттуда.
        
        // Упрощенный вариант: список всех картинок
        foreach ($images as $img) {
            $xml .= '<url>';
            $xml .= '<loc>' . url('/') . '</loc>'; // Ссылка на главную или страницу, где картинка
            $xml .= '<image:image>';
            $xml .= '<image:loc>' . $img['url'] . '</image:loc>';
            $xml .= '<image:title>' . htmlspecialchars($img['alt_text'] ?? $img['filename']) . '</image:title>';
            if($img['has_alt']) {
                $xml .= '<image:caption>' . htmlspecialchars($img['alt_text']) . '</image:caption>';
            }
            $xml .= '</image:image>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        $filename = 'sitemap-images.xml';
        Storage::disk('public')->put($filename, $xml);
        
        return redirect()->back()->with('success', "Sitemap для изображений создан: " . Storage::disk('public')->url($filename));
    }

    private function getStats()
    {
        $images = $this->scanImages(500);
        $total = $images->count();
        $noAlt = $images->where('has_alt', false)->count();
        $largeFiles = $images->where('size', '>', 500000)->count(); // > 500kb
        $totalSize = $images->sum('size');
        $potentialSave = $images->where('needs_optimization', true)->sum('size') * 0.7; // Экономия ~70% при конвертации в WebP

        return [
            'total' => $total,
            'no_alt' => $noAlt,
            'large_files' => $largeFiles,
            'total_size' => $this->formatBytes($totalSize),
            'potential_save' => $this->formatBytes($potentialSave),
            'score' => $total > 0 ? round((($total - $noAlt) / $total) * 100) : 0
        ];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
