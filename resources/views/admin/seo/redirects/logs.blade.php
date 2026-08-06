@extends('admin.layouts.app')

@section('title', 'Логи 404 ошибок')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-clipboard-list text-warning"></i>
                    Логи 404 ошибок
                </h1>
                <a href="{{ route('admin.seo.redirects') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Назад к редиректам
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>URL</th>
                            <th>Referer</th>
                            <th>IP адрес</th>
                            <th>User Agent</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td><code>{{ $log->url }}</code></td>
                            <td>{{ $log->referer ?: '—' }}</td>
                            <td>{{ $log->ip_address ?: '—' }}</td>
                            <td><small class="text-muted">{{ Str::limit($log->user_agent, 50) }}</small></td>
                            <td>{{ $log->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.seo.redirects.store') }}?from_url={{ urlencode($log->url) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Редирект
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Логи не найдены
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
