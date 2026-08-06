@extends('admin.layouts.app')

@section('title', $product->name . ' - VertexCMS')
@section('page_title', $product->name)
@section('page_subtitle', 'Product Details')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.ecommerce.products.index') }}" class="text-sm text-slate-600 hover:text-slate-950">Back to Products</a>
            @if (auth()->user()?->hasPermission('ecommerce.products.edit'))
                <a href="{{ route('admin.ecommerce.products.edit', $product) }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    Edit Product
                </a>
            @endif
        </div>

        <div class="space-y-6">
            <section class="rounded-lg border border-slate-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold">Basic Information</h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Product Name</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Slug</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->slug ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">SKU</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->sku ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Status</dt>
                        <dd class="mt-1">
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                {{ $product->status }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Price</dt>
                        <dd class="mt-1 text-sm text-slate-900">${{ number_format($product->price, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Compare Price</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->compare_price ? '$'.number_format($product->compare_price, 2) : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Cost</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->cost ? '$'.number_format($product->cost, 2) : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Quantity</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->quantity }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Track Inventory</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->track_inventory ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Published At</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->published_at?->format('d.m.Y H:i') ?? 'Not published' }}</dd>
                    </div>
                </dl>

                @if($product->description)
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <dt class="text-sm font-medium text-slate-500">Description</dt>
                        <dd class="mt-2 whitespace-pre-wrap text-sm text-slate-900">{{ $product->description }}</dd>
                    </div>
                @endif
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold">SEO Settings</h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-slate-500">Meta Title</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->meta_title ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-slate-500">Meta Description</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->meta_description ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-slate-500">Meta Keywords</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->meta_keywords ?? '-' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold">Product Media</h2>
                @if($product->media && $product->media->count() > 0)
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @foreach($product->media as $media)
                            <div class="overflow-hidden rounded-lg border border-slate-200">
                                <img src="{{ $media->getUrl() }}" alt="{{ $media->alt ?? $product->name }}" class="h-32 w-full object-cover">
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500">No media attached to this product.</p>
                @endif
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold">Activity</h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Created By</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->creator?->name ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Created At</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->created_at?->format('d.m.Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Last Updated By</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->updater?->name ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Last Updated At</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $product->updated_at?->format('d.m.Y H:i') }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
@endsection
