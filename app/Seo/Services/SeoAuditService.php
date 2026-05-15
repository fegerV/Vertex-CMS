<?php

namespace App\Seo\Services;

use App\Models\Page;
use App\Models\Redirect;
use App\Models\Term;
use Illuminate\Support\Collection;

class SeoAuditService
{
    private const TITLE_LIMIT = 60;
    private const DESCRIPTION_LIMIT = 160;

    public function __construct(
        private readonly SeoContentAnalysisService $contentAnalysis,
    ) {
    }

    public function overview(): array
    {
        $pages = $this->publishedPages();
        $terms = $this->indexedTerms();
        $contentAnalysis = $this->contentAnalysis->overview($pages);
        $issues = collect()
            ->merge($pages->flatMap(fn (Page $page): array => $this->auditPage($page)))
            ->merge($terms->flatMap(fn (Term $term): array => $this->auditTerm($term)))
            ->merge($this->duplicateTitleIssues($pages, $terms))
            ->sortBy([
                fn (array $issue): int => $this->severityWeight($issue['severity']),
                fn (array $issue): string => (string) $issue['entity_label'],
            ])
            ->values();

        $redirects = Redirect::query()->orderByDesc('hits')->latest()->get();

        return [
            'totals' => [
                'published_pages' => $pages->count(),
                'term_archives' => $terms->count(),
                'indexed_entries' => $pages->filter(fn (Page $page): bool => $this->isIncludedInSitemap(
                    $page->seoMeta?->robots ?? 'index, follow',
                    $page->seoMeta?->include_in_sitemap ?? true,
                ))->count() + $terms->filter(fn (Term $term): bool => $this->isIncludedInSitemap(
                    $term->seo_json['robots'] ?? 'index, follow',
                    $term->seo_json['include_in_sitemap'] ?? true,
                ))->count(),
                'active_redirects' => $redirects->where('enabled', true)->count(),
                'issues' => $issues->count(),
            ],
            'coverage' => [
                'pages' => [
                    'title' => $pages->filter(fn (Page $page): bool => filled($page->seoMeta?->title))->count(),
                    'description' => $pages->filter(fn (Page $page): bool => filled($page->seoMeta?->description))->count(),
                    'total' => $pages->count(),
                ],
                'terms' => [
                    'title' => $terms->filter(fn (Term $term): bool => filled($term->seo_json['title'] ?? null))->count(),
                    'description' => $terms->filter(fn (Term $term): bool => filled($term->seo_json['description'] ?? null))->count(),
                    'total' => $terms->count(),
                ],
            ],
            'redirects' => [
                'runtime_enabled' => true,
                'total' => $redirects->count(),
                'enabled' => $redirects->where('enabled', true)->count(),
                'top_hits' => $redirects
                    ->sortByDesc('hits')
                    ->take(5)
                    ->map(fn (Redirect $redirect): array => [
                        'from_url' => $redirect->from_url,
                        'to_url' => $redirect->to_url,
                        'status_code' => $redirect->status_code,
                        'hits' => (int) $redirect->hits,
                        'enabled' => (bool) $redirect->enabled,
                    ])
                    ->values()
                    ->all(),
            ],
            'content_analysis' => $contentAnalysis,
            'issues' => $issues->take(25)->all(),
        ];
    }

    private function publishedPages(): Collection
    {
        return Page::query()
            ->with('seoMeta')
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('title')
            ->get();
    }

    private function indexedTerms(): Collection
    {
        return Term::query()
            ->with('taxonomy')
            ->whereHas('pages', function ($query): void {
                $query
                    ->where('status', 'published')
                    ->where(fn ($builder) => $builder->whereNull('published_at')->orWhere('published_at', '<=', now()));
            })
            ->orderBy('name')
            ->get();
    }

    private function auditPage(Page $page): array
    {
        $seo = $page->seoMeta;
        $issues = [];
        $title = trim((string) ($seo?->title ?? ''));
        $description = trim((string) ($seo?->description ?? ''));
        $robots = (string) ($seo?->robots ?? 'index, follow');
        $includeInSitemap = (bool) ($seo?->include_in_sitemap ?? true);

        if ($title === '') {
            $issues[] = $this->issue(
                'warning',
                'page',
                $page->title,
                'Пустой SEO title',
                'У страницы не заполнен отдельный SEO title, поэтому фронтенд будет использовать обычный заголовок страницы.',
                route('admin.pages.edit', $page),
            );
        } elseif (mb_strlen($title) > self::TITLE_LIMIT) {
            $issues[] = $this->issue(
                'warning',
                'page',
                $page->title,
                'Слишком длинный SEO title',
                'Длина SEO title превышает рекомендуемые '.self::TITLE_LIMIT.' символов.',
                route('admin.pages.edit', $page),
            );
        }

        if ($description === '') {
            $issues[] = $this->issue(
                'danger',
                'page',
                $page->title,
                'Пустой meta description',
                'У опубликованной страницы нет отдельного meta description.',
                route('admin.pages.edit', $page),
            );
        } elseif (mb_strlen($description) > self::DESCRIPTION_LIMIT) {
            $issues[] = $this->issue(
                'warning',
                'page',
                $page->title,
                'Слишком длинный meta description',
                'Длина meta description превышает рекомендуемые '.self::DESCRIPTION_LIMIT.' символов.',
                route('admin.pages.edit', $page),
            );
        }

        if ($includeInSitemap && $robots !== 'index, follow') {
            $issues[] = $this->issue(
                'danger',
                'page',
                $page->title,
                'Конфликт sitemap и robots',
                'Страница отмечена для sitemap, но robots не разрешает нормальную индексацию.',
                route('admin.pages.edit', $page),
            );
        }

        return $issues;
    }

    private function auditTerm(Term $term): array
    {
        $seo = $term->seo_json ?? [];
        $issues = [];
        $title = trim((string) ($seo['title'] ?? ''));
        $description = trim((string) ($seo['description'] ?? ''));
        $robots = (string) ($seo['robots'] ?? 'index, follow');
        $includeInSitemap = (bool) ($seo['include_in_sitemap'] ?? true);
        $editUrl = route('admin.taxonomies.terms.edit', [$term->taxonomy, $term]);

        if ($title === '') {
            $issues[] = $this->issue(
                'warning',
                'term',
                $term->name,
                'Пустой SEO title архива',
                'У архивной страницы термина не задан отдельный SEO title, поэтому будет использоваться fallback.',
                $editUrl,
            );
        } elseif (mb_strlen($title) > self::TITLE_LIMIT) {
            $issues[] = $this->issue(
                'warning',
                'term',
                $term->name,
                'Слишком длинный SEO title архива',
                'Длина SEO title архива превышает рекомендуемые '.self::TITLE_LIMIT.' символов.',
                $editUrl,
            );
        }

        if ($description === '') {
            $issues[] = $this->issue(
                'warning',
                'term',
                $term->name,
                'Пустой meta description архива',
                'У архивной страницы термина нет отдельного meta description.',
                $editUrl,
            );
        } elseif (mb_strlen($description) > self::DESCRIPTION_LIMIT) {
            $issues[] = $this->issue(
                'warning',
                'term',
                $term->name,
                'Слишком длинный meta description архива',
                'Длина meta description архива превышает рекомендуемые '.self::DESCRIPTION_LIMIT.' символов.',
                $editUrl,
            );
        }

        if ($includeInSitemap && $robots !== 'index, follow') {
            $issues[] = $this->issue(
                'danger',
                'term',
                $term->name,
                'Конфликт sitemap и robots у архива',
                'Архив термина включен в sitemap, но robots не разрешает нормальную индексацию.',
                $editUrl,
            );
        }

        return $issues;
    }

    private function duplicateTitleIssues(Collection $pages, Collection $terms): Collection
    {
        $entries = collect();

        foreach ($pages as $page) {
            $title = trim((string) ($page->seoMeta?->title ?? $page->title));
            if ($title !== '') {
                $entries->push([
                    'title' => $title,
                    'label' => $page->title,
                    'url' => route('admin.pages.edit', $page),
                ]);
            }
        }

        foreach ($terms as $term) {
            $title = trim((string) (($term->seo_json ?? [])['title'] ?? "{$term->name} | ".config_value('site.name', 'VertexCMS')));
            if ($title !== '') {
                $entries->push([
                    'title' => $title,
                    'label' => $term->name,
                    'url' => route('admin.taxonomies.terms.edit', [$term->taxonomy, $term]),
                ]);
            }
        }

        return $entries
            ->groupBy('title')
            ->filter(fn (Collection $group): bool => $group->count() > 1)
            ->map(function (Collection $group, string $title): array {
                $labels = $group->pluck('label')->implode(', ');

                return $this->issue(
                    'warning',
                    'global',
                    $title,
                    'Повторяющийся title',
                    'Одинаковый title используется у нескольких сущностей: '.$labels.'.',
                    $group->first()['url'] ?? null,
                );
            })
            ->values();
    }

    private function issue(
        string $severity,
        string $scope,
        string $entityLabel,
        string $title,
        string $message,
        ?string $editUrl
    ): array {
        return [
            'severity' => $severity,
            'scope' => $scope,
            'entity_label' => $entityLabel,
            'title' => $title,
            'message' => $message,
            'edit_url' => $editUrl,
        ];
    }

    private function isIncludedInSitemap(string $robots, bool $includeInSitemap): bool
    {
        return $includeInSitemap && $robots === 'index, follow';
    }

    private function severityWeight(string $severity): int
    {
        return match ($severity) {
            'danger' => 0,
            'warning' => 1,
            default => 2,
        };
    }
}
