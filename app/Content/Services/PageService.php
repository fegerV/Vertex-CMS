<?php

namespace App\Content\Services;

use App\Core\Services\SlugService;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use App\System\Services\ActivityLogService;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class PageService
{
    public const STATUSES = ['draft', 'published', 'scheduled', 'archived'];

    public function __construct(
        private readonly SlugService $slug,
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function create(array $payload, User $user): Page
    {
        $payload = $this->preparePayload($payload);

        $page = Page::query()->create([
            ...$payload,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->createRevision($page, $user);
        $this->activityLog->record('pages.create', 'page', $page->id, "Page \"{$page->title}\" created.");

        return $page;
    }

    public function update(Page $page, array $payload, User $user): Page
    {
        $payload = $this->preparePayload($payload, $page);

        $page->forceFill([
            ...$payload,
            'updated_by' => $user->id,
        ])->save();

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

        if ($page && $parentId === $page->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'Page cannot be its own parent.',
            ]);
        }

        if ($slug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Slug could not be generated.',
            ]);
        }

        $this->ensureSlugIsUnique($slug, $parentId, $page?->id);

        $content = $this->normalizeContent($payload['content_json'] ?? null);

        return [
            'parent_id' => $parentId,
            'title' => $payload['title'],
            'slug' => $slug,
            'uri' => $this->buildUri($slug, $parentId),
            'status' => $status,
            'template' => $payload['template'] ?: 'default',
            'content_json' => $content,
            'published_at' => $this->publishedAt($status, $page),
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
            'seo_json' => Arr::wrap($page->seoMeta?->toArray()),
            'created_at' => now(),
        ]);
    }
}
