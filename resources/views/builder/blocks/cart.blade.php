{{-- resources/views/builder/blocks/cart.blade.php --}}
@php
    $items = $settings['items'] ?? [];
    $showCoupon = $settings['show_coupon'] ?? true;
    $showShipping = $settings['show_shipping'] ?? true;
    $cssClass = $settings['css_class'] ?? '';
    
    $subtotal = array_sum(array_column($items, 'price'));
    $shipping = $showShipping ? 10 : 0;
    $total = $subtotal + $shipping;
@endphp

<div class="vc-cart {{ $cssClass }} max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Корзина</h2>
    
    @if(count($items) > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-slate-500">Товар</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-slate-500">Цена</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-slate-500">Кол-во</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-slate-500">Итого</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr class="border-t">
                            <td class="px-6 py-4">{{ $item['name'] ?? 'Товар' }}</td>
                            <td class="px-6 py-4 text-right">${{ number_format($item['price'] ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-right">{{ $item['quantity'] ?? 1 }}</td>
                            <td class="px-6 py-4 text-right">${{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="p-6 bg-slate-50 border-t">
                @if($showCoupon)
                    <div class="flex gap-2 mb-4">
                        <input type="text" placeholder="Промокод" class="flex-1 border rounded px-3 py-2">
                        <button class="bg-slate-900 text-white px-4 py-2 rounded">Применить</button>
                    </div>
                @endif
                
                <div class="space-y-2">
                    <div class="flex justify-between"><span>Подытог:</span><span>${{ number_format($subtotal, 2) }}</span></div>
                    @if($showShipping)
                        <div class="flex justify-between"><span>Доставка:</span><span>${{ number_format($shipping, 2) }}</span></div>
                    @endif
                    <div class="flex justify-between font-bold text-lg border-t pt-2"><span>Итого:</span><span>${{ number_format($total, 2) }}</span></div>
                </div>
                
                <button class="w-full mt-4 bg-slate-900 text-white px-6 py-3 rounded-lg hover:bg-slate-800">Оформить заказ</button>
            </div>
        </div>
    @else
        <div class="text-center py-12 bg-white rounded-lg">
            <p class="text-slate-500 mb-4">Корзина пуста</p>
            <a href="/catalog" class="text-blue-600 hover:underline">Перейти в каталог</a>
        </div>
    @endif
</div>
