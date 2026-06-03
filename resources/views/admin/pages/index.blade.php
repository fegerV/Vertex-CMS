@extends('admin.layouts.app')

@section('title', __('pages.title').' - VertexCMS')
@section('page_title', __('pages.title'))
@section('page_subtitle', __('pages.subtitle'))

@section('content')
    @if (auth()->user()?->hasPermission('pages.create'))
        <header class="mb-6 flex flex-wrap items-center justify-end gap-4">
            <a href="{{ route('admin.pages.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                {{ __('pages.create') }}
            </a>
        </header>
    @endif

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <table class="w-full border-collapse text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('pages.name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('pages.uri') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('pages.status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('pages.updated') }}</th>
                    <th class="px-4 py-3 text-right font-medium">{{ __('pages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pages as $page)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $page->title }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $page->uri }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                {{ $page->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $page->updated_at?->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @if (auth()->user()?->hasPermission('pages.edit'))
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="rounded-md border border-slate-300 px-3 py-1.5 hover:bg-slate-50">
                                        {{ __('pages.edit') }}
                                    </a>
                                    <a href="{{ route('admin.pages.builder', $page) }}" class="rounded-md border border-slate-300 px-3 py-1.5 hover:bg-slate-50">
                                        {{ __('pages.builder') }}
                                    </a>
                                @endif
                                @if (auth()->user()?->hasPermission('pages.delete'))
                                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-red-200 px-3 py-1.5 text-red-700 hover:bg-red-50">
                                            {{ __('pages.delete') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                            {{ __('pages.no_pages') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-4">
        {{ $pages->links() }}
    </div>
@endsection
