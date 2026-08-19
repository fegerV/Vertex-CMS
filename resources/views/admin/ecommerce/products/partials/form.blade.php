<div class="grid gap-6 md:grid-cols-2">
    <!-- Basic Information -->
    <div class="md:col-span-2">
        <h3 class="mb-4 text-sm font-medium text-slate-700">Basic Information</h3>
    </div>

    <label class="block md:col-span-2">
        <span class="mb-1 block text-sm font-medium text-slate-700">Product Name <span class="text-red-500">*</span></span>
        <input
            type="text"
            name="name"
            value="{{ old('name', $product->name ?? '') }}"
            required
            placeholder="Enter product name"
            class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
        >
        @error('name')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium text-slate-700">Slug</span>
        <input
            type="text"
            name="slug"
            value="{{ old('slug', $product->slug ?? '') }}"
            placeholder="auto-generated"
            class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
        >
        <span class="mt-1 block text-xs text-slate-500">Leave empty to auto-generate from name</span>
        @error('slug')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium text-slate-700">SKU</span>
        <input
            type="text"
            name="sku"
            value="{{ old('sku', $product->sku ?? '') }}"
            placeholder="e.g., PROD-001"
            class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm font-mono outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
        >
        @error('sku')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
        <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900">
            <option value="draft" @selected(old('status', $product->status ?? 'draft') === 'draft')">📝 Draft</option>
            <option value="active" @selected(old('status', $product->status ?? '') === 'active')">✅ Active</option>
            <option value="archived" @selected(old('status', $product->status ?? '') === 'archived')">🗄️ Archived</option>
        </select>
        @error('status')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>
</div>

<!-- Pricing & Inventory -->
<div class="mt-6 border-t border-slate-100 pt-6">
    <h3 class="mb-4 text-sm font-medium text-slate-700">Pricing & Inventory</h3>
    
    <div class="grid gap-6 md:grid-cols-3">
        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Price ($) <span class="text-red-500">*</span></span>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                <input
                    type="number"
                    name="price"
                    value="{{ old('price', $product->price ?? '') }}"
                    step="0.01"
                    min="0"
                    required
                    placeholder="0.00"
                    class="w-full rounded-md border border-slate-300 py-2.5 pl-7 pr-3 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                >
            </div>
            @error('price')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Compare Price ($)</span>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                <input
                    type="number"
                    name="compare_price"
                    value="{{ old('compare_price', $product->compare_price ?? '') }}"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="w-full rounded-md border border-slate-300 py-2.5 pl-7 pr-3 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                >
            </div>
            <span class="mt-1 block text-xs text-slate-500">Show original price for sales</span>
            @error('compare_price')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Cost ($)</span>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                <input
                    type="number"
                    name="cost"
                    value="{{ old('cost', $product->cost ?? '') }}"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="w-full rounded-md border border-slate-300 py-2.5 pl-7 pr-3 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                >
            </div>
            <span class="mt-1 block text-xs text-slate-500">For profit calculation</span>
            @error('cost')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Quantity <span class="text-red-500">*</span></span>
            <input
                type="number"
                name="quantity"
                value="{{ old('quantity', $product->quantity ?? 0) }}"
                min="0"
                required
                placeholder="0"
                class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
            >
            @error('quantity')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="flex items-center gap-3 md:col-span-2">
            <input
                type="checkbox"
                name="track_inventory"
                value="1"
                @checked(old('track_inventory', $product->track_inventory ?? true))
                class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
            >
            <div>
                <span class="block text-sm font-medium text-slate-700">Track Inventory</span>
                <span class="block text-xs text-slate-500">Reduce stock automatically on sales</span>
            </div>
        </label>
    </div>
</div>

<!-- Description -->
<div class="mt-6 border-t border-slate-100 pt-6">
    <h3 class="mb-4 text-sm font-medium text-slate-700">Description</h3>
    
    <label class="block">
        <textarea
            name="description"
            rows="8"
            placeholder="Write a detailed product description..."
            class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
        >{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>
</div>

<!-- SEO Settings -->
<div class="mt-6 border-t border-slate-100 pt-6">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-sm font-medium text-slate-700">SEO Settings</h3>
        <span class="text-xs text-slate-500">Optional - for search engines</span>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <label class="block md:col-span-2">
            <span class="mb-1 block text-sm font-medium text-slate-700">Meta Title</span>
            <input
                type="text"
                name="meta_title"
                value="{{ old('meta_title', $product->meta_title ?? '') }}"
                placeholder="SEO title (50-60 characters recommended)"
                class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
            >
            @error('meta_title')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block md:col-span-2">
            <span class="mb-1 block text-sm font-medium text-slate-700">Meta Description</span>
            <textarea
                name="meta_description"
                rows="3"
                placeholder="Brief description for search results (150-160 characters recommended)"
                class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
            >{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
            @error('meta_description')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block md:col-span-2">
            <span class="mb-1 block text-sm font-medium text-slate-700">Meta Keywords</span>
            <input
                type="text"
                name="meta_keywords"
                value="{{ old('meta_keywords', $product->meta_keywords ?? '') }}"
                placeholder="keyword1, keyword2, keyword3"
                class="w-full rounded-md border border-slate-300 px-3 py-2.5 text-sm outline-none transition-colors focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
            >
            <span class="mt-1 block text-xs text-slate-500">Comma-separated keywords (optional)</span>
            @error('meta_keywords')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>
    </div>
</div>

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
