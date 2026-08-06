@extends('admin.layouts.app')

@section('title', 'Карты ключевых слов - Авто-линки')

@section('content')
<div class="container-fluid">
    <!-- Заголовок и действия -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-link me-2"></i>Карты ключевых слов
        </h1>
        <div>
            <a href="{{ route('admin.seo.keyword-maps.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Создать карту
            </a>
            <button class="btn btn-success" onclick="syncKeywords()">
                <i class="fas fa-sync me-1"></i>Синхронизировать
            </button>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Всего карт</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Активные</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Неактивные</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactive'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Авто-линки</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['auto_link'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bolt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица карт ключевых слов -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Список карт ключевых слов</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="bulkActions" data-bs-toggle="dropdown">
                    Массовые действия
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('enable')"><i class="fas fa-play me-2"></i>Включить</a></li>
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('disable')"><i class="fas fa-pause me-2"></i>Выключить</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            @if($keywordMaps->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="keywordMapsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">
                                <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                            </th>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Целевой URL</th>
                            <th>Ключевых слов</th>
                            <th>AI вариантов</th>
                            <th>Макс. ссылок</th>
                            <th>Статус</th>
                            <th>Авто-линки</th>
                            <th width="15%">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keywordMaps as $map)
                        <tr>
                            <td>
                                <input type="checkbox" class="map-checkbox" value="{{ $map->id }}">
                            </td>
                            <td>{{ $map->id }}</td>
                            <td>
                                <strong>{{ $map->name }}</strong>
                                @if($map->description)
                                <br><small class="text-muted">{{ Str::limit($map->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ $map->target_url }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                                    {{ $map->target_url }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ count($map->keywords ?? []) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ count($map->ai_variants ?? []) }}</span>
                            </td>
                            <td>{{ $map->max_links_per_post }}</td>
                            <td>
                                @if($map->is_enabled)
                                <span class="badge bg-success">Активно</span>
                                @else
                                <span class="badge bg-secondary">Неактивно</span>
                                @endif
                            </td>
                            <td>
                                @if($map->auto_link_on_publish)
                                <span class="badge bg-success"><i class="fas fa-bolt"></i></span>
                                @else
                                <span class="badge bg-secondary"><i class="fas fa-pause"></i></span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.seo.keyword-maps.edit', $map) }}" class="btn btn-outline-primary" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-info" onclick="generateAiVariants({{ $map->id }})" title="Генерировать AI варианты">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="previewLinks({{ $map->id }})" title="Предпросмотр">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <form action="{{ route('admin.seo.keyword-maps.destroy', $map) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить эту карту ключевых слов?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            <div class="d-flex justify-content-center">
                {{ $keywordMaps->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-map fa-4x text-gray-300 mb-3"></i>
                <h5 class="text-muted">Карты ключевых слов не созданы</h5>
                <p class="text-muted">Создайте первую карту для автоматической простановки ссылок</p>
                <a href="{{ route('admin.seo.keyword-maps.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Создать карту
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Модальное окно предпросмотра -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Предпросмотр авто-ссылок</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Текст контента для проверки:</label>
                        <textarea class="form-control" id="previewContent" rows="5" placeholder="Введите текст для проверки..."></textarea>
                    </div>
                    <div id="previewResult" class="border rounded p-3 bg-light" style="display:none;">
                        <h6>Результат:</h6>
                        <div id="previewOutput"></div>
                        <hr>
                        <p class="mb-0"><strong>Добавлено ссылок:</strong> <span id="linksCount">0</span> из <span id="maxLinks">0</span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" onclick="runPreview()">Проверить</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentMapId = null;

function toggleAll(source) {
    document.querySelectorAll('.map-checkbox').forEach(cb => cb.checked = source.checked);
}

function bulkAction(action) {
    const selected = document.querySelectorAll('.map-checkbox:checked');
    if (selected.length === 0) {
        alert('Выберите хотя бы одну карту');
        return;
    }
    
    if (!confirm(`Вы уверены, что хотите ${action === 'enable' ? 'включить' : 'выключить'} выбранные карты?`)) {
        return;
    }
    
    const ids = Array.from(selected).map(cb => cb.value);
    
    fetch('{{ route('admin.seo.keyword-maps.bulk-toggle') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ids, action })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при выполнении действия');
    });
}

function generateAiVariants(mapId) {
    if (!confirm('Сгенерировать AI-варианты для этой карты? Это может занять несколько секунд.')) {
        return;
    }
    
    fetch(`/admin/seo/keyword-maps/${mapId}/generate-ai`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Успешно сгенерировано ${data.count} AI-вариантов!`);
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при генерации вариантов');
    });
}

function previewLinks(mapId) {
    currentMapId = mapId;
    document.getElementById('previewModal').querySelector('.modal-title').textContent = `Предпросмотр (Карта #${mapId})`;
    document.getElementById('previewResult').style.display = 'none';
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

function runPreview() {
    const content = document.getElementById('previewContent').value;
    if (!content.trim()) {
        alert('Введите текст для проверки');
        return;
    }
    
    fetch('{{ route('admin.seo.keyword-maps.preview') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            content: content,
            keyword_map_id: currentMapId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('previewOutput').innerHTML = data.modified;
            document.getElementById('linksCount').textContent = data.links_added;
            document.getElementById('maxLinks').textContent = data.max_links;
            document.getElementById('previewResult').style.display = 'block';
        } else {
            alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при предпросмотре');
    });
}

function syncKeywords() {
    // Здесь можно добавить логику синхронизации с внешними источниками
    alert('Функция синхронизации будет доступна в следующей версии');
}
</script>
@endpush

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@endsection
