@php
    $image = $settings['image'] ?? null;
    $title = $settings['title'] ?? 'Product Name';
    $description = $settings['description'] ?? '';
    $price = $settings['price'] ?? 0;
    $oldPrice = $settings['old_price'] ?? null;
    $currency = $settings['currency'] ?? '₽';
    $rating = $settings['rating'] ?? 5;
    $reviewsCount = $settings['reviews_count'] ?? 0;
    $buttonText = $settings['button_text'] ?? 'Add to Cart';
@endphp

<div class="vc-product-card group bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
    <div class="relative aspect-square overflow-hidden bg-gray-100">
        @if($image)
            <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-300">
                <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
        @endif
        
        @if($oldPrice)
            <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Sale</span>
        @endif
    </div>
    
    <div class="p-4">
        @if($rating > 0)
            <div class="flex items-center text-yellow-400 mb-1">
                @for($i = 0; $i < 5; $i++)
                    <svg class="w-3 h-3 {{ $i < floor($rating) ? 'fill-current' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                @endfor
                @if($reviewsCount > 0)
                    <span class="text-gray-400 text-[10px] ml-1">({{ $reviewsCount }})</span>
                @endif
            </div>
        @endif
        
        <h4 class="font-bold text-gray-900 mb-1 line-clamp-1 hover:text-blue-600 transition-colors">
            <a href="#">{{ $title }}</a>
        </h4>
        
        @if($description)
            <p class="text-gray-500 text-xs mb-3 line-clamp-2">{{ $description }}</p>
        @endif
        
        <div class="flex items-center justify-between mt-auto">
            <div class="flex flex-col">
                @if($oldPrice)
                    <span class="text-gray-400 line-through text-[10px]">{{ $oldPrice }}{{ $currency }}</span>
                @endif
                <span class="text-lg font-black text-gray-900">{{ $price }}{{ $currency }}</span>
            </div>
            
            <button class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            </button>
        </div>
    </div>
</div>
