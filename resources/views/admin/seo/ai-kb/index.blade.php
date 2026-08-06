@extends('admin.layouts.app')

@section('title', 'AI База Знаний - RAG Консультант')

@section('content')
<div class="container-fluid">
    <!-- Заголовок с описанием -->
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start">
            <i class="fas fa-brain fa-2x text-primary mr-3"></i>
            <div>
                <h5 class="alert-heading mb-2">AI База Знаний (RAG)</h5>
                <p class="mb-0">
                    Система умных консультаций на основе ваших данных. Загрузите документы с ценами, 
                    условиями работы и FAQ — AI будет отвечать клиентам точно по вашей базе знаний.
                </p>
                <hr class="my-2">
                <small class="text-muted">
                    <strong>Как это работает:</strong> Документы разбиваются на фрагменты → создаются векторные эмбеддинги → 
                    при вопросе AI находит релевантные фрагменты → генерирует ответ строго по вашим данным.
                </small>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card stats-card bg-primary text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-folder fa-2x mb-2"></i>
                    <h3>{{ $stats['categories'] }}</h3>
                    <small>Категорий</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card bg-success text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <h3>{{ $stats['documents'] }}</h3>
                    <small>Документов</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card bg-info text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-th-large fa-2x mb-2"></i>
                    <h3>{{ $stats['chunks'] }}</h3>
                    <small>Чанков</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card bg-warning text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3>{{ $stats['processed'] }}</h3>
                    <small>Обработано</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card bg-purple text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-comments fa-2x mb-2"></i>
                    <h3>{{ $stats['chat_sessions'] }}</h3>
                    <small>Сессий чата</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card bg-danger text-white shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-message fa-2x mb-2"></i>
                    <h3>{{ $stats['total_messages'] }}</h3>
                    <small>Сообщений</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Кнопки действий -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold text-primary">Управление базой знаний</h6>
                <div>
                    <a href="{{ route('admin.seo.ai-kb.document.edit') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Добавить документ
                    </a>
                    <a href="{{ route('admin.seo.ai-kb.categories') }}" class="btn btn-outline-secondary btn-sm ml-2">
                        <i class="fas fa-folder"></i> Категории
                    </a>
                    <a href="{{ route('admin.seo.ai-kb.chat-history') }}" class="btn btn-outline-info btn-sm ml-2">
                        <i class="fas fa-comments"></i> История чатов
                    </a>
                    <a href="{{ route('admin.seo.ai-kb.settings') }}" class="btn btn-outline-dark btn-sm ml-2">
                        <i class="fas fa-cog"></i> Настройки
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица документов -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 font-weight-bold">Документы базы знаний</h6>
        </div>
        <div class="card-body p-0">
            @if($documents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 py-3">Название</th>
                            <th class="border-0 py-3">Категория</th>
                            <th class="border-0 py-3">Тип</th>
                            <th class="border-0 py-3">Статус</th>
                            <th class="border-0 py-3">Чанков</th>
                            <th class="border-0 py-3">Дата</th>
                            <th class="border-0 py-3 text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $doc->title }}</div>
                                <small class="text-muted">{{ Str::limit($doc->content, 100) }}</small>
                            </td>
                            <td>
                                @if($doc->category)
                                    <span class="badge badge-secondary">{{ $doc->category->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($doc->file_path)
                                    <span class="badge badge-info">{{ $doc->mime_type }}</span>
                                @else
                                    <span class="badge badge-light">Текст</span>
                                @endif
                            </td>
                            <td>
                                @if($doc->is_processed)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Обработан</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Ожидает</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $doc->chunks->count() }}</span>
                            </td>
                            <td>{{ $doc->created_at->format('d.m.Y H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.seo.ai-kb.document.edit', $doc->id) }}" 
                                   class="btn btn-sm btn-outline-primary" title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.seo.ai-kb.document.reprocess', $doc->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Переобработать">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.seo.ai-kb.document.delete', $doc->id) }}" 
                                      method="POST" class="d-inline" 
                                      onsubmit="return confirm('Удалить документ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="p-3">
                {{ $documents->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">База знаний пуста</h5>
                <p class="text-muted">Добавьте первый документ с информацией о ценах или услугах</p>
                <a href="{{ route('admin.seo.ai-kb.document.edit') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Добавить документ
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .stats-card { transition: transform 0.2s; }
    .stats-card:hover { transform: translateY(-5px); }
    .bg-purple { background-color: #6f42c1 !important; }
</style>
@endpush

@push('scripts')
<script>
    // Автообновление страницы каждые 30 секунд для актуализации статистики
    setTimeout(() => {
        location.reload();
    }, 30000);
</script>
@endpush
@endsection
