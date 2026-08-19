@php
    $parentOptions = $taxonomy->terms
        ->filter(fn ($existingTerm) => ! $term->exists || $existingTerm->id !== $term->id)
        ->values();
@endphp

<section class="vc-panel vc-panel-muted p-5 vc-form-section">
    <div>
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Основные свойства</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Название и slug определяют публичный URL термина и его отображение в фильтрах и архивах.</p>
    </div>

    <div class="vc-form-grid vc-form-grid-2">
        <label class="vc-field">
            <span class="vc-field-label">Название</span>
            <input type="text" name="name" value="{{ old('name', $term->name) }}" required class="vc-input">
            @error('name') <span class="vc-field-error">{{ $message }}</span> @enderror
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Slug</span>
            <input type="text" name="slug" value="{{ old('slug', $term->slug) }}" required class="vc-input">
            @error('slug') <span class="vc-field-error">{{ $message }}</span> @enderror
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Родительский термин</span>
            <select name="parent_id" class="vc-select" @disabled(! $taxonomy->hierarchical)>
                <option value="">Без родителя</option>
                @foreach ($parentOptions as $parentTerm)
                    <option value="{{ $parentTerm->id }}" @selected((string) old('parent_id', $term->parent_id) === (string) $parentTerm->id)>
                        {{ $parentTerm->name }}
                    </option>
                @endforeach
            </select>
            @if (! $taxonomy->hierarchical)
                <span class="vc-field-help">Включите иерархический режим на уровне таксономии, чтобы использовать родительские термины.</span>
            @endif
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Порядок сортировки</span>
            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $term->sort_order ?? 0) }}" class="vc-input">
        </label>

        <label class="vc-field md:col-span-2">
            <span class="vc-field-label">Описание</span>
            <textarea name="description" rows="4" class="vc-textarea">{{ old('description', $term->description) }}</textarea>
        </label>
    </div>
</section>

<section class="vc-panel vc-panel-muted p-5 vc-form-section">
    <div>
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">SEO архива</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Эти поля управляют title, description, canonical, robots и включением архива термина в sitemap.</p>
    </div>

    <div class="vc-form-grid vc-form-grid-2">
        <label class="vc-field">
            <span class="vc-field-label">SEO title</span>
            <input type="text" name="seo_title" value="{{ old('seo_title', $term->seo_json['title'] ?? '') }}" class="vc-input">
        </label>

        <label class="vc-field">
            <span class="vc-field-label">SEO description</span>
            <textarea name="seo_description" rows="4" class="vc-textarea">{{ old('seo_description', $term->seo_json['description'] ?? '') }}</textarea>
        </label>

        <label class="vc-field md:col-span-2">
            <span class="vc-field-label">Canonical URL</span>
            <input type="url" name="seo_canonical_url" value="{{ old('seo_canonical_url', $term->seo_json['canonical_url'] ?? '') }}" class="vc-input">
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Robots</span>
            <select name="seo_robots" class="vc-select">
                @foreach (\App\Seo\Services\SeoMetaService::ROBOTS as $robots)
                    <option value="{{ $robots }}" @selected(old('seo_robots', $term->seo_json['robots'] ?? 'index, follow') === $robots)>
                        {{ $robots }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="vc-checkbox-row md:col-span-2">
            <input
                type="checkbox"
                name="seo_include_in_sitemap"
                value="1"
                @checked(old('seo_include_in_sitemap', $term->seo_json['include_in_sitemap'] ?? true))
                class="rounded border-slate-300"
            >
            <span class="text-sm text-[var(--vc-text)]">Включать архив термина в sitemap.xml</span>
        </label>
    </div>
</section>

<div class="flex justify-end">
    <button class="vc-button vc-button-primary" type="submit">
        Сохранить термин
    </button>
</div>
