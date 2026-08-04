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
@endphp

<div class="pb-video pb-video--{{ $ratio }}">
    <div class="pb-video__wrapper">
        @if($embedUrl)
            <iframe 
                src="{{ $embedUrl }}?@if($autoplay)autoplay=1@endif@if($loop)&loop=1@endif@if($muted)&muted=1@endif"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                @if(!$controls) style="pointer-events: none;"@endif
            ></iframe>
        @elseif($videoType === 'html5' && $videoUrl)
            <video 
                src="{{ $videoUrl }}"
                @if($autoplay)autoplay muted@endif
                @if($loop)loop@endif
                @if($controls)controls@endif
                class="pb-video__html5"
            ></video>
        @endif
    </div>
</div>
