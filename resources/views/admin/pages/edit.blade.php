@extends('admin.layouts.app')

@section('title', 'Редактирование страницы - VertexCMS')
@section('page_title', 'Редактирование страницы')
@section('page_subtitle', $page->title)

@section('content')
    <div class="mx-auto grid max-w-[1600px] gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-5">
            <section class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface)] p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <a href="{{ route('admin.pages.index') }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Back to pages</a>
                        <h1 class="mt-2 text-3xl font-semibold text-[var(--vc-text)]">{{ $page->title }}</h1>
                        <p class="mt-2 max-w-3xl text-sm text-[var(--vc-text-soft)]">
                            Edit content, SEO and page metadata here, then switch into the dedicated builder for structured layout work.
                        </p>
                        <p class="mt-3 text-sm text-[var(--vc-text-soft)]">
                            Permalink: <span class="font-medium text-[var(--vc-text)]">{{ $page->uri }}</span>
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-[var(--vc-border)] px-3 py-1 text-xs uppercase tracking-wide text-[var(--vc-text-soft)]">{{ ucfirst($page->status) }}</span>
                        <span class="rounded-full border border-[var(--vc-primary)] bg-sky-50 px-3 py-1 text-xs uppercase tracking-wide text-sky-700">Editor</span>
                        <span class="rounded-full border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-1 text-xs uppercase tracking-wide text-[var(--vc-text-soft)]">SEO Preview</span>
                        <a href="{{ route('admin.pages.builder', $page) }}" class="rounded-full border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-1 text-xs uppercase tracking-wide text-[var(--vc-text-soft)] hover:text-[var(--vc-text)]">
                            Builder
                        </a>
                    </div>
                </div>
            </section>

            <form id="page-editor-form" method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('admin.pages.partials.form')
            </form>
        </div>

        @include('admin.pages.partials.wp-sidebar')
    </div>
@endsection
