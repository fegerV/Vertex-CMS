@php
    $url = $settings['url'] ?? '';
    if ($url === '' && filled($settings['media_id'] ?? null)) {
        $url = '/api/media/' . e($settings['media_id']);
    }
    $alt = $settings['alt'] ?? '';
    $width = $settings['width'] ?? '100%';
    $height = $settings['height'] ?? 'auto';
    $radius = $settings['radius'] ?? 'none';
    $shadow = $settings['shadow'] ?? 'none';
    
    $radiusClasses = [
        'none' => '',
        'sm' => 'rounded-sm',
        'md' => 'rounded-md',
        'lg' => 'rounded-lg',
        'full' => 'rounded-full',
    ];
    
    $shadowClasses = [
        'none' => '',
        'sm' => 'shadow-sm',
        'md' => 'shadow',
        'lg' => 'shadow-lg',
    ];
    
    $classString = 'vc-image ' . ($radiusClasses[$radius] ?? '') . ' ' . ($shadowClasses[$shadow] ?? '');
@endphp

@if($url === '')
    <div class="vc-media-placeholder vc-image-placeholder flex min-h-40 items-center justify-center bg-gray-100 text-gray-500">Image placeholder</div>
@else
    <img src="{{ $url }}" alt="{{ $alt }}" class="{{ $classString }}" style="width: {{ $width }}; height: {{ $height }};">
@endif
