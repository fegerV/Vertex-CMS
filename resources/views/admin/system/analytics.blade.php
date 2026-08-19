@extends('admin.layouts.app')

@section('title', 'Аналитика - VertexCMS')
@section('page_title', 'Аналитика')
@section('page_subtitle', 'Сводка посещаемости страниц и архивов терминов')

@section('content')
    <div class="space-y-6">
        <div class="vc-toolbar">
            <div class="vc-toolbar-meta">
                <span class="vc-toolbar-title">Трафик сайта</span>
                <span class="vc-toolbar-text">Cookieless-аналитика по страницам и архивам таксономий. Выберите окно анализа ниже.</span>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.system.analytics') }}" class="vc-panel p-4">
            <div class="vc-chip-row">
                @foreach ([7, 30, 90] as $window)
                    <button
                        type="submit"
                        name="days"
                        value="{{ $window }}"
                        class="vc-button {{ (int) $analytics['days'] === $window ? 'vc-button-primary' : 'vc-button-secondary' }}"
                    >
                        {{ $window }} дней
                    </button>
                @endforeach
            </div>
        </form>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Визиты</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ number_format($analytics['totals']['visits']) }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Всего за последние {{ $analytics['days'] }} дней</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Посетители</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ number_format($analytics['totals']['visitors']) }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Оценка уникальных пользователей без cookie</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Сегодня</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ number_format($analytics['totals']['today_visits']) }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">{{ number_format($analytics['totals']['today_visitors']) }} посетителей</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Вчера</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ number_format($analytics['totals']['yesterday_visits']) }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">{{ number_format($analytics['totals']['yesterday_visitors']) }} посетителей</p>
            </article>
        </section>

        <section class="vc-panel p-5">
            <div>
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Динамика по дням</h2>
                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Ежедневная активность по страницам и архивам терминов.</p>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table class="vc-table text-sm">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Визиты</th>
                            <th>Посетители</th>
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

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="vc-table-wrap">
                <div class="border-b border-[var(--vc-border)] px-4 py-4">
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Топ страниц</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Самые посещаемые публичные страницы за выбранный период.</p>
                </div>
                <table class="vc-table text-sm">
                    <thead>
                        <tr>
                            <th>Заголовок</th>
                            <th>Путь</th>
                            <th>Визиты</th>
                            <th>Посетители</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($analytics['top_pages'] as $row)
                            <tr>
                                <td class="font-medium text-[var(--vc-text)]">{{ $row->title ?: 'Страница без названия' }}</td>
                                <td>{{ $row->path }}</td>
                                <td>{{ number_format($row->visits) }}</td>
                                <td>{{ number_format($row->visitors) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-[var(--vc-text-muted)]">Данных по страницам пока нет.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <section class="vc-table-wrap">
                <div class="border-b border-[var(--vc-border)] px-4 py-4">
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Топ архивов терминов</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Самые посещаемые архивы таксономий за выбранный период.</p>
                </div>
                <table class="vc-table text-sm">
                    <thead>
                        <tr>
                            <th>Термин</th>
                            <th>Путь</th>
                            <th>Визиты</th>
                            <th>Посетители</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($analytics['top_terms'] as $row)
                            <tr>
                                <td class="font-medium text-[var(--vc-text)]">{{ $row->title ?: 'Термин без названия' }}</td>
                                <td>{{ $row->path }}</td>
                                <td>{{ number_format($row->visits) }}</td>
                                <td>{{ number_format($row->visitors) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-[var(--vc-text-muted)]">Данных по архивам терминов пока нет.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>

        <section class="vc-table-wrap">
            <div class="border-b border-[var(--vc-border)] px-4 py-4">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Последняя активность</h2>
                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Свежие агрегированные записи трекера по страницам и архивам.</p>
            </div>
            <table class="vc-table text-sm">
                <thead>
                    <tr>
                        <th>Тип</th>
                        <th>Заголовок</th>
                        <th>Путь</th>
                        <th>Последний визит</th>
                        <th>Визиты</th>
                        <th>Посетители</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($analytics['recent'] as $row)
                        <tr>
                            <td><span class="vc-badge">{{ $row->kind }}</span></td>
                            <td class="font-medium text-[var(--vc-text)]">{{ $row->title ?: '—' }}</td>
                            <td>{{ $row->path }}</td>
                            <td>{{ $row->last_visited_at?->format('d.m.Y H:i') ?: '—' }}</td>
                            <td>{{ number_format($row->visits) }}</td>
                            <td>{{ number_format($row->visitors) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-[var(--vc-text-muted)]">Трафик пока не зафиксирован.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection
