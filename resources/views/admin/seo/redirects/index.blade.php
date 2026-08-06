@extends('admin.layouts.app')

@section('title', '404 Monitor и Редиректы')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-exchange-alt text-primary"></i>
                    404 Monitor и Редиректы
                </h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRedirectModal">
                    <i class="fas fa-plus"></i> Добавить редирект
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">404 Ошибки</h5>
                    <h2 class="mb-0">{{ number_format($error404Count) }}</h2>
                    <small>Всего зафиксировано</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Активные редиректы</h5>
                    <h2 class="mb-0">{{ number_format($redirects->total()) }}</h2>
                    <small>Настроено правил</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Быстрые действия</h5>
                    <form action="{{ route('admin.seo.redirects.import-404') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-light btn-sm">
                            <i class="fas fa-download"></i> Импорт 404 в редиректы
                        </button>
                    </form>
                    <a href="{{ route('admin.seo.redirects.logs') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-list"></i> Все логи
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица редиректов -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Список редиректов</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Откуда (From)</th>
                            <th>Куда (To)</th>
                            <th>Тип</th>
                            <th>Статус</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($redirects as $redirect)
                        <tr>
                            <td><code>{{ $redirect->from_url }}</code></td>
                            <td><code>{{ $redirect->to_url }}</code></td>
                            <td>
                                <span class="badge bg-{{ $redirect->type === 301 ? 'primary' : 'warning' }}">
                                    {{ $redirect->type }}
                                </span>
                            </td>
                            <td>
                                @if($redirect->is_active)
                                    <span class="badge bg-success">Активен</span>
                                @else
                                    <span class="badge bg-secondary">Неактивен</span>
                                @endif
                            </td>
                            <td>{{ $redirect->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <form action="{{ route('admin.seo.redirects.destroy', $redirect->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить редирект?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Редиректы не настроены
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($redirects->hasPages())
        <div class="card-footer">
            {{ $redirects->links() }}
        </div>
        @endif
    </div>

    <!-- Последние 404 ошибки -->
    @if($recent404s->count() > 0)
    <div class="card mt-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0">Последние 404 ошибки</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>URL</th>
                            <th>Referer</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent404s as $log)
                        <tr>
                            <td><code>{{ $log->url }}</code></td>
                            <td>{{ $log->referer ?: '—' }}</td>
                            <td>{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Модальное окно добавления редиректа -->
<div class="modal fade" id="addRedirectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.seo.redirects.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Добавить редирект</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">URL откуда *</label>
                        <input type="text" name="from_url" class="form-control" placeholder="/old-page" required>
                        <small class="text-muted">Например: /old-page или https://site.com/old-page</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL куда *</label>
                        <input type="text" name="to_url" class="form-control" placeholder="/new-page" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип редиректа *</label>
                        <select name="type" class="form-select">
                            <option value="301">301 - Постоянный (Permanent)</option>
                            <option value="302">302 - Временный (Temporary)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
