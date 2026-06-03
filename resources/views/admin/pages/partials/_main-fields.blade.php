<section class="vc-form-surface vc-form-section">
    <div>
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Основной контент</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Здесь задаются заголовок страницы, slug и permalink. Публикация и атрибуты вынесены в правую колонку, как в WordPress.</p>
    </div>

    <label class="vc-field">
        <span class="vc-field-label">Заголовок страницы</span>
        <span class="vc-field-help">Основное название страницы в админке и базовый источник для SEO fallback и генерации slug.</span>
        <input
            type="text"
            name="title"
            value="{{ old('title', $page->title) }}"
            required
            class="vc-input"
            placeholder="Например: Оживляющие портреты"
        >
        @error('title')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <div class="vc-form-grid vc-form-grid-2">
        <label class="vc-field">
            <span class="vc-field-label">Slug</span>
            <span class="vc-field-help">Можно оставить пустым, тогда slug будет создан автоматически из заголовка.</span>
            <input
                type="text"
                name="slug"
                value="{{ old('slug', $page->slug) }}"
                class="vc-input"
                placeholder="avto-iz-zagolovka"
            >
            @error('slug')
                <span class="vc-field-error">{{ $message }}</span>
            @enderror
        </label>

        <div class="vc-field">
            <span class="vc-field-label">Permalink</span>
            <span class="vc-field-help">Публичный адрес страницы после сохранения.</span>
            <div class="vc-input flex items-center">
                {{ $page->uri ?: 'Появится после первого сохранения' }}
            </div>
        </div>
    </div>
</section>
