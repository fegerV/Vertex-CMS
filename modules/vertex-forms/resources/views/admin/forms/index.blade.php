@extends('admin.layouts.app')

@section('title', __('forms.nav_label'))
@section('page_title', __('forms.listing_title'))
@section('page_subtitle', __('forms.listing_subtitle'))

@section('content')
<div>
    <!-- Header Actions -->
    <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            @if(auth()->user()?->hasPermission('forms.create'))
            <a href="{{ route('admin.forms.create') }}" class="vc-button vc-button-primary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('forms.nav_create') }}
            </a>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <div class="relative">
                @php $searchPlaceholder = __('forms.search_placeholder'); @endphp
                <input 
                    type="text" 
                    id="searchForms" 
                    placeholder="{{ $searchPlaceholder }}"
                    class="vc-input pl-9 py-2 text-sm w-64"
                    oninput="filterForms(this.value)"
                >
                <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('forms.title') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('forms.type') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('forms.status') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('forms.submissions_total') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('forms.created_at') }}</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('forms.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="formsTableBody">
                    @forelse($forms as $form)
                    <tr class="hover:bg-gray-50 transition-colors" data-form-id="{{ $form->id }}" data-search="{{ Str::lower($form->name.' '.$form->slug.' '.$form->type) }}">
                        <td class="px-3 py-2 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded bg-blue-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $form->name }}</div>
                                    <div class="text-sm text-gray-500">/{{ $form->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <span class="vc-badge vc-badge-{{ $form->type === 'calculator' ? 'blue' : ($form->type === 'survey' ? 'green' : 'gray') }}">
                                {{ match($form->type) {
                                    'calculator' => __('forms.form_type_calculator'),
                                    'survey' => __('forms.form_type_survey'),
                                    'poll' => __('forms.form_type_poll'),
                                    default => __('forms.form_type_standard'),
                                } }}
                            </span>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if($form->is_active)
                                <span class="vc-badge vc-badge-green">{{ __('forms.status_active') }}</span>
                            @else
                                <span class="vc-badge vc-badge-red">{{ __('forms.status_inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                            {{ $stats[$form->id]['total'] ?? 0 }}
                            <span class="text-xs text-gray-400">{{ __('forms.pagination_today', ['today' => $stats[$form->id]['today'] ?? 0]) }}</span>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                            {{ $form->created_at?->format('d.m.Y') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-right text-sm font-medium space-x-1">
                            @if(auth()->user()?->hasPermission('forms.edit'))
                            <a href="{{ route('admin.forms.edit', $form) }}" class="vc-button vc-button-secondary p-2" title="{{ __('forms.edit_form') }}">
                                ✏️
                            </a>
                            <form method="POST" action="{{ route('admin.forms.duplicate', $form) }}" class="inline">
                                @csrf
                                <button class="vc-button vc-button-secondary p-2" title="{{ __('forms.duplicate_form') }}" onclick="return confirm(@js(__('forms.confirm_duplicate')))">📋</button>
                            </form>
                            <a href="{{ route('admin.forms.preview', $form) }}" class="vc-button vc-button-secondary p-2" title="{{ __('forms.preview_form') }}" target="_blank">
                                👁️
                            </a>
                            @endif
                            @if(auth()->user()?->hasPermission('forms.delete'))
                            <button 
                                onclick="deleteForm({{ $form->id }}, '{{ addslashes($form->name) }}')" 
                                class="vc-button vc-button-danger p-2"
                                title="{{ __('forms.delete_form') }}"
                            >
                                🗑️
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-semibold">{{ __('forms.empty_title') }}</p>
                                <p class="text-sm mt-1">{{ __('forms.empty_subtitle') }}</p>
                                <a href="{{ route('admin.forms.create') }}" class="mt-4 vc-button vc-button-primary">
                                    {{ __('forms.empty_cta') }}
                                </a>
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
function deleteForm(id, name) {
    const message = @js(__('forms.confirm_delete', ['name' => ':name'])).replace(/\{name\}|:name/g, name);
    if (!confirm(message)) return;
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
        else alert(@js(__('forms.error_submission_failed')) + ': ' + (d.message || @js(__('forms.error_unknown'))));
    });
}

function filterForms(query) {
    const normalized = String(query || '').trim().toLocaleLowerCase();
    document.querySelectorAll('#formsTableBody tr[data-form-id]').forEach((row) => {
        row.hidden = normalized !== '' && !row.dataset.search.includes(normalized);
    });
}
</script>
@endsection
