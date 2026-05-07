@extends('admin.layouts.app')

@section('title', 'Кеш - VertexCMS')
@section('page_title', 'Кеш')
@section('page_subtitle', 'Состояние кеша и ручная очистка')

@section('content')
    <section class="grid gap-4 md:grid-cols-2">
        @foreach ($status as $key => $value)
            <article class="rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-sm font-medium text-slate-500">{{ $key }}</p>
                <p class="mt-2 break-words text-lg">
                    @if (is_bool($value))
                        {{ $value ? 'Да' : 'Нет' }}
                    @else
                        {{ $value }}
                    @endif
                </p>
            </article>
        @endforeach
    </section>

    <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5">
        <h2 class="text-lg font-semibold">Очистка кеша</h2>
        <div class="mt-4 flex flex-wrap gap-3">
            @foreach (['all' => 'Весь кеш', 'application' => 'Кеш приложения', 'pages' => 'Кеш страниц'] as $scope => $label)
                <form method="POST" action="{{ route('admin.system.cache.clear') }}">
                    @csrf
                    <input type="hidden" name="scope" value="{{ $scope }}">
                    <button class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">
                        {{ $label }}
                    </button>
                </form>
            @endforeach
        </div>
    </section>
@endsection
