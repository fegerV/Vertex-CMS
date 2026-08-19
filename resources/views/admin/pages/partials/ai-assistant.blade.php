@php
    $aiEnabled = (bool) config_value('ai.enabled', false);
    $aiProvider = config_value('ai.default_provider', 'openai');
    $aiModel = config_value('ai.default_model', '');
    $aiConfigured = filled(config_value('ai.openai_api_key'))
        || filled(config_value('ai.anthropic_api_key'))
        || (filled(config_value('ai.custom_api_base')) && filled(config_value('ai.custom_api_key')));
@endphp

<aside
    data-ai-assistant
    data-enabled="{{ $aiEnabled ? '1' : '0' }}"
    data-configured="{{ $aiConfigured ? '1' : '0' }}"
    data-provider="{{ $aiProvider }}"
    data-model="{{ $aiModel }}"
    data-providers-endpoint="{{ url('/admin/api/ai/providers') }}"
    data-chat-endpoint="{{ url('/admin/api/ai/chat') }}"
    class="vc-panel sticky top-6 space-y-5 p-5"
>
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-[var(--vc-text)]">AI Assistant</h2>
            <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Drafts for text, FAQ, CTA, SEO, and builder JSON with explicit apply-only flow.</p>
        </div>
        <span class="vc-badge {{ $aiEnabled && $aiConfigured ? '' : 'opacity-70' }}" data-ai-badge>
            {{ $aiEnabled && $aiConfigured ? 'Ready' : 'Setup needed' }}
        </span>
    </div>

    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4 text-sm">
        <div class="flex items-center justify-between gap-2">
            <span class="font-semibold text-[var(--vc-text)]">Provider</span>
            <span class="text-[var(--vc-text-muted)]" data-ai-provider>{{ $aiProvider }}</span>
        </div>
        <div class="mt-2 flex items-center justify-between gap-2">
            <span class="font-semibold text-[var(--vc-text)]">Model</span>
            <span class="text-[var(--vc-text-muted)]" data-ai-model>{{ $aiModel !== '' ? $aiModel : 'Not set' }}</span>
        </div>
        <div class="mt-2 flex items-center justify-between gap-2">
            <span class="font-semibold text-[var(--vc-text)]">Draft mode</span>
            <span class="text-[var(--vc-text-muted)]">AI never saves without confirmation</span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2">
        @foreach ([
            'text' => 'Text',
            'faq' => 'FAQ',
            'cta' => 'CTA',
            'seo' => 'SEO',
            'builder' => 'Builder',
        ] as $action => $label)
            <button
                type="button"
                data-ai-action="{{ $action }}"
                class="vc-button vc-button-secondary justify-center px-3 py-2 text-sm"
                @disabled(! $aiEnabled || ! $aiConfigured)
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Instruction</span>
        <textarea
            data-ai-prompt
            rows="7"
            class="vc-textarea"
            placeholder="Example: Write a compact hero section for the services page and suggest a stronger SEO description."
            @disabled(! $aiEnabled || ! $aiConfigured)
        ></textarea>
    </label>

    <div class="flex items-center gap-3">
        <button
            type="button"
            data-ai-run
            class="vc-button vc-button-primary flex-1 justify-center px-4 py-3"
            @disabled(! $aiEnabled || ! $aiConfigured)
        >
            Generate draft
        </button>
        <span class="text-xs text-[var(--vc-text-muted)]" data-ai-current-action>Action: text</span>
    </div>

    <div class="rounded-2xl border border-dashed border-[var(--vc-border-strong)] bg-[var(--vc-surface-muted)] px-4 py-3 text-sm text-[var(--vc-text-muted)]" data-ai-status>
        {{ $aiEnabled && $aiConfigured
            ? 'AI is ready. Generate a draft, review it, then apply it to the form.'
            : 'Enable the AI module and configure a provider key before using AI drafts.' }}
    </div>

    <section data-ai-preview-wrap class="hidden space-y-4 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-[var(--vc-text)]">Draft Preview</h3>
            <span class="vc-badge" data-ai-preview-kind>blocks</span>
        </div>

        <pre data-ai-preview class="max-h-80 overflow-auto whitespace-pre-wrap rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface)] p-3 text-xs text-[var(--vc-text)]"></pre>

        <div class="grid gap-2 sm:grid-cols-2">
            <button type="button" data-ai-apply="blocks" class="vc-button vc-button-primary hidden justify-center px-4 py-3">Add Blocks to JSON</button>
            <button type="button" data-ai-apply="seo" class="vc-button vc-button-primary hidden justify-center px-4 py-3">Apply SEO Fields</button>
            <button type="button" data-ai-apply="builder" class="vc-button vc-button-danger hidden justify-center px-4 py-3">Replace Builder JSON</button>
            <button type="button" data-ai-clear class="vc-button vc-button-secondary justify-center px-4 py-3">Discard Draft</button>
        </div>

        <p class="text-xs text-[var(--vc-text-muted)]">
            Applying a draft only updates the current form values. Save the page separately to persist changes.
        </p>
    </section>
</aside>

<script>
    (() => {
        const defaultPrompts = {
            text: 'Write a concise conversion-oriented text block for this page.',
            faq: 'Create a short FAQ for this page.',
            cta: 'Suggest a CTA section with a clear next step.',
            seo: 'Generate an SEO title and meta description.',
            builder: 'Create a starter builder JSON draft for this page.',
        };

        const toneClasses = {
            idle: 'text-[var(--vc-text-muted)]',
            success: 'text-emerald-600',
            error: 'text-rose-600',
        };

        const buildDefaultContent = () => ({
            version: '1.0',
            layout: 'default',
            settings: {
                container: '1200px',
                background: '#ffffff',
            },
            sections: [],
        });

        const initAssistant = (root) => {
            const promptInput = root.querySelector('[data-ai-prompt]');
            const runButton = root.querySelector('[data-ai-run]');
            const statusBox = root.querySelector('[data-ai-status]');
            const previewWrap = root.querySelector('[data-ai-preview-wrap]');
            const previewNode = root.querySelector('[data-ai-preview]');
            const previewKind = root.querySelector('[data-ai-preview-kind]');
            const providerNode = root.querySelector('[data-ai-provider]');
            const modelNode = root.querySelector('[data-ai-model]');
            const currentActionNode = root.querySelector('[data-ai-current-action]');
            const form = root.closest('.grid')?.querySelector('form');
            const contentField = form?.querySelector('[name="content_json"]');
            const seoTitleField = form?.querySelector('[name="seo_title"]');
            const seoDescriptionField = form?.querySelector('[name="seo_description"]');
            const titleField = form?.querySelector('[name="title"]');
            const slugField = form?.querySelector('[name="slug"]');
            const statusField = form?.querySelector('[name="status"]');
            const csrfToken = form?.querySelector('input[name="_token"]')?.value ?? '';
            const providersEndpoint = root.dataset.providersEndpoint;
            const chatEndpoint = root.dataset.chatEndpoint;

            if (!promptInput || !runButton || !statusBox || !previewWrap || !previewNode || !previewKind || !contentField) {
                return;
            }

            let currentAction = 'text';
            let currentProvider = root.dataset.provider || 'openai';
            let currentDraft = null;

            const setStatus = (message, tone = 'idle') => {
                statusBox.textContent = message;
                statusBox.classList.remove(...Object.values(toneClasses));
                statusBox.classList.add(toneClasses[tone] || toneClasses.idle);
            };

            const updateCurrentAction = () => {
                currentActionNode.textContent = `Action: ${currentAction}`;
            };

            const parseContent = () => {
                try {
                    const parsed = JSON.parse(contentField.value || '');
                    return parsed && typeof parsed === 'object' ? parsed : buildDefaultContent();
                } catch (error) {
                    return buildDefaultContent();
                }
            };

            const ensureSection = (content) => {
                if (!Array.isArray(content.sections)) {
                    content.sections = [];
                }

                if (content.sections.length === 0) {
                    content.sections.push({
                        id: `section-${Date.now()}`,
                        blocks: [],
                    });
                }

                if (!Array.isArray(content.sections[0].blocks)) {
                    content.sections[0].blocks = [];
                }

                return content.sections[0];
            };

            const collectContext = () => ({
                title: titleField?.value?.trim() || 'New page',
                uri: slugField?.value?.trim() ? `/${slugField.value.trim().replace(/^\/+/, '')}` : '/',
                status: statusField?.value || 'draft',
                seo: {
                    title: seoTitleField?.value || '',
                    description: seoDescriptionField?.value || '',
                },
            });

            const renderDraft = () => {
                if (!currentDraft) {
                    previewWrap.classList.add('hidden');
                    return;
                }

                previewWrap.classList.remove('hidden');
                previewKind.textContent = currentDraft.kind || 'draft';
                previewNode.textContent = currentDraft.preview || '';

                root.querySelectorAll('[data-ai-apply]').forEach((button) => {
                    button.classList.add('hidden');
                });

                if (currentDraft.kind === 'seo') {
                    root.querySelector('[data-ai-apply="seo"]')?.classList.remove('hidden');
                } else if (currentDraft.kind === 'builder') {
                    root.querySelector('[data-ai-apply="builder"]')?.classList.remove('hidden');
                } else {
                    root.querySelector('[data-ai-apply="blocks"]')?.classList.remove('hidden');
                }
            };

            const postJson = async (url, payload) => {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data?.error?.message || 'AI request failed.');
                }

                return data;
            };

            const loadProviders = async () => {
                try {
                    const response = await fetch(providersEndpoint, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const payload = await response.json().catch(() => ({}));
                    const items = payload?.data?.items || [];
                    const activeProvider = items.find((item) => item.active) || items.find((item) => item.id === currentProvider);

                    if (activeProvider) {
                        currentProvider = activeProvider.id;
                        providerNode.textContent = activeProvider.name;
                        modelNode.textContent = activeProvider.default_model || 'Not set';
                    }
                } catch (error) {
                    setStatus('AI provider metadata could not be loaded. Draft generation is still available for configured providers.', 'idle');
                }
            };

            const requestDraft = async () => {
                runButton.disabled = true;
                setStatus('Generating AI draft...', 'idle');

                try {
                    const context = collectContext();
                    const payload = await postJson(chatEndpoint, {
                        provider: currentProvider,
                        action: currentAction,
                        instruction: promptInput.value.trim() || defaultPrompts[currentAction],
                        page: {
                            title: context.title,
                            uri: context.uri,
                            status: context.status,
                        },
                        seo: context.seo,
                    });

                    currentDraft = payload?.data?.draft || null;
                    renderDraft();
                    setStatus('Draft generated. Review it, then apply it to the form if it is acceptable.', 'success');
                } catch (error) {
                    setStatus(error.message, 'error');
                } finally {
                    runButton.disabled = false;
                }
            };

            const applyBlocks = () => {
                if (!currentDraft?.blocks) {
                    return;
                }

                const content = parseContent();
                const section = ensureSection(content);

                section.blocks = [...section.blocks, ...currentDraft.blocks];
                contentField.value = JSON.stringify(content, null, 2);
                setStatus('Draft blocks applied to the form. Save the page to persist changes.', 'success');
            };

            const applySeo = () => {
                if (!currentDraft?.seo) {
                    return;
                }

                if (seoTitleField) {
                    seoTitleField.value = currentDraft.seo.title || '';
                }

                if (seoDescriptionField) {
                    seoDescriptionField.value = currentDraft.seo.description || '';
                }

                setStatus('SEO draft applied to the form. Save the page to persist changes.', 'success');
            };

            const applyBuilder = () => {
                if (!currentDraft?.builder) {
                    return;
                }

                contentField.value = JSON.stringify(currentDraft.builder, null, 2);
                setStatus('Builder JSON replaced in the form. Save the page to persist changes.', 'success');
            };

            root.querySelectorAll('[data-ai-action]').forEach((button) => {
                button.addEventListener('click', () => {
                    currentAction = button.dataset.aiAction || 'text';
                    updateCurrentAction();

                    if (!promptInput.value.trim()) {
                        promptInput.value = defaultPrompts[currentAction] || '';
                    }
                });
            });

            runButton.addEventListener('click', requestDraft);
            root.querySelector('[data-ai-apply="blocks"]')?.addEventListener('click', applyBlocks);
            root.querySelector('[data-ai-apply="seo"]')?.addEventListener('click', applySeo);
            root.querySelector('[data-ai-apply="builder"]')?.addEventListener('click', applyBuilder);
            root.querySelector('[data-ai-clear]')?.addEventListener('click', () => {
                currentDraft = null;
                previewWrap.classList.add('hidden');
                setStatus('Draft discarded. Generate a new one when needed.', 'idle');
            });

            updateCurrentAction();
            loadProviders();
        };

        document.querySelectorAll('[data-ai-assistant]').forEach(initAssistant);
    })();
</script>
