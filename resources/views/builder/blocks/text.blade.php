@php
    $style = collect([
        filled($settings['color'] ?? null) ? 'color: '.($settings['color']) : null,
        filled($settings['align'] ?? null) ? 'text-align: '.($settings['align']) : null,
        filled($settings['font_size'] ?? null) ? 'font-size: '.(is_numeric($settings['font_size']) ? $settings['font_size'].'px' : $settings['font_size']) : null,
    ])->filter()->implode('; ');
@endphp
<div class="vc-text" @if($style !== '') style="{{ $style }}" @endif>{!! nl2br(e($settings['text'] ?? $settings['content'] ?? '')) !!}</div>
