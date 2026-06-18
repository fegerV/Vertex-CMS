@php
    $items = is_array($settings['items'] ?? null) ? $settings['items'] : [];
    $currency = $settings['currency'] ?? '$';
    $showCoupon = (bool) ($settings['show_coupon'] ?? true);
    $showShipping = (bool) ($settings['show_shipping'] ?? true);
    $subtotal = collect($items)->sum(function ($item) {
        $quantity = (float) ($item['quantity'] ?? 1);
        $price = (float) ($item['price'] ?? 0);

        return $quantity * $price;
    });
    $shipping = $showShipping && $subtotal > 0 ? 9 : 0;
    $total = $subtotal + $shipping;
@endphp

<div class="vc-cart rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Cart Summary</h3>
            <p class="text-sm text-slate-500">Preview of cart rows, totals and optional checkout helpers.</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ count($items) }} items</span>
    </div>

    <div class="mt-4 space-y-3">
        @forelse($items as $item)
            @php
                $title = $item['title'] ?? 'Cart item';
                $quantity = (float) ($item['quantity'] ?? 1);
                $price = (float) ($item['price'] ?? 0);
                $lineTotal = $quantity * $price;
            @endphp
            <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                <div>
                    <div class="font-medium text-slate-900">{{ $title }}</div>
                    <div class="text-xs text-slate-500">Qty {{ rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.') ?: '1' }}</div>
                </div>
                <div class="text-right">
                    <div class="font-semibold text-slate-900">{{ number_format($lineTotal, 2) }}{{ $currency }}</div>
                    <div class="text-xs text-slate-500">{{ number_format($price, 2) }}{{ $currency }} each</div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                Cart preview is empty. Add line items in the builder inspector.
            </div>
        @endforelse
    </div>

    <div class="mt-5 space-y-2 border-t border-slate-200 pt-4 text-sm">
        <div class="flex items-center justify-between text-slate-600">
            <span>Subtotal</span>
            <span>{{ number_format($subtotal, 2) }}{{ $currency }}</span>
        </div>
        @if($showShipping)
            <div class="flex items-center justify-between text-slate-600">
                <span>Shipping</span>
                <span>{{ number_format($shipping, 2) }}{{ $currency }}</span>
            </div>
        @endif
        <div class="flex items-center justify-between text-base font-semibold text-slate-900">
            <span>Total</span>
            <span>{{ number_format($total, 2) }}{{ $currency }}</span>
        </div>
    </div>

    @if($showCoupon)
        <div class="mt-4 flex gap-2">
            <input type="text" value="WELCOME10" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600" readonly>
            <button type="button" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Apply</button>
        </div>
    @endif

    <button type="button" class="mt-4 w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white">Proceed to checkout</button>
</div>
