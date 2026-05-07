@php
    $count = $settings['count'] ?? 2;
    $gap = $settings['gap'] ?? 'md';
    
    $gaps = [
        'sm' => 'gap-2',
        'md' => 'gap-4',
        'lg' => 'gap-8',
    ];
    
    $gridCols = [
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-4',
    ];
@endphp

<div class="vc-columns grid {{ $gridCols[$count] ?? 'grid-cols-1' }} {{ $gaps[$gap] ?? 'gap-4' }}">
    @foreach($settings['columns'] ?? [] as $column)
        <div class="vc-column">
            @foreach($column['blocks'] ?? [] as $block)
                {!! app(\App\Builder\Services\PageBuilderService::class)->compileBlock($block['type'] ?? 'unknown', $block['settings'] ?? []) !!}
            @endforeach
        </div>
    @endforeach
</div>
