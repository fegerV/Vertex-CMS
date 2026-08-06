@extends('admin.layouts.app')

@section('title', 'Анализатор Изображений')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">SEO Анализатор Изображений</h1>
        <div>
            <button class="btn btn-primary btn-sm" onclick="enableLazyLoad()">
                <i class="fas fa-bolt"></i> Включить Lazy Load
            </button>
            <button class="btn btn-success btn-sm" onclick="generateSitemap()">
                <i class="fas fa-sitemap"></i> Sitemap для картинок
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Всего изображений</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-images fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Без ALT тега</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['no_alt'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Тяжелые файлы (>500KB)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['large_files'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-weight-hanging fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Общий размер</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_size'] }}</div>
                            <div class="text-xs text-muted">Потенциальная экономия: {{ $stats['potential_save'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-hard-drive fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Прогресс бар SEO Score -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">SEO Score Изображений</h6>
            <div class="dropdown no-arrow">
                <span class="badge {{ $stats['score'] >= 80 ? 'badge-success' : ($stats['score'] >= 50 ? 'badge-warning' : 'badge-danger') }} p-2">
                    {{ $stats['score'] }}/100
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="progress mb-3" style="height: 30px;">
                <div class="progress-bar {{ $stats['score'] >= 80 ? 'bg-success' : ($stats['score'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                     role="progressbar" style="width: {{ $stats['score'] }}%;" 
                     aria-valuenow="{{ $stats['score'] }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $stats['score'] }}%
                </div>
            </div>
            <p class="text-muted small">Рассчитывается на основе наличия ALT-тегов у всех изображений.</p>
        </div>
    </div>

    <!-- Таблица изображений -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Список изображений для оптимизации</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.seo.images.update-alt') }}" method="POST" id="altForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAll" onclick="toggleAll(this)">
                                </th>
                                <th>Превью</th>
                                <th>Файл</th>
                                <th>Размер</th>
                                <th>Тип</th>
                                <th>ALT Текст</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($images as $index => $img)
                            <tr class="{{ !$img['has_alt'] || $img['needs_optimization'] ? 'table-warning' : '' }}">
                                <td>
                                    <input type="checkbox" name="images[]" value="{{ $img['path'] }}" class="img-checkbox">
                                    <input type="hidden" name="alt_values[{{ $index }}]" id="alt-input-{{ $index }}" value="{{ $img['alt_text'] }}">
                                </td>
                                <td>
                                    <img src="{{ $img['url'] }}" alt="preview" style="max-width: 80px; max-height: 60px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td>
                                    <small class="font-weight-bold">{{ $img['filename'] }}</small><br>
                                    <small class="text-muted">{{ Str::limit($img['path'], 40) }}</small>
                                </td>
                                <td>{{ $img['size_human'] }}</td>
                                <td><span class="badge badge-secondary">{{ strtoupper($img['extension']) }}</span></td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           id="alt-text-{{ $index }}" 
                                           value="{{ $img['alt_text'] }}" 
                                           placeholder="Нет ALT"
                                           onchange="document.getElementById('alt-input-{{ $index }}').value = this.value">
                                </td>
                                <td>
                                    @if(!$img['has_alt'])
                                        <span class="badge badge-danger">Нет ALT</span>
                                    @endif
                                    @if($img['needs_optimization'])
                                        <span class="badge badge-warning">Нужно сжать</span>
                                    @endif
                                    @if($img['has_alt'] && !$img['needs_optimization'])
                                        <span class="badge badge-success">OK</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" onclick="generateAiAlt({{ $index }}, '{{ $img['path'] }}')">
                                        <i class="fas fa-magic"></i> AI
                                    </button>
                                    @if($img['needs_optimization'])
                                        <button type="button" class="btn btn-sm btn-primary" onclick="compressImage('{{ $img['path'] }}')">
                                            <i class="fas fa-compress-arrows-alt"></i> Сжать
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Сохранить все ALT теги
                    </button>
                    <button type="button" class="btn btn-primary" onclick="bulkCompress()">
                        <i class="fas fa-compress-arrows-alt"></i> Сжать выбранные
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAll(source) {
    checkboxes = document.getElementsByClassName('img-checkbox');
    for(c in checkboxes) {
        checkboxes[c].checked = typeof checkboxes[c] != 'undefined' ? source.checked : '';
    }
}

function generateAiAlt(index, path) {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('{{ route("admin.seo.images.generate-alt") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ path: path })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            document.getElementById('alt-text-' + index).value = data.alt;
            document.getElementById('alt-input-' + index).value = data.alt;
            alert('AI сгенерировал: ' + data.alt);
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic"></i> AI';
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic"></i> AI';
    });
}

function compressImage(path) {
    if(!confirm('Сжать это изображение в WebP?')) return;
    
    fetch('{{ route("admin.seo.images.compress") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ paths: [path] })
    })
    .then(response => {
        if(response.ok) window.location.reload();
    });
}

function bulkCompress() {
    let paths = [];
    document.querySelectorAll('.img-checkbox:checked').forEach(cb => {
        paths.push(cb.value);
    });
    
    if(paths.length === 0) {
        alert('Выберите изображения для сжатия');
        return;
    }
    
    if(!confirm('Сжать ' + paths.length + ' изображений в WebP?')) return;
    
    fetch('{{ route("admin.seo.images.compress") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ paths: paths })
    })
    .then(response => {
        if(response.ok) window.location.reload();
    });
}

function enableLazyLoad() {
    if(!confirm('Применить Lazy Load ко всем изображениям на сайте? Это может занять время.')) return;
    
    fetch('{{ route("admin.seo.images.lazy-load") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if(response.ok) window.location.reload();
    });
}

function generateSitemap() {
    if(!confirm('Сгенерировать Sitemap для изображений?')) return;
    
    fetch('{{ route("admin.seo.images.sitemap") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if(response.ok) window.location.reload();
    });
}
</script>
@endsection
