@extends('admin.layouts.app')

@section('title', 'Shopping Cart - VertexCMS')
@section('page_title', 'Shopping Cart')
@section('page_subtitle', 'View current cart contents')

@section('content')
    <div class="mx-auto max-w-4xl">
        @if($cartItems->count() > 0)
            <section class="mb-6 overflow-hidden rounded-lg border border-slate-200 bg-white">
                <table class="w-full border-collapse text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Product</th>
                            <th class="px-4 py-3 font-medium">Price</th>
                            <th class="px-4 py-3 font-medium">Quantity</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cartItems as $item)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $item->product?->name ?? $item->product_name ?? 'Unknown Product' }}</div>
                                    @if($item->variant)
                                        <div class="text-xs text-slate-500">{{ $item->variant }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600">${{ number_format($item->price, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 font-medium">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        @if (auth()->user()?->hasPermission('ecommerce.cart.manage'))
                                            <form method="POST" action="{{ route('admin.ecommerce.cart.remove', $item) }}" onsubmit="return confirm('Remove this item from cart?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-md border border-red-200 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50">
                                                    Remove
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold">Cart Summary</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Subtotal</dt>
                        <dd class="font-medium">${{ number_format($totals['subtotal'] ?? 0, 2) }}</dd>
                    </div>
                    @if(isset($totals['tax']) && $totals['tax'] > 0)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Tax</dt>
                            <dd class="font-medium">${{ number_format($totals['tax'], 2) }}</dd>
                        </div>
                    @endif
                    @if(isset($totals['discount']) && $totals['discount'] > 0)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Discount</dt>
                            <dd class="font-medium text-green-600">-${{ number_format($totals['discount'], 2) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-slate-100 pt-3">
                        <dt class="font-medium">Total</dt>
                        <dd class="font-bold">${{ number_format($totals['total'] ?? 0, 2) }}</dd>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500">
                        <dt>Items in cart</dt>
                        <dd>{{ $cartItems->count() }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex gap-3">
                    @if (auth()->user()?->hasPermission('ecommerce.orders.create'))
                        <a href="{{ route('admin.ecommerce.cart.checkout') }}" class="flex-1 rounded-md bg-slate-950 px-4 py-2 text-center text-sm font-medium text-white hover:bg-slate-800">
                            Proceed to Checkout
                        </a>
                    @endif
                    <form method="POST" action="{{ route('admin.ecommerce.cart.clear') }}" onsubmit="return confirm('Are you sure you want to clear the entire cart?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">
                            Clear Cart
                        </button>
                    </form>
                </div>
            </section>
        @else
            <section class="rounded-lg border border-slate-200 bg-white p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-slate-900">Cart is empty</h3>
                <p class="mt-2 text-sm text-slate-500">No items in the shopping cart yet.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.ecommerce.products.index') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Browse Products
                    </a>
                </div>
            </section>
        @endif
    </div>
@endsection
