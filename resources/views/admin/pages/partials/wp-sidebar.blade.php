@php
    $publicUrl = $page->exists && $page->uri ? url(ltrim($page->uri, '/')) : null;
@endphp

<aside class="space-y-4">
    <section class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface)] p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-[var(--vc-text)]">Publish</h2>
            <span class="rounded-full border border-[var(--vc-border)] px-2 py-0.5 text-xs text-[var(--vc-text-soft)]">
                {{ $page->exists ? ucfirst($page->status ?: 'draft') : 'Draft' }}
            </span>
        </div>

        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <dt class="text-[var(--vc-text-soft)]">Mode</dt>
                <dd class="font-medium text-[var(--vc-text)]">{{ $page->exists ? 'Update' : 'Create' }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-[var(--vc-text-soft)]">Builder</dt>
                <dd class="font-medium text-[var(--vc-text)]">{{ $page->exists ? 'Available' : 'After first save' }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-[var(--vc-text-soft)]">Public URI</dt>
                <dd class="max-w-[180px] truncate text-right font-medium text-[var(--vc-text)]">
                    {{ $page->exists ? $page->uri : 'Generated after save' }}
                </dd>
            </div>
        </dl>

        <div class="mt-5 space-y-2">
            <button form="page-editor-form" type="submit" class="vc-button vc-button-primary w-full justify-center">
                {{ $page->exists ? 'Update Page' : 'Save Draft' }}
            </button>

            @if ($page->exists)
                <a href="{{ route('admin.pages.builder', $page) }}" class="vc-button vc-button-secondary w-full justify-center">
                    Open Builder
                </a>
            @else
                <button type="button" disabled class="vc-button vc-button-secondary w-full justify-center opacity-60">
                    Builder After Save
                </button>
            @endif

            @if ($publicUrl)
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="vc-button vc-button-secondary w-full justify-center">
                    Preview Public Page
                </a>
            @endif
        </div>
    </section>

    <section class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface)] p-5 shadow-sm">
        <h2 class="text-base font-semibold text-[var(--vc-text)]">Editor Modes</h2>
        <div class="mt-4 space-y-2 text-sm">
            <div class="rounded-2xl border border-[var(--vc-primary)] bg-sky-50 px-3 py-2 text-sky-700">
                Editor
            </div>
            <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-2 text-[var(--vc-text-soft)]">
                SEO Preview
            </div>
            <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-2 text-[var(--vc-text-soft)]">
                Builder
            </div>
        </div>
        <p class="mt-3 text-xs text-[var(--vc-text-soft)]">
            This screen keeps content, taxonomy and SEO together. Layout composition stays in the dedicated page builder.
        </p>
    </section>

    @if (auth()->user()?->hasPermission('ai.use'))
        @include('admin.pages.partials.ai-assistant')
    @endif
</aside>
