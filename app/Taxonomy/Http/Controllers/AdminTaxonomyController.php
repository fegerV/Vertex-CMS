<?php

namespace App\Taxonomy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use App\System\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminTaxonomyController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function index(): View
    {
        return view('admin.taxonomies.index', [
            'taxonomies' => Taxonomy::query()
                ->withCount('terms')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.taxonomies.create', [
            'taxonomy' => new Taxonomy([
                'entity_type' => 'page',
                'hierarchical' => false,
                'settings_json' => [],
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validated($request);

        $taxonomy = Taxonomy::query()->create($payload);

        $this->activityLog->record('taxonomy.create', 'taxonomy', $taxonomy->id, "Taxonomy \"{$taxonomy->slug}\" created.");

        return redirect()
            ->route('admin.taxonomies.edit', $taxonomy)
            ->with('status', 'Taxonomy created.');
    }

    public function edit(Taxonomy $taxonomy): View
    {
        return view('admin.taxonomies.edit', [
            'taxonomy' => $taxonomy->load(['terms' => fn ($query) => $query->with('parent')->withCount('pages')->orderBy('sort_order')->orderBy('name')]),
        ]);
    }

    public function update(Request $request, Taxonomy $taxonomy): RedirectResponse
    {
        $payload = $this->validated($request, $taxonomy);

        $taxonomy->forceFill($payload)->save();

        $this->activityLog->record('taxonomy.edit', 'taxonomy', $taxonomy->id, "Taxonomy \"{$taxonomy->slug}\" updated.");

        return redirect()
            ->route('admin.taxonomies.edit', $taxonomy)
            ->with('status', 'Taxonomy saved.');
    }

    public function destroy(Taxonomy $taxonomy): RedirectResponse
    {
        $this->activityLog->record('taxonomy.delete', 'taxonomy', $taxonomy->id, "Taxonomy \"{$taxonomy->slug}\" deleted.");
        $taxonomy->delete();

        return redirect()
            ->route('admin.taxonomies.index')
            ->with('status', 'Taxonomy deleted.');
    }

    private function validated(Request $request, ?Taxonomy $taxonomy = null): array
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('taxonomies', 'slug')->ignore($taxonomy)],
            'entity_type' => ['required', Rule::in(['page'])],
            'hierarchical' => ['nullable', 'boolean'],
            'archive_title' => ['nullable', 'string', 'max:255'],
            'archive_description' => ['nullable', 'string', 'max:1000'],
        ]);

        return [
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'entity_type' => $payload['entity_type'],
            'hierarchical' => $request->boolean('hierarchical'),
            'settings_json' => array_filter([
                'archive_title' => $payload['archive_title'] ?? null,
                'archive_description' => $payload['archive_description'] ?? null,
            ], fn ($value) => $value !== null && $value !== ''),
        ];
    }
}
