{{-- resources/views/builder/blocks/breadcrumbs.blade.php --}}
@php
    $items = $settings['items'] ?? [['title' => 'Главная', 'url' => '/'], ['title' => 'Текущая страница', 'url' => null]];
    $separator = $settings['separator'] ?? '/';
    $cssClass = $settings['css_class'] ?? '';
@endphp

<nav class="vc-breadcrumbs {{ $cssClass }}" aria-label="Хлебные крошки">
    <ol class="flex items-center space-x-2 text-sm">
        @foreach($items as $index => $item)
            <li class="flex items-center">
                @if($index > 0)
                    <span class="mx-2 text-slate-400">{{ $separator }}</span>
                @endif
                @if(isset($item['url']) && $item['url'] !== null && $index < count($items) - 1)
                    <a href="{{ $item['url'] }}" class="text-blue-600 hover:underline">{{ $item['title'] }}</a>
                @else
                    <span class="text-slate-500">{{ $item['title'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
