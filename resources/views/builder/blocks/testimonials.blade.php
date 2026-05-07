@php
    $testimonials = $settings['testimonials'] ?? [];
    $layout = $settings['layout'] ?? 'carousel';
    $autoplay = $settings['autoplay'] ?? false;
    $showRating = $settings['show_rating'] ?? true;
@endphp

<div class="vc-testimonials" 
     @if($layout === 'carousel' || $layout === 'slider')
        x-data="{ 
            active: 0, 
            total: {{ count($testimonials) }},
            next() { this.active = (this.active + 1) % this.total },
            prev() { this.active = (this.active - 1 + this.total) % this.total }
        }"
        @if($autoplay) x-init="setInterval(() => next(), 5000)" @endif
     @endif
>
    @if($layout === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($testimonials as $item)
                @include('builder.blocks.partials.testimonial-card', ['item' => $item, 'showRating' => $showRating])
            @endforeach
        </div>
    @else
        <div class="relative overflow-hidden">
            <div class="flex transition-transform duration-500 ease-in-out" :style="'transform: translateX(-' + (active * 100) + '%)'">
                @foreach($testimonials as $item)
                    <div class="w-full flex-shrink-0 px-4">
                        @include('builder.blocks.partials.testimonial-card', ['item' => $item, 'showRating' => $showRating])
                    </div>
                @endforeach
            </div>
            
            @if(count($testimonials) > 1)
                <button @click="prev()" class="absolute left-0 top-1/2 -translate-y-1/2 p-2 bg-white rounded-full shadow-md text-gray-800 hover:text-blue-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button @click="next()" class="absolute right-0 top-1/2 -translate-y-1/2 p-2 bg-white rounded-full shadow-md text-gray-800 hover:text-blue-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            @endif
        </div>
    @endif
</div>
