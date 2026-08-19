@extends('admin.layouts.app')

@section('title', 'Семантическое ядро - SEO')

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Семантическое ядро</h1>
        <button onclick="document.getElementById('addKeywordModal').classList.remove('hidden')"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            + Добавить ключевое слово
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Список ключевых слов -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Все ключевые слова</h2>
            
            @if($keywords->isEmpty())
                <p class="text-slate-500 dark:text-slate-400">Ключевые слова еще не добавлены</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($keywords as $keyword => $count)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $keyword }}
                            <span class="ml-2 px-2 py-0.5 text-xs bg-blue-200 dark:bg-blue-800 rounded-full">{{ $count }}</span>
                            <button onclick="deleteKeyword('{{ $keyword }}')" class="ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-400">×</button>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Статистика -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Статистика</h2>
            <div class="space-y-4">
                <div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">Всего ключевых слов</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $keywords->count() }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">Страниц с ключевыми словами</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $pages->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Привязка к страницам -->
    <div class="mt-6 bg-white dark:bg-slate-800 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Привязка к страницам</h2>
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Страница</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Ключевые слова</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                @foreach($pages as $page)
                @if($page->seoMeta?->keywords)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $page->title }}</div>
                        <div class="text-sm text-slate-500">{{ $page->uri }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach(explode(',', $page->seoMeta->keywords) as $kw)
                                <span class="px-2 py-1 text-xs bg-slate-100 dark:bg-slate-700 rounded">{{ trim($kw) }}</span>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal для добавления ключевого слова -->
<div id="addKeywordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-slate-800">
        <form method="POST" action="{{ route('admin.seo.keywords.add') }}">
            @csrf
            <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100 mb-4">Добавить ключевое слово</h3>
            <input type="text" name="keyword" required
                   class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 mb-4"
                   placeholder="Введите ключевое слово">
            <select name="page_id" 
                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500 mb-4">
                <option value="">Выберите страницу (опционально)</option>
                @foreach($pages as $page)
                    <option value="{{ $page->id }}">{{ $page->title }}</option>
                @endforeach
            </select>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addKeywordModal').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-500 text-white rounded hover:bg-slate-600">
                    Отмена
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Добавить
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function deleteKeyword(keyword) {
    if (!confirm('Удалить ключевое слово "' + keyword + '"?')) return;
    
    fetch(`{{ url('/admin/seo/keywords') }}/${encodeURIComponent(keyword)}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }).then(() => location.reload());
}
</script>
@endsection
