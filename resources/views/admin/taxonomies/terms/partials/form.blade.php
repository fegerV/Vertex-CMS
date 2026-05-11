@php
    $parentOptions = $taxonomy->terms
        ->filter(fn ($existingTerm) => ! $term->exists || $existingTerm->id !== $term->id)
        ->values();
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Name</span>
        <input type="text" name="name" value="{{ old('name', $term->name) }}" required class="vc-input">
        @error('name')
            <span class="mt-2 block text-sm text-rose-500">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Slug</span>
        <input type="text" name="slug" value="{{ old('slug', $term->slug) }}" required class="vc-input">
        @error('slug')
            <span class="mt-2 block text-sm text-rose-500">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Parent term</span>
        <select name="parent_id" class="vc-select" @disabled(! $taxonomy->hierarchical)>
            <option value="">No parent</option>
            @foreach ($parentOptions as $parentTerm)
                <option value="{{ $parentTerm->id }}" @selected((string) old('parent_id', $term->parent_id) === (string) $parentTerm->id)>
                    {{ $parentTerm->name }}
                </option>
            @endforeach
        </select>
        @if (! $taxonomy->hierarchical)
            <span class="mt-2 block text-xs text-[var(--vc-text-muted)]">Enable hierarchical mode on the taxonomy to use parent terms.</span>
        @endif
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Sort order</span>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $term->sort_order ?? 0) }}" class="vc-input">
    </label>

    <label class="block sm:col-span-2">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Description</span>
        <textarea name="description" rows="4" class="vc-textarea">{{ old('description', $term->description) }}</textarea>
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">SEO title</span>
        <input type="text" name="seo_title" value="{{ old('seo_title', $term->seo_json['title'] ?? '') }}" class="vc-input">
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">SEO description</span>
        <textarea name="seo_description" rows="4" class="vc-textarea">{{ old('seo_description', $term->seo_json['description'] ?? '') }}</textarea>
    </label>
</div>

<div class="flex justify-end">
    <button class="vc-button vc-button-primary px-4 py-3">
        Save term
    </button>
</div>
