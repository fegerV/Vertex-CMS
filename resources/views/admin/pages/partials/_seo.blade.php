@php
    $seo = $page->seoMeta;
@endphp

<section class="vc-form-surface vc-form-section">
    <div>
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">SEO</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Метаданные страницы для поисковых систем, сниппетов, Open Graph и sitemap.</p>
    </div>

    <label class="vc-field">
        <span class="vc-field-label">SEO title</span>
        <span class="vc-field-help">Отдельный title для сниппета. Если поле пустое, используется заголовок страницы.</span>
        <input
            type="text"
            name="seo_title"
            value="{{ old('seo_title', $seo?->title) }}"
            class="vc-input"
        >
        @error('seo_title')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <label class="vc-field">
        <span class="vc-field-label">SEO description</span>
        <span class="vc-field-help">Краткое описание для поисковой выдачи и социальных превью.</span>
        <textarea
            name="seo_description"
            rows="3"
            class="vc-textarea"
        >{{ old('seo_description', $seo?->description) }}</textarea>
        @error('seo_description')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <div class="vc-form-grid vc-form-grid-2">
        <label class="vc-field">
            <span class="vc-field-label">Canonical URL</span>
            <input
                type="url"
                name="seo_canonical_url"
                value="{{ old('seo_canonical_url', $seo?->canonical_url) }}"
                class="vc-input"
            >
            @error('seo_canonical_url')
                <span class="vc-field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Robots</span>
            <select name="seo_robots" class="vc-select">
                @foreach ($robotsOptions as $robots)
                    <option value="{{ $robots }}" @selected(old('seo_robots', $seo?->robots ?? 'index, follow') === $robots)>{{ $robots }}</option>
                @endforeach
            </select>
            @error('seo_robots')
                <span class="vc-field-error">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="vc-form-grid vc-form-grid-2">
        <label class="vc-field">
            <span class="vc-field-label">OG title</span>
            <input
                type="text"
                name="seo_og_title"
                value="{{ old('seo_og_title', $seo?->og_title) }}"
                class="vc-input"
            >
        </label>

        <label class="vc-field">
            <span class="vc-field-label">OG image media ID</span>
            <input
                type="number"
                name="seo_og_image"
                value="{{ old('seo_og_image', $seo?->og_image) }}"
                class="vc-input"
            >
        </label>
    </div>

    <label class="vc-field">
        <span class="vc-field-label">OG description</span>
        <textarea
            name="seo_og_description"
            rows="3"
            class="vc-textarea"
        >{{ old('seo_og_description', $seo?->og_description) }}</textarea>
    </label>

    <label class="vc-field">
        <span class="vc-field-label">Schema JSON</span>
        <span class="vc-field-help">Оставьте пустым, если странице не нужна ручная schema.org разметка.</span>
        <textarea
            name="seo_schema_json"
            rows="6"
            class="vc-textarea font-mono text-sm"
        >{{ old('seo_schema_json', $seo?->schema_json ? json_encode($seo->schema_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        @error('seo_schema_json')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <label class="vc-checkbox-row text-sm text-[var(--vc-text)]">
        <input
            type="checkbox"
            name="seo_include_in_sitemap"
            value="1"
            @checked(old('seo_include_in_sitemap', $seo?->include_in_sitemap ?? true))
            class="rounded border-slate-300"
        >
        <span>Включать страницу в sitemap.xml</span>
    </label>
</section>
