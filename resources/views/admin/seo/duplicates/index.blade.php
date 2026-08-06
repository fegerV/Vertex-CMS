@extends('admin.layouts.app')

@section('title', 'Поиск дубликатов контента')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-copy text-warning"></i>
                    Поиск дубликатов контента
                </h1>
                <form action="{{ route('admin.seo.duplicates.scan') }}" method="POST" class="d-inline">
                    @csrf
                    <div class="input-group" style="width: 300px;">
                        <select name="similarity" class="form-select">
                            <option value="75">75% схожесть</option>
                            <option value="85" selected>85% схожесть</option>
                            <option value="90">90% схожесть</option>
                            <option value="95">95% схожесть</option>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Сканировать
                        </button>
                    </div>
                </form>
            </div>
            <p class="text-muted">Найдите страницы с похожим контентом для улучшения SEO</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($lastScanAt)
        <div class="alert alert-info">
            <i class="fas fa-clock"></i> Последнее сканирование: {{ $lastScanAt->format('d.m.Y H:i') }}
        </div>
    @endif

    @if(empty($duplicates['by_content']) && empty($duplicates['by_title']) && empty($duplicates['by_meta_description']))
        <div class="alert alert-warning">
            <i class="fas fa-info-circle"></i> 
            Дубликаты не найдены. Нажмите "Сканировать" для проверки.
        </div>
    @else
        <!-- Дубликаты по контенту -->
        @if(!empty($duplicates['by_content']))
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt"></i>
                    Дубликаты контента ({{ count($duplicates['by_content']) }} групп)
                </h5>
            </div>
            <div class="card-body">
                @foreach($duplicates['by_content'] as $group)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Схожесть: {{ $group['similarity'] }}%</strong>
                        <span class="badge bg-danger">{{ count($group['pages']) }} страниц</span>
                    </div>
                    <ul class="list-group">
                        @foreach($group['pages'] as $page)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.pages.edit', $page->id) }}" target="_blank">
                                    {{ $page->title ?: 'Без названия' }}
                                </a>
                                <br>
                                <small class="text-muted"><code>{{ $page->slug }}</code></small>
                            </div>
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Редактировать
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> 
                            Рекомендация: Уникализируйте контент, добавьте больше деталей или объедините страницы.
                        </small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Дубликаты по заголовкам -->
        @if(!empty($duplicates['by_title']))
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0">
                    <i class="fas fa-heading"></i>
                    Дубликаты Title ({{ count($duplicates['by_title']) }} групп)
                </h5>
            </div>
            <div class="card-body">
                @foreach($duplicates['by_title'] as $group)
                <div class="border rounded p-3 mb-3">
                    <div class="mb-2">
                        <strong>Title:</strong> {{ $group['value'] }}
                        <span class="badge bg-warning ms-2">{{ count($group['pages']) }} страниц</span>
                    </div>
                    <ul class="list-group">
                        @foreach($group['pages'] as $page)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.pages.edit', $page->id) }}" target="_blank">
                                    {{ $page->title ?: 'Без названия' }}
                                </a>
                                <br>
                                <small class="text-muted"><code>{{ $page->slug }}</code></small>
                            </div>
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Редактировать
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Дубликаты по Meta Description -->
        @if(!empty($duplicates['by_meta_description']))
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-align-left"></i>
                    Дубликаты Meta Description ({{ count($duplicates['by_meta_description']) }} групп)
                </h5>
            </div>
            <div class="card-body">
                @foreach($duplicates['by_meta_description'] as $group)
                <div class="border rounded p-3 mb-3">
                    <div class="mb-2">
                        <strong>Description:</strong> {{ Str::limit($group['value'], 100) }}
                        <span class="badge bg-info ms-2">{{ count($group['pages']) }} страниц</span>
                    </div>
                    <ul class="list-group">
                        @foreach($group['pages'] as $page)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.pages.edit', $page->id) }}" target="_blank">
                                    {{ $page->title ?: 'Без названия' }}
                                </a>
                                <br>
                                <small class="text-muted"><code>{{ $page->slug }}</code></small>
                            </div>
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Редактировать
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endif

    <!-- Рекомендации -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-lightbulb text-warning"></i> Рекомендации по устранению дубликатов</h5>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>Уникализируйте контент:</strong> Добавьте уникальные описания, изображения, видео.</li>
                <li><strong>Используйте canonical:</strong> Укажите каноническую версию страницы в настройках SEO.</li>
                <li><strong>Объедините страницы:</strong> Если страницы очень похожи, рассмотрите возможность их объединения.</li>
                <li><strong>Настройте 301 редиректы:</strong> Для устаревших страниц с дублирующимся контентом.</li>
                <li><strong>noindex:</strong> Для технических страниц, которые не должны индексироваться.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
