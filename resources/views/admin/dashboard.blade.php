@extends('admin.layouts.app')

@section('title', $siteName.' - Dashboard')
@section('page_title', $siteName)
@section('page_subtitle', 'Сводка по сайту и последние действия')

@section('content')
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="vc-panel vc-kpi p-5">
            <p class="vc-kpi-label">Страницы</p>
            <strong class="vc-kpi-value mt-4 block">{{ $stats['pages'] }}</strong>
            <p class="vc-kpi-meta mt-3">Все контентные единицы в системе</p>
        </article>
        <article class="vc-panel vc-kpi p-5">
            <p class="vc-kpi-label">Опубликовано</p>
            <strong class="vc-kpi-value mt-4 block">{{ $stats['published_pages'] }}</strong>
            <p class="vc-kpi-meta mt-3">Страницы доступны посетителям</p>
        </article>
        <article class="vc-panel vc-kpi p-5">
            <p class="vc-kpi-label">Черновики</p>
            <strong class="vc-kpi-value mt-4 block">{{ $stats['draft_pages'] }}</strong>
            <p class="vc-kpi-meta mt-3">Материалы в работе у редакторов</p>
        </article>
        <article class="vc-panel vc-kpi p-5">
            <p class="vc-kpi-label">Медиа</p>
            <strong class="vc-kpi-value mt-4 block">{{ $stats['media_files'] }}</strong>
            <p class="vc-kpi-meta mt-3">Файлы, изображения и ресурсы</p>
        </article>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-[1.45fr_0.9fr]">
        <div class="vc-panel vc-panel-strong p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-[var(--vc-text)]">Последние действия</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Живой срез действий в админке и системе.</p>
                </div>
                <span class="vc-badge">Live audit</span>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($recentActivity as $activity)
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-[var(--vc-text)]">{{ $activity->action }}</p>
                            <span class="text-xs font-medium text-[var(--vc-text-soft)]">{{ $activity->created_at?->format('d.m H:i') }}</span>
                        </div>
                        <p class="mt-2 text-sm text-[var(--vc-text-muted)]">{{ $activity->description }}</p>
                    </div>
                @empty
                    <p class="text-sm text-[var(--vc-text-muted)]">Действий пока нет.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <section class="vc-panel p-6">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Фокус на сегодня</h2>
                <div class="mt-4 space-y-3">
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-4">
                        <p class="text-sm font-semibold text-[var(--vc-text)]">Проверить черновики</p>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Сейчас в работе {{ $stats['draft_pages'] }} черновиков.</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-4">
                        <p class="text-sm font-semibold text-[var(--vc-text)]">Обновить библиотеку медиа</p>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">В системе уже {{ $stats['media_files'] }} медиа-элементов.</p>
                    </div>
                </div>
            </section>

            <section class="vc-panel p-6">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Быстрые ориентиры</h2>
                <dl class="mt-4 space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-[var(--vc-text-muted)]">Имя сайта</dt>
                        <dd class="text-sm font-semibold text-[var(--vc-text)]">{{ $siteName }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-[var(--vc-text-muted)]">Статус контента</dt>
                        <dd class="text-sm font-semibold text-[var(--vc-text)]">{{ $stats['published_pages'] }} live / {{ $stats['draft_pages'] }} draft</dd>
                    </div>
                </dl>
            </section>
        </div>
    </section>
@endsection
