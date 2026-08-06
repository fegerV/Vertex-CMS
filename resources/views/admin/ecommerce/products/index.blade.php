@extends('admin.layouts.app')

@section('title', 'Products - VertexCMS')
@section('page_title', 'Products')
@section('page_subtitle', 'Manage your e-commerce products')

@section('content')
    @if (auth()->user()?->hasPermission('ecommerce.products.create'))
        <header class="mb-6 flex flex-wrap items-center justify-end gap-4">
            <a href="{{ route('admin.ecommerce.products.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Create Product
            </a>
        </header>
    @endif

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <table class="w-full border-collapse text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Name</th>
                    <th class="px-4 py-3 font-medium">SKU</th>
                    <th class="px-4 py-3 font-medium">Price</th>
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
                        <td class="px-4 py-3 text-slate-600">{{ $product->sku ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">${{ number_format($product->price, 2) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $product->quantity }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                {{ $product->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $product->updated_at?->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @if (auth()->user()?->hasPermission('ecommerce.products.edit'))
                                    <a href="{{ route('admin.ecommerce.products.edit', $product) }}" class="rounded-md border border-slate-300 px-3 py-1.5 hover:bg-slate-50">
                                        Edit
                                    </a>
                                @endif
                                @if (auth()->user()?->hasPermission('ecommerce.products.view'))
                                    <a href="{{ route('admin.ecommerce.products.show', $product) }}" class="rounded-md border border-slate-300 px-3 py-1.5 hover:bg-slate-50">
                                        View
                                    </a>
                                @endif
                                @if (auth()->user()?->hasPermission('ecommerce.products.delete'))
                                    <form method="POST" action="{{ route('admin.ecommerce.products.destroy', $product) }}" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-red-200 px-3 py-1.5 text-red-700 hover:bg-red-50">
                                            Delete
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
