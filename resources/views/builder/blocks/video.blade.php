@php
    $type = $settings['type'] ?? 'youtube';
    $url = $settings['url'] ?? '';
    $ratio = $settings['ratio'] ?? '16:9';
    
    $ratios = [
        '16:9' => 'aspect-video',
        '4:3' => 'aspect-square',
        '1:1' => 'aspect-square',
        '21:9' => 'aspect-[21/9]',
    ];
    
    $ratioClass = $ratios[$ratio] ?? 'aspect-video';
@endphp

<div class="vc-video w-full {{ $ratioClass }}">
    @if($url === '')
        <div class="vc-media-placeholder vc-video-placeholder flex h-full items-center justify-center bg-gray-100 text-gray-500">Video placeholder</div>
    @elseif($type === 'youtube')
        @php
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
            $id = $matches[1] ?? '';
        @endphp
        @if($id)
            <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $id }}" frameborder="0" allowfullscreen></iframe>
        @else
            <div class="flex items-center justify-center bg-gray-100 text-gray-500 h-full">Invalid YouTube URL</div>
        @endif
    @elseif($type === 'vimeo')
        @php
            preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $url, $matches);
            $id = $matches[1] ?? '';
        @endphp
        @if($id)
            <iframe class="w-full h-full" src="https://player.vimeo.com/video/{{ $id }}" frameborder="0" allowfullscreen></iframe>
        @else
            <div class="flex items-center justify-center bg-gray-100 text-gray-500 h-full">Invalid Vimeo URL</div>
        @endif
    @else
        <video src="{{ $url }}" class="w-full h-full" controls></video>
    @endif
</div>
