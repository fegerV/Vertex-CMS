@php
    $level = in_array($settings['level'] ?? 'h2', ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) ? $settings['level'] : 'h2';
    $style = collect([
        filled($settings['color'] ?? null) ? 'color: '.($settings['color']) : null,
        filled($settings['align'] ?? null) ? 'text-align: '.($settings['align']) : null,
        filled($settings['font_size'] ?? null) ? 'font-size: '.(is_numeric($settings['font_size']) ? $settings['font_size'].'px' : $settings['font_size']) : null,
        filled($settings['font_weight'] ?? null) ? 'font-weight: '.($settings['font_weight']) : null,
    ])->filter()->implode('; ');
@endphp
<{{ $level }} class="vc-heading" @if($style !== '') style="{{ $style }}" @endif>{{ $settings['text'] ?? '' }}</{{ $level }}>
