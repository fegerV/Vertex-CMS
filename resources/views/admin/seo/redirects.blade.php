@extends('admin.layouts.app')

@section('title', 'Redirects - VertexCMS')
@section('page_title', 'SEO Redirects')
@section('page_subtitle', 'Управление правилами перенаправления поверх активного runtime-слоя')

@section('content')
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-3">
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Всего правил</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ $stats['total'] }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Все записи в redirect runtime, включая отключённые.</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Активные</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ $stats['enabled'] }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Именно эти правила участвуют в публичном разрешении URL.</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Максимум hits</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ number_format($stats['top_hits']) }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Помогает быстро увидеть, какие redirects реально используются.</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <article class="vc-panel p-6">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Новое правило</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Поддерживаются относительные пути и абсолютные URL. Новое правило сразу попадает в публичный runtime.</p>
                </div>

                <form method="POST" action="{{ route('admin.redirects.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="vc-label">Откуда</label>
                        <input type="text" name="from_url" value="{{ old('from_url') }}" class="vc-input" placeholder="/old-page">
                        @error('from_url')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="vc-label">Куда</label>
                        <input type="text" name="to_url" value="{{ old('to_url') }}" class="vc-input" placeholder="/new-page">
                        @error('to_url')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="vc-label">Код</label>
                            <select name="status_code" class="vc-select">
                                @foreach ($defaultStatusCodes as $code)
                                    <option value="{{ $code }}" @selected((int) old('status_code', 301) === $code)>{{ $code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="flex items-center gap-3 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-3 text-sm text-[var(--vc-text)]">
                            <input type="checkbox" name="enabled" value="1" @checked(old('enabled', true))>
                            <span>Сразу включить правило</span>
                        </label>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button class="vc-button vc-button-primary px-4 py-3">Создать redirect</button>
                        <a href="{{ route('admin.seo.dashboard') }}" class="vc-button vc-button-secondary px-4 py-3">Назад к SEO</a>
                    </div>
                </form>
            </article>

            <article class="vc-panel p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Поиск и фильтры</h2>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Ищите по source или destination URL и быстро сужайте список до нужных правил.</p>
                    </div>
                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                        Runtime active
                    </span>
                </div>

                <form method="GET" action="{{ route('admin.redirects.index') }}" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-[1.5fr_180px_180px_auto]">
                    <div>
                        <label class="vc-label">Поиск</label>
                        <input type="text" name="q" value="{{ request('q') }}" class="vc-input" placeholder="/old-page или /catalog">
                    </div>
                    <div>
                        <label class="vc-label">Код</label>
                        <select name="status_code" class="vc-select">
                            <option value="">Все</option>
                            @foreach ($defaultStatusCodes as $code)
                                <option value="{{ $code }}" @selected((string) request('status_code') === (string) $code)>{{ $code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="vc-label">Статус</label>
                        <select name="enabled" class="vc-select">
                            <option value="">Все</option>
                            <option value="1" @selected(request('enabled') === '1')>Только активные</option>
                            <option value="0" @selected(request('enabled') === '0')>Только отключённые</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-3">
                        <button class="vc-button vc-button-secondary px-4 py-3">Применить</button>
                        <a href="{{ route('admin.redirects.index') }}" class="vc-button border border-[var(--vc-border)] bg-white px-4 py-3 text-[var(--vc-text)] hover:bg-[var(--vc-surface-muted)]">Сбросить</a>
                    </div>
                </form>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Когда использовать 301</p>
                        <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Для постоянного переноса страницы, когда старый адрес уже не должен участвовать в индексации.</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Когда использовать 302 / 307</p>
                        <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Для временного сценария, когда старый URL ещё актуален и может вернуться в работу.</p>
                    </div>
                </div>
            </article>
        </section>

        <section class="vc-panel p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Текущие правила</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Каждое правило можно обновить inline. Изменения сразу повлияют на публичное разрешение redirects.</p>
                </div>
                <span class="inline-flex rounded-full border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-3 py-1 text-xs font-semibold text-[var(--vc-text-muted)]">
                    {{ $redirects->total() }} найдено
                </span>
            </div>

            <div class="mt-5 space-y-4">
                @forelse ($redirects as $redirect)
                    <form method="POST" action="{{ route('admin.redirects.update', $redirect) }}" class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        @csrf
                        @method('PUT')
                        <div class="grid gap-4 xl:grid-cols-[1.2fr_1.2fr_120px_140px_120px_auto] xl:items-end">
                            <div>
                                <label class="vc-label">Откуда</label>
                                <input type="text" name="from_url" value="{{ old('from_url', $redirect->from_url) }}" class="vc-input">
                            </div>
                            <div>
                                <label class="vc-label">Куда</label>
                                <input type="text" name="to_url" value="{{ old('to_url', $redirect->to_url) }}" class="vc-input">
                            </div>
                            <div>
                                <label class="vc-label">Код</label>
                                <select name="status_code" class="vc-select">
                                    @foreach ($defaultStatusCodes as $code)
                                        <option value="{{ $code }}" @selected((int) $redirect->status_code === $code)>{{ $code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="vc-label">Hits</label>
                                <div class="vc-input flex items-center">{{ number_format((int) $redirect->hits) }}</div>
                            </div>
                            <label class="flex items-center gap-3 rounded-2xl border border-[var(--vc-border)] bg-white px-4 py-3 text-sm text-[var(--vc-text)]">
                                <input type="checkbox" name="enabled" value="1" @checked($redirect->enabled)>
                                <span>Активно</span>
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <button class="vc-button vc-button-secondary px-4 py-3">Сохранить</button>
                                <button form="delete-redirect-{{ $redirect->id }}" class="vc-button border border-rose-200 bg-white px-4 py-3 text-rose-700 hover:bg-rose-50">Удалить</button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-redirect-{{ $redirect->id }}" method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @empty
                    <div class="rounded-2xl border border-dashed border-[var(--vc-border)] px-4 py-8 text-sm text-[var(--vc-text-muted)]">
                        Redirect rules пока не созданы.
                    </div>
                @endforelse
            </div>

            <div class="mt-5">
                @if ($redirects->hasPages())
                    {{ $redirects->links() }}
                @endif
            </div>
        </section>
    </div>
@endsection
