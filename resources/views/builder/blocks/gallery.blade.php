@php
    $images = $settings['images'] ?? [];
    $columns = $settings['columns'] ?? 3;
    $gap = $settings['gap'] ?? 'md';
    $radius = $settings['radius'] ?? 'md';
    $lightbox = $settings['lightbox'] ?? true;
@endphp

<div class="pb-gallery pb-gallery--cols-{{ $columns }} pb-gallery--gap-{{ $gap }}">
    @foreach($images as $index => $image)
        @php
            $mediaId = $image['media_id'] ?? null;
            $alt = $image['alt'] ?? '';
            $imageUrl = $mediaId ? asset('storage/media/' . $mediaId . '.jpg') : ($image['url'] ?? '');
        @endphp
        <div class="pb-gallery__item">
            <img 
                src="{{ $imageUrl }}"
                alt="{{ $alt }}"
                class="pb-gallery__image pb-image--radius-{{ $radius }}"
                loading="lazy"
                @if($lightbox) data-lightbox="gallery" data-index="{{ $index }}" @endif
            />
        </div>
    @endforeach
</div>

@if($lightbox)
    <!-- Lightbox markup будет добавлен через JS -->
@endif
