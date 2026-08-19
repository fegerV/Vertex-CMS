@php
    $type = $settings['type'] ?? 'disc';
    $listTag = ($type === 'decimal') ? 'ol' : 'ul';
    $listClasses = [
        'disc' => 'list-disc',
        'decimal' => 'list-decimal',
        'none' => 'list-none',
    ];
    $listClass = $listClasses[$type] ?? 'list-disc';
@endphp

<{{ $listTag }} class="vc-list {{ $listClass }} ml-6 space-y-1">
    @foreach($settings['items'] ?? [] as $item)
        <li class="vc-list-item">
            {{ is_array($item) ? ($item['content'] ?? '') : $item }}
        </li>
    @endforeach
</{{ $listTag }}>
