@php
    $videoType = $settings['type'] ?? 'youtube';
    $videoUrl = $settings['url'] ?? '';
    $autoplay = $settings['autoplay'] ?? false;
    $loop = $settings['loop'] ?? false;
    $muted = $settings['muted'] ?? false;
    $controls = $settings['controls'] ?? true;
    $ratio = $settings['ratio'] ?? '16:9';
    
    // Получаем embed URL
    $embedUrl = '';
    if ($videoType === 'youtube') {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\?\/]+)/', $videoUrl, $matches);
        $embedUrl = isset($matches[1]) ? 'https://www.youtube.com/embed/' . $matches[1] : '';
    } elseif ($videoType === 'vimeo') {
        preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches);
        $embedUrl = isset($matches[1]) ? 'https://player.vimeo.com/video/' . $matches[1] : '';
    }
    $embedQuery = http_build_query(array_filter([
        'autoplay' => $autoplay ? 1 : null,
        'loop' => $loop ? 1 : null,
        'muted' => $muted ? 1 : null,
    ], static fn ($value) => $value !== null));
@endphp

<div class="pb-video pb-video--{{ $ratio }}">
    <div class="pb-video__wrapper">
        @if($embedUrl)
            <iframe 
                src="{{ $embedUrl }}{{ $embedQuery !== '' ? '?'.$embedQuery : '' }}"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                @if(! $controls) style="pointer-events: none;" @endif
            ></iframe>
        @elseif($videoType === 'html5' && $videoUrl)
            <video 
                src="{{ $videoUrl }}"
                @if($autoplay) autoplay muted @endif
                @if($loop) loop @endif
                @if($controls) controls @endif
                class="pb-video__html5"
            ></video>
        @else
            <div class="vc-media-placeholder vc-video-placeholder">Video placeholder</div>
        @endif
    </div>
</div>
