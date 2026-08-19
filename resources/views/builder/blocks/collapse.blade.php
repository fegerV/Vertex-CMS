{{-- resources/views/builder/blocks/collapse.blade.php --}}
@php
    $title = $settings['title'] ?? 'Заголовок';
    $content = $settings['content'] ?? 'Скрытый контент...';
    $open = $settings['open'] ?? false;
    $cssClass = $settings['css_class'] ?? '';
    $uniqueId = 'collapse-' . uniqid();
@endphp

<div class="vc-collapse {{ $cssClass }}" id="{{ $uniqueId }}">
    <button class="w-full flex justify-between items-center p-4 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors"
            onclick="document.getElementById('{{ $uniqueId }}-content').classList.toggle('hidden'); this.querySelector('.chevron').style.transform = this.querySelector('.chevron').style.transform === 'rotate(180deg)' ? 'rotate(0deg)' : 'rotate(180deg)';">
        <span class="font-medium">{{ $title }}</span>
        <span class="chevron transition-transform {{ $open ? '' : '-rotate-90' }}" style="transition: transform 0.3s;">▼</span>
    </button>
    <div id="{{ $uniqueId }}-content" class="{{ $open ? '' : 'hidden' }} p-4">
        <div class="text-slate-600">{{ $content }}</div>
    </div>
</div>
