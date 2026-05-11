<?php

namespace App\Taxonomy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use App\Models\Term;
use App\System\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminTermController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function create(Taxonomy $taxonomy): View
    {
        return view('admin.taxonomies.terms.create', [
            'taxonomy' => $taxonomy->load('terms'),
            'term' => new Term([
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request, Taxonomy $taxonomy): RedirectResponse
    {
        $payload = $this->validated($request, $taxonomy);

        $term = $taxonomy->terms()->create($payload);

        $this->activityLog->record('term.create', 'term', $term->id, "Term \"{$term->slug}\" created for taxonomy \"{$taxonomy->slug}\".");

        return redirect()
            ->route('admin.taxonomies.edit', $taxonomy)
            ->with('status', 'Term created.');
    }

    public function edit(Taxonomy $taxonomy, Term $term): View
    {
        abort_unless($term->taxonomy_id === $taxonomy->id, 404);

        return view('admin.taxonomies.terms.edit', [
            'taxonomy' => $taxonomy->load('terms'),
            'term' => $term->loadCount('pages'),
        ]);
    }

    public function update(Request $request, Taxonomy $taxonomy, Term $term): RedirectResponse
    {
        abort_unless($term->taxonomy_id === $taxonomy->id, 404);

        $payload = $this->validated($request, $taxonomy, $term);

        $term->forceFill($payload)->save();

        $this->activityLog->record('term.edit', 'term', $term->id, "Term \"{$term->slug}\" updated.");

        return redirect()
            ->route('admin.taxonomies.edit', $taxonomy)
            ->with('status', 'Term saved.');
    }

    public function destroy(Taxonomy $taxonomy, Term $term): RedirectResponse
    {
        abort_unless($term->taxonomy_id === $taxonomy->id, 404);

        $this->activityLog->record('term.delete', 'term', $term->id, "Term \"{$term->slug}\" deleted.");
        $term->delete();

        return redirect()
            ->route('admin.taxonomies.edit', $taxonomy)
            ->with('status', 'Term deleted.');
    }

    private function validated(Request $request, Taxonomy $taxonomy, ?Term $term = null): array
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('terms', 'slug')
                    ->where(fn ($query) => $query->where('taxonomy_id', $taxonomy->id))
                    ->ignore($term),
            ],
            'parent_id' => ['nullable', 'integer', Rule::exists('terms', 'id')],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
        ]);

        $parentId = filled($payload['parent_id'] ?? null) ? (int) $payload['parent_id'] : null;

        if ($parentId) {
            $parentBelongsToTaxonomy = $taxonomy->terms()->whereKey($parentId)->exists();
            abort_unless($parentBelongsToTaxonomy, 422, 'Parent term must belong to the same taxonomy.');
        }

        if ($term && $parentId === $term->id) {
            abort(422, 'Term cannot be its own parent.');
        }

        return [
            'parent_id' => $parentId,
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'description' => $payload['description'] ?? null,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'seo_json' => array_filter([
                'title' => $payload['seo_title'] ?? null,
                'description' => $payload['seo_description'] ?? null,
            ], fn ($value) => $value !== null && $value !== ''),
        ];
    }
}
