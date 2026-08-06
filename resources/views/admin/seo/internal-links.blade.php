@extends('admin.layouts.app')

@section('title', 'Внутренние ссылки - SEO')

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Внутренняя перелинковка</h1>
        <a href="{{ route('admin.seo.orphan-pages') }}" 
           class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">
            📄 Сиротские страницы ({{ collect($linkData)->filter(fn($item) => $item['incoming_count'] === 0)->count() }})
        </a>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <div class="text-sm text-slate-500 dark:text-slate-400">Всего страниц</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ count($linkData) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <div class="text-sm text-slate-500 dark:text-slate-400">Страниц с входящими ссылками</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                {{ collect($linkData)->filter(fn($item) => $item['incoming_count'] > 0)->count() }}
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <div class="text-sm text-slate-500 dark:text-slate-400">Сиротские страницы</div>
            <div class="text-2xl font-bold text-orange-600">
                {{ collect($linkData)->filter(fn($item) => $item['incoming_count'] === 0)->count() }}
            </div>
        </div>
    </div>

    <!-- Таблица связей -->
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Страница</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Исходящие ссылки</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Входящие ссылки</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Статус</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                @foreach($linkData as $item)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $item['page']->title }}</div>
                        <div class="text-sm text-slate-500">{{ $item['page']->uri }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-900 dark:text-slate-100">{{ count($item['outgoing']) }}</div>
                        @if(count($item['outgoing']) > 0)
                            <details class="mt-2">
                                <summary class="text-xs text-blue-600 cursor-pointer">Показать</summary>
                                <ul class="text-xs text-slate-500 mt-1 space-y-1">
                                    @foreach(array_slice($item['outgoing'], 0, 5) as $link)
                                        <li>→ {{ $link['target']->title }}</li>
                                    @endforeach
                                    @if(count($item['outgoing']) > 5)
                                        <li>... еще {{ count($item['outgoing']) - 5 }}</li>
                                    @endif
                                </ul>
                            </details>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold {{ $item['incoming_count'] === 0 ? 'text-orange-600' : 'text-green-600' }}">
                            {{ $item['incoming_count'] }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($item['incoming_count'] === 0)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                Сиротская
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                OK
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
