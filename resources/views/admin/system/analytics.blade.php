@extends('admin.layouts.app')

@section('title', 'Analytics - VertexCMS')
@section('page_title', 'Analytics')
@section('page_subtitle', 'Traffic overview for public pages and term archives')

@section('content')
    <form method="GET" action="{{ route('admin.system.analytics') }}" class="vc-panel mb-6 flex flex-wrap items-center gap-3 p-4">
        @foreach ([7, 30, 90] as $window)
            <button
                type="submit"
                name="days"
                value="{{ $window }}"
                class="vc-button {{ (int) $analytics['days'] === $window ? 'vc-button-primary' : 'vc-button-secondary' }} px-4 py-3"
            >
                {{ $window }} days
            </button>
        @endforeach
    </form>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="vc-panel p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Visits</p>
            <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ number_format($analytics['totals']['visits']) }}</p>
            <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Total for last {{ $analytics['days'] }} days</p>
        </article>
        <article class="vc-panel p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Unique Visitors</p>
            <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ number_format($analytics['totals']['visitors']) }}</p>
            <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Cookieless daily estimate</p>
        </article>
        <article class="vc-panel p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Today</p>
            <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ number_format($analytics['totals']['today_visits']) }}</p>
            <p class="mt-2 text-sm text-[var(--vc-text-muted)]">{{ number_format($analytics['totals']['today_visitors']) }} visitors</p>
        </article>
        <article class="vc-panel p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Yesterday</p>
            <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ number_format($analytics['totals']['yesterday_visits']) }}</p>
            <p class="mt-2 text-sm text-[var(--vc-text-muted)]">{{ number_format($analytics['totals']['yesterday_visitors']) }} visitors</p>
        </article>
    </section>

    <section class="vc-panel mt-6 p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Trend</h2>
                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Daily traffic across pages and term archives.</p>
            </div>
        </div>
        <div class="mt-5 overflow-x-auto">
            <table class="vc-table text-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Visits</th>
                        <th>Visitors</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($analytics['trend'] as $day)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($day['date'])->format('d.m.Y') }}</td>
                            <td class="font-medium text-[var(--vc-text)]">{{ number_format($day['visits']) }}</td>
                            <td>{{ number_format($day['visitors']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="vc-table-wrap">
            <div class="border-b border-[var(--vc-border)] px-4 py-4">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Top Pages</h2>
                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Most visited public pages for the selected window.</p>
            </div>
            <table class="vc-table text-sm">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Path</th>
                        <th>Visits</th>
                        <th>Visitors</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($analytics['top_pages'] as $row)
                        <tr>
                            <td class="font-medium text-[var(--vc-text)]">{{ $row->title ?: 'Untitled page' }}</td>
                            <td>{{ $row->path }}</td>
                            <td>{{ number_format($row->visits) }}</td>
                            <td>{{ number_format($row->visitors) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-[var(--vc-text-muted)]">No page traffic recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="vc-table-wrap">
            <div class="border-b border-[var(--vc-border)] px-4 py-4">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Top Term Archives</h2>
                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Most visited taxonomy archives for the selected window.</p>
            </div>
            <table class="vc-table text-sm">
                <thead>
                    <tr>
                        <th>Term</th>
                        <th>Path</th>
                        <th>Visits</th>
                        <th>Visitors</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($analytics['top_terms'] as $row)
                        <tr>
                            <td class="font-medium text-[var(--vc-text)]">{{ $row->title ?: 'Untitled term' }}</td>
                            <td>{{ $row->path }}</td>
                            <td>{{ number_format($row->visits) }}</td>
                            <td>{{ number_format($row->visitors) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-[var(--vc-text-muted)]">No term archive traffic recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <section class="vc-table-wrap mt-6">
        <div class="border-b border-[var(--vc-border)] px-4 py-4">
            <h2 class="text-lg font-semibold text-[var(--vc-text)]">Recent Activity</h2>
            <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Latest public traffic buckets updated by the tracker.</p>
        </div>
        <table class="vc-table text-sm">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Path</th>
                    <th>Last Seen</th>
                    <th>Visits</th>
                    <th>Visitors</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($analytics['recent'] as $row)
                    <tr>
                        <td><span class="vc-badge">{{ $row->kind }}</span></td>
                        <td class="font-medium text-[var(--vc-text)]">{{ $row->title ?: '-' }}</td>
                        <td>{{ $row->path }}</td>
                        <td>{{ $row->last_visited_at?->format('d.m.Y H:i') ?: '-' }}</td>
                        <td>{{ number_format($row->visits) }}</td>
                        <td>{{ number_format($row->visitors) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[var(--vc-text-muted)]">Traffic has not been recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
