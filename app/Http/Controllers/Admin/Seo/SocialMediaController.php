<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SocialMediaController extends Controller
{
    /**
     * Отображение редактора социальных сетей и Open Graph
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        
        // Получаем настройки Open Graph
        $ogSettings = [
            'og_title' => $settings['og_title'] ?? config('app.name'),
            'og_description' => $settings['og_description'] ?? '',
            'og_image' => $settings['og_image'] ?? null,
            'og_site_name' => $settings['og_site_name'] ?? config('app.name'),
            'twitter_card' => $settings['twitter_card'] ?? 'summary_large_image',
            'twitter_site' => $settings['twitter_site'] ?? '',
            'twitter_creator' => $settings['twitter_creator'] ?? '',
            'facebook_app_id' => $settings['facebook_app_id'] ?? '',
            'facebook_page' => $settings['facebook_page'] ?? '',
            'vk_url' => $settings['vk_url'] ?? '',
            'instagram_url' => $settings['instagram_url'] ?? '',
            'youtube_url' => $settings['youtube_url'] ?? '',
            'telegram_url' => $settings['telegram_url'] ?? '',
            'linkedin_url' => $settings['linkedin_url'] ?? '',
        ];
        
        return view('admin.seo.social.index', compact('ogSettings'));
    }

    /**
     * Сохранение настроек социальных сетей и Open Graph
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|url|max:500',
            'og_site_name' => 'nullable|string|max:255',
            'twitter_card' => 'nullable|in:summary,summary_large_image',
            'twitter_site' => 'nullable|string|max:255',
            'twitter_creator' => 'nullable|string|max:255',
            'facebook_app_id' => 'nullable|string|max:100',
            'facebook_page' => 'nullable|url|max:500',
            'vk_url' => 'nullable|url|max:500',
            'instagram_url' => 'nullable|url|max:500',
            'youtube_url' => 'nullable|url|max:500',
            'telegram_url' => 'nullable|url|max:500',
            'linkedin_url' => 'nullable|url|max:500',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->back()->with('success', 'Настройки социальных сетей сохранены!');
    }

    /**
     * Загрузка изображения для Open Graph
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $path = $request->file('image')->store('og-images', 'public');
        $url = Storage::url($path);

        return response()->json([
            'success' => true,
            'url' => asset($url),
        ]);
    }

    /**
     * Предпросмотр как будет выглядеть ссылка в соцсетях
     */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|url',
            'url' => 'nullable|url',
        ]);

        return view('admin.seo.social.preview', compact('data'));
    }

    /**
     * Генерация мета-тегов для страницы
     */
    public function generatePageMeta(Page $page)
    {
        $ogData = [
            'title' => $page->meta_title ?: $page->title,
            'description' => $page->meta_description,
            'image' => $page->featured_image ? asset($page->featured_image) : null,
            'url' => url($page->slug),
        ];

        return response()->json([
            'success' => true,
            'meta' => $this->generateMetaTags($ogData),
        ]);
    }

    /**
     * Генерация HTML мета-тегов
     */
    private function generateMetaTags($data)
    {
        $tags = [];
        
        // Basic Meta
        $tags[] = '<meta property="og:title" content="' . e($data['title']) . '">';
        $tags[] = '<meta property="og:description" content="' . e($data['description']) . '">';
        $tags[] = '<meta property="og:url" content="' . e($data['url']) . '">';
        $tags[] = '<meta property="og:type" content="website">';
        
        if (!empty($data['image'])) {
            $tags[] = '<meta property="og:image" content="' . e($data['image']) . '">';
            $tags[] = '<meta property="og:image:width" content="1200">';
            $tags[] = '<meta property="og:image:height" content="630">';
        }
        
        // Twitter Card
        $tags[] = '<meta name="twitter:card" content="summary_large_image">';
        $tags[] = '<meta name="twitter:title" content="' . e($data['title']) . '">';
        $tags[] = '<meta name="twitter:description" content="' . e($data['description']) . '">';
        
        if (!empty($data['image'])) {
            $tags[] = '<meta name="twitter:image" content="' . e($data['image']) . '">';
        }

        return implode("\n", $tags);
    }
}
