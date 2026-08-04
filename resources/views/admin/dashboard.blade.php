@extends('admin.layouts.app')

@section('title', $siteName.' - Dashboard')
@section('page_title', $siteName)
@section('page_subtitle', 'Сводка по сайту и последние действия')

@section('content')
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Страницы</p>
                    <strong class="mt-2 block text-3xl font-bold text-slate-950">{{ $stats['pages'] }}</strong>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Опубликовано</p>
                    <strong class="mt-2 block text-3xl font-bold text-emerald-600">{{ $stats['published_pages'] }}</strong>
                </div>
                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Черновики</p>
                    <strong class="mt-2 block text-3xl font-bold text-amber-600">{{ $stats['draft_pages'] }}</strong>
                </div>
                <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Медиа</p>
                    <strong class="mt-2 block text-3xl font-bold text-purple-600">{{ $stats['media_files'] }}</strong>
                </div>
                <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </article>
    </section>

    <section class="mt-8 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-950">Последние действия</h2>

        <div class="mt-4 space-y-3">
            @forelse ($recentActivity as $activity)
                <div class="flex items-start gap-3 border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-950 truncate">{{ $activity->action }}</p>
                        <p class="text-sm text-slate-500">{{ $activity->description }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm text-slate-500">Действий пока нет.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
