@php
    $products = $settings['products'] ?? [];
    $columns = $settings['columns'] ?? 4;
    $layout = $settings['layout'] ?? 'grid';
    $showRating = $settings['show_rating'] ?? true;
    $showPrice = $settings['show_price'] ?? true;
    $showAddToCart = $settings['show_add_to_cart'] ?? true;
    
    $gridCols = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        3 => 'grid-cols-2 md:grid-cols-3',
        4 => 'grid-cols-2 md:grid-cols-4',
        5 => 'grid-cols-2 md:grid-cols-5',
        6 => 'grid-cols-3 md:grid-cols-6',
    ];
@endphp

<div class="vc-product-list">
    @if($layout === 'list')
        <div class="space-y-4">
            @foreach($products as $product)
                {{-- Horizontal card layout --}}
                <div class="flex bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                    <div class="w-32 h-32 flex-shrink-0 bg-gray-100">
                        @if(!empty($product['image']))
                            <img src="{{ $product['image'] }}" alt="{{ $product['title'] ?? '' }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="p-4 flex flex-col justify-center flex-grow">
                        <h4 class="font-bold text-gray-900 mb-1">{{ $product['title'] ?? '' }}</h4>
                        @if($showPrice)
                            <div class="text-lg font-black text-blue-600">{{ $product['price'] ?? 0 }}₽</div>
                        @endif
                    </div>
                    @if($showAddToCart)
                        <div class="p-4 flex items-center">
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-bold">Add</button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="grid {{ $gridCols[$columns] ?? 'grid-cols-4' }} gap-4">
            @foreach($products as $product)
                {!! app(\App\Builder\Services\PageBuilderService::class)->compileBlock('product-card', [
                    'title' => $product['title'] ?? '',
                    'price' => $product['price'] ?? 0,
                    'image' => $product['image'] ?? null,
                    'rating' => $showRating ? 5 : 0,
                ]) !!}
            @endforeach
        </div>
    @endif
</div>
