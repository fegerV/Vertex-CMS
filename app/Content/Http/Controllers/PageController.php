<?php

namespace App\Content\Http\Controllers;

use App\Content\Models\Page;
use App\Content\Services\PageService;
use App\Core\Http\Controllers\Controller;
use App\Seo\Services\SeoMetaService;
use App\Content\Http\Requests\PageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function __construct(
        private PageService $pages,
        private \App\Builder\Services\PageRenderer $renderer
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Pages/Index', [
            'pages' => $this->pages->all()
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Create', [
            'templates' => $this->pages->templates(),
            'defaultContent' => $this->pages->defaultContent(),
            'locales' => ['ru' => 'Русский', 'en' => 'English'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $page = $this->pages->create($this->validated($request), $request->user());

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Страница создана.');
    }

    public function edit(Page $page): Response
    {
        return Inertia::render('Admin/Pages/Edit', [
            'page' => $page->load('seoMeta', 'translations'),
            'templates' => $this->pages->templates(),
            'locales' => ['ru' => 'Русский', 'en' => 'English'],
            'renderedHtml' => $this->renderer->render($page->content_json ?? $this->pages->defaultContent()),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page = $this->pages->update($page, $this->validated($request), $request->user());

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Страница сохранена.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->pages->delete($page, request()->user());

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
            'locale' => ['required', 'string', 'in:ru,en'],
            'translation_group' => ['nullable', 'string', 'max:50'],
            'content_json' => ['nullable', 'array'],
            'custom_fields_json' => ['nullable', 'array'],
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
