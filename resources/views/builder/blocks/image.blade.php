@php
    $url = $settings['url'] ?? '';
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

<img src="{{ $url }}" alt="{{ $alt }}" class="{{ $classString }}" style="width: {{ $width }}; height: {{ $height }};">
