@extends('admin.layouts.app')

@section('title', 'AI Генерация Alt для изображений')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-magic text-primary"></i>
                AI Генерация Alt-текстов для изображений
            </h1>
            <p class="text-muted">Автоматическое создание описаний для изображений с помощью AI</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Всего страниц</h5>
                    <h2 class="mb-0">{{ $pages->count() }}</h2>
                    <small>Проанализировано</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Изображений без Alt</h5>
                    <h2 class="mb-0">
                        {{ collect($imagesWithoutAlt)->sum('count') }}
                    </h2>
                    <small>Требуют внимания</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Список страниц с изображениями без alt -->
    @if(count($imagesWithoutAlt) > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Страницы с изображениями без Alt</h5>
        </div>
        <div class="card-body">
            @foreach($imagesWithoutAlt as $item)
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h6 class="mb-0">
                            <a href="{{ route('admin.pages.edit', $item['page']->id) }}" target="_blank">
                                {{ $item['page']->title ?: 'Без названия' }}
                            </a>
                        </h6>
                        <small class="text-muted"><code>{{ $item['page']->slug }}</code></small>
                    </div>
                    <div>
                        <span class="badge bg-warning">{{ $item['count'] }} изображений</span>
                        <button class="btn btn-sm btn-primary" onclick="bulkGenerate({{ $item['page']->id }})">
                            <i class="fas fa-magic"></i> Сгенерировать все
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Изображение</th>
                                <th>Текущий Alt</th>
                                <th>Сгенерированный Alt</th>
                                <th>Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item['images'] as $image)
                            <tr data-page-id="{{ $item['page']->id }}" data-image-src="{{ $image['src'] }}">
                                <td style="max-width: 200px;">
                                    <img src="{{ $image['src'] }}" alt="" style="height: 50px;" class="rounded me-2">
                                    <small class="text-muted d-block" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $image['src'] }}
                                    </small>
                                </td>
                                <td>
                                    @if(empty($image['alt']))
                                        <span class="badge bg-danger">Отсутствует</span>
                                    @else
                                        {{ $image['alt'] }}
                                    @endif
                                </td>
                                <td>
                                    <span class="generated-alt text-muted">—</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="generateAlt(this, '{{ $image['src'] }}', {{ $item['page']->id }})">
                                        <i class="fas fa-wand-magic-sparkles"></i> Генерировать
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Все изображения имеют Alt-тексты! Отличная работа.
    </div>
    @endif

    <!-- Рекомендации -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-lightbulb text-warning"></i> Рекомендации по Alt-текстам</h5>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>Будьте описательными:</strong> Alt должен точно описывать содержимое изображения.</li>
                <li><strong>Используйте ключевые слова:</strong> Включайте релевантные ключевые слова, но без спама.</li>
                <li><strong>Оптимальная длина:</strong> 100-125 символов для лучших результатов.</li>
                <li><strong>Не начинайте с "Изображение...":</strong> Скринридеры уже объявляют элемент как изображение.</li>
                <li><strong>Декоративные изображения:</strong> Используйте пустой alt="" для декоративных элементов.</li>
            </ul>
        </div>
    </div>
</div>

<script>
async function generateAlt(button, imageUrl, pageId) {
    const row = button.closest('tr');
    const altCell = row.querySelector('.generated-alt');
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    try {
        const response = await fetch('{{ route("admin.seo.ai-images.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                image_url: imageUrl,
                page_id: pageId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            altCell.textContent = result.alt_text;
            altCell.classList.remove('text-muted');
            altCell.classList.add('text-success');
            
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-success');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Ошибка генерации');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> Генерировать';
    }
}

async function bulkGenerate(pageId) {
    if (!confirm('Сгенерировать Alt-тексты для всех изображений на этой странице?')) {
        return;
    }
    
    const rows = document.querySelectorAll(`tr[data-page-id="${pageId}"]`);
    let processed = 0;
    
    for (const row of rows) {
        const button = row.querySelector('button[onclick^="generateAlt"]');
        const imageUrl = row.dataset.imageSrc;
        
        if (button) {
            await generateAlt(button, imageUrl, pageId);
            processed++;
        }
    }
    
    alert(`Обработано ${processed} изображений!`);
}
</script>
@endsection
