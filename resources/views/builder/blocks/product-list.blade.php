{{-- resources/views/builder/blocks/product-list.blade.php --}}
@php
    $products = $settings['products'] ?? [];
    $columns = $settings['columns'] ?? 4;
    $layout = $settings['layout'] ?? 'grid';
    $showRating = $settings['show_rating'] ?? true;
    $showPrice = $settings['show_price'] ?? true;
    $showAddToCart = $settings['show_add_to_cart'] ?? true;
    $cssClass = $settings['css_class'] ?? '';
@endphp

<div class="vc-product-list {{ $cssClass }}">
    <div class="grid {{ $layout === 'grid' ? 'grid-cols-' . $columns : 'grid-cols-1' }} gap-6">
        @foreach($products as $product)
            <div class="vc-product-item border rounded-lg p-4 hover:shadow-lg transition-shadow">
                @if(isset($product['image']))
                    <img src="{{ $product['image'] }}" alt="{{ $product['title'] ?? '' }}" class="w-full h-48 object-cover rounded mb-4">
                @endif
                <h3 class="font-semibold text-lg mb-2">{{ $product['title'] ?? 'Товар' }}</h3>
                @if($showRating && isset($product['rating']))
                    <div class="flex items-center mb-2">
                        @for($i = 0; $i < 5; $i++)
                            <span class="{{ $i < ($product['rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                        @endfor
                    </div>
                @endif
                @if($showPrice && isset($product['price']))
                    <p class="text-xl font-bold text-slate-900 mb-3">${{ number_format($product['price'], 2) }}</p>
                @endif
                @if($showAddToCart)
                    <button class="w-full bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-800">В корзину</button>
                @endif
            </div>
        @endforeach
    </div>
</div>
