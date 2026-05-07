@php
    $style = $settings['style'] ?? 'primary';
    $size = $settings['size'] ?? 'md';
    $target = $settings['target'] ?? '_self';
    $url = $settings['url'] ?? '#';
    
    $classes = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700',
        'secondary' => 'bg-gray-600 text-white hover:bg-gray-700',
        'outline' => 'border-2 border-blue-600 text-blue-600 hover:bg-blue-50',
        'ghost' => 'text-blue-600 hover:bg-blue-50',
        'sm' => 'px-3 py-1 text-sm',
        'md' => 'px-4 py-2',
        'lg' => 'px-6 py-3 text-lg',
    ];
    
    $baseClass = 'vc-button inline-block rounded font-medium transition-colors';
    $classString = $baseClass . ' ' . ($classes[$style] ?? $classes['primary']) . ' ' . ($classes[$size] ?? $classes['md']);
@endphp

<a href="{{ $url }}" target="{{ $target }}" class="{{ $classString }}">
    {{ $settings['text'] ?? 'Button' }}
</a>
