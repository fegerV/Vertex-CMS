@extends('layouts.app')

@section('title', 'Order Confirmation')

@section('content')
<div class="checkout-success max-w-3xl mx-auto px-4 py-12">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Thank You for Your Order!</h1>
        <p class="text-gray-600">Your order has been placed successfully.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4 pb-4 border-b">
            <div>
                <h2 class="text-xl font-semibold">Order #{{ $order->id }}</h2>
                <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
            </div>
            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                {{ ucfirst($order->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="font-semibold mb-2">Customer Information</h3>
                <p class="text-gray-700">{{ $order->customer_name }}</p>
                <p class="text-gray-700">{{ $order->customer_email }}</p>
                @if($order->phone)
                    <p class="text-gray-700">{{ $order->phone }}</p>
                @endif
            </div>
            
            <div>
                <h3 class="font-semibold mb-2">Payment Information</h3>
                <p class="text-gray-700">Payment Method: {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                <p class="text-gray-700">Payment Status: 
                    <span class="px-2 py-1 bg-{{ $order->payment_status === 'paid' ? 'green' : 'yellow' }}-100 text-{{ $order->payment_status === 'paid' ? 'green' : 'yellow' }}-800 rounded text-sm">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </p>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="font-semibold mb-2">Shipping Address</h3>
            @php
                $shipping = $order->shipping_address_json ?? [];
            @endphp
            <p class="text-gray-700">
                {{ $shipping['first_name'] ?? '' }} {{ $shipping['last_name'] ?? '' }}<br>
                {{ $shipping['address_line_1'] ?? '' }}<br>
                @if(!empty($shipping['address_line_2']))
                    {{ $shipping['address_line_2'] }}<br>
                @endif
                {{ $shipping['city'] ?? '' }}, {{ $shipping['state'] ?? '' }} {{ $shipping['postal_code'] ?? '' }}<br>
                {{ $shipping['country'] ?? '' }}
            </p>
        </div>

        <div class="border-t pt-4">
            <h3 class="font-semibold mb-3">Order Items</h3>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium">{{ $item->product_name }}</p>
                            <p class="text-sm text-gray-500">SKU: {{ $item->sku }}</p>
                            <p class="text-sm text-gray-500">Quantity: {{ $item->quantity }}</p>
                        </div>
                        <p class="font-medium">${{ number_format($item->total, 2) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t mt-4 pt-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span>${{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Tax</span>
                <span>${{ number_format($order->tax, 2) }}</span>
            </div>
            @if($order->discount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>Discount</span>
                    <span>-${{ number_format($order->discount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-lg font-bold pt-2 border-t">
                <span>Total</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    @if(!empty($order->notes))
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="font-semibold mb-2">Order Notes</h3>
            <p class="text-gray-700">{{ $order->notes }}</p>
        </div>
    @endif

    <div class="text-center space-x-4">
        <a href="{{ route('ecommerce.catalog') }}" 
           class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
            Continue Shopping
        </a>
        @auth
            <a href="{{ route('account.orders') }}" 
               class="inline-block bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                View All Orders
            </a>
        @endauth
    </div>

    <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-800">
            <strong>Important:</strong> A confirmation email has been sent to {{ $order->customer_email }}. 
            Please keep your order number (#{{ $order->id }}) for your records.
        </p>
    </div>
</div>
@endsection
