@extends('admin.layouts.app')
@section('title', 'Webhooks - VertexCMS')
@section('page_title', 'Webhooks')
@section('page_subtitle', 'Signed, queued deliveries to external services')
@section('content')
<div class="space-y-6">
    <form method="POST" action="{{ route('admin.webhooks.store') }}" class="vc-panel p-5 space-y-4">
        @csrf
        <div><h2 class="text-lg font-semibold">New webhook</h2><p class="text-sm text-[var(--vc-text-muted)]">Only public HTTPS endpoints are accepted. System signature headers cannot be overridden.</p></div>
        <div class="grid gap-4 md:grid-cols-2"><label class="vc-field"><span class="vc-label">Name</span><input class="vc-input" name="name" required></label><label class="vc-field"><span class="vc-label">Endpoint URL</span><input class="vc-input" type="url" name="url" placeholder="https://example.com/hooks/vertex" required></label></div>
        <fieldset><legend class="vc-label mb-2">Events</legend><div class="grid gap-2 md:grid-cols-3">@foreach($events as $event => $label)<label class="flex gap-2 text-sm"><input type="checkbox" name="events[]" value="{{ $event }}"> {{ $label }}</label>@endforeach</div></fieldset>
        <div class="grid gap-4 md:grid-cols-3"><label class="vc-field"><span class="vc-label">Retries</span><input class="vc-input" type="number" name="retry_count" value="3" min="1" max="10"></label><label class="vc-field"><span class="vc-label">Timeout (seconds)</span><input class="vc-input" type="number" name="timeout" value="30" min="1" max="60"></label><label class="flex items-end gap-2 pb-3"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
        @if($errors->any())<div class="vc-alert vc-alert-danger">{{ $errors->first() }}</div>@endif
        <button class="vc-button vc-button-primary">Create webhook</button>
    </form>
    <section class="vc-table-wrap"><table class="vc-table text-sm"><thead><tr><th>Name</th><th>Endpoint / events</th><th>Latest delivery</th><th>Actions</th></tr></thead><tbody>
    @forelse($webhooks as $webhook)<tr><td class="font-medium">{{ $webhook->name }}<div class="text-xs text-[var(--vc-text-muted)]">{{ $webhook->is_active ? 'Active' : 'Paused' }}</div></td><td><div class="break-all">{{ $webhook->url }}</div><div class="text-xs text-[var(--vc-text-muted)]">{{ implode(', ', $webhook->events ?? []) }}</div></td><td>@php($log=$webhook->logs->first()) @if($log)<span class="{{ $log->success ? 'text-emerald-600' : 'text-red-600' }}">{{ $log->success ? 'Delivered' : 'Failed' }}</span> · {{ $log->created_at?->diffForHumans() }}@else Never @endif</td><td><div class="flex gap-2"><form method="POST" action="{{ route('admin.webhooks.test', $webhook) }}">@csrf<button class="vc-button vc-button-secondary">Test</button></form><form method="POST" action="{{ route('admin.webhooks.destroy', $webhook) }}" onsubmit="return confirm('Delete webhook?')">@csrf @method('DELETE')<button class="vc-button vc-button-danger">Delete</button></form></div></td></tr>
    @empty<tr><td colspan="4" class="text-center text-[var(--vc-text-muted)]">No webhooks configured.</td></tr>@endforelse
    </tbody></table></section>
</div>
@endsection
