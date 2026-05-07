@php
    $items = $settings['items'] ?? [];
    $separator = $settings['separator'] ?? '/';
@endphp

<nav class="vc-breadcrumbs flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        @foreach($items as $index => $item)
            <li class="inline-flex items-center">
                @if($index > 0)
                    <span class="mx-2 text-gray-400">{{ $separator }}</span>
                @endif
                
                @if(!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        {{ $item['title'] ?? '' }}
                    </a>
                @else
                    <span class="text-sm font-medium text-gray-500">
                        {{ $item['title'] ?? '' }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
