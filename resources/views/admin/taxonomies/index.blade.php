@extends('admin.layouts.app')

@section('title', 'Taxonomies - VertexCMS')
@section('page_title', 'Taxonomies')
@section('page_subtitle', 'Categories, tags, and public archive structure')

@section('content')
    @if (auth()->user()?->hasPermission('taxonomy.create'))
        <div class="mb-6 flex justify-end">
            <a href="{{ route('admin.taxonomies.create') }}" class="vc-button vc-button-primary px-4 py-3">
                Create taxonomy
            </a>
        </div>
    @endif

    <section class="vc-table-wrap">
        <table class="vc-table text-sm">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Entity</th>
                    <th>Mode</th>
                    <th>Terms</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($taxonomies as $taxonomy)
                    <tr>
                        <td class="font-medium text-[var(--vc-text)]">{{ $taxonomy->name }}</td>
                        <td>{{ $taxonomy->slug }}</td>
                        <td>{{ $taxonomy->entity_type }}</td>
                        <td><span class="vc-badge">{{ $taxonomy->hierarchical ? 'Hierarchical' : 'Flat' }}</span></td>
                        <td><span class="vc-badge">{{ $taxonomy->terms_count }}</span></td>
                        <td>
                            <div class="flex justify-end gap-2">
                                @if (auth()->user()?->hasPermission('taxonomy.edit'))
                                    <a href="{{ route('admin.taxonomies.edit', $taxonomy) }}" class="vc-button vc-button-secondary px-3 py-2">
                                        Manage
                                    </a>
                                @endif
                                @if (auth()->user()?->hasPermission('taxonomy.delete'))
                                    <form method="POST" action="{{ route('admin.taxonomies.destroy', $taxonomy) }}">
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
                        <td colspan="6" class="py-10 text-center text-[var(--vc-text-muted)]">No taxonomies yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
