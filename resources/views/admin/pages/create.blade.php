@extends('admin.layouts.app')

@section('title', 'Создание страницы - VertexCMS')
@section('page_title', 'Создание страницы')
@section('page_subtitle', 'Базовая информация, контент, SEO и таксономии')

@section('content')
    <div class="mx-auto grid max-w-[1600px] gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-5">
            <section class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface)] p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <a href="{{ route('admin.pages.index') }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Back to pages</a>
                        <h1 class="mt-2 text-3xl font-semibold text-[var(--vc-text)]">Create Page</h1>
                        <p class="mt-2 max-w-3xl text-sm text-[var(--vc-text-soft)]">
                            WordPress-style document shell: save the page first, then switch into the visual builder for layout work.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-[var(--vc-border)] px-3 py-1 text-xs uppercase tracking-wide text-[var(--vc-text-soft)]">Draft</span>
                        <span class="rounded-full border border-[var(--vc-primary)] bg-sky-50 px-3 py-1 text-xs uppercase tracking-wide text-sky-700">Editor</span>
                        <span class="rounded-full border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-1 text-xs uppercase tracking-wide text-[var(--vc-text-soft)]">SEO Preview</span>
                        <span class="rounded-full border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-1 text-xs uppercase tracking-wide text-[var(--vc-text-soft)]">Builder After Save</span>
                    </div>
                </div>
            </section>

            <form id="page-editor-form" method="POST" action="{{ route('admin.pages.store') }}" class="space-y-6">
                @csrf
                @include('admin.pages.partials.form')
            </form>
        </div>

        @include('admin.pages.partials.wp-sidebar')
    </div>
@endsection
