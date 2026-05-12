@extends('admin.layouts.app')

@section('title', 'Email шаблоны - VertexCMS')
@section('page_title', 'Шаблоны писем')
@section('page_subtitle', 'Управление шаблонами и отправка')

@section('content')
<div id="email-templates" class="vc-email-manager">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.settings.edit') }}#mail" class="vc-button vc-button-secondary">
                ⚙️ Настройки SMTP
            </a>
        </div>
        @can('mail.edit')
            <a href="{{ route('admin.email-templates.create') }}" class="vc-button vc-button-primary">
                + Новый шаблон
            </a>
        @endcan
    </div>

    <!-- Filters -->
    <section class="vc-panel vc-panel-strong p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-64">
                <input type="text" v-model="search" placeholder="Поиск по названию или ключу..." class="vc-input w-full">
            </div>
            <select v-model="filterCategory" class="vc-input w-auto">
                <option value="">Все категории</option>
                <option v-for="(count, cat) in categories" :value="cat">@{{ cat }} (@{{ count }})</option>
            </select>
            <select v-model="filterStatus" class="vc-input w-auto">
                <option value="">Все статусы</option>
                <option value="1">Активные</option>
                <option value="0">Неактивные</option>
            </select>
        </div>
    </section>

    <!-- Grid -->
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($templates as $template)
            <article class="vc-panel vc-panel-strong p-5 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-semibold text-[var(--vc-text)] text-lg">@{{ $template->name }}</h3>
                    <span class="text-xs px-2 py-1 rounded-full"
                          :class="$template->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                        {{ $template->is_active ? 'Активен' : 'Неактивен' }}
                    </span>
                </div>
                <div class="text-sm text-[var(--vc-text-muted)] mb-2">Ключ: <code class="bg-[var(--vc-surface-muted)] px-1 rounded">@{{ $template->key }}</code></div>
                <div class="text-sm text-[var(--vc-text-muted)] mb-2">Категория: @{{ $template->category }}</div>
                <div class="text-sm text-[var(--vc-text-muted)] mb-4 line-clamp-2">@{{ $template->subject }}</div>

                <div class="flex items-center gap-2 mt-4">
                    @can('mail.view')
                        <a href="{{ route('admin.email-templates.preview', $template) }}" target="_blank" class="vc-button vc-button-secondary vc-button-sm">
                            👁 Превью
                        </a>
                    @endcan
                    @can('mail.edit')
                        <a href="{{ route('admin.email-templates.edit', $template) }}" class="vc-button vc-button-secondary vc-button-sm">
                            ✏️ Редактировать
                        </a>
                        <form method="POST" action="{{ route('admin.email-templates.send-test', $template) }}" class="inline" onsubmit="return confirm('Отправить тестовое письмо?')">
                            @csrf
                            <input type="email" name="test_email" value="{{ auth()->user()->email }}" class="vc-input w-32 text-sm" placeholder="email" required>
                            <button type="submit" class="vc-button vc-button-primary vc-button-sm">Тест</button>
                        </form>
                    @endcan
                    @can('mail.delete')
                        @if(!$template->is_system)
                            <form method="POST" action="{{ route('admin.email-templates.destroy', $template) }}" class="inline" onsubmit="return confirm('Удалить шаблон?')">
                                @csrf
                                @method('DELETE')
                                <button class="vc-button vc-button-danger vc-button-sm">🗑 Удалить</button>
                            </form>
                        @endif
                    @endcan
                </div>
            </article>
        @empty
            <div class="col-span-full vc-panel p-8 text-center text-[var(--vc-text-muted)]">
                Шаблонов пока нет. <a href="{{ route('admin.email-templates.create') }}" class="vc-link">Создать первый</a>
            </div>
        @endforelse
    </section>
</div>
@endsection

@push('scripts')
<script>
    const { createApp, ref, computed, onMounted } = Vue;

    createApp({
        setup() {
            const search = ref('');
            const filterCategory = ref('');
            const filterStatus = ref('');

            const categories = @json($categories ?? []);

            // Filtering is handled by backend pagination; can be extended later

            return {
                search,
                filterCategory,
                filterStatus,
                categories,
            };
        },
    }).mount('#email-templates');
</script>
@endpush
