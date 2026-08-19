@extends('admin.layouts.app')

@section('title', 'Orders - VertexCMS')
@section('page_title', 'Orders')
@section('page_subtitle', 'Manage customer orders')

@section('content')
    <section class="mb-6 overflow-hidden rounded-lg border border-slate-200 bg-white">
        <table class="w-full border-collapse text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Order #</th>
                    <th class="px-4 py-3 font-medium">Customer</th>
                    <th class="px-4 py-3 font-medium">Items</th>
                    <th class="px-4 py-3 font-medium">Total</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Payment</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">#{{ $order->id }}</td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium">{{ $order->customer_name ?? 'Guest' }}</div>
                            <div class="text-xs text-slate-500">{{ $order->customer_email }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $order->items->count() }}</td>
                        <td class="px-4 py-3 font-medium">${{ number_format($order->total, 2) }}</td>
                        <td class="px-4 py-3">
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
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $paymentColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'refunded' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $paymentColors[$order->payment_status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $order->payment_status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @if (auth()->user()?->hasPermission('ecommerce.orders.view'))
                                    <a href="{{ route('admin.ecommerce.orders.show', $order) }}" class="rounded-md border border-slate-300 px-3 py-1.5 hover:bg-slate-50">
                                        View
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-500">
                            No orders found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@endsection
