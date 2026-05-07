<?php

namespace App\Content\Http\Controllers;

use App\Content\Services\PageService;
use App\Http\Controllers\Controller;
use App\Models\Page;
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
            'pages' => Page::query()->latest()->paginate(20),
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
            'page' => $page,
            'parentPages' => Page::query()
                ->whereKeyNot($page->id)
                ->orderBy('title')
                ->get(),
            'statuses' => PageService::STATUSES,
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
        return $request->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists('pages', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(PageService::STATUSES)],
            'template' => ['nullable', 'string', 'max:255'],
            'content_json' => ['nullable', 'string'],
        ]);
    }
}
