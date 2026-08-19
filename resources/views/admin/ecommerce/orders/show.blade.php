@extends('admin.layouts.app')

@section('title', 'Order #' . $order->id . ' - VertexCMS')
@section('page_title', 'Order #' . $order->id)
@section('page_subtitle', 'Order Details')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.ecommerce.orders.index') }}" class="text-sm text-slate-600 hover:text-slate-950">Back to Orders</a>
            <div class="flex gap-2">
                @if ($order->status !== 'cancelled' && $order->status !== 'delivered')
                    @if (auth()->user()?->hasPermission('ecommerce.orders.update'))
                        <form method="POST" action="{{ route('admin.ecommerce.orders.cancel', $order) }}" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                            @csrf
                            <button class="rounded-md border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                                Cancel Order
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <section class="rounded-lg border border-slate-200 bg-white p-6">
                    <h2 class="mb-4 text-lg font-semibold">Order Items</h2>
                    <div class="overflow-hidden rounded-lg border border-slate-200">
                        <table class="w-full border-collapse text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Product</th>
                                    <th class="px-4 py-3 font-medium">Price</th>
                                    <th class="px-4 py-3 font-medium">Qty</th>
                                    <th class="px-4 py-3 font-medium text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr class="border-t border-slate-100">
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ $item->product_name }}</div>
                                            @if($item->variant)
                                                <div class="text-xs text-slate-500">{{ $item->variant }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">${{ number_format($item->price, 2) }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $item->quantity }}</td>
                                        <td class="px-4 py-3 text-right font-medium">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if($order->notes)
                    <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6">
                        <h2 class="mb-4 text-lg font-semibold">Order Notes</h2>
                        <p class="whitespace-pre-wrap text-sm text-slate-700">{{ $order->notes }}</p>
                    </section>
                @endif
            </div>

            <div class="space-y-6">
                <section class="rounded-lg border border-slate-200 bg-white p-6">
                    <h2 class="mb-4 text-lg font-semibold">Order Summary</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Subtotal</dt>
                            <dd class="font-medium">${{ number_format($order->subtotal, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Tax</dt>
                            <dd class="font-medium">${{ number_format($order->tax, 2) }}</dd>
                        </div>
                        @if($order->discount > 0)
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Discount</dt>
                                <dd class="font-medium text-green-600">-${{ number_format($order->discount, 2) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-slate-100 pt-3">
                            <dt class="font-medium">Total</dt>
                            <dd class="font-bold">${{ number_format($order->total, 2) }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-6">
                    <h2 class="mb-4 text-lg font-semibold">Status</h2>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-slate-500">Order Status</span>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'processing' => 'bg-purple-100 text-purple-800',
                                    'shipped' => 'bg-indigo-100 text-indigo-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'refunded' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <div class="mt-1">
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-sm text-slate-500">Payment Status</span>
                            @php
                                $paymentColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'refunded' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <div class="mt-1">
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $paymentColors[$order->payment_status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $order->payment_status }}
                                </span>
                            </div>
                        </div>
                        @if($order->paid_at)
                            <div>
                                <span class="text-sm text-slate-500">Paid At</span>
                                <div class="mt-1 text-sm">{{ $order->paid_at->format('d.m.Y H:i') }}</div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-6">
                    <h2 class="mb-4 text-lg font-semibold">Customer Information</h2>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-slate-500">Name</dt>
                            <dd class="font-medium">{{ $order->customer_name ?? 'Guest' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Email</dt>
                            <dd class="font-medium">{{ $order->customer_email }}</dd>
                        </div>
                        @if($order->user)
                            <div>
                                <dt class="text-slate-500">User ID</dt>
                                <dd class="font-medium">#{{ $order->user_id }} ({{ $order->user->name }})</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                @if($order->shipping_address_json)
                    <section class="rounded-lg border border-slate-200 bg-white p-6">
                        <h2 class="mb-4 text-lg font-semibold">Shipping Address</h2>
                        <address class="not-italic">
                            <p class="text-sm text-slate-700">
                                {{ $order->shipping_address_json['name'] ?? '' }}<br>
                                {{ $order->shipping_address_json['address'] ?? '' }}<br>
                                {{ $order->shipping_address_json['city'] ?? '' }}
                                {{ $order->shipping_address_json['state'] ?? '' }}
                                {{ $order->shipping_address_json['zip'] ?? '' }}<br>
                                {{ $order->shipping_address_json['country'] ?? '' }}
                            </p>
                        </address>
                    </section>
                @endif

                <section class="rounded-lg border border-slate-200 bg-white p-6">
                    <h2 class="mb-4 text-lg font-semibold">Order Metadata</h2>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-slate-500">Created At</dt>
                            <dd class="font-medium">{{ $order->created_at?->format('d.m.Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Updated At</dt>
                            <dd class="font-medium">{{ $order->updated_at?->format('d.m.Y H:i') }}</dd>
                        </div>
                        @if($order->payment_transaction_id)
                            <div>
                                <dt class="text-slate-500">Transaction ID</dt>
                                <dd class="font-medium">{{ $order->payment_transaction_id }}</dd>
                            </div>
                        @endif
                        @if($order->payment_method)
                            <div>
                                <dt class="text-slate-500">Payment Method</dt>
                                <dd class="font-medium">{{ $order->payment_method }}</dd>
                            </div>
                        @endif
                    </dl>
                </section>
            </div>
        </div>
    </div>
@endsection
