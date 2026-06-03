<?php

namespace App\Content\Services;

use App\Core\Services\SlugService;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Term;
use App\Models\User;
use App\Seo\Services\SeoMetaService;
use App\System\Services\ActivityLogService;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class PageService
{
    public const STATUSES = ['draft', 'published', 'scheduled', 'archived'];

    public function __construct(
        private readonly SlugService $slug,
        private readonly ActivityLogService $activityLog,
        private readonly SeoMetaService $seoMeta,
    ) {
    }

    public function create(array $payload, User $user): Page
    {
        $payload = $this->preparePayload($payload);

        $page = Page::query()->create([
            ...$this->pageAttributes($payload),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->seoMeta->updateFor($page, $payload);
        $this->syncTerms($page, $payload['term_ids'] ?? []);
        $page->load('seoMeta');
        $this->createRevision($page, $user);
        $this->activityLog->record('pages.create', 'page', $page->id, "Page \"{$page->title}\" created.");

        return $page;
    }

    public function update(Page $page, array $payload, User $user): Page
    {
        $payload = $this->preparePayload($payload, $page);

        $page->forceFill([
            ...$this->pageAttributes($payload),
            'updated_by' => $user->id,
        ])->save();

        $this->seoMeta->updateFor($page, $payload);
        $this->syncTerms($page, $payload['term_ids'] ?? []);
        $page->load('seoMeta');
        $this->createRevision($page, $user);
        $this->activityLog->record('pages.edit', 'page', $page->id, "Page \"{$page->title}\" updated.");

        return $page;
    }

    public function delete(Page $page, User $user): void
    {
        $page->forceFill(['updated_by' => $user->id])->save();
        $page->delete();

        $this->activityLog->record('pages.delete', 'page', $page->id, "Page \"{$page->title}\" deleted.");
    }

    public function defaultContent(): array
    {
        return [
            'version' => '1.0',
            'layout' => 'default',
            'settings' => [
                'container' => '1200px',
                'background' => '#ffffff',
            ],
            'sections' => [],
        ];
    }

    private function preparePayload(array $payload, ?Page $page = null): array
    {
        $parentId = filled($payload['parent_id'] ?? null) ? (int) $payload['parent_id'] : null;
        $status = $payload['status'] ?? 'draft';
        $slug = $this->slug->make(($payload['slug'] ?? '') ?: $payload['title']);

        $this->validateParent($parentId, $page);
        $this->validateSlug($slug);
        $this->ensureSlugIsUnique($slug, $parentId, $page?->id);

        return [
            'parent_id' => $parentId,
            'title' => $payload['title'],
            'slug' => $slug,
            'uri' => $this->buildUri($slug, $parentId),
            'status' => $status,
            'template' => $payload['template'] ?: 'default',
            'content_json' => $this->normalizeContent($payload['content_json'] ?? null),
            'custom_fields_json' => $this->normalizeCustomFieldsPayload($payload['custom_fields_json'] ?? null),
            'published_at' => $this->publishedAt($status, $page),
            ...$this->extractSeoPayload($payload),
        ];
    }

    private function validateParent(?int $parentId, ?Page $page): void
    {
        if ($page && $parentId === $page->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'Page cannot be its own parent.',
            ]);
        }
    }

    private function validateSlug(string $slug): void
    {
        if ($slug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Slug could not be generated.',
            ]);
        }
    }

    /**
     * Extract SEO-related fields from payload.
     */
    private function extractSeoPayload(array $payload): array
    {
        return [
            'seo_title' => $payload['seo_title'] ?? null,
            'seo_description' => $payload['seo_description'] ?? null,
            'seo_canonical_url' => $payload['seo_canonical_url'] ?? null,
            'seo_robots' => $payload['seo_robots'] ?? 'index, follow',
            'seo_og_title' => $payload['seo_og_title'] ?? null,
            'seo_og_description' => $payload['seo_og_description'] ?? null,
            'seo_og_image' => $payload['seo_og_image'] ?? null,
            'seo_schema_json' => $payload['seo_schema_json'] ?? null,
            'seo_include_in_sitemap' => $payload['seo_include_in_sitemap'] ?? true,
        ];
    }

    private function ensureSlugIsUnique(string $slug, ?int $parentId, ?int $ignoreId = null): void
    {
        $exists = Page::query()
            ->where('slug', $slug)
            ->where(function ($query) use ($parentId): void {
                $parentId === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $parentId);
            })
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'slug' => 'Slug already exists on this level.',
            ]);
        }
    }

    private function buildUri(string $slug, ?int $parentId): string
    {
        if ($parentId === null) {
            return '/'.$slug;
        }

        $parent = Page::query()->findOrFail($parentId);

        return rtrim($parent->uri, '/').'/'.$slug;
    }

    private function normalizeContent(mixed $content): array
    {
        if (blank($content)) {
            return $this->defaultContent();
        }

        if (is_array($content)) {
            return $content;
        }

        $decoded = json_decode((string) $content, true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'content_json' => 'Content JSON is invalid.',
            ]);
        }

        return $decoded;
    }

    private function publishedAt(string $status, ?Page $page): mixed
    {
        if ($status === 'published') {
            return $page?->published_at ?? now();
        }

        if ($status === 'scheduled') {
            return $page?->published_at;
        }

        return null;
    }

    private function createRevision(Page $page, User $user): void
    {
        PageRevision::query()->create([
            'page_id' => $page->id,
            'user_id' => $user->id,
            'title' => $page->title,
            'content_json' => $page->content_json,
            'custom_fields_json' => $page->custom_fields_json,
            'seo_json' => $page->seoMeta?->toArray() ?? [],
            'action' => 'page-save',
            'created_at' => now(),
        ]);
    }

    private function pageAttributes(array $payload): array
    {
        return Arr::only($payload, [
            'parent_id',
            'title',
            'slug',
            'uri',
            'status',
            'template',
            'content_json',
            'custom_fields_json',
            'published_at',
        ]);
    }

    private function syncTerms(Page $page, array $termIds): void
    {
        $validTermIds = Term::query()
            ->whereIn('id', $termIds)
            ->pluck('id')
            ->all();

        $page->terms()->sync($validTermIds);
    }

    public function normalizeCustomFieldsPayload(mixed $fields): array
    {
        if (blank($fields)) {
            return [];
        }

        if (is_array($fields)) {
            return array_values(array_filter(array_map([$this, 'normalizeCustomField'], $fields)));
        }

        $decoded = json_decode((string) $fields, true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'custom_fields_json' => 'Custom fields JSON is invalid.',
            ]);
        }

        return array_values(array_filter(array_map([$this, 'normalizeCustomField'], $decoded)));
    }

    private function normalizeCustomField(mixed $field): ?array
    {
        if (! is_array($field)) {
            return null;
        }

        $key = trim((string) ($field['key'] ?? ''));
        if ($key === '') {
            return null;
        }

        return [
            'key' => $key,
            'label' => trim((string) ($field['label'] ?? $key)),
            'type' => trim((string) ($field['type'] ?? 'text')),
            'value' => $field['value'] ?? null,
            'description' => trim((string) ($field['description'] ?? '')),
        ];
    }
}
