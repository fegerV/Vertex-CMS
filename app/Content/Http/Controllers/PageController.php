<?php

namespace App\Content\Http\Controllers;

use App\Builder\Services\PageRenderer;
use App\Content\Services\PageService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Models\CustomFieldGroup;
use App\Models\Page;
use App\Models\Taxonomy;
use App\Seo\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        private readonly PageService $pages,
        private readonly PageRenderer $renderer,
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
            'fieldGroups' => $this->fieldGroupsForTemplate('default'),
            'allFieldGroups' => CustomFieldGroup::query()->orderBy('name')->get(),
            'taxonomies' => Taxonomy::query()->with('terms')->orderBy('name')->get(),
            'parentPages' => Page::query()->orderBy('title')->get(),
            'statuses' => PageService::STATUSES,
            'robotsOptions' => SeoMetaService::ROBOTS,
        ]);
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $page = $this->pages->create($request->sanitized(), $request->user());

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Страница создана.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page->load(['seoMeta', 'terms']),
            'fieldGroups' => $this->fieldGroupsForTemplate($page->template ?: 'default'),
            'allFieldGroups' => CustomFieldGroup::query()->orderBy('name')->get(),
            'taxonomies' => Taxonomy::query()->with('terms')->orderBy('name')->get(),
            'parentPages' => Page::query()
                ->whereKeyNot($page->id)
                ->orderBy('title')
                ->get(),
            'statuses' => PageService::STATUSES,
            'robotsOptions' => SeoMetaService::ROBOTS,
        ]);
    }

    public function preview(Page $page): View
    {
        $page->load('seoMeta');

        return view('admin.pages.preview', [
            'page' => $page,
            'renderedHtml' => $this->renderer->render($page->content_json ?? $this->pages->defaultContent()),
        ]);
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $page = $this->pages->update($page, $request->sanitized(), $request->user());

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

    private function fieldGroupsForTemplate(string $template)
    {
        return CustomFieldGroup::query()
            ->orderBy('name')
            ->get()
            ->filter(fn (CustomFieldGroup $group) => $group->appliesToPageTemplate($template))
            ->values();
    }
}
