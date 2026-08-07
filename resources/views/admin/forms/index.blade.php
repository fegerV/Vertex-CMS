@extends('admin.layouts.app')

@section('title', 'Формы')
@section('page_title', 'Конструктор форм')
@section('page_subtitle', 'Универсальный конструктор калькуляторов и форм')

@section('content')
<div class="space-y-4">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2">
            @can('forms.create')
            <a href="{{ route('admin.forms.create') }}" class="vc-button vc-button-primary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Новая форма
            </a>
            @endcan
            <a href="{{ route('admin.dashboard') }}" class="vc-button vc-button-secondary">
                Назад к дашборду
            </a>
        </div>
        <div class="flex items-center gap-2">
            <div class="relative">
                <input 
                    type="text" 
                    id="searchForms" 
                    placeholder="Поиск форм..." 
                    class="vc-input pl-8 py-1.5 text-sm w-56"
                    x-model="searchQuery"
                >
                <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Forms Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Отправки</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Создана</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="formsTableBody">
                    @forelse($forms as $form)
                    <tr class="hover:bg-gray-50 transition-colors form-row" 
                        data-form-id="{{ $form->id }}"
                        data-form-name="{{ strtolower($form->name) }}"
                        data-form-slug="{{ strtolower($form->slug) }}"
                        data-form-type="{{ strtolower($form->type) }}">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded bg-blue-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $form->name }}</div>
                                    <div class="text-xs text-gray-500">/{{ $form->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="vc-badge vc-badge-{{ $form->type === 'calculator' ? 'blue' : ($form->type === 'survey' ? 'green' : 'gray') }}">
                                {{ match($form->type) {
                                    'calculator' => 'Калькулятор',
                                    'survey' => 'Опрос',
                                    'poll' => 'Голосование',
                                    default => 'Стандартная',
                                } }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($form->is_active)
                                <span class="vc-badge vc-badge-green">Активна</span>
                            @else
                                <span class="vc-badge vc-badge-red">Отключена</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $stats[$form->id]['total'] ?? 0 }}
                            <span class="text-xs text-gray-400">(сегодня: {{ $stats[$form->id]['today'] ?? 0 }})</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $form->created_at?->format('d.m.Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium space-x-1">
                            @can('forms.edit')
                            <a href="{{ route('admin.forms.edit', $form) }}" class="vc-button vc-button-secondary p-1.5" title="Редактировать">
                                ✏️
                            </a>
                            <form action="{{ route('admin.forms.duplicate', $form) }}" method="POST" class="inline" onsubmit="return confirm('Дублировать форму?')">
                                @csrf
                                <button type="submit" class="vc-button vc-button-secondary p-1.5" title="Дублировать">
                                    📋
                                </button>
                            </form>
                            <a href="{{ route('admin.forms.preview', $form) }}" class="vc-button vc-button-secondary p-1.5" title="Предпросмотр" target="_blank">
                                👁️
                            </a>
                            @endcan
                            @can('forms.delete')
                            <button 
                                onclick="deleteForm({{ $form->id }}, '{{ addslashes($form->name) }}')" 
                                class="vc-button vc-button-danger p-1.5"
                                title="Удалить"
                            >
                                🗑️
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm font-semibold">Формы не созданы</p>
                                <p class="text-xs mt-1">Создайте первую форму, чтобы начать собирать заявки.</p>
                                @can('forms.create')
                                <a href="{{ route('admin.forms.create') }}" class="mt-3 vc-button vc-button-primary">
                                    + Создать форму
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Поиск форм
document.getElementById('searchForms').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.form-row').forEach(row => {
        const name = row.dataset.formName;
        const slug = row.dataset.formSlug;
        const type = row.dataset.formType;
        const matches = !query || name.includes(query) || slug.includes(query) || type.includes(query);
        row.style.display = matches ? '' : 'none';
    });
});

function deleteForm(id, name) {
    if (!confirm(`Удалить форму "${name}"? Все данные будут потеряны.`)) return;
    fetch(`/admin/forms/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) location.reload();
        else alert('Ошибка: ' + (d.message || 'unknown'));
    });
}
</script>
@endsection
