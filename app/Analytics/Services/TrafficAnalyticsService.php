<?php

namespace App\Analytics\Services;

use App\Models\AnalyticsAggregate;
use App\Models\AnalyticsVisitor;
use App\Models\Page;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrafficAnalyticsService
{
    public function trackPage(Page $page, Request $request): void
    {
        $this->track(
            kind: $page->uri === '/' ? 'home' : 'page',
            path: $page->uri,
            title: $page->title,
            request: $request,
            page: $page,
        );
    }

    public function trackTermArchive(Term $term, Request $request): void
    {
        $this->track(
            kind: 'term',
            path: "/taxonomy/{$term->taxonomy?->slug}/{$term->slug}",
            title: $term->name,
            request: $request,
            term: $term,
        );
    }

    public function overview(int $days = 30): array
    {
        $days = max(7, min($days, 180));
        $startDate = now()->subDays($days - 1)->toDateString();
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $baseQuery = AnalyticsAggregate::query()->whereDate('visit_date', '>=', $startDate);

        $totals = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(visits), 0) as visits, COALESCE(SUM(visitors), 0) as visitors')
            ->first();

        $todayTotals = AnalyticsAggregate::query()
            ->whereDate('visit_date', $today)
            ->selectRaw('COALESCE(SUM(visits), 0) as visits, COALESCE(SUM(visitors), 0) as visitors')
            ->first();

        $yesterdayTotals = AnalyticsAggregate::query()
            ->whereDate('visit_date', $yesterday)
            ->selectRaw('COALESCE(SUM(visits), 0) as visits, COALESCE(SUM(visitors), 0) as visitors')
            ->first();

        $trend = (clone $baseQuery)
            ->selectRaw('visit_date, SUM(visits) as visits, SUM(visitors) as visitors')
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->get();

        $topPages = AnalyticsAggregate::query()
            ->whereDate('visit_date', '>=', $startDate)
            ->whereIn('kind', ['home', 'page'])
            ->selectRaw('page_id, path, MAX(title) as title, SUM(visits) as visits, SUM(visitors) as visitors, MAX(last_visited_at) as last_visited_at')
            ->groupBy('page_id', 'path')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        $topTerms = AnalyticsAggregate::query()
            ->whereDate('visit_date', '>=', $startDate)
            ->where('kind', 'term')
            ->selectRaw('term_id, path, MAX(title) as title, SUM(visits) as visits, SUM(visitors) as visitors, MAX(last_visited_at) as last_visited_at')
            ->groupBy('term_id', 'path')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        $recent = AnalyticsAggregate::query()
            ->latest('last_visited_at')
            ->limit(12)
            ->get();

        return [
            'days' => $days,
            'totals' => [
                'visits' => (int) ($totals->visits ?? 0),
                'visitors' => (int) ($totals->visitors ?? 0),
                'today_visits' => (int) ($todayTotals->visits ?? 0),
                'today_visitors' => (int) ($todayTotals->visitors ?? 0),
                'yesterday_visits' => (int) ($yesterdayTotals->visits ?? 0),
                'yesterday_visitors' => (int) ($yesterdayTotals->visitors ?? 0),
            ],
            'trend' => $this->normalizeTrend($trend, $days),
            'top_pages' => $topPages,
            'top_terms' => $topTerms,
            'recent' => $recent,
        ];
    }

    private function track(
        string $kind,
        string $path,
        string $title,
        Request $request,
        ?Page $page = null,
        ?Term $term = null,
    ): void {
        if ($this->shouldIgnore($request)) {
            return;
        }

        $visitDate = now()->toDateString();
        $aggregateKey = sha1(implode('|', [$visitDate, $kind, $path, $page?->id, $term?->id]));
        $visitorHash = sha1(implode('|', [
            $visitDate,
            $this->normalizedIp($request),
            substr((string) $request->userAgent(), 0, 255),
        ]));

        DB::transaction(function () use ($aggregateKey, $kind, $path, $title, $page, $term, $visitDate, $visitorHash): void {
            $aggregate = AnalyticsAggregate::query()->firstOrCreate(
                ['aggregate_key' => $aggregateKey],
                [
                    'visit_date' => $visitDate,
                    'kind' => $kind,
                    'page_id' => $page?->id,
                    'term_id' => $term?->id,
                    'path' => $path,
                    'title' => $title,
                    'visits' => 0,
                    'visitors' => 0,
                    'last_visited_at' => now(),
                ],
            );

            $aggregate->forceFill([
                'title' => $title,
                'path' => $path,
                'last_visited_at' => now(),
            ])->save();

            $aggregate->increment('visits');

            $wasUnique = AnalyticsVisitor::query()->firstOrCreate(
                [
                    'aggregate_id' => $aggregate->id,
                    'visitor_hash' => $visitorHash,
                ],
                [
                    'created_at' => now(),
                ],
            )->wasRecentlyCreated;

            if ($wasUnique) {
                $aggregate->increment('visitors');
            }
        });
    }

    private function shouldIgnore(Request $request): bool
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return true;
        }

        $userAgent = strtolower((string) $request->userAgent());

        if ($userAgent === '') {
            return false;
        }

        return (bool) preg_match('/bot|crawl|spider|preview|lighthouse|headless|facebookexternalhit|slurp|bingpreview/', $userAgent);
    }

    private function normalizedIp(Request $request): string
    {
        return trim((string) $request->ip()) !== '' ? (string) $request->ip() : 'unknown';
    }

    private function normalizeTrend(Collection $trend, int $days): Collection
    {
        $map = $trend->keyBy(fn ($row) => (string) $row->visit_date);

        return collect(range($days - 1, 0))
            ->map(function (int $offset) use ($map) {
                $date = now()->subDays($offset)->toDateString();
                $row = $map->get($date);

                return [
                    'date' => $date,
                    'visits' => (int) ($row->visits ?? 0),
                    'visitors' => (int) ($row->visitors ?? 0),
                ];
            });
    }
}
