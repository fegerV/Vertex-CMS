<section class="vc-form-surface vc-form-section">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-[var(--vc-text)]">Page Builder</h2>
            <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Визуальная сборка страницы вынесена в отдельный экран. Здесь остаётся только совместимый JSON payload и быстрый переход в Builder.</p>
        </div>

        @if ($page->canAccessBuilder())
            <a href="{{ route('admin.pages.builder', $page) }}" class="vc-button vc-button-secondary px-4 py-3">
                Открыть Builder
            </a>
        @else
            <button type="button" disabled class="vc-button vc-button-secondary px-4 py-3 opacity-60">
                Builder после сохранения
            </button>
        @endif
    </div>

    <details class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface)] p-4">
        <summary class="cursor-pointer text-sm font-semibold text-[var(--vc-text)]">Показать JSON контента</summary>

        <label class="vc-field mt-4">
            <span class="vc-field-label">Payload страницы</span>
            <textarea
                id="content_json_input"
                name="content_json"
                rows="12"
                class="vc-textarea font-mono text-sm"
            >{{ old('content_json', json_encode($page->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
            @error('content_json')
                <span class="vc-field-error">{{ $message }}</span>
            @enderror
        </label>
    </details>
</section>
