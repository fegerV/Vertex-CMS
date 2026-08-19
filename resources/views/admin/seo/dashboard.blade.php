@extends('admin.layouts.app')

@section('title', 'SEO - VertexCMS')
@section('page_title', 'SEO')
@section('page_subtitle', 'Метаданные, redirects, sitemap и контентный аудит')

@section('content')
    @php
        $totals = $dashboard['totals'];
        $coverage = $dashboard['coverage'];
        $redirects = $dashboard['redirects'];
        $contentAnalysis = $dashboard['content_analysis'];
        $issues = $dashboard['issues'];

        $badgeClasses = [
            'danger' => 'border-rose-200 bg-rose-50 text-rose-700',
            'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
            'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'muted' => 'border-[var(--vc-border)] bg-[var(--vc-surface-muted)] text-[var(--vc-text-muted)]',
        ];
    @endphp

    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Опубликованные страницы</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ $totals['published_pages'] }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Страницы, которые реально участвуют в публичном SEO-контуре.</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Архивы терминов</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ $totals['term_archives'] }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Taxonomy archive pages с опубликованным контентом.</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Активные redirects</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ $totals['active_redirects'] }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Runtime-редиректы отрабатывают раньше catch-all page route.</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Audit issues</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ $totals['issues'] }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Предупреждения по мета-полям, структуре контента и redirect/runtime-сигналам.</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1fr_1fr]">
            <article class="vc-panel p-6">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Meta coverage</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Показывает, сколько сущностей имеют явно заполненные поля, а не только fallback-значения.</p>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-sm font-semibold text-[var(--vc-text)]">Страницы</p>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--vc-text-muted)]">SEO title</dt>
                                <dd class="font-semibold text-[var(--vc-text)]">{{ $coverage['pages']['title'] }} / {{ $coverage['pages']['total'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--vc-text-muted)]">Description</dt>
                                <dd class="font-semibold text-[var(--vc-text)]">{{ $coverage['pages']['description'] }} / {{ $coverage['pages']['total'] }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-sm font-semibold text-[var(--vc-text)]">Архивы терминов</p>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--vc-text-muted)]">SEO title</dt>
                                <dd class="font-semibold text-[var(--vc-text)]">{{ $coverage['terms']['title'] }} / {{ $coverage['terms']['total'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--vc-text-muted)]">Description</dt>
                                <dd class="font-semibold text-[var(--vc-text)]">{{ $coverage['terms']['description'] }} / {{ $coverage['terms']['total'] }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                    <p class="font-semibold text-[var(--vc-text)]">Что уже входит в встроенный SEO-модуль</p>
                    <ul class="mt-3 space-y-2 text-sm text-[var(--vc-text-muted)]">
                        <li>Meta title, description, canonical, robots, Open Graph, schema JSON</li>
                        <li>Public sitemap.xml и data-driven robots.txt</li>
                        <li>SEO для страниц и taxonomy archives</li>
                        <li>Runtime redirects со статусом и счётчиком hits</li>
                        <li>Audit layer для контроля качества и конфликтов</li>
                    </ul>
                </div>
            </article>

            <article class="vc-panel p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Redirect runtime</h2>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Редиректы больше не являются только CRUD-таблицей: публичный middleware разрешает их до page route.</p>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $redirects['runtime_enabled'] ? $badgeClasses['success'] : $badgeClasses['danger'] }}">
                            {{ $redirects['runtime_enabled'] ? 'Активен' : 'Отключён' }}
                        </span>
                        <a href="{{ route('admin.redirects.index') }}" class="vc-button vc-button-secondary px-4 py-2 text-sm">
                            Управлять
                        </a>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Всего правил</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $redirects['total'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Включено</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $redirects['enabled'] }}</p>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($redirects['top_hits'] as $redirect)
                        <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $redirect['enabled'] ? $badgeClasses['success'] : $badgeClasses['muted'] }}">
                                    {{ $redirect['status_code'] }}
                                </span>
                                <span class="text-xs text-[var(--vc-text-soft)]">{{ number_format($redirect['hits']) }} hits</span>
                            </div>
                            <p class="mt-3 break-all text-sm font-semibold text-[var(--vc-text)]">{{ $redirect['from_url'] }}</p>
                            <p class="mt-1 break-all text-sm text-[var(--vc-text-muted)]">{{ $redirect['to_url'] }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[var(--vc-border)] px-4 py-6 text-sm text-[var(--vc-text-muted)]">
                            Redirect rules пока не заведены. Когда они появятся, этот блок покажет самые используемые переходы.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="vc-panel p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Content analysis</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Структурные подсказки по H1, meta description и alt coverage на уровне опубликованных страниц.</p>
                </div>
                <span class="inline-flex rounded-full border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-3 py-1 text-xs font-semibold text-[var(--vc-text-muted)]">
                    {{ count($contentAnalysis['pages']) }} страниц с подсказками
                </span>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Single H1</p>
                    <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $contentAnalysis['totals']['pages_with_single_h1'] }}</p>
                </div>
                <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Нет H1</p>
                    <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $contentAnalysis['totals']['pages_missing_h1'] }}</p>
                </div>
                <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Multiple H1</p>
                    <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $contentAnalysis['totals']['pages_with_multiple_h1'] }}</p>
                </div>
                <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Images total</p>
                    <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $contentAnalysis['totals']['images_total'] }}</p>
                </div>
                <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Без alt</p>
                    <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $contentAnalysis['totals']['images_missing_alt'] }}</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                @forelse ($contentAnalysis['pages'] as $page)
                    <article class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-5 py-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-base font-semibold text-[var(--vc-text)]">{{ $page['title'] }}</p>
                                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ $page['uri'] }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex rounded-full border border-[var(--vc-border)] px-2.5 py-1 text-xs font-semibold text-[var(--vc-text-muted)]">
                                    H1: {{ $page['h1_count'] }}
                                </span>
                                <span class="inline-flex rounded-full border border-[var(--vc-border)] px-2.5 py-1 text-xs font-semibold text-[var(--vc-text-muted)]">
                                    Images: {{ $page['image_count'] }}
                                </span>
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $page['images_missing_alt'] > 0 ? $badgeClasses['warning'] : $badgeClasses['success'] }}">
                                    Alt missing: {{ $page['images_missing_alt'] }}
                                </span>
                                <a href="{{ $page['edit_url'] }}" class="vc-button vc-button-secondary px-4 py-2 text-sm">
                                    Открыть
                                </a>
                            </div>
                        </div>

                        @if (! empty($page['heading_outline']))
                            <div class="mt-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Heading outline</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($page['heading_outline'] as $heading)
                                        <span class="inline-flex rounded-full border border-[var(--vc-border)] bg-white px-3 py-1 text-xs text-[var(--vc-text-muted)]">
                                            {{ $heading }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 grid gap-4 lg:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Issues</p>
                                <ul class="mt-2 space-y-2 text-sm text-[var(--vc-text)]">
                                    @foreach ($page['issues'] as $issue)
                                        <li class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2">{{ $issue }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Hints</p>
                                <ul class="mt-2 space-y-2 text-sm text-[var(--vc-text)]">
                                    @forelse ($page['suggestions'] as $suggestion)
                                        <li class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2">{{ $suggestion }}</li>
                                    @empty
                                        <li class="rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-[var(--vc-text-muted)]">Дополнительных подсказок пока нет.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        @if ($page['suggested_description'])
                            <div class="mt-4 rounded-2xl border border-[var(--vc-border)] bg-white px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Suggested description</p>
                                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">{{ $page['suggested_description'] }}</p>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-[var(--vc-border)] px-4 py-6 text-sm text-[var(--vc-text-muted)]">
                        Сейчас у опубликованных страниц нет контентных SEO-подсказок.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="vc-panel p-6">
            <div>
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Ключевые SEO-проблемы</h2>
                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Список собирается на сервере из pages, term archives и runtime-сигналов без отдельного фонового индекса.</p>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($issues as $issue)
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badgeClasses[$issue['severity']] ?? $badgeClasses['muted'] }}">
                                    {{ strtoupper($issue['severity']) }}
                                </span>
                                <span class="inline-flex rounded-full border border-[var(--vc-border)] px-2.5 py-1 text-xs font-semibold text-[var(--vc-text-muted)]">
                                    {{ strtoupper($issue['scope']) }}
                                </span>
                            </div>
                            @if ($issue['edit_url'])
                                <a href="{{ $issue['edit_url'] }}" class="text-sm font-medium text-sky-600 hover:text-sky-500">
                                    Открыть
                                </a>
                            @endif
                        </div>
                        <p class="mt-3 text-sm font-semibold text-[var(--vc-text)]">{{ $issue['title'] }}: {{ $issue['entity_label'] }}</p>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ $issue['message'] }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-[var(--vc-border)] px-4 py-6 text-sm text-[var(--vc-text-muted)]">
                        Критичных audit issues сейчас не найдено.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
