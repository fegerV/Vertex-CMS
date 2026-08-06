@php
    $image = $settings['image'] ?? null;
    $title = $settings['title'] ?? 'Название товара';
    $description = $settings['description'] ?? '';
    $price = $settings['price'] ?? 99.99;
    $oldPrice = $settings['old_price'] ?? null;
    $currency = $settings['currency'] ?? '₽';
    $rating = $settings['rating'] ?? 5;
    $reviewsCount = $settings['reviews_count'] ?? 0;
    $buttonText = $settings['button_text'] ?? 'В корзину';
@endphp

<div class="pb-product-card">
    @if($image)
        <div class="pb-product-card__image">
            <img src="{{ asset('storage/' . $image) }}" alt="{{ $title }}" loading="lazy"/>
        </div>
    @endif
    
    <div class="pb-product-card__content">
        <h3 class="pb-product-card__title">{{ $title }}</h3>
        
        @if($description)
            <p class="pb-product-card__description">{{ Str::limit($description, 80) }}</p>
        @endif
        
        @if($rating > 0)
            <div class="pb-product-card__rating">
                @for($i = 0; $i < 5; $i++)
                    <span class="pb-star {{ $i < $rating ? 'pb-star--active' : '' }}">★</span>
                @endfor
                @if($reviewsCount > 0)
                    <span class="pb-product-card__reviews">({{ $reviewsCount }})</span>
                @endif
            </div>
        @endif
        
        <div class="pb-product-card__price">
            @if($oldPrice && $oldPrice > $price)
                <span class="pb-product-card__old-price">{{ number_format($oldPrice, 2) }} {{ $currency }}</span>
            @endif
            <span class="pb-product-card__current-price">{{ number_format($price, 2) }} {{ $currency }}</span>
        </div>
        
        <button class="pb-button pb-button--primary pb-product-card__button">
            {{ $buttonText }}
        </button>
    </div>
</div>
