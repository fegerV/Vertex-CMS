@php
    use Illuminate\Support\Facades\Schema;

    $layout = in_array(($settings['layout'] ?? 'grid'), ['grid', 'masonry', 'slider', 'carousel'], true) ? $settings['layout'] : 'grid';
    $columns = max(1, min((int) ($settings['columns'] ?? 3), 6));
    $tabletColumns = max(1, min((int) ($settings['tablet_columns'] ?? min($columns, 2)), 4));
    $mobileColumns = max(1, min((int) ($settings['mobile_columns'] ?? 1), 2));
    $gap = $settings['gap'] ?? 'md';
    $radius = $settings['radius'] ?? 'md';
    $aspectRatio = $settings['aspect_ratio'] ?? '4:3';
    $objectFitValue = $settings['object_fit'] ?? 'cover';
    $objectFit = in_array($objectFitValue, ['cover', 'contain'], true) ? $objectFitValue : 'cover';
    $captionModeValue = $settings['caption_mode'] ?? 'overlay';
    $captionMode = in_array($captionModeValue, ['none', 'overlay', 'below'], true) ? $captionModeValue : 'overlay';
    $useLightbox = (bool) ($settings['lightbox'] ?? true);
    $lightboxEffectValue = $settings['lightbox_effect'] ?? 'zoom';
    $lightboxEffect = in_array($lightboxEffectValue, ['fade', 'zoom'], true) ? $lightboxEffectValue : 'zoom';
    $showArrows = (bool) ($settings['show_arrows'] ?? true);
    $showDots = (bool) ($settings['show_dots'] ?? true);
    $autoplay = (bool) ($settings['autoplay'] ?? false);
    $interval = max(1000, (int) ($settings['interval'] ?? 5000));

    $gaps = [
        'none' => '0rem',
        'sm' => '0.5rem',
        'md' => '1rem',
        'lg' => '2rem',
    ];

    $radiusClasses = [
        'none' => 'vc-gallery-radius-none',
        'sm' => 'vc-gallery-radius-sm',
        'md' => 'vc-gallery-radius-md',
        'lg' => 'vc-gallery-radius-lg',
    ];

    $ratioMap = [
        'auto' => 'auto',
        '1:1' => '1 / 1',
        '4:3' => '4 / 3',
        '3:2' => '3 / 2',
        '16:9' => '16 / 9',
        '21:9' => '21 / 9',
    ];

    $rawImages = is_array($settings['images'] ?? null) ? $settings['images'] : [];
    $images = collect($rawImages)
        ->filter(fn ($image) => is_array($image))
        ->map(function (array $image) {
            $imageUrl = (string) ($image['url'] ?? '');

            if ($imageUrl === '' && filled($image['media_id'] ?? null)) {
                $imageUrl = Schema::hasTable('media')
                    ? (\App\Models\Media::query()->find($image['media_id'])?->url ?? '/api/media/' . $image['media_id'])
                    : '/api/media/' . $image['media_id'];
            }

            return array_merge($image, ['url' => $imageUrl]);
        })
        ->filter(fn ($image) => filled($image['url'] ?? null))
        ->values();

    $galleryId = 'vc-gallery-' . substr(md5(json_encode($settings)), 0, 10);
    $style = implode('; ', [
        '--vc-gallery-columns: ' . $columns,
        '--vc-gallery-tablet-columns: ' . $tabletColumns,
        '--vc-gallery-mobile-columns: ' . $mobileColumns,
        '--vc-gallery-gap: ' . ($gaps[$gap] ?? $gaps['md']),
        '--vc-gallery-ratio: ' . ($ratioMap[$aspectRatio] ?? $ratioMap['4:3']),
    ]);
@endphp

@if ($images->isEmpty())
    <div class="vc-media-placeholder vc-gallery-placeholder">
        <strong>Gallery placeholder</strong>
        <span>Add images in the builder inspector to render the gallery.</span>
    </div>
@else
    <div
        id="{{ $galleryId }}"
        class="vc-gallery vc-gallery-layout-{{ $layout }} vc-gallery-caption-{{ $captionMode }} {{ $radiusClasses[$radius] ?? 'vc-gallery-radius-md' }}"
        style="{{ $style }}"
        data-vc-gallery
        data-layout="{{ $layout }}"
        data-autoplay="{{ $autoplay ? 'true' : 'false' }}"
        data-interval="{{ $interval }}"
    >
        <div class="vc-gallery-track">
            @foreach($images as $index => $image)
                @php
                    $caption = trim((string) ($image['caption'] ?? ''));
                    $alt = trim((string) ($image['alt'] ?? $caption));
                    $link = trim((string) ($image['link'] ?? ''));
                    $targetUrl = $useLightbox ? $image['url'] : ($link !== '' ? $link : $image['url']);
                    $isAnchor = $useLightbox || $link !== '';
                @endphp

                <figure class="vc-gallery-item vc-gallery-fit-{{ $objectFit }}">
                    @if ($isAnchor)
                        <a
                            class="vc-gallery-link"
                            href="{{ $targetUrl }}"
                            @if ($useLightbox)
                                data-lightbox="gallery"
                                data-vc-lightbox
                                data-vc-lightbox-group="{{ $galleryId }}"
                                data-vc-lightbox-caption="{{ $caption }}"
                                data-vc-lightbox-effect="{{ $lightboxEffect }}"
                            @else
                                rel="noopener"
                            @endif
                        >
                    @endif

                    <img src="{{ $image['url'] }}" alt="{{ $alt }}" loading="lazy">

                    @if ($caption !== '' && $captionMode !== 'none')
                        <figcaption class="vc-gallery-caption">{{ $caption }}</figcaption>
                    @endif

                    @if ($isAnchor)
                        </a>
                    @endif
                </figure>
            @endforeach
        </div>

        @if (in_array($layout, ['slider', 'carousel'], true) && $images->count() > 1)
            @if ($showArrows)
                <button class="vc-gallery-nav vc-gallery-nav-prev" type="button" data-vc-gallery-prev aria-label="Previous image">‹</button>
                <button class="vc-gallery-nav vc-gallery-nav-next" type="button" data-vc-gallery-next aria-label="Next image">›</button>
            @endif

            @if ($showDots)
                <div class="vc-gallery-dots" aria-label="Gallery slides">
                    @foreach($images as $index => $image)
                        <button type="button" data-vc-gallery-dot="{{ $index }}" aria-label="Go to image {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endif
