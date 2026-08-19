@extends('admin.layouts.app')

@section('title', 'Конструктор Schema.org')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-code text-primary"></i>
                Конструктор Schema.org (JSON-LD)
            </h1>
            <p class="text-muted">Создавайте микроразметку для улучшенного отображения в поиске</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Форма создания -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Параметры разметки</h5>
                </div>
                <div class="card-body">
                    <form id="schemaForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Тип схемы *</label>
                            <select name="type" id="schemaType" class="form-select" required>
                                <option value="">Выберите тип...</option>
                                <option value="Organization">Organization (Организация)</option>
                                <option value="LocalBusiness">LocalBusiness (Местный бизнес)</option>
                                <option value="Product">Product (Товар)</option>
                                <option value="Article">Article (Статья)</option>
                                <option value="FAQPage">FAQPage (Вопросы и ответы)</option>
                                <option value="BreadcrumbList">BreadcrumbList (Хлебные крошки)</option>
                                <option value="WebSite">WebSite (Веб-сайт)</option>
                                <option value="Person">Person (Персона)</option>
                            </select>
                        </div>

                        <!-- Динамические поля будут здесь -->
                        <div id="dynamicFields"></div>

                        <div class="mb-3">
                            <label class="form-label">Привязка к странице</label>
                            <select name="page_id" id="pageId" class="form-select">
                                <option value="">Не привязывать</option>
                                @foreach($pages as $page)
                                    <option value="{{ $page->id }}">{{ $page->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-magic"></i> Сгенерировать
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Предпросмотр -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Результат (JSON-LD)</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyResult()">
                        <i class="fas fa-copy"></i> Копировать
                    </button>
                </div>
                <div class="card-body">
                    <pre id="schemaResult" class="bg-dark text-light p-3 rounded" style="min-height: 300px; overflow-x: auto;">// Здесь появится сгенерированная разметка</pre>
                    
                    <form id="saveForm" method="POST" action="{{ route('admin.seo.schema-builder.save') }}" class="mt-3" style="display: none;">
                        @csrf
                        <input type="hidden" name="schema_json" id="schemaJson">
                        <input type="hidden" name="schema_type" id="schemaTypeHidden">
                        <input type="hidden" name="page_id" id="schemaPageId">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Сохранить на страницу
                        </button>
                    </form>
                </div>
            </div>

            <!-- Пример использования -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Как использовать</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Выберите тип схемы</li>
                        <li>Заполните поля</li>
                        <li>Нажмите "Сгенерировать"</li>
                        <li>Скопируйте JSON-LD код</li>
                        <li>Добавьте на страницу в секцию <code>&lt;head&gt;</code></li>
                    </ol>
                    <hr>
                    <h6>Проверка валидности:</h6>
                    <a href="https://search.google.com/structured-data/testing-tool" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-external-link-alt"></i> Google Structured Data Testing Tool
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const schemaFields = {
    Organization: `
        <div class="mb-3"><label class="form-label">Название</label><input type="text" name="data[name]" class="form-control field-input" value="{{ config('app.name') }}"></div>
        <div class="mb-3"><label class="form-label">URL сайта</label><input type="url" name="data[url]" class="form-control field-input" value="{{ config('app.url') }}"></div>
        <div class="mb-3"><label class="form-label">Логотип (URL)</label><input type="url" name="data[logo]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Телефон</label><input type="text" name="data[phone]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Facebook</label><input type="url" name="data[facebook]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Twitter</label><input type="url" name="data[twitter]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">VK</label><input type="url" name="data[vk]" class="form-control field-input"></div>
    `,
    LocalBusiness: `
        <div class="mb-3"><label class="form-label">Название</label><input type="text" name="data[name]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Адрес (улица)</label><input type="text" name="data[street_address]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Город</label><input type="text" name="data[city]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Индекс</label><input type="text" name="data[postal_code]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Страна</label><input type="text" name="data[country]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Телефон</label><input type="text" name="data[phone]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Часы работы</label><input type="text" name="data[opening_hours]" class="form-control field-input" placeholder="Mo-Fr 09:00-18:00"></div>
    `,
    Product: `
        <div class="mb-3"><label class="form-label">Название товара</label><input type="text" name="data[name]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Описание</label><textarea name="data[description]" class="form-control field-input"></textarea></div>
        <div class="mb-3"><label class="form-label">Цена</label><input type="number" name="data[price]" class="form-control field-input" step="0.01"></div>
        <div class="mb-3"><label class="form-label">Валюта</label><input type="text" name="data[currency]" class="form-control field-input" value="RUB"></div>
        <div class="mb-3"><label class="form-label">Бренд</label><input type="text" name="data[brand]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Рейтинг (0-5)</label><input type="number" name="data[rating]" class="form-control field-input" min="0" max="5" step="0.1"></div>
        <div class="mb-3"><label class="form-label">Количество отзывов</label><input type="number" name="data[review_count]" class="form-control field-input"></div>
    `,
    Article: `
        <div class="mb-3"><label class="form-label">Заголовок</label><input type="text" name="data[headline]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Изображение (URL)</label><input type="url" name="data[image]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Автор</label><input type="text" name="data[author]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Издатель</label><input type="text" name="data[publisher]" class="form-control field-input" value="{{ config('app.name') }}"></div>
    `,
    FAQPage: `
        <div class="mb-3">
            <label class="form-label">Вопросы и ответы</label>
            <div id="faqContainer">
                <div class="faq-item border p-2 mb-2">
                    <input type="text" name="data[questions][0][question]" class="form-control mb-2" placeholder="Вопрос">
                    <textarea name="data[questions][0][answer]" class="form-control" placeholder="Ответ"></textarea>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addFaq()">+ Добавить вопрос</button>
        </div>
    `,
    BreadcrumbList: `
        <div class="mb-3">
            <label class="form-label">Элементы навигации</label>
            <div id="breadcrumbContainer">
                <div class="bc-item border p-2 mb-2">
                    <input type="text" name="data[items][0][name]" class="form-control mb-2" placeholder="Название">
                    <input type="url" name="data[items][0][url]" class="form-control" placeholder="URL">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addBreadcrumb()">+ Добавить элемент</button>
        </div>
    `,
    WebSite: `
        <div class="mb-3"><label class="form-label">Название сайта</label><input type="text" name="data[name]" class="form-control field-input" value="{{ config('app.name') }}"></div>
        <div class="mb-3"><label class="form-label">URL сайта</label><input type="url" name="data[url]" class="form-control field-input" value="{{ config('app.url') }}"></div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="data[search]" class="form-check-input" id="enableSearch" value="1">
            <label class="form-check-label" for="enableSearch">Добавить поиск по сайту</label>
        </div>
    `,
    Person: `
        <div class="mb-3"><label class="form-label">ФИО</label><input type="text" name="data[name]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Должность</label><input type="text" name="data[job_title]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Компания</label><input type="text" name="data[company]" class="form-control field-input"></div>
        <div class="mb-3"><label class="form-label">Сайт</label><input type="url" name="data[url]" class="form-control field-input"></div>
    `
};

document.getElementById('schemaType').addEventListener('change', function() {
    const container = document.getElementById('dynamicFields');
    const type = this.value;
    
    if (type && schemaFields[type]) {
        container.innerHTML = schemaFields[type];
    } else {
        container.innerHTML = '';
    }
});

document.getElementById('schemaForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        if (key === '_token') continue;
        
        // Обработка вложенных ключей
        const match = key.match(/(\w+)\[(\w+)\](?:\[(\d+)\])?\[(\w+)\]?/);
        if (match) {
            const [, parent, child, index, subchild] = match;
            if (!data[parent]) data[parent] = {};
            
            if (index !== undefined) {
                if (!data[parent][child]) data[parent][child] = [];
                if (subchild) {
                    if (!data[parent][child][index]) data[parent][child][index] = {};
                    data[parent][child][index][subchild] = value;
                } else {
                    data[parent][child][index] = value;
                }
            } else {
                data[parent][child] = value;
            }
        } else if (key.includes('[')) {
            const parts = key.split(/[\[\]]+/).filter(Boolean);
            let current = data;
            for (let i = 0; i < parts.length - 1; i++) {
                if (!current[parts[i]]) current[parts[i]] = {};
                current = current[parts[i]];
            }
            current[parts[parts.length - 1]] = value;
        } else {
            data[key] = value;
        }
    }
    
    try {
        const response = await fetch('{{ route("admin.seo.schema-builder.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('schemaResult').textContent = result.preview;
            document.getElementById('schemaJson').value = result.preview;
            document.getElementById('schemaTypeHidden').value = data.type;
            document.getElementById('saveForm').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Ошибка генерации');
    }
});

function addFaq() {
    const container = document.getElementById('faqContainer');
    const index = container.children.length;
    const html = `
        <div class="faq-item border p-2 mb-2">
            <input type="text" name="data[questions][${index}][question]" class="form-control mb-2" placeholder="Вопрос">
            <textarea name="data[questions][${index}][answer]" class="form-control" placeholder="Ответ"></textarea>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function addBreadcrumb() {
    const container = document.getElementById('breadcrumbContainer');
    const index = container.children.length;
    const html = `
        <div class="bc-item border p-2 mb-2">
            <input type="text" name="data[items][${index}][name]" class="form-control mb-2" placeholder="Название">
            <input type="url" name="data[items][${index}][url]" class="form-control" placeholder="URL">
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function copyResult() {
    const text = document.getElementById('schemaResult').textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('Скопировано в буфер обмена!');
    });
}
</script>
@endsection
