@extends('admin.layouts.app')

@section('title', $siteName.' - '.__('admin.dashboard'))
@section('page_title', $siteName)
@section('page_subtitle', __('admin.summary'))

@section('content')
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">{{ __('admin.pages') }}</p>
            <strong class="mt-2 block text-3xl">{{ $stats['pages'] }}</strong>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">{{ __('admin.published') }}</p>
            <strong class="mt-2 block text-3xl">{{ $stats['published_pages'] }}</strong>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">{{ __('admin.drafts') }}</p>
            <strong class="mt-2 block text-3xl">{{ $stats['draft_pages'] }}</strong>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">{{ __('admin.media') }}</p>
            <strong class="mt-2 block text-3xl">{{ $stats['media_files'] }}</strong>
        </article>
    </section>

    <section class="mt-8 rounded-lg border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-semibold">{{ __('admin.recent_activity') }}</h2>

        <div class="mt-4 space-y-3">
            @forelse ($recentActivity as $activity)
                <div class="border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                    <p class="font-medium">{{ $activity->action }}</p>
                    <p class="text-sm text-slate-500">{{ $activity->description }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('admin.no_activity') }}</p>
            @endforelse
        </div>
    </section>
@endsection
