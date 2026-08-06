@extends('admin.layouts.app')

@section('title', 'Google Search Console')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fab fa-google text-primary"></i>
                    Google Search Console
                </h1>
                @if(!$isConnected)
                <a href="{{ route('admin.seo.search-console.connect') }}" class="btn btn-primary">
                    <i class="fas fa-link"></i> Подключить
                </a>
                @else
                <button class="btn btn-outline-primary" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Обновить
                </button>
                @endif
            </div>
        </div>
    </div>

    @if(!$isConnected)
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Подключение к Google Search Console</h5>
            <p>Для получения данных о позициях и ошибках индексации необходимо подключить ваш сайт к Google Search Console.</p>
            <ul>
                <li>Кликните "Подключить" для авторизации через Google</li>
                <li>Выберите свой сайт в списке свойств</li>
                <li>Разрешите доступ к данным Search Console</li>
            </ul>
        </div>
    @else
        <!-- Статистика -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Клики</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_clicks']) }}</h3>
                        <small>Всего</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">Показы</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_impressions']) }}</h3>
                        <small>Всего</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">CTR</h6>
                        <h3 class="mb-0">{{ $stats['ctr'] }}%</h3>
                        <small>Средний</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="card-title">Позиция</h6>
                        <h3 class="mb-0">{{ $stats['avg_position'] }}</h3>
                        <small>Средняя</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Поисковые запросы -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Поисковые запросы</h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="fetchQueries()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Запрос</th>
                                        <th>Клики</th>
                                        <th>Показы</th>
                                        <th>CTR</th>
                                        <th>Позиция</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($queries as $query)
                                    <tr>
                                        <td>{{ $query['query'] }}</td>
                                        <td>{{ number_format($query['clicks']) }}</td>
                                        <td>{{ number_format($query['impressions']) }}</td>
                                        <td>{{ $query['ctr'] }}%</td>
                                        <td>
                                            <span class="badge bg-{{ $query['position'] <= 3 ? 'success' : ($query['position'] <= 10 ? 'warning' : 'secondary') }}">
                                                {{ $query['position'] }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            Нет данных. Нажмите "Обновить" для загрузки.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ошибки индексации -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Ошибки</h5>
                        <button class="btn btn-sm btn-outline-danger" onclick="fetchErrors()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($errors as $error)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <span class="badge bg-{{ $error['type'] === '404' ? 'danger' : 'warning' }} me-1">
                                            {{ $error['type'] }}
                                        </span>
                                        {{ $error['url'] }}
                                    </h6>
                                    <small class="text-muted">{{ $error['last_crawled']->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1 text-muted">
                                    <small>Количество: {{ $error['count'] }}</small>
                                </p>
                                <a href="{{ route('admin.seo.redirects.store') }}?from_url={{ urlencode($error['url']) }}" 
                                   class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="fas fa-plus"></i> Создать редирект
                                </a>
                            </div>
                            @empty
                            <div class="list-group-item text-center py-4 text-muted">
                                Ошибок не найдено
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
async function refreshData() {
    await fetchQueries();
    await fetchErrors();
    location.reload();
}

async function fetchQueries() {
    try {
        const response = await fetch('{{ route("admin.seo.search-console.fetch-queries") }}');
        const result = await response.json();
        if (result.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function fetchErrors() {
    try {
        const response = await fetch('{{ route("admin.seo.search-console.fetch-errors") }}');
        const result = await response.json();
        if (result.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
</script>
@endsection
