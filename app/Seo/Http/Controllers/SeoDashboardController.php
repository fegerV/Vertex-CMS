<?php

namespace App\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Term;
use App\Seo\Services\SeoAuditService;
use App\Seo\Services\SeoContentAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoDashboardController extends Controller
{
    public function __construct(
        private readonly SeoAuditService $audit,
        private readonly SeoContentAnalysisService $analysis,
    ) {
    }

    public function index(): View
    {
        return view('admin.seo.dashboard', [
            'dashboard' => $this->audit->overview(),
        ]);
    }

    /**
     * Анализ контента - страница со списком всех страниц для аудита
     */
    public function analysis(): View
    {
        $pages = Page::query()
            ->with(['seoMeta', 'author'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.seo.analysis', [
            'pages' => $pages,
        ]);
    }

    /**
     * AJAX: Анализ конкретной страницы
     */
    public function analyzePage(Request $request): JsonResponse
    {
        $request->validate([
            'page_id' => 'required|exists:pages,id',
        ]);

        $page = Page::findOrFail($request->page_id);
        $analysis = $this->analysis->analyze($page);

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Массовое редактирование мета-тегов
     */
    public function bulkEditor(): View
    {
        $pages = Page::query()
            ->with('seoMeta')
            ->orderBy('title')
            ->paginate(50);

        return view('admin.seo.bulk-editor', [
            'pages' => $pages,
        ]);
    }

    /**
     * Массовое обновление мета-тегов
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:pages,id',
            'updates.*.title' => 'nullable|string|max:255',
            'updates.*.description' => 'nullable|string|max:160',
            'updates.*.keywords' => 'nullable|string',
            'updates.*.canonical' => 'nullable|url',
        ]);

        $updated = 0;
        foreach ($request->updates as $update) {
            $page = Page::find($update['id']);
            if ($page) {
                $page->updateSeoMeta([
                    'title' => $update['title'] ?? null,
                    'description' => $update['description'] ?? null,
                    'keywords' => $update['keywords'] ?? null,
                    'canonical_url' => $update['canonical'] ?? null,
                ]);
                $updated++;
            }
        }

        return redirect()->back()->with('success', "Обновлено страниц: {$updated}");
    }

    /**
     * Роботы и файлы
     */
    public function files(): View
    {
        return view('admin.seo.files');
    }

    /**
     * Семантическое ядро
     */
    public function semantics(): View
    {
        // Получаем все ключевые слова из страниц
        $keywords = collect();
        
        $pages = Page::query()
            ->with('seoMeta')
            ->whereNotNull('seo_meta_keywords')
            ->get();

        foreach ($pages as $page) {
            if ($page->seoMeta?->keywords) {
                $kwList = explode(',', $page->seoMeta->keywords);
                foreach ($kwList as $kw) {
                    $kw = trim($kw);
                    if (!empty($kw)) {
                        $keywords->put($kw, ($keywords->get($kw) ?? 0) + 1);
                    }
                }
            }
        }

        $keywords = $keywords->sortDesc();

        return view('admin.seo.semantics', [
            'keywords' => $keywords,
            'pages' => $pages,
        ]);
    }

    public function addKeyword(Request $request): RedirectResponse
    {
        $request->validate([
            'keyword' => 'required|string|max:255',
            'page_id' => 'nullable|exists:pages,id',
        ]);

        // Логика добавления ключевого слова
        return redirect()->back()->with('success', 'Ключевое слово добавлено');
    }

    public function deleteKeyword(Request $request, string $keyword): RedirectResponse
    {
        // Логика удаления ключевого слова
        return redirect()->back()->with('success', 'Ключевое слово удалено');
    }

    /**
     * Внутренние ссылки
     */
    public function internalLinks(): View
    {
        $pages = Page::all();
        $linkData = [];

        foreach ($pages as $page) {
            $content = $page->content ?? '';
            $outgoingLinks = [];
            
            // Поиск ссылок в контенте
            preg_match_all('/href=["\']([^"\']+)["\']/', $content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $link) {
                    $targetPage = Page::where('uri', $link)->first();
                    if ($targetPage) {
                        $outgoingLinks[] = [
                            'target' => $targetPage,
                            'anchor' => $link,
                        ];
                    }
                }
            }

            $linkData[] = [
                'page' => $page,
                'outgoing' => $outgoingLinks,
                'incoming_count' => 0, // Будет заполнено позже
            ];
        }

        // Подсчет входящих ссылок
        foreach ($linkData as &$item) {
            $incoming = 0;
            foreach ($linkData as $other) {
                foreach ($other['outgoing'] as $link) {
                    if ($link['target']->id === $item['page']->id) {
                        $incoming++;
                    }
                }
            }
            $item['incoming_count'] = $incoming;
        }

        return view('admin.seo.internal-links', [
            'linkData' => $linkData,
        ]);
    }

    public function suggestLinks(Request $request): JsonResponse
    {
        // AI-powered link suggestions
        return response()->json([
            'success' => true,
            'suggestions' => [],
        ]);
    }

    /**
     * Сиротские страницы
     */
    public function orphanPages(): View
    {
        $pages = Page::all();
        $orphanPages = [];

        foreach ($pages as $page) {
            $hasIncomingLink = false;
            
            foreach ($pages as $otherPage) {
                if ($otherPage->id !== $page->id) {
                    $content = $otherPage->content ?? '';
                    if (strpos($content, $page->uri) !== false || strpos($content, url($page->uri)) !== false) {
                        $hasIncomingLink = true;
                        break;
                    }
                }
            }

            if (!$hasIncomingLink) {
                $orphanPages[] = $page;
            }
        }

        return view('admin.seo.orphan-pages', [
            'orphanPages' => $orphanPages,
        ]);
    }

    /**
     * AI-Ассистент
     */
    public function aiAssistant(): View
    {
        $pages = Page::query()
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.seo.ai-assistant', [
            'pages' => $pages,
        ]);
    }

    public function generateMetaTags(Request $request): JsonResponse
    {
        $request->validate([
            'page_id' => 'required|exists:pages,id',
        ]);

        $page = Page::findOrFail($request->page_id);
        
        // Здесь будет интеграция с AI для генерации мета-тегов
        // Пока используем базовую логику
        $title = substr($page->title, 0, 60);
        $description = substr(strip_tags($page->content ?? ''), 0, 160);

        return response()->json([
            'success' => true,
            'title' => $title,
            'description' => $description,
        ]);
    }

    public function generateContent(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string',
            'type' => 'required|in:title,description,content',
        ]);

        // Здесь будет интеграция с AI API
        return response()->json([
            'success' => true,
            'content' => 'AI-generated content will appear here',
        ]);
    }

    /**
     * Настройки SEO
     */
    public function settings(): View
    {
        return view('admin.seo.settings');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        // Логика обновления настроек SEO
        return redirect()->back()->with('success', 'Настройки SEO обновлены');
    }
}
