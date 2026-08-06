@extends('admin.layouts.app')

@section('title', 'Все упоминания бренда')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Упоминания бренда в AI</h1>
        <a href="{{ route('admin.seo.ai-monitor.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Назад к дашборду
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Фильтр упоминаний</h6>
                <div class="btn-group">
                    <a href="{{ route('admin.seo.ai-monitor.mentions', ['filter' => 'all']) }}" 
                       class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">Все</a>
                    <a href="{{ route('admin.seo.ai-monitor.mentions', ['filter' => 'positive']) }}" 
                       class="btn btn-sm {{ $filter === 'positive' ? 'btn-success' : 'btn-outline-success' }}">Позитив</a>
                    <a href="{{ route('admin.seo.ai-monitor.mentions', ['filter' => 'neutral']) }}" 
                       class="btn btn-sm {{ $filter === 'neutral' ? 'btn-secondary' : 'btn-outline-secondary' }}">Нейтрально</a>
                    <a href="{{ route('admin.seo.ai-monitor.mentions', ['filter' => 'negative']) }}" 
                       class="btn btn-sm {{ $filter === 'negative' ? 'btn-danger' : 'btn-outline-danger' }}">Негатив</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Модель ИИ</th>
                            <th>Поисковый запрос</th>
                            <th>Тональность</th>
                            <th>Сниппет</th>
                            <th>Источник</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mentions as $mention)
                        <tr>
                            <td>{{ $mention['date'] }}</td>
                            <td><span class="badge badge-info">{{ $mention['model'] }}</span></td>
                            <td>{{ $mention['query'] }}</td>
                            <td>
                                @if($mention['sentiment'] === 'positive')
                                    <span class="badge badge-success"><i class="fas fa-smile"></i> Позитив</span>
                                @elseif($mention['sentiment'] === 'negative')
                                    <span class="badge badge-danger"><i class="fas fa-frown"></i> Негатив</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-meh"></i> Нейтрально</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ Str::limit($mention['snippet'], 80) }}</small></td>
                            <td>
                                @if($mention['is_source'])
                                    <a href="{{ $mention['source_url'] }}" target="_blank" class="btn btn-sm btn-success">
                                        <i class="fas fa-external-link-alt"></i> Источник
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
