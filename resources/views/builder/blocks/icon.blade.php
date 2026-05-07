@php
    $icon = $settings['icon'] ?? 'star';
    $size = $settings['size'] ?? 'md';
    $color = $settings['color'] ?? 'currentColor';
    $background = $settings['background'] ?? null;
    $radius = $settings['radius'] ?? 'none';
    
    $sizes = [
        'sm' => 'w-4 h-4',
        'md' => 'w-6 h-6',
        'lg' => 'w-8 h-8',
        'xl' => 'w-12 h-12',
    ];
    
    $containerSizes = [
        'sm' => 'p-1',
        'md' => 'p-2',
        'lg' => 'p-3',
        'xl' => 'p-4',
    ];
    
    $radii = [
        'none' => 'rounded-none',
        'sm' => 'rounded-sm',
        'md' => 'rounded-full',
        'lg' => 'rounded-lg',
    ];
    
    $iconClass = $sizes[$size] ?? $sizes['md'];
    $containerClass = $radii[$radius] ?? 'rounded-none';
@endphp

<div class="vc-icon inline-flex items-center justify-center {{ $containerClass }}" 
     style="@if($background) background-color: {{ $background }}; @endif color: {{ $color }};">
    @if($icon === 'star')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.175 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
    @elseif($icon === 'heart')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
    @elseif($icon === 'check')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
    @elseif($icon === 'x')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
    @elseif($icon === 'arrow')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
    @else
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" /></svg>
    @endif
</div>
