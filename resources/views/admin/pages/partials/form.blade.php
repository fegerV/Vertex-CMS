<div class="grid gap-5 sm:grid-cols-2">
    <label class="block sm:col-span-2">
        <span class="mb-1 block text-sm font-medium">Title</span>
        <input
            type="text"
            name="title"
            value="{{ old('title', $page->title) }}"
            required
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('title')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Slug</span>
        <input
            type="text"
            name="slug"
            value="{{ old('slug', $page->slug) }}"
            placeholder="auto-from-title"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('slug')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Parent Page</span>
        <select name="parent_id" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
            <option value="">No parent</option>
            @foreach ($parentPages as $parentPage)
                <option value="{{ $parentPage->id }}" @selected((string) old('parent_id', $page->parent_id) === (string) $parentPage->id)>
                    {{ $parentPage->title }} ({{ $parentPage->uri }})
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Status</span>
        <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $page->status) === $status)>
                    {{ $status }}
                </option>
            @endforeach
        </select>
        @error('status')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Template</span>
        <input
            type="text"
            name="template"
            value="{{ old('template', $page->template ?: 'default') }}"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('template')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>
</div>

<label class="block">
    <span class="mb-1 block text-sm font-medium">Content JSON</span>
    <textarea
        name="content_json"
        rows="12"
        class="w-full rounded-md border border-slate-300 px-3 py-2 font-mono text-sm outline-none focus:border-slate-900"
    >{{ old('content_json', json_encode($page->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
    @error('content_json')
        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
    @enderror
</label>

@php
    $customFieldsJson = old('custom_fields_json', json_encode($page->custom_fields_json ?? [], JSON_UNESCAPED_UNICODE));
    $fieldGroupsPayload = ($allFieldGroups ?? $fieldGroups ?? collect())->map(fn ($group) => [
        'id' => $group->id,
        'name' => $group->name,
        'handle' => $group->handle,
        'description' => $group->description,
        'scope' => $group->scope,
        'fields_json' => $group->fields_json,
        'rules_json' => $group->rules_json,
    ])->values()->all();
    $currentTemplate = old('template', $page->template ?: 'default');
    $seo = $page->seoMeta;
@endphp

<section class="space-y-5 border-t border-slate-100 pt-5">
    <div>
        <h2 class="text-lg font-semibold">Custom Fields</h2>
        <p class="mt-1 text-sm text-slate-500">Lightweight ACF-style fields for page-specific metadata, hero subtitles, badges, CTA labels, and other template data.</p>
    </div>

    <input type="hidden" name="custom_fields_json" id="custom_fields_json_input" value="{{ $customFieldsJson }}">
    <input type="hidden" id="custom-fields-current-template" value="{{ $currentTemplate }}">

    <div id="custom-fields-editor" class="space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
        <div class="grid gap-3 rounded-lg border border-slate-200 bg-white p-4 lg:grid-cols-[minmax(0,1fr)_auto_auto_auto_auto]">
            <label class="block">
                <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Preset</span>
                <select id="custom-field-group-select" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Custom set</option>
                </select>
            </label>
            <button type="button" id="apply-field-group" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-100">
                Apply preset
            </button>
            <button type="button" id="save-field-group" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-100">
                Save as preset
            </button>
            <button type="button" id="update-field-group" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-100">
                Update preset
            </button>
            <button type="button" id="delete-field-group" class="rounded-md border border-red-200 bg-white px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                Delete preset
            </button>
        </div>

        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-slate-700">Field Group</p>
                <p id="field-group-meta" class="text-xs text-slate-500">Build a reusable preset from the current field list or apply an existing preset.</p>
            </div>
            <button type="button" id="add-custom-field" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm hover:bg-slate-100">
                Add field
            </button>
        </div>

        <div id="custom-fields-list" class="space-y-3"></div>
        <p class="text-xs text-slate-500">Each field stores `key`, `label`, `type`, `value`, and `description` in structured JSON.</p>
    </div>

    @error('custom_fields_json')
        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
    @enderror
</section>

<section class="space-y-5 border-t border-slate-100 pt-5">
    <div>
        <h2 class="text-lg font-semibold">SEO</h2>
        <p class="mt-1 text-sm text-slate-500">Page metadata for search engines and social previews.</p>
    </div>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">SEO title</span>
        <input
            type="text"
            name="seo_title"
            value="{{ old('seo_title', $seo?->title) }}"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >
        @error('seo_title')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">SEO description</span>
        <textarea
            name="seo_description"
            rows="3"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >{{ old('seo_description', $seo?->description) }}</textarea>
        @error('seo_description')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <div class="grid gap-5 sm:grid-cols-2">
        <label class="block">
            <span class="mb-1 block text-sm font-medium">Canonical URL</span>
            <input
                type="url"
                name="seo_canonical_url"
                value="{{ old('seo_canonical_url', $seo?->canonical_url) }}"
                class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
            >
            @error('seo_canonical_url')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium">Robots</span>
            <select name="seo_robots" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
                @foreach ($robotsOptions as $robots)
                    <option value="{{ $robots }}" @selected(old('seo_robots', $seo?->robots ?? 'index, follow') === $robots)>
                        {{ $robots }}
                    </option>
                @endforeach
            </select>
            @error('seo_robots')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <label class="block">
            <span class="mb-1 block text-sm font-medium">OG title</span>
            <input
                type="text"
                name="seo_og_title"
                value="{{ old('seo_og_title', $seo?->og_title) }}"
                class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
            >
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium">OG image media ID</span>
            <input
                type="number"
                name="seo_og_image"
                value="{{ old('seo_og_image', $seo?->og_image) }}"
                class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
            >
        </label>
    </div>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">OG description</span>
        <textarea
            name="seo_og_description"
            rows="3"
            class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
        >{{ old('seo_og_description', $seo?->og_description) }}</textarea>
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Schema JSON</span>
        <textarea
            name="seo_schema_json"
            rows="6"
            class="w-full rounded-md border border-slate-300 px-3 py-2 font-mono text-sm outline-none focus:border-slate-900"
        >{{ old('seo_schema_json', $seo?->schema_json ? json_encode($seo->schema_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        @error('seo_schema_json')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="flex items-center gap-2 text-sm">
        <input
            type="checkbox"
            name="seo_include_in_sitemap"
            value="1"
            @checked(old('seo_include_in_sitemap', $seo?->include_in_sitemap ?? true))
            class="rounded border-slate-300"
        >
        <span>Include in sitemap.xml</span>
    </label>
</section>

<div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5">
    @if ($page->exists)
        <p class="text-sm text-slate-500">URI: {{ $page->uri }}</p>
    @else
        <p class="text-sm text-slate-500">URI will be generated automatically.</p>
    @endif

    <button type="submit" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
        Save
    </button>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const list = document.getElementById('custom-fields-list');
                const hiddenInput = document.getElementById('custom_fields_json_input');
                const addButton = document.getElementById('add-custom-field');
                const groupSelect = document.getElementById('custom-field-group-select');
                const applyGroupButton = document.getElementById('apply-field-group');
                const saveGroupButton = document.getElementById('save-field-group');
                const updateGroupButton = document.getElementById('update-field-group');
                const deleteGroupButton = document.getElementById('delete-field-group');
                const groupMeta = document.getElementById('field-group-meta');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const templateInput = document.querySelector('input[name="template"]');
                const templateStateInput = document.getElementById('custom-fields-current-template');

                if (!list || !hiddenInput || !addButton || !groupSelect || !applyGroupButton || !saveGroupButton || !updateGroupButton || !deleteGroupButton || !groupMeta || !templateStateInput) {
                    return;
                }

                const parseState = () => {
                    try {
                        const parsed = JSON.parse(hiddenInput.value || '[]');
                        return Array.isArray(parsed) ? parsed : [];
                    } catch (error) {
                        return [];
                    }
                };

                const state = parseState();
                const fieldGroups = @json($fieldGroupsPayload);
                const fieldTypes = [
                    { value: 'text', label: 'Text' },
                    { value: 'textarea', label: 'Textarea' },
                    { value: 'number', label: 'Number' },
                    { value: 'boolean', label: 'Boolean' },
                    { value: 'url', label: 'URL' },
                ];
                const scopeOptions = [
                    { value: 'all_pages', label: 'All pages' },
                    { value: 'template', label: 'Only selected templates' },
                    { value: 'except_template', label: 'All except selected templates' },
                ];

                const escapeHtml = (value) => String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');

                const sync = () => {
                    hiddenInput.value = JSON.stringify(state);
                };

                const cloneFields = (fields) => JSON.parse(JSON.stringify(Array.isArray(fields) ? fields : []));
                const currentTemplate = () => {
                    const templateValue = templateInput?.value || templateStateInput.value || 'default';
                    return String(templateValue).trim() || 'default';
                };

                const groupAppliesToTemplate = (group, template) => {
                    const scope = String(group.scope || 'all_pages');
                    const templates = Array.isArray(group.rules_json?.templates) ? group.rules_json.templates.map(String) : [];

                    if (scope === 'template') {
                        return templates.includes(template);
                    }

                    if (scope === 'except_template') {
                        return !templates.includes(template);
                    }

                    return true;
                };

                const availableGroups = () => fieldGroups.filter((group) => groupAppliesToTemplate(group, currentTemplate()));
                const selectedGroup = () => fieldGroups.find((group) => String(group.id) === groupSelect.value) || null;
                const scopeLabel = (scope) => scopeOptions.find((option) => option.value === scope)?.label || 'All pages';

                const renderGroupSelect = () => {
                    const currentValue = groupSelect.value;
                    groupSelect.innerHTML = '<option value="">Custom set</option>' + availableGroups().map((group) =>
                        `<option value="${group.id}">${escapeHtml(group.name)}</option>`
                    ).join('');

                    if (availableGroups().some((group) => String(group.id) === currentValue)) {
                        groupSelect.value = currentValue;
                    } else {
                        groupSelect.value = '';
                    }

                    const group = selectedGroup();
                    groupMeta.textContent = group
                        ? `${group.handle} - ${scopeLabel(group.scope)}${group.description ? ' - ' + group.description : ''}`
                        : `Build a reusable preset from the current field list or apply an existing preset. Current template: ${currentTemplate()}`;
                    applyGroupButton.disabled = !group;
                    updateGroupButton.disabled = !group;
                    deleteGroupButton.disabled = !group;
                };

                const askScopeConfig = (existingScope = 'all_pages', existingRules = {}) => {
                    const scopePrompt = window.prompt(
                        'Preset scope: all_pages | template | except_template',
                        existingScope
                    );

                    if (!scopePrompt) {
                        return null;
                    }

                    const normalizedScope = scopePrompt.trim();
                    let templates = [];

                    if (normalizedScope === 'template' || normalizedScope === 'except_template') {
                        const templatesPrompt = window.prompt(
                            'Templates list (comma separated)',
                            Array.isArray(existingRules.templates) ? existingRules.templates.join(', ') : currentTemplate()
                        );

                        if (!templatesPrompt) {
                            return null;
                        }

                        templates = templatesPrompt
                            .split(',')
                            .map((item) => item.trim())
                            .filter(Boolean);
                    }

                    return {
                        scope: normalizedScope,
                        rules: templates.length > 0 ? { templates } : {},
                    };
                };

                const renderValueInput = (field, index) => {
                    if (field.type === 'textarea') {
                        return `<textarea data-index="${index}" data-prop="value" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">${escapeHtml(field.value)}</textarea>`;
                    }

                    if (field.type === 'boolean') {
                        return `<label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" data-index="${index}" data-prop="value" ${field.value ? 'checked' : ''} class="rounded border-slate-300"> <span>Enabled</span></label>`;
                    }

                    const inputType = field.type === 'number' ? 'number' : (field.type === 'url' ? 'url' : 'text');

                    return `<input type="${inputType}" data-index="${index}" data-prop="value" value="${escapeHtml(field.value)}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">`;
                };

                const render = () => {
                    if (state.length === 0) {
                        list.innerHTML = '<div class="rounded-md border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm text-slate-400">No custom fields yet</div>';
                        sync();
                        return;
                    }

                    list.innerHTML = state.map((field, index) => `
                        <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-medium text-slate-700">${escapeHtml(field.label || field.key || 'Field')}</div>
                                <button type="button" data-remove="${index}" class="text-sm text-red-600 hover:text-red-700">Remove</button>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="block">
                                    <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Key</span>
                                    <input type="text" data-index="${index}" data-prop="key" value="${escapeHtml(field.key)}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                </label>
                                <label class="block">
                                    <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Label</span>
                                    <input type="text" data-index="${index}" data-prop="label" value="${escapeHtml(field.label)}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                </label>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="block">
                                    <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Type</span>
                                    <select data-index="${index}" data-prop="type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                        ${fieldTypes.map((type) => `<option value="${type.value}"${type.value === (field.type || 'text') ? ' selected' : ''}>${type.label}</option>`).join('')}
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Description</span>
                                    <input type="text" data-index="${index}" data-prop="description" value="${escapeHtml(field.description)}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                </label>
                            </div>
                            <div>
                                <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Value</span>
                                ${renderValueInput(field, index)}
                            </div>
                        </div>
                    `).join('');

                    sync();
                };

                const requestGroup = async (url, method, payload) => {
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || data.error || 'Field group request failed');
                    }

                    return data.group;
                };

                list.addEventListener('input', (event) => {
                    const target = event.target;
                    if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement)) {
                        return;
                    }

                    const index = Number(target.dataset.index);
                    const prop = target.dataset.prop;

                    if (!prop || !state[index]) {
                        return;
                    }

                    if (target instanceof HTMLInputElement && target.type === 'checkbox') {
                        state[index][prop] = target.checked;
                    } else {
                        state[index][prop] = target.value;
                    }

                    if (prop === 'type') {
                        state[index].value = target.value === 'boolean' ? false : '';
                        render();
                        return;
                    }

                    sync();
                });

                list.addEventListener('click', (event) => {
                    const target = event.target;
                    if (!(target instanceof HTMLElement) || target.dataset.remove === undefined) {
                        return;
                    }

                    state.splice(Number(target.dataset.remove), 1);
                    render();
                });

                addButton.addEventListener('click', () => {
                    state.push({
                        key: '',
                        label: '',
                        type: 'text',
                        value: '',
                        description: '',
                    });
                    render();
                });

                groupSelect.addEventListener('change', () => {
                    renderGroupSelect();
                });

                templateInput?.addEventListener('input', () => {
                    templateStateInput.value = currentTemplate();
                    renderGroupSelect();
                });

                applyGroupButton.addEventListener('click', () => {
                    const group = selectedGroup();
                    if (!group) {
                        alert('Select a preset first.');
                        return;
                    }

                    const shouldMerge = confirm('Merge preset fields with the current set? Click Cancel to replace the current set.');
                    const nextFields = cloneFields(group.fields_json || []);

                    if (shouldMerge) {
                        const existingKeys = new Set(state.map((field) => field.key));
                        nextFields.forEach((field) => {
                            if (!existingKeys.has(field.key)) {
                                state.push(field);
                            }
                        });
                    } else {
                        state.splice(0, state.length, ...nextFields);
                    }

                    render();
                });

                saveGroupButton.addEventListener('click', async () => {
                    const name = window.prompt('Preset name');
                    if (!name) return;

                    const description = window.prompt('Preset description (optional)') || '';
                    const scopeConfig = askScopeConfig('all_pages', {});
                    if (!scopeConfig) return;

                    try {
                        const group = await requestGroup('/admin/custom-field-groups', 'POST', {
                            name,
                            description,
                            scope: scopeConfig.scope,
                            rules: scopeConfig.rules,
                            fields: state,
                        });

                        fieldGroups.push(group);
                        groupSelect.value = String(group.id);
                        renderGroupSelect();
                    } catch (error) {
                        alert(error.message);
                    }
                });

                updateGroupButton.addEventListener('click', async () => {
                    const group = selectedGroup();
                    if (!group) {
                        alert('Select a preset to update.');
                        return;
                    }

                    const name = window.prompt('Preset name', group.name);
                    if (!name) return;

                    const description = window.prompt('Preset description', group.description || '') || '';
                    const scopeConfig = askScopeConfig(group.scope || 'all_pages', group.rules_json || {});
                    if (!scopeConfig) return;

                    try {
                        const updated = await requestGroup(`/admin/custom-field-groups/${group.id}`, 'PUT', {
                            name,
                            handle: group.handle,
                            description,
                            scope: scopeConfig.scope,
                            rules: scopeConfig.rules,
                            fields: state,
                        });

                        const index = fieldGroups.findIndex((item) => item.id === updated.id);
                        if (index !== -1) {
                            fieldGroups[index] = updated;
                        }
                        groupSelect.value = String(updated.id);
                        renderGroupSelect();
                    } catch (error) {
                        alert(error.message);
                    }
                });

                deleteGroupButton.addEventListener('click', async () => {
                    const group = selectedGroup();
                    if (!group || !confirm(`Delete preset "${group.name}"?`)) {
                        return;
                    }

                    try {
                        const response = await fetch(`/admin/custom-field-groups/${group.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });
                        const data = await response.json();
                        if (!response.ok || !data.ok) {
                            throw new Error(data.message || data.error || 'Delete failed');
                        }
                        const index = fieldGroups.findIndex((item) => item.id === group.id);
                        if (index !== -1) {
                            fieldGroups.splice(index, 1);
                        }
                        groupSelect.value = '';
                        renderGroupSelect();
                    } catch (error) {
                        alert(error.message);
                    }
                });

                renderGroupSelect();
                render();
            });
        </script>
    @endpush
@endonce
