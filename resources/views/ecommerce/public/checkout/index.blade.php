@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="checkout-container max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Checkout</h1>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ecommerce.checkout.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf

        <!-- Left Column: Customer & Address -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Customer Information -->
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Customer Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                        <input type="text" name="customer[name]" id="customer_name" 
                               value="{{ old('customer.name', $customerData['name'] ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    
                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" name="customer[email]" id="customer_email" 
                               value="{{ old('customer.email', $customerData['email'] ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="customer_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="customer[phone]" id="customer_phone" 
                               value="{{ old('customer.phone', $customerData['phone'] ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </section>

            <!-- Shipping Address -->
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Shipping Address</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="shipping_first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                        <input type="text" name="shipping_address[first_name]" id="shipping_first_name" 
                               value="{{ old('shipping_address.first_name') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    
                    <div>
                        <label for="shipping_last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                        <input type="text" name="shipping_address[last_name]" id="shipping_last_name" 
                               value="{{ old('shipping_address.last_name') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="shipping_address_line_1" class="block text-sm font-medium text-gray-700">Address Line 1 *</label>
                        <input type="text" name="shipping_address[address_line_1]" id="shipping_address_line_1" 
                               value="{{ old('shipping_address.address_line_1') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="shipping_address_line_2" class="block text-sm font-medium text-gray-700">Address Line 2</label>
                        <input type="text" name="shipping_address[address_line_2]" id="shipping_address_line_2" 
                               value="{{ old('shipping_address.address_line_2') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="shipping_city" class="block text-sm font-medium text-gray-700">City *</label>
                        <input type="text" name="shipping_address[city]" id="shipping_city" 
                               value="{{ old('shipping_address.city') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    
                    <div>
                        <label for="shipping_state" class="block text-sm font-medium text-gray-700">State/Province *</label>
                        <input type="text" name="shipping_address[state]" id="shipping_state" 
                               value="{{ old('shipping_address.state') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    
                    <div>
                        <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700">Postal Code *</label>
                        <input type="text" name="shipping_address[postal_code]" id="shipping_postal_code" 
                               value="{{ old('shipping_address.postal_code') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    
                    <div>
                        <label for="shipping_country" class="block text-sm font-medium text-gray-700">Country *</label>
                        <select name="shipping_address[country]" id="shipping_country" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="US" {{ old('shipping_address.country', 'US') == 'US' ? 'selected' : '' }}>United States</option>
                            <option value="CA" {{ old('shipping_address.country') == 'CA' ? 'selected' : '' }}>Canada</option>
                            <option value="GB" {{ old('shipping_address.country') == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                            <option value="DE" {{ old('shipping_address.country') == 'DE' ? 'selected' : '' }}>Germany</option>
                            <option value="FR" {{ old('shipping_address.country') == 'FR' ? 'selected' : '' }}>France</option>
                            <option value="AU" {{ old('shipping_address.country') == 'AU' ? 'selected' : '' }}>Australia</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="shipping_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="shipping_address[phone]" id="shipping_phone" 
                               value="{{ old('shipping_address.phone') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </section>

            <!-- Billing Address -->
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Billing Address</h2>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="billing_address[same_as_shipping]" value="1" 
                               id="same_as_shipping" 
                               {{ old('billing_address.same_as_shipping', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               onchange="toggleBillingAddress()">
                        <span class="ml-2 text-sm text-gray-700">Same as shipping address</span>
                    </label>
                </div>

                <div id="billing_address_fields" class="grid grid-cols-1 md:grid-cols-2 gap-4" style="display: none;">
                    <div>
                        <label for="billing_first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="billing_address[first_name]" id="billing_first_name" 
                               value="{{ old('billing_address.first_name') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="billing_last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="billing_address[last_name]" id="billing_last_name" 
                               value="{{ old('billing_address.last_name') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="billing_address_line_1" class="block text-sm font-medium text-gray-700">Address Line 1</label>
                        <input type="text" name="billing_address[address_line_1]" id="billing_address_line_1" 
                               value="{{ old('billing_address.address_line_1') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="billing_city" class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" name="billing_address[city]" id="billing_city" 
                               value="{{ old('billing_address.city') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="billing_state" class="block text-sm font-medium text-gray-700">State/Province</label>
                        <input type="text" name="billing_address[state]" id="billing_state" 
                               value="{{ old('billing_address.state') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="billing_postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input type="text" name="billing_address[postal_code]" id="billing_postal_code" 
                               value="{{ old('billing_address.postal_code') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label for="billing_country" class="block text-sm font-medium text-gray-700">Country</label>
                        <select name="billing_address[country]" id="billing_country" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="US">United States</option>
                            <option value="CA">Canada</option>
                            <option value="GB">United Kingdom</option>
                            <option value="DE">Germany</option>
                            <option value="FR">France</option>
                            <option value="AU">Australia</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Payment Method -->
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Payment Method</h2>
                
                <div class="space-y-3">
                    @foreach ($paymentMethods as $method)
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="{{ $method }}" 
                                   {{ old('payment_method') == $method ? 'checked' : '' }}
                                   class="text-indigo-600 focus:ring-indigo-500" required>
                            <span class="ml-3 capitalize">{{ str_replace('_', ' ', $method) }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            <!-- Shipping Method -->
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Shipping Method</h2>
                
                <div class="space-y-3">
                    @foreach ($shippingMethods as $method)
                        <label class="flex items-center justify-between p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <div class="flex items-center">
                                <input type="radio" name="shipping_method" value="{{ $method['id'] }}" 
                                       {{ old('shipping_method', 'standard') == $method['id'] ? 'checked' : '' }}
                                       class="text-indigo-600 focus:ring-indigo-500" required>
                                <span class="ml-3">{{ $method['name'] }}</span>
                            </div>
                            <span class="font-medium">${{ number_format($method['price'], 2) }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            <!-- Order Notes -->
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Order Notes (Optional)</h2>
                <textarea name="notes" id="notes" rows="3" 
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                          placeholder="Special instructions for your order...">{{ old('notes') }}</textarea>
            </section>

            <!-- Terms Acceptance -->
            <section class="bg-white rounded-lg shadow p-6">
                <label class="flex items-start">
                    <input type="checkbox" name="terms_accepted" value="1" 
                           class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <span class="ml-2 text-sm text-gray-700">
                        I accept the <a href="#" class="text-indigo-600 hover:underline">Terms and Conditions</a> 
                        and <a href="#" class="text-indigo-600 hover:underline">Privacy Policy</a>. *
                    </span>
                </label>
            </section>
        </div>

        <!-- Right Column: Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                
                <div class="space-y-3 mb-6">
                    @foreach ($cartItems as $item)
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-medium text-sm">{{ $item->product->name }}</p>
                                <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                            </div>
                            <p class="font-medium text-sm">${{ number_format($item->product->price * $item->quantity, 2) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="border-t pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">${{ number_format($totals['subtotal'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax</span>
                        <span class="font-medium">${{ number_format($totals['tax'], 2) }}</span>
                    </div>
                    @if ($totals['discount'] > 0)
                        <div class="flex justify-between text-sm text-green-600">
                            <span>Discount</span>
                            <span class="font-medium">-${{ number_format($totals['discount'], 2) }}</span>
                        </div>
                    @endif
                    <div class="border-t pt-2 flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span>${{ number_format($totals['total'], 2) }}</span>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full mt-6 bg-indigo-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                    Place Order
                </button>

                <p class="text-xs text-gray-500 mt-4 text-center">
                    Secure checkout powered by Vertex CMS
                </p>
            </div>
        </div>
    </form>
</div>

<script>
function toggleBillingAddress() {
    const checkbox = document.getElementById('same_as_shipping');
    const billingFields = document.getElementById('billing_address_fields');
    billingFields.style.display = checkbox.checked ? 'none' : 'grid';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleBillingAddress();
});
</script>
@endsection
