@extends('admin.layouts.app')

@section('title', 'Кеш - VertexCMS')
@section('page_title', 'Кеш')
@section('page_subtitle', 'Состояние кеша и ручная очистка')

@section('content')
    <section class="grid gap-4 md:grid-cols-2">
        @foreach ($status as $key => $value)
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">{{ $key }}</p>
                <p class="mt-3 break-words text-lg font-semibold text-[var(--vc-text)]">
                    @if (is_bool($value))
                        {{ $value ? 'Да' : 'Нет' }}
                    @else
                        {{ $value }}
                    @endif
                </p>
            </article>
        @endforeach
    </section>

    @if (auth()->user()?->hasPermission('cache.clear'))
        <section class="vc-panel mt-6 p-5">
            <h2 class="text-lg font-semibold text-[var(--vc-text)]">Очистка кеша</h2>
            <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Ручное управление кэшем для диагностики и обслуживания.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach (['all' => 'Весь кеш', 'application' => 'Кеш приложения', 'pages' => 'Кеш страниц'] as $scope => $label)
                    <form method="POST" action="{{ route('admin.system.cache.clear') }}">
                        @csrf
                        <input type="hidden" name="scope" value="{{ $scope }}">
                        <button class="vc-button {{ $scope === 'all' ? 'vc-button-primary' : 'vc-button-secondary' }} px-4 py-3">
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    @endif
@endsection
