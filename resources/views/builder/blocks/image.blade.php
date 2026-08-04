@php
    $mediaId = $settings['media_id'] ?? null;
    $url = $settings['url'] ?? '';
    $alt = $settings['alt'] ?? '';
    $width = $settings['width'] ?? '100%';
    $height = $settings['height'] ?? 'auto';
    $radius = $settings['radius'] ?? 'none';
    $shadow = $settings['shadow'] ?? 'none';
    
    // Если есть media_id, получаем URL из базы
    if ($mediaId) {
        $imageUrl = asset('storage/media/' . $mediaId . '.jpg'); // Упрощенно
    } else {
        $imageUrl = $url;
    }
@endphp

<img 
    src="{{ $imageUrl }}"
    alt="{{ $alt }}"
    class="pb-image pb-image--radius-{{ $radius }} pb-image--shadow-{{ $shadow }}"
    style="
        width: {{ $width }};
        height: {{ $height }};
    "
    loading="lazy"
/>
