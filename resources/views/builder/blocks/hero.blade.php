@php
    $background = $settings['background'] ?? '';
    $title = $settings['title'] ?? '';
    $subtitle = $settings['subtitle'] ?? '';
    $buttonText = $settings['button_text'] ?? '';
    $buttonUrl = $settings['button_url'] ?? '#';
    $buttonTarget = $settings['button_target'] ?? '_self';
    $titleColor = $settings['title_color'] ?? '#ffffff';
    $subtitleColor = $settings['subtitle_color'] ?? '#ffffff';
    $buttonBgColor = $settings['button_bg_color'] ?? '#3b82f6';
    $buttonTextColor = $settings['button_text_color'] ?? '#ffffff';
    $buttonBorderColor = $settings['button_border_color'] ?? 'transparent';
    $paddingTop = (int) ($settings['padding_top'] ?? 80);
    $paddingBottom = (int) ($settings['padding_bottom'] ?? 80);

    $style = trim(collect([
        $background ? "background-image: url('{$background}')" : null,
        $background ? 'background-size: cover' : null,
        $background ? 'background-position: center' : null,
        "padding-top: {$paddingTop}px",
        "padding-bottom: {$paddingBottom}px",
        'text-align: center',
        'color: white',
    ])->filter()->implode('; '));
@endphp

<section class="vc-hero" style="{{ $style }}">
    <div class="vc-hero-content">
        @if ($title !== '')
            <h1 class="vc-hero-title" style="color: {{ $titleColor }}; margin-bottom: 0.5rem;">{{ $title }}</h1>
        @endif

        @if ($subtitle !== '')
            <p class="vc-hero-subtitle" style="color: {{ $subtitleColor }}; margin-bottom: 1.5rem;">{{ $subtitle }}</p>
        @endif

        @if ($buttonText !== '')
            <a
                href="{{ $buttonUrl }}"
                target="{{ $buttonTarget }}"
                class="vc-hero-button"
                style="display: inline-block; background-color: {{ $buttonBgColor }}; color: {{ $buttonTextColor }}; border: 2px solid {{ $buttonBorderColor }}; padding: 0.75rem 1.5rem; text-decoration: none; font-weight: 500; border-radius: 0.375rem;"
            >{{ $buttonText }}</a>
        @endif
    </div>
</section>
