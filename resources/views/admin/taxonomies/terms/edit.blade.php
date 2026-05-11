@extends('admin.layouts.app')

@section('title', 'Edit Term - VertexCMS')
@section('page_title', 'Edit Term')
@section('page_subtitle', $term->name)

@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <form method="POST" action="{{ route('admin.taxonomies.terms.update', [$taxonomy, $term]) }}" class="vc-panel space-y-6 p-6">
            @csrf
            @method('PUT')
            @include('admin.taxonomies.terms.partials.form')
        </form>

        <aside class="space-y-6">
            <section class="vc-panel p-6">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Usage</h2>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Attached pages: <span class="vc-badge">{{ $term->pages_count }}</span></p>
                <p class="mt-3 text-sm text-[var(--vc-text-muted)]">
                    Archive URL: <a href="{{ route('frontend.term-archive', [$taxonomy->slug, $term->slug]) }}" class="underline" target="_blank">{{ route('frontend.term-archive', [$taxonomy->slug, $term->slug]) }}</a>
                </p>
            </section>

            @if (auth()->user()?->hasPermission('taxonomy.delete'))
                <section class="vc-panel p-6">
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Danger Zone</h2>
                    <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Deleting a term also removes page attachments for this term.</p>
                    <form method="POST" action="{{ route('admin.taxonomies.terms.destroy', [$taxonomy, $term]) }}" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button class="vc-button vc-button-danger px-4 py-3">
                            Delete term
                        </button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
@endsection
