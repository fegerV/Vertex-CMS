@extends('admin.layouts.app')

@section('title', 'Сиротские страницы - SEO')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Сиротские страницы</h1>
        <p class="text-slate-600 dark:text-slate-400 mt-1">
            Страницы, на которые нет внутренних ссылок с других страниц сайта. 
            Такие страницы плохо индексируются поисковыми системами.
        </p>
    </div>

    @if(empty($orphanPages))
        <div class="bg-green-100 border border-green-400 text-green-700 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="font-semibold">Отлично!</h3>
                    <p>Все страницы имеют входящие ссылки.</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Страница</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">URI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Дата обновления</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($orphanPages as $page)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $page->title }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-sm text-slate-500">{{ $page->uri }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-slate-500">{{ $page->updated_at->format('d.m.Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.pages.edit', $page) }}" 
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3">
                                Редактировать
                            </a>
                            <a href="{{ url($page->uri) }}" target="_blank" 
                               class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                Просмотр →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <h3 class="font-semibold text-blue-800 dark:text-blue-300 mb-2">💡 Как исправить:</h3>
            <ul class="list-disc list-inside text-blue-700 dark:text-blue-400 space-y-1">
                <li>Добавьте ссылки на эти страницы из других материалов сайта</li>
                <li>Создайте блок "Читайте также" или "Полезные материалы"</li>
                <li>Добавьте страницы в навигационное меню</li>
                <li>Используйте хлебные крошки (breadcrumbs)</li>
            </ul>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('admin.seo.internal-links') }}" 
           class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
            ← Вернуться к обзору внутренних ссылок
        </a>
    </div>
</div>
@endsection
