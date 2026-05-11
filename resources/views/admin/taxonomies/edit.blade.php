@extends('admin.layouts.app')

@section('title', 'Edit Taxonomy - VertexCMS')
@section('page_title', 'Edit Taxonomy')
@section('page_subtitle', $taxonomy->name)

@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <section class="space-y-6">
            <form method="POST" action="{{ route('admin.taxonomies.update', $taxonomy) }}" class="vc-panel space-y-6 p-6">
                @csrf
                @method('PUT')
                @include('admin.taxonomies.partials.form')
            </form>

            <section class="vc-table-wrap">
                <div class="flex items-center justify-between gap-3 border-b border-[var(--vc-border)] px-4 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Terms</h2>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Manage the values attached to this taxonomy.</p>
                    </div>
                    @if (auth()->user()?->hasPermission('taxonomy.create'))
                        <a href="{{ route('admin.taxonomies.terms.create', $taxonomy) }}" class="vc-button vc-button-primary px-4 py-3">
                            Add term
                        </a>
                    @endif
                </div>
                <table class="vc-table text-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Parent</th>
                            <th>Pages</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($taxonomy->terms as $term)
                            <tr>
                                <td class="font-medium text-[var(--vc-text)]">{{ $term->name }}</td>
                                <td>{{ $term->slug }}</td>
                                <td>{{ $term->parent?->name ?: '-' }}</td>
                                <td><span class="vc-badge">{{ $term->pages_count }}</span></td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        @if (auth()->user()?->hasPermission('taxonomy.edit'))
                                            <a href="{{ route('admin.taxonomies.terms.edit', [$taxonomy, $term]) }}" class="vc-button vc-button-secondary px-3 py-2">
                                                Edit
                                            </a>
                                        @endif
                                        @if (auth()->user()?->hasPermission('taxonomy.delete'))
                                            <form method="POST" action="{{ route('admin.taxonomies.terms.destroy', [$taxonomy, $term]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="vc-button vc-button-danger px-3 py-2">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-[var(--vc-text-muted)]">No terms in this taxonomy yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </section>

        <aside class="space-y-6">
            <section class="vc-panel p-6">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Archive</h2>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">
                    Public archive base: <span class="font-medium">{{ url('/taxonomy/'.$taxonomy->slug) }}</span>
                </p>
                <p class="mt-3 text-sm text-[var(--vc-text-muted)]">
                    The actual public entry point is term-based, for example `/taxonomy/{{ $taxonomy->slug }}/services`.
                </p>
            </section>

            @if (auth()->user()?->hasPermission('taxonomy.delete'))
                <section class="vc-panel p-6">
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Danger Zone</h2>
                    <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Deleting a taxonomy removes all nested terms and page attachments.</p>
                    <form method="POST" action="{{ route('admin.taxonomies.destroy', $taxonomy) }}" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button class="vc-button vc-button-danger px-4 py-3">
                            Delete taxonomy
                        </button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
@endsection
