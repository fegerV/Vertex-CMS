@extends('admin.layouts.app')

@section('title', 'Редактирование документа - AI База Знаний')

@section('content')
<div class="container-fluid">
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start">
            <i class="fas fa-file-alt fa-2x text-primary mr-3"></i>
            <div>
                <h5 class="alert-heading mb-2">{{ $mode === 'create' ? 'Новый документ' : 'Редактирование документа' }}</h5>
                <p class="mb-0">
                    Добавьте информацию о ценах, услугах или условиях работы. Документ будет автоматически разбит на фрагменты 
                    и использован для ответов AI-консультанта. Поддерживаются форматы: TXT, PDF, DOCX.
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.seo.ai-kb.document.save') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($mode === 'edit')
            <input type="hidden" name="id" value="{{ $document->id }}">
        @endif

        <div class="row">
            <!-- Основная колонка -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 font-weight-bold">Содержимое документа</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title" class="font-weight-bold">Заголовок *</label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $document->title) }}"
                                   placeholder="Например: Прайс-лист на услуги 2024"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Краткое название документа для удобного поиска в админке
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="content" class="font-weight-bold">Текст документа *</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="15"
                                      placeholder="Введите текст с ценами, условиями или FAQ..."
                                      required>{{ old('content', $document->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Полный текст документа. Будет автоматически разбит на фрагменты по 500 символов
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Или загрузите файл</label>
                            <div class="custom-file">
                                <input type="file" 
                                       class="custom-file-input @error('file') is-invalid @enderror" 
                                       id="file" 
                                       name="file"
                                       accept=".txt,.pdf,.docx,.doc">
                                <label class="custom-file-label" for="file" data-browse="Выбрать">
                                    {{ $document->file_path ? 'Файл загружен: ' . basename($document->file_path) : 'Выберите файл (TXT, PDF, DOCX)' }}
                                </label>
                            </div>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted d-block mt-2">
                                Текст будет автоматически извлечен из файла. 
                                <br><strong>Примечание:</strong> Для PDF требуется установка pdftotext.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Боковая колонка -->
            <div class="col-lg-4">
                <!-- Категория -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 font-weight-bold">Категория</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="category_id">Выберите категорию</label>
                            <select class="form-control @error('category_id') is-invalid @enderror" 
                                    id="category_id" 
                                    name="category_id">
                                <option value="">Без категории</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" 
                                            {{ old('category_id', $document->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <a href="{{ route('admin.seo.ai-kb.categories') }}" class="btn btn-outline-primary btn-block btn-sm">
                            <i class="fas fa-plus"></i> Создать новую категорию
                        </a>
                    </div>
                </div>

                <!-- Информация -->
                @if($mode === 'edit')
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 font-weight-bold">Информация</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Статус:</small>
                            @if($document->is_processed)
                                <span class="badge badge-success">Обработан</span>
                            @else
                                <span class="badge badge-warning">Требует обработки</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Чанков:</small>
                            <span class="badge badge-primary">{{ $document->chunks->count() }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Слов:</small>
                            <span class="badge badge-secondary">{{ str_word_count($document->content) }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Создан:</small>
                            <span class="text-dark">{{ $document->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Кнопки действий -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block btn-lg mb-3">
                            <i class="fas fa-save"></i> {{ $mode === 'create' ? 'Создать и обработать' : 'Сохранить и переобработать' }}
                        </button>
                        <a href="{{ route('admin.seo.ai-kb.index') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-arrow-left"></i> Назад к списку
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Обработка выбора файла для отображения имени
    document.getElementById('file').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'Выберите файл';
        var label = document.querySelector('.custom-file-label');
        label.textContent = fileName;
    });

    // Предупреждение при уходе со страницы без сохранения
    @if($mode === 'edit')
    let formChanged = false;
    document.querySelector('form').addEventListener('change', () => formChanged = true);
    window.addEventListener('beforeunload', (e) => {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    @endif
</script>
@endpush
@endsection
