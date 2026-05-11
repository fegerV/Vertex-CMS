<div class="grid gap-5 sm:grid-cols-2">
    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Name</span>
        <input type="text" name="name" value="{{ old('name', $taxonomy->name) }}" required class="vc-input">
        @error('name')
            <span class="mt-2 block text-sm text-rose-500">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Slug</span>
        <input type="text" name="slug" value="{{ old('slug', $taxonomy->slug) }}" required class="vc-input">
        @error('slug')
            <span class="mt-2 block text-sm text-rose-500">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Entity type</span>
        <select name="entity_type" class="vc-select">
            <option value="page" @selected(old('entity_type', $taxonomy->entity_type ?: 'page') === 'page')>Page</option>
        </select>
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Archive title</span>
        <input type="text" name="archive_title" value="{{ old('archive_title', $taxonomy->settings_json['archive_title'] ?? '') }}" class="vc-input">
    </label>

    <label class="block sm:col-span-2">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Archive description</span>
        <textarea name="archive_description" rows="4" class="vc-textarea">{{ old('archive_description', $taxonomy->settings_json['archive_description'] ?? '') }}</textarea>
    </label>

    <label class="vc-checkbox-row sm:col-span-2">
        <input type="checkbox" name="hierarchical" value="1" @checked(old('hierarchical', $taxonomy->hierarchical)) class="rounded border-slate-300">
        <span class="text-sm text-[var(--vc-text-muted)]">Enable parent/child term structure</span>
    </label>
</div>

<div class="flex justify-end">
    <button class="vc-button vc-button-primary px-4 py-3">
        Save taxonomy
    </button>
</div>
