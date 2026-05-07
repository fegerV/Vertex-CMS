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
