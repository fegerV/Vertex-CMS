{{-- resources/views/builder/blocks/columns.blade.php --}}
@php
    $count = $settings['count'] ?? 3;
    $gap = $settings['gap'] ?? 'md';
    $columns = $settings['columns'] ?? [];
    $cssClass = $settings['css_class'] ?? '';
    $bgColor = $settings['background_color'] ?? '';
    
    $gapClasses = [
        'sm' => 'gap-2',
        'md' => 'gap-4',
        'lg' => 'gap-6',
    ];
@endphp

<div class="vc-columns {{ $gapClasses[$gap] ?? 'gap-4' }} {{ $cssClass }}" 
     style="{{ $bgColor ? 'background-color: ' . $bgColor . ';' : '' }}">
    @foreach(array_slice($columns, 0, $count) as $index => $column)
        <div class="vc-column flex-1 min-w-0" 
             style="flex: 0 0 {{ ($column['width'] ?? (12 / $count)) / 12 * 100 }}%;">
            <div class="h-full">
                @if(isset($column['blocks']) && is_array($column['blocks']))
                    @foreach($column['blocks'] as $nestedBlock)
                        @includeIf('builder.blocks.' . $nestedBlock['_type'], ['settings' => $nestedBlock['settings'] ?? []])
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach
</div>

<style>
.vc-columns {
    display: flex;
    flex-wrap: wrap;
}
.vc-column {
    padding: 0;
}
@media (max-width: 768px) {
    .vc-column {
        flex: 0 0 100% !important;
    }
}
</style>
