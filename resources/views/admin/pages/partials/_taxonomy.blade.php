@php
    $selectedTermIds = collect(old('term_ids', $page->terms?->pluck('id')->all() ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

@if (($taxonomies ?? collect())->isNotEmpty())
    <section class="vc-form-surface vc-form-section">
        <div>
            <h2 class="text-lg font-semibold text-[var(--vc-text)]">Таксономии</h2>
            <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Привяжите категории и теги, чтобы страница участвовала в архивах терминов и taxonomy API.</p>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            @foreach ($taxonomies as $taxonomy)
                <div class="rounded-lg border border-[var(--vc-border)] bg-[var(--vc-surface)] p-4">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-[var(--vc-text)]">{{ $taxonomy->name }}</h3>
                        <p class="text-xs text-[var(--vc-text-soft)]">Slug: {{ $taxonomy->slug }}</p>
                    </div>

                    <div class="space-y-2">
                        @forelse ($taxonomy->terms as $term)
                            <label class="flex items-start gap-3 rounded-md border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-2 text-sm text-[var(--vc-text)]">
                                <input
                                    type="checkbox"
                                    name="term_ids[]"
                                    value="{{ $term->id }}"
                                    @checked(in_array((string) $term->id, $selectedTermIds, true))
                                    class="mt-0.5 rounded border-slate-300"
                                >
                                <span>
                                    <span class="block font-medium text-[var(--vc-text)]">{{ $term->name }}</span>
                                    @if ($term->description)
                                        <span class="block text-xs text-[var(--vc-text-soft)]">{{ $term->description }}</span>
                                    @endif
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-[var(--vc-text-soft)]">В этой таксономии пока нет терминов.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        @error('term_ids')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
        @error('term_ids.*')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </section>
@endif
