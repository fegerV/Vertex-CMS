<section class="vc-panel vc-panel-muted p-5 vc-form-section">
    <div>
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Основные свойства</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Название и slug определяют публичный архив и отображение таксономии в админке.</p>
    </div>

    <div class="vc-form-grid vc-form-grid-2">
        <label class="vc-field">
            <span class="vc-field-label">Название</span>
            <input type="text" name="name" value="{{ old('name', $taxonomy->name) }}" required class="vc-input">
            @error('name') <span class="vc-field-error">{{ $message }}</span> @enderror
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Slug</span>
            <span class="vc-field-help">Используется в URL архива и в API.</span>
            <input type="text" name="slug" value="{{ old('slug', $taxonomy->slug) }}" required class="vc-input">
            @error('slug') <span class="vc-field-error">{{ $message }}</span> @enderror
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Тип сущности</span>
            <select name="entity_type" class="vc-select">
                <option value="page" @selected(old('entity_type', $taxonomy->entity_type ?: 'page') === 'page')>Страница</option>
            </select>
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Заголовок архива</span>
            <input type="text" name="archive_title" value="{{ old('archive_title', $taxonomy->settings_json['archive_title'] ?? '') }}" class="vc-input">
        </label>

        <label class="vc-field md:col-span-2">
            <span class="vc-field-label">Описание архива</span>
            <textarea name="archive_description" rows="4" class="vc-textarea">{{ old('archive_description', $taxonomy->settings_json['archive_description'] ?? '') }}</textarea>
        </label>

        <label class="vc-checkbox-row md:col-span-2">
            <input type="checkbox" name="hierarchical" value="1" @checked(old('hierarchical', $taxonomy->hierarchical)) class="rounded border-slate-300">
            <span class="text-sm text-[var(--vc-text)]">Включить структуру родитель/дочерний термин</span>
        </label>
    </div>
</section>

<div class="flex justify-end">
    <button class="vc-button vc-button-primary" type="submit">
        Сохранить таксономию
    </button>
</div>
