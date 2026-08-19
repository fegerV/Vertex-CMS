@extends('admin.layouts.app')

@section('title', 'Массовое редактирование - SEO')

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Массовое редактирование мета-тегов</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.seo.bulk-update') }}" id="bulkForm">
        @csrf
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Страница</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Title (max 60)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description (max 160)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Keywords</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($pages as $page)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="hidden" name="updates[{{ $loop->index }}][id]" value="{{ $page->id }}">
                            <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $page->title }}</div>
                            <div class="text-sm text-slate-500">{{ $page->uri }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <input type="text" 
                                   name="updates[{{ $loop->index }}][title]" 
                                   value="{{ old('updates.'.$loop->index.'.title', $page->seoMeta?->title ?? '') }}"
                                   class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500"
                                   maxlength="255">
                        </td>
                        <td class="px-6 py-4">
                            <textarea name="updates[{{ $loop->index }}][description]" 
                                      rows="2"
                                      class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">{{ old('updates.'.$loop->index.'.description', $page->seoMeta?->description ?? '') }}</textarea>
                        </td>
                        <td class="px-6 py-4">
                            <input type="text" 
                                   name="updates[{{ $loop->index }}][keywords]" 
                                   value="{{ old('updates.'.$loop->index.'.keywords', $page->seoMeta?->keywords ?? '') }}"
                                   class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="keyword1, keyword2">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4 flex justify-between items-center">
                {{ $pages->links() }}
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Сохранить все изменения
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
