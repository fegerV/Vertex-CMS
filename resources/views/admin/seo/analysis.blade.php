@extends('admin.layouts.app')

@section('title', 'Анализ контента - SEO')

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Анализ контента</h1>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Страница</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">SEO Score</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                @foreach($pages as $page)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $page->title }}</div>
                        <div class="text-sm text-slate-500">{{ $page->uri }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            --
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-slate-900 dark:text-slate-100 max-w-xs truncate">
                            {{ $page->seoMeta?->title ?? 'Не задан' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-slate-900 dark:text-slate-100 max-w-xs truncate">
                            {{ $page->seoMeta?->description ?? 'Не задан' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="analyzePage({{ $page->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                            Анализировать
                        </button>
                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                            Редактировать
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="p-4">
            {{ $pages->links() }}
        </div>
    </div>
</div>

<!-- Modal для результатов анализа -->
<div id="analysisModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-slate-800">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100 mb-4">Результаты анализа</h3>
            <div id="analysisResults"></div>
            <div class="mt-4">
                <button onclick="document.getElementById('analysisModal').classList.add('hidden')" 
                    class="px-4 py-2 bg-slate-500 text-white rounded hover:bg-slate-600">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function analyzePage(pageId) {
    fetch('{{ route("admin.seo.analyze-page") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ page_id: pageId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('analysisResults').innerHTML = '<pre>' + JSON.stringify(data.analysis, null, 2) + '</pre>';
            document.getElementById('analysisModal').classList.remove('hidden');
        }
    });
}
</script>
@endsection
