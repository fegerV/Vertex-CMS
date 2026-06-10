@extends('admin.layouts.app')

@section('title', $siteName.' - '.__('admin.dashboard'))
@section('page_title', $siteName)
@section('page_subtitle', __('admin.summary'))

@section('content')
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="vc-panel vc-kpi p-5">
            <p class="vc-kpi-label">{{ __('admin.pages') }}</p>
            <strong class="vc-kpi-value mt-4 block">{{ $stats['pages'] }}</strong>
            <p class="vc-kpi-meta mt-3">{{ __('admin.pages_meta') ?? 'Все контентные единицы в системе' }}</p>
        </article>
        <article class="vc-panel vc-kpi p-5">
            <p class="vc-kpi-label">{{ __('admin.published') }}</p>
            <strong class="vc-kpi-value mt-4 block">{{ $stats['published_pages'] }}</strong>
            <p class="vc-kpi-meta mt-3">{{ __('admin.published_meta') ?? 'Страницы доступны посетителям' }}</p>
        </article>
        <article class="vc-panel vc-kpi p-5">
            <p class="vc-kpi-label">{{ __('admin.drafts') }}</p>
            <strong class="vc-kpi-value mt-4 block">{{ $stats['draft_pages'] }}</strong>
            <p class="vc-kpi-meta mt-3">{{ __('admin.drafts_meta') ?? 'Материалы в работе у редакторов' }}</p>
        </article>
        <article class="vc-panel vc-kpi p-5">
            <p class="vc-kpi-label">{{ __('admin.media') }}</p>
            <strong class="vc-kpi-value mt-4 block">{{ $stats['media_files'] }}</strong>
            <p class="vc-kpi-meta mt-3">{{ __('admin.media_meta') ?? 'Файлы, изображения и ресурсы' }}</p>
        </article>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-[1.45fr_0.9fr]">
        <div class="vc-panel vc-panel-strong p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-[var(--vc-text)]">{{ __('admin.recent_activity') }}</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ __('admin.recent_activity_desc') ?? 'Живой срез действий в админке и системе.' }}</p>
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
                    <p class="text-sm text-[var(--vc-text-muted)]">{{ __('admin.no_activity') }}</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <section class="vc-panel p-6">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">{{ __('admin.focus_today') ?? 'Фокус на сегодня' }}</h2>
                <div class="mt-4 space-y-3">
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-4">
                        <p class="text-sm font-semibold text-[var(--vc-text)]">{{ __('admin.check_drafts') ?? 'Проверить черновики' }}</p>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ __('admin.drafts_count', ['count' => $stats['draft_pages']]) ?? "Сейчас в работе {$stats['draft_pages']} черновиков." }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-4">
                        <p class="text-sm font-semibold text-[var(--vc-text)]">{{ __('admin.update_media') ?? 'Обновить библиотеку медиа' }}</p>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ __('admin.media_count', ['count' => $stats['media_files']]) ?? "В системе уже {$stats['media_files']} медиа-элементов." }}</p>
                    </div>
                </div>
            </section>

            <section class="vc-panel p-6">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">{{ __('admin.quick_links') ?? 'Быстрые ориентиры' }}</h2>
                <dl class="mt-4 space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-[var(--vc-text-muted)]">{{ __('settings.fields.site_name') }}</dt>
                        <dd class="text-sm font-semibold text-[var(--vc-text)]">{{ $siteName }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-[var(--vc-text-muted)]">{{ __('admin.content_status') ?? 'Статус контента' }}</dt>
                        <dd class="text-sm font-semibold text-[var(--vc-text)]">{{ $stats['published_pages'] }} live / {{ $stats['draft_pages'] }} draft</dd>
                    </div>
                </dl>
            </section>
        </div>
    </section>
@endsection
