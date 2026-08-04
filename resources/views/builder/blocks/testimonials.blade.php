@php
    $testimonials = $settings['testimonials'] ?? [];
    $layout = $settings['layout'] ?? 'carousel';
    $autoplay = $settings['autoplay'] ?? false;
    $showRating = $settings['show_rating'] ?? true;
@endphp

<div class="pb-testimonials pb-testimonials--{{ $layout }}" @if($autoplay) data-autoplay="true" @endif>
    <div class="pb-testimonials__wrapper">
        @foreach($testimonials as $index => $testimonial)
            <div class="pb-testimonial-item">
                @if($testimonial['avatar'])
                    <img src="{{ asset('storage/' . $testimonial['avatar']) }}" alt="{{ $testimonial['author'] }}" class="pb-testimonial-item__avatar"/>
                @endif
                <div class="pb-testimonial-item__content">
                    @if($showRating && isset($testimonial['rating']))
                        <div class="pb-testimonial-item__rating">
                            @for($i = 0; $i < 5; $i++)
                                <span class="pb-star {{ $i < ($testimonial['rating'] ?? 0) ? 'pb-star--active' : '' }}">★</span>
                            @endfor
                        </div>
                    @endif
                    <blockquote class="pb-testimonial-item__text">{{ $testimonial['text'] ?? '' }}</blockquote>
                    <footer class="pb-testimonial-item__author">
                        <cite>{{ $testimonial['author'] ?? '' }}</cite>
                        @if(isset($testimonial['role']))
                            <span class="pb-testimonial-item__role">{{ $testimonial['role'] }}</span>
                        @endif
                    </footer>
                </div>
            </div>
        @endforeach
    </div>
    
    @if($layout === 'carousel' && count($testimonials) > 1)
        <button class="pb-testimonials__nav pb-testimonials__nav--prev" aria-label="Предыдущий">←</button>
        <button class="pb-testimonials__nav pb-testimonials__nav--next" aria-label="Следующий">→</button>
    @endif
</div>
