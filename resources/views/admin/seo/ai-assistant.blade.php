@extends('admin.layouts.app')

@section('title', 'AI-Ассистент - SEO')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">🤖 AI-Ассистент для SEO</h1>
        <p class="text-slate-600 dark:text-slate-400 mt-1">
            Генерация мета-тегов, заголовков и контента с помощью искусственного интеллекта
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Генератор мета-тегов -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Генерация Meta-тегов</h2>
            
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Выберите страницу
            </label>
            <select id="pageSelect" 
                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 mb-4">
                <option value="">-- Выберите страницу --</option>
                @foreach($pages as $page)
                    <option value="{{ $page->id }}">{{ $page->title }}</option>
                @endforeach
            </select>

            <button onclick="generateMetaTags()" 
                    class="w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 mb-4">
                ✨ Сгенерировать с AI
            </button>

            <div id="metaResults" class="hidden space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title</label>
                    <input type="text" id="generatedTitle" readonly
                           class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                    <div class="text-xs text-slate-500 mt-1"><span id="titleLength">0</span>/60 символов</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea id="generatedDescription" rows="3" readonly
                              class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100"></textarea>
                    <div class="text-xs text-slate-500 mt-1"><span id="descLength">0</span>/160 символов</div>
                </div>
                <button onclick="applyMetaTags()" 
                        class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Применить к странице
                </button>
            </div>
        </div>

        <!-- Генератор контента -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Генерация контента</h2>
            
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Тип контента
            </label>
            <select id="contentType" 
                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 mb-4">
                <option value="title">Заголовок (Title)</option>
                <option value="description">Описание (Description)</option>
                <option value="content">Текст страницы</option>
            </select>

            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Промпт / Ключевые слова
            </label>
            <textarea id="contentPrompt" rows="3" 
                      placeholder="Например: 'SEO оптимизированный текст о наших услугах веб-разработки, ключевые слова: создание сайтов, веб-дизайн, продвижение'"
                      class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 mb-4"></textarea>

            <button onclick="generateContent()" 
                    class="w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 mb-4">
                ✨ Сгенерировать
            </button>

            <div id="contentResults" class="hidden">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Результат</label>
                <textarea id="generatedContent" rows="6" readonly
                          class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100"></textarea>
                <button onclick="copyContent()" 
                        class="mt-2 w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    📋 Копировать
                </button>
            </div>
        </div>
    </div>

    <!-- Подсказки по улучшению -->
    <div class="mt-6 bg-white dark:bg-slate-800 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">💡 Рекомендации по SEO</h2>
        <ul class="space-y-3">
            <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-slate-700 dark:text-slate-300">Используйте уникальные Title и Description для каждой страницы</span>
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-slate-700 dark:text-slate-300">Длина Title должна быть 50-60 символов, Description — 150-160</span>
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-slate-700 dark:text-slate-300">Включайте основные ключевые слова в начало Title</span>
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span class="text-slate-700 dark:text-slate-300">Избегайте переспама ключевыми словами</span>
            </li>
        </ul>
    </div>
</div>

<script>
let selectedPageId = null;
let generatedMeta = { title: '', description: '' };

document.getElementById('pageSelect').addEventListener('change', function() {
    selectedPageId = this.value;
});

function generateMetaTags() {
    if (!selectedPageId) {
        alert('Пожалуйста, выберите страницу');
        return;
    }

    fetch('{{ route("admin.seo.ai.generate-meta") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ page_id: selectedPageId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            generatedMeta = { title: data.title, description: data.description };
            document.getElementById('generatedTitle').value = data.title;
            document.getElementById('generatedDescription').value = data.description;
            document.getElementById('titleLength').textContent = data.title.length;
            document.getElementById('descLength').textContent = data.description.length;
            document.getElementById('metaResults').classList.remove('hidden');
        }
    });
}

function applyMetaTags() {
    // Здесь будет логика применения мета-тегов к странице
    alert('Мета-теги готовы к применению! Перейдите на страницу редактирования для сохранения.');
}

function generateContent() {
    const type = document.getElementById('contentType').value;
    const prompt = document.getElementById('contentPrompt').value;

    if (!prompt) {
        alert('Введите промпт/ключевые слова');
        return;
    }

    fetch('{{ route("admin.seo.ai.generate-content") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ type, prompt })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('generatedContent').value = data.content;
            document.getElementById('contentResults').classList.remove('hidden');
        }
    });
}

function copyContent() {
    const content = document.getElementById('generatedContent');
    content.select();
    document.execCommand('copy');
    alert('Скопировано в буфер обмена!');
}
</script>
@endsection
