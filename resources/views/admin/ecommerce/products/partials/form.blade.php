<div class="grid gap-5 sm:grid-cols-2">
    <label class="block sm:col-span-2">
        <span class="mb-1 block text-sm font-medium">Product Name</span>
        <input
            type="text"
            name="name"
            value="{{ old('name', $product->name ?? '') }}"
            required
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('name')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Slug</span>
        <input
            type="text"
            name="slug"
            value="{{ old('slug', $product->slug ?? '') }}"
            placeholder="auto-from-title"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('slug')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">SKU</span>
        <input
            type="text"
            name="sku"
            value="{{ old('sku', $product->sku ?? '') }}"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('sku')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Status</span>
        <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
            <option value="draft" @selected(old('status', $product->status ?? 'draft') === 'draft')">Draft</option>
            <option value="active" @selected(old('status', $product->status ?? '') === 'active')">Active</option>
            <option value="archived" @selected(old('status', $product->status ?? '') === 'archived')">Archived</option>
        </select>
        @error('status')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Price ($)</span>
        <input
            type="number"
            name="price"
            value="{{ old('price', $product->price ?? '') }}"
            step="0.01"
            min="0"
            required
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('price')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Compare Price ($)</span>
        <input
            type="number"
            name="compare_price"
            value="{{ old('compare_price', $product->compare_price ?? '') }}"
            step="0.01"
            min="0"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('compare_price')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Cost ($)</span>
        <input
            type="number"
            name="cost"
            value="{{ old('cost', $product->cost ?? '') }}"
            step="0.01"
            min="0"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('cost')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Quantity</span>
        <input
            type="number"
            name="quantity"
            value="{{ old('quantity', $product->quantity ?? 0) }}"
            min="0"
            required
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('quantity')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="flex items-center gap-2 sm:col-span-2">
        <input
            type="checkbox"
            name="track_inventory"
            value="1"
            @checked(old('track_inventory', $product->track_inventory ?? true))
            class="rounded border-slate-300"
        >
        <span>Track Inventory</span>
    </label>
</div>

<label class="block">
    <span class="mb-1 block text-sm font-medium">Description</span>
    <textarea
        name="description"
        rows="6"
        class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
    >{{ old('description', $product->description ?? '') }}</textarea>
    @error('description')
        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
    @enderror
</label>

<section class="space-y-5 border-t border-slate-100 pt-5">
    <div>
        <h2 class="text-lg font-semibold">SEO Settings</h2>
        <p class="mt-1 text-sm text-slate-500">Search engine optimization settings for this product.</p>
    </div>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Meta Title</span>
        <input
            type="text"
            name="meta_title"
            value="{{ old('meta_title', $product->meta_title ?? '') }}"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('meta_title')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Meta Description</span>
        <textarea
            name="meta_description"
            rows="3"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
        @error('meta_description')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Meta Keywords</span>
        <input
            type="text"
            name="meta_keywords"
            value="{{ old('meta_keywords', $product->meta_keywords ?? '') }}"
            placeholder="keyword1, keyword2, keyword3"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('meta_keywords')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>
</section>

<div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5">
    <p class="text-sm text-slate-500">
        @if(isset($product) && $product->exists)
            Product ID: {{ $product->id }}
        @else
            New product will be created.
        @endif
    </p>

    <button type="submit" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
        {{ isset($product) && $product->exists ? 'Update Product' : 'Create Product' }}
    </button>
</div>
