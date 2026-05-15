@php
    $publicUrl = $page->getPublicUrl();
    $isNew = ! $page->exists;
@endphp

<aside class="space-y-4 xl:sticky xl:top-24">
    <section class="vc-form-surface vc-form-section">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-[var(--vc-text)]">Публикация</h2>
            <span class="vc-badge">{{ $isNew ? 'Draft' : ucfirst($page->status ?: 'draft') }}</span>
        </div>

        <div class="vc-field">
            <span class="vc-field-label">Статус</span>
            <select name="status" class="vc-select">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $page->status) === $status)>{{ $status }}</option>
                @endforeach
            </select>
            @error('status')
                <span class="vc-field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="space-y-2 text-sm">
            <div class="flex items-center justify-between gap-3">
                <span class="text-[var(--vc-text-soft)]">Режим</span>
                <span class="font-medium text-[var(--vc-text)]">{{ $isNew ? 'Создание' : 'Обновление' }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-[var(--vc-text-soft)]">Builder</span>
                <span class="font-medium text-[var(--vc-text)]">{{ $page->canAccessBuilder() ? 'Доступен' : 'После первого сохранения' }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-[var(--vc-text-soft)]">UX Preview</span>
                <span class="font-medium text-[var(--vc-text)]">{{ $page->canAccessBuilder() ? 'Доступен' : 'После первого сохранения' }}</span>
            </div>
        </div>

        <div class="space-y-2">
            <button type="submit" class="vc-button vc-button-primary w-full justify-center">
                {{ $isNew ? 'Сохранить черновик' : 'Обновить страницу' }}
            </button>

            @if ($page->canAccessBuilder())
                <a href="{{ route('admin.pages.preview', $page) }}" target="_blank" rel="noopener" class="vc-button vc-button-secondary w-full justify-center">
                    UX Preview
                </a>
                <a href="{{ route('admin.pages.builder', $page) }}" class="vc-button vc-button-secondary w-full justify-center">
                    Открыть Builder
                </a>
            @else
                <button type="button" disabled class="vc-button vc-button-secondary w-full justify-center opacity-60">
                    UX Preview после сохранения
                </button>
                <button type="button" disabled class="vc-button vc-button-secondary w-full justify-center opacity-60">
                    Builder после сохранения
                </button>
            @endif

            @if ($publicUrl)
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="vc-button vc-button-secondary w-full justify-center">
                    Открыть публичную страницу
                </a>
            @endif

            @if ($page->exists)
                <button form="page-delete-form" type="submit" class="vc-button vc-button-danger w-full justify-center">
                    Удалить страницу
                </button>
            @endif
        </div>
    </section>

    <section class="vc-form-surface vc-form-section">
        <div>
            <h2 class="text-base font-semibold text-[var(--vc-text)]">Атрибуты страницы</h2>
            <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Иерархия и шаблон вынесены в правую колонку, как в классическом редакторе WordPress.</p>
        </div>

        <label class="vc-field">
            <span class="vc-field-label">Родительская страница</span>
            <select name="parent_id" class="vc-select">
                <option value="">Без родителя</option>
                @foreach ($parentPages as $parentPage)
                    <option value="{{ $parentPage->id }}" @selected((string) old('parent_id', $page->parent_id) === (string) $parentPage->id)>
                        {{ $parentPage->title }} ({{ $parentPage->uri }})
                    </option>
                @endforeach
            </select>
            @error('parent_id')
                <span class="vc-field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Шаблон</span>
            <input
                type="text"
                name="template"
                value="{{ old('template', $page->template ?: 'default') }}"
                class="vc-input"
            >
            @error('template')
                <span class="vc-field-error">{{ $message }}</span>
            @enderror
        </label>
    </section>

    <section class="vc-form-surface vc-form-section">
        <div>
            <h2 class="text-base font-semibold text-[var(--vc-text)]">Подсказка</h2>
            <p class="text-sm text-[var(--vc-text-muted)]">Редактор страницы отвечает за title, slug, SEO, taxonomy и custom fields, а визуальная сборка контента идёт в отдельном Builder-экране.</p>
        </div>
    </section>

    @if (auth()->user()?->hasPermission('ai.use'))
        @include('admin.pages.partials.ai-assistant')
    @endif
</aside>
