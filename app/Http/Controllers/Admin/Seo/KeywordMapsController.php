<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\Models\SeoKeywordMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KeywordMapsController extends Controller
{
    /**
     * Display a listing of keyword maps.
     */
    public function index()
    {
        $keywordMaps = SeoKeywordMap::orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total' => SeoKeywordMap::count(),
            'active' => SeoKeywordMap::where('is_enabled', true)->count(),
            'inactive' => SeoKeywordMap::where('is_enabled', false)->count(),
            'auto_link' => SeoKeywordMap::where('auto_link_on_publish', true)->count(),
        ];
        
        return view('admin.seo.keyword-maps.index', compact('keywordMaps', 'stats'));
    }

    /**
     * Show the form for creating a new keyword map.
     */
    public function create()
    {
        return view('admin.seo.keyword-maps.create');
    }

    /**
     * Store a newly created keyword map in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_url' => 'required|url',
            'keywords' => 'required|array|min:1',
            'keywords.*' => 'required|string|max:255',
            'is_enabled' => 'boolean',
            'case_sensitive' => 'boolean',
            'max_links_per_post' => 'integer|min:1|max:50',
            'auto_link_on_publish' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['keywords'] = json_encode($validated['keywords']);
        $validated['ai_variants'] = json_encode([]);
        $validated['is_enabled'] = $request->has('is_enabled');
        $validated['case_sensitive'] = $request->has('case_sensitive');
        $validated['auto_link_on_publish'] = $request->has('auto_link_on_publish');

        SeoKeywordMap::create($validated);

        return redirect()->route('admin.seo.keyword-maps.index')
            ->with('success', 'Карта ключевых слов успешно создана!');
    }

    /**
     * Display the specified keyword map.
     */
    public function show(SeoKeywordMap $keywordMap)
    {
        return view('admin.seo.keyword-maps.show', compact('keywordMap'));
    }

    /**
     * Show the form for editing the specified keyword map.
     */
    public function edit(SeoKeywordMap $keywordMap)
    {
        return view('admin.seo.keyword-maps.edit', compact('keywordMap'));
    }

    /**
     * Update the specified keyword map in storage.
     */
    public function update(Request $request, SeoKeywordMap $keywordMap)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_url' => 'required|url',
            'keywords' => 'required|array|min:1',
            'keywords.*' => 'required|string|max:255',
            'is_enabled' => 'boolean',
            'case_sensitive' => 'boolean',
            'max_links_per_post' => 'integer|min:1|max:50',
            'auto_link_on_publish' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['keywords'] = json_encode($validated['keywords']);
        $validated['is_enabled'] = $request->has('is_enabled');
        $validated['case_sensitive'] = $request->has('case_sensitive');
        $validated['auto_link_on_publish'] = $request->has('auto_link_on_publish');

        $keywordMap->update($validated);

        return redirect()->route('admin.seo.keyword-maps.index')
            ->with('success', 'Карта ключевых слов успешно обновлена!');
    }

    /**
     * Remove the specified keyword map from storage.
     */
    public function destroy(SeoKeywordMap $keywordMap)
    {
        $keywordMap->delete();

        return redirect()->route('admin.seo.keyword-maps.index')
            ->with('success', 'Карта ключевых слов удалена!');
    }

    /**
     * Generate AI variants for keywords.
     */
    public function generateAiVariants(Request $request, SeoKeywordMap $keywordMap)
    {
        $keywords = $keywordMap->keywords ?? [];
        
        if (empty($keywords)) {
            return response()->json(['error' => 'Нет ключевых слов для генерации вариантов'], 400);
        }

        // Имитация AI-генерации вариантов (синонимы, варианты написания)
        $aiVariants = [];
        
        foreach ($keywords as $keyword) {
            // Генерация вариантов на основе правила
            $variants = $this->generateKeywordVariants($keyword);
            $aiVariants = array_merge($aiVariants, $variants);
        }

        $aiVariants = array_unique($aiVariants);
        
        // Сохраняем AI-варианты
        $keywordMap->addAiVariants($aiVariants);

        return response()->json([
            'success' => true,
            'variants' => $aiVariants,
            'count' => count($aiVariants),
        ]);
    }

    /**
     * Генерация вариантов ключевого слова (AI-подобная логика)
     */
    private function generateKeywordVariants(string $keyword): array
    {
        $variants = [];
        
        // Варианты с предлогами
        $prepositions = ['о', 'об', 'про', 'для', 'с', 'без'];
        foreach ($prepositions as $prep) {
            $variants[] = "$prep $keyword";
        }
        
        // Множественное/единственное число (упрощенно)
        if (substr($keyword, -1) === 'а' || substr($keyword, -1) === 'я') {
            $variants[] = substr($keyword, 0, -1); // единственное число
        } elseif (substr($keyword, -2) === 'ии' || substr($keyword, -2) === 'ы') {
            $variants[] = substr($keyword, 0, -2) . 'ия'; // единственное число
        }
        
        // Синонимы и связанные слова (можно расширить через API)
        $relatedWords = [
            'seo' => ['поисковая оптимизация', 'продвижение сайта', 'SEO-оптимизация'],
            'маркетинг' => ['продвижение', 'реклама', 'digital-маркетинг'],
            'контент' => ['содержание', 'материалы', 'публикации'],
        ];
        
        foreach ($relatedWords as $key => $synonyms) {
            if (stripos($keyword, $key) !== false) {
                $variants = array_merge($variants, $synonyms);
            }
        }
        
        // Удаление дубликатов
        return array_values(array_unique($variants));
    }

    /**
     * Preview how links will be applied to content.
     */
    public function preview(Request $request)
    {
        $content = $request->input('content', '');
        $keywordMapId = $request->input('keyword_map_id');
        
        if (!$keywordMapId) {
            return response()->json(['error' => 'Не указана карта ключевых слов'], 400);
        }
        
        $keywordMap = SeoKeywordMap::findOrFail($keywordMapId);
        $allKeywords = $keywordMap->getAllKeywords();
        
        $linkCount = 0;
        $maxLinks = $keywordMap->max_links_per_post;
        
        // Простой алгоритм замены (можно улучшить)
        $modifiedContent = $content;
        
        foreach ($allKeywords as $keyword) {
            if ($linkCount >= $maxLinks) {
                break;
            }
            
            $pattern = '/\b(' . preg_quote($keyword, '/') . ')\b/i';
            
            // Проверяем, есть ли уже ссылка
            if (!preg_match('/<a[^>]*href=["\']' . preg_quote($keywordMap->target_url, '/') . '["\'][^>]*>' . preg_quote($keyword, '/') . '<\/a>/i', $modifiedContent)) {
                $replacement = '<a href="' . $keywordMap->target_url . '" title="' . $keywordMap->name . '">$1</a>';
                $modifiedContent = preg_replace($pattern, $replacement, $modifiedContent, 1, $count);
                
                if ($count > 0) {
                    $linkCount += $count;
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'original' => $content,
            'modified' => $modifiedContent,
            'links_added' => $linkCount,
            'max_links' => $maxLinks,
        ]);
    }

    /**
     * Bulk enable/disable keyword maps.
     */
    public function bulkToggle(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action'); // 'enable' or 'disable'
        
        if (empty($ids)) {
            return back()->with('error', 'Не выбраны карты ключевых слов');
        }
        
        $status = ($action === 'enable');
        SeoKeywordMap::whereIn('id', $ids)->update(['is_enabled' => $status]);
        
        return back()->with('success', 'Карты ключевых слов успешно ' . ($status ? 'включены' : 'выключены'));
    }
}
