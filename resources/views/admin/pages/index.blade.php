@extends('admin.layouts.app')

@section('title', 'Страницы - VertexCMS')
@section('page_title', 'Страницы')
@section('page_subtitle', 'Создание, публикация и управление страницами сайта')

@section('content')
    @if (auth()->user()?->hasPermission('pages.create'))
        <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="relative max-w-sm flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input 
                    type="text" 
                    id="searchPages" 
                    placeholder="Поиск страниц..." 
                    class="w-full pl-10 pr-4 py-2 rounded-md border border-slate-300 text-sm focus:border-slate-950 focus:ring-1 focus:ring-slate-950 outline-none"
                >
            </div>
            <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center gap-2 rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Создать страницу
            </a>
        </header>
    @endif

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-medium">Название</th>
                        <th class="px-4 py-3 font-medium">URI</th>
                        <th class="px-4 py-3 font-medium">Статус</th>
                        <th class="px-4 py-3 font-medium">Обновлено</th>
                        <th class="px-4 py-3 text-right font-medium">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors group">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <span class="font-medium text-slate-950">{{ $page->title }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 font-mono text-xs">{{ $page->uri }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $page->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($page->status === 'draft' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $page->status === 'published' ? 'bg-emerald-500' : ($page->status === 'draft' ? 'bg-amber-500' : 'bg-slate-500') }}"></span>
                                    {{ $page->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $page->updated_at?->format('d.m.Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    @if (auth()->user()?->hasPermission('pages.edit'))
                                        <a href="{{ route('admin.pages.edit', $page) }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-white hover:text-blue-600 transition-colors" title="Редактировать">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            <span class="hidden sm:inline">Изменить</span>
                                        </a>
                                        <a href="{{ route('admin.pages.builder', $page) }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-white hover:text-purple-600 transition-colors" title="Конструктор">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                                            <span class="hidden sm:inline">Builder</span>
                                        </a>
                                    @endif
                                    @if (auth()->user()?->hasPermission('pages.delete'))
                                        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Вы уверены, что хотите удалить эту страницу? Это действие нельзя отменить.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-md border border-red-200 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-50 transition-colors" title="Удалить">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <span class="hidden sm:inline">Удалить</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-slate-500 font-medium">Страниц пока нет</p>
                                <p class="text-slate-400 text-sm mt-1">Создайте первую страницу, чтобы начать</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">
        {{ $pages->links() }}
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('searchPages')?.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
</script>
@endpush
