<div class="grid gap-5 sm:grid-cols-2">
    <label class="block sm:col-span-2">
        <span class="mb-1 block text-sm font-medium">Название</span>
        <input
            type="text"
            name="title"
            value="{{ old('title', $page->title) }}"
            required
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('title')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Slug</span>
        <input
            type="text"
            name="slug"
            value="{{ old('slug', $page->slug) }}"
            placeholder="auto-from-title"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('slug')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Родительская страница</span>
        <select name="parent_id" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
            <option value="">Без родителя</option>
            @foreach ($parentPages as $parentPage)
                <option value="{{ $parentPage->id }}" @selected((string) old('parent_id', $page->parent_id) === (string) $parentPage->id)>
                    {{ $parentPage->title }} ({{ $parentPage->uri }})
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Статус</span>
        <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $page->status) === $status)>
                    {{ $status }}
                </option>
            @endforeach
        </select>
        @error('status')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Шаблон</span>
        <input
            type="text"
            name="template"
            value="{{ old('template', $page->template ?: 'default') }}"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('template')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>
</div>

<label class="block">
    <span class="mb-1 block text-sm font-medium">Content JSON</span>
    <textarea
        name="content_json"
        rows="12"
        class="w-full rounded-md border border-slate-300 px-3 py-2 font-mono text-sm outline-none focus:border-slate-900"
    >{{ old('content_json', json_encode($page->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
    @error('content_json')
        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
    @enderror
</label>

@php
    $seo = $page->seoMeta;
@endphp

<section class="space-y-5 border-t border-slate-100 pt-5">
    <div>
        <h2 class="text-lg font-semibold">SEO</h2>
        <p class="mt-1 text-sm text-slate-500">Meta-данные страницы для поисковиков и соцсетей.</p>
    </div>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">SEO title</span>
        <input
            type="text"
            name="seo_title"
            value="{{ old('seo_title', $seo?->title) }}"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('seo_title')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">SEO description</span>
        <textarea
            name="seo_description"
            rows="3"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >{{ old('seo_description', $seo?->description) }}</textarea>
        @error('seo_description')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <div class="grid gap-5 sm:grid-cols-2">
        <label class="block">
            <span class="mb-1 block text-sm font-medium">Canonical URL</span>
            <input
                type="url"
                name="seo_canonical_url"
                value="{{ old('seo_canonical_url', $seo?->canonical_url) }}"
                class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
            >
            @error('seo_canonical_url')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium">Robots</span>
            <select name="seo_robots" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
                @foreach ($robotsOptions as $robots)
                    <option value="{{ $robots }}" @selected(old('seo_robots', $seo?->robots ?? 'index, follow') === $robots)>
                        {{ $robots }}
                    </option>
                @endforeach
            </select>
            @error('seo_robots')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <label class="block">
            <span class="mb-1 block text-sm font-medium">OG title</span>
            <input
                type="text"
                name="seo_og_title"
                value="{{ old('seo_og_title', $seo?->og_title) }}"
                class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
            >
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium">OG image media ID</span>
            <input
                type="number"
                name="seo_og_image"
                value="{{ old('seo_og_image', $seo?->og_image) }}"
                class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
            >
        </label>
    </div>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">OG description</span>
        <textarea
            name="seo_og_description"
            rows="3"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >{{ old('seo_og_description', $seo?->og_description) }}</textarea>
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Schema JSON</span>
        <textarea
            name="seo_schema_json"
            rows="6"
            class="w-full rounded-md border border-slate-300 px-3 py-2 font-mono text-sm outline-none focus:border-slate-900"
        >{{ old('seo_schema_json', $seo?->schema_json ? json_encode($seo->schema_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        @error('seo_schema_json')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="flex items-center gap-2 text-sm">
        <input
            type="checkbox"
            name="seo_include_in_sitemap"
            value="1"
            @checked(old('seo_include_in_sitemap', $seo?->include_in_sitemap ?? true))
            class="rounded border-slate-300"
        >
        <span>Включить в sitemap.xml</span>
    </label>
</section>

<div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5">
    @if ($page->exists)
        <p class="text-sm text-slate-500">URI: {{ $page->uri }}</p>
    @else
        <p class="text-sm text-slate-500">URI будет создан автоматически.</p>
    @endif

    <button type="submit" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
        Сохранить
    </button>
</div>
