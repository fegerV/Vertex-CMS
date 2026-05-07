<?php

namespace App\Content\Http\Controllers;

use App\Content\Services\PageService;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Seo\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        private readonly PageService $pages,
    ) {
    }

    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::query()->with('seoMeta')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'page' => new Page([
                'status' => 'draft',
                'template' => 'default',
                'content_json' => $this->pages->defaultContent(),
            ]),
            'parentPages' => Page::query()->orderBy('title')->get(),
            'statuses' => PageService::STATUSES,
            'robotsOptions' => SeoMetaService::ROBOTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $page = $this->pages->create($this->validated($request), $request->user());

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Страница создана.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page->load('seoMeta'),
            'parentPages' => Page::query()
                ->whereKeyNot($page->id)
                ->orderBy('title')
                ->get(),
            'statuses' => PageService::STATUSES,
            'robotsOptions' => SeoMetaService::ROBOTS,
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page = $this->pages->update($page, $this->validated($request), $request->user());

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Страница сохранена.');
    }

    public function destroy(Request $request, Page $page): RedirectResponse
    {
        $this->pages->delete($page, $request->user());

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Страница удалена.');
    }

    private function validated(Request $request): array
    {
        $payload = $request->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists('pages', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(PageService::STATUSES)],
            'template' => ['nullable', 'string', 'max:255'],
            'content_json' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_canonical_url' => ['nullable', 'url', 'max:500'],
            'seo_robots' => ['nullable', Rule::in(SeoMetaService::ROBOTS)],
            'seo_og_title' => ['nullable', 'string', 'max:255'],
            'seo_og_description' => ['nullable', 'string', 'max:500'],
            'seo_og_image' => ['nullable', 'integer'],
            'seo_schema_json' => ['nullable', 'json'],
            'seo_include_in_sitemap' => ['nullable', 'boolean'],
        ]);

        $payload['seo_include_in_sitemap'] = $request->boolean('seo_include_in_sitemap');

        return $payload;
    }
}
