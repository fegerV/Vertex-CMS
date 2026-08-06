@extends('admin.layouts.app')

@section('title', 'Products - VertexCMS')
@section('page_title', 'Products')
@section('page_subtitle', 'Manage your e-commerce products')

@section('content')
    @if (auth()->user()?->hasPermission('ecommerce.products.create'))
        <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-1 items-center gap-3">
                <form method="GET" action="{{ route('admin.ecommerce.products.index') }}" class="relative w-full max-w-xs">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search products..."
                        class="w-full rounded-md border border-slate-300 py-2 pl-3 pr-10 text-sm outline-none focus:border-slate-900"
                    >
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </form>

                <select
                    name="status"
                    onchange="window.location.href=this.value"
                    class="rounded-md border border-slate-300 py-2 pl-3 pr-8 text-sm outline-none focus:border-slate-900"
                >
                    <option value="{{ route('admin.ecommerce.products.index') }}" {{ request('status') === null ? 'selected' : '' }}>All Status</option>
                    <option value="{{ route('admin.ecommerce.products.index', ['status' => 'active']) }}" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="{{ route('admin.ecommerce.products.index', ['status' => 'draft']) }}" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="{{ route('admin.ecommerce.products.index', ['status' => 'archived']) }}" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>

            <a href="{{ route('admin.ecommerce.products.create') }}" class="inline-flex items-center gap-2 rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Product
            </a>
        </header>
    @endif

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <table class="w-full border-collapse text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">
                        <a href="{{ route('admin.ecommerce.products.index', array_merge(request()->except('sort'), ['sort' => 'name'])) }}" class="flex items-center gap-1 hover:text-slate-900">
                            Name
                            @if(request('sort') === 'name') ↓ @endif
                        </a>
                    </th>
                    <th class="px-4 py-3 font-medium">SKU</th>
                    <th class="px-4 py-3 font-medium">
                        <a href="{{ route('admin.ecommerce.products.index', array_merge(request()->except('sort'), ['sort' => 'price'])) }}" class="flex items-center gap-1 hover:text-slate-900">
                            Price
                            @if(request('sort') === 'price') ↓ @endif
                        </a>
                    </th>
                    <th class="px-4 py-3 font-medium">Quantity</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Updated</th>
                    <th class="px-4 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            @if($product->sku)
                                <span class="font-mono text-xs">{{ $product->sku }}</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <span class="font-medium">${{ number_format($product->price, 2) }}</span>
                            @if($product->compare_price && $product->compare_price > $product->price)
                                <span class="ml-2 text-xs text-slate-400 line-through">${{ number_format($product->compare_price, 2) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <div class="flex items-center gap-2">
                                <span>{{ $product->quantity }}</span>
                                @if($product->track_inventory && $product->quantity <= 5)
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700" title="Low stock">
                                        !
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'draft' => 'bg-slate-100 text-slate-700',
                                    'archived' => 'bg-gray-100 text-gray-600',
                                ];
                            @endphp
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusColors[$product->status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $product->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $product->updated_at?->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @if (auth()->user()?->hasPermission('ecommerce.products.view'))
                                    <a href="{{ route('admin.ecommerce.products.show', $product) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-50" title="View details">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                @endif
                                @if (auth()->user()?->hasPermission('ecommerce.products.edit'))
                                    <a href="{{ route('admin.ecommerce.products.edit', $product) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-50" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                @endif
                                @if (auth()->user()?->hasPermission('ecommerce.products.delete'))
                                    <form method="POST" action="{{ route('admin.ecommerce.products.destroy', $product) }}" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-red-200 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50" title="Delete">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                            No products found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
