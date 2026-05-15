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
@endphp

<section class="vc-panel vc-panel-muted p-5 vc-form-section">
    <div>
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Пользовательские поля</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Лёгкий аналог ACF для метаданных шаблона: подзаголовков, бейджей, CTA-подписей и других служебных данных страницы.</p>
    </div>

    <input type="hidden" name="custom_fields_json" id="custom_fields_json_input" value="{{ $customFieldsJson }}">
    <input type="hidden" id="custom-fields-current-template" value="{{ $currentTemplate }}">

    <div id="custom-fields-editor" class="space-y-3 rounded-lg border border-[var(--vc-border)] bg-[var(--vc-surface)] p-4">
        <div class="grid gap-3 rounded-lg border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] p-4 lg:grid-cols-[minmax(0,1fr)_auto_auto_auto_auto]">
            <label class="vc-field">
                <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-[var(--vc-text-soft)]">Пресет</span>
                <select id="custom-field-group-select" class="vc-select text-sm">
                    <option value="">Произвольный набор</option>
                </select>
            </label>
            <button type="button" id="apply-field-group" class="vc-button vc-button-secondary">
                Применить
            </button>
            <button type="button" id="save-field-group" class="vc-button vc-button-secondary">
                Сохранить как пресет
            </button>
            <button type="button" id="update-field-group" class="vc-button vc-button-secondary">
                Обновить
            </button>
            <button type="button" id="delete-field-group" class="vc-button vc-button-danger">
                Удалить
            </button>
        </div>

        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-[var(--vc-text)]">Группа полей</p>
                <p id="field-group-meta" class="text-xs text-[var(--vc-text-soft)]">Соберите повторно используемый пресет из текущего набора полей или примените уже созданный.</p>
            </div>
            <button type="button" id="add-custom-field" class="vc-button vc-button-secondary">
                Добавить поле
            </button>
        </div>

        <div id="custom-fields-list" class="space-y-3"></div>
        <p class="text-xs text-[var(--vc-text-soft)]">Каждое поле сохраняет `key`, `label`, `type`, `value` и `description` в структурированном JSON.</p>
    </div>

    @error('custom_fields_json')
        <span class="vc-field-error">{{ $message }}</span>
    @enderror
</section>

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
            { value: 'text', label: 'Текст' },
            { value: 'textarea', label: 'Многострочный текст' },
            { value: 'number', label: 'Число' },
            { value: 'boolean', label: 'Переключатель' },
            { value: 'url', label: 'Ссылка' },
        ];
        const scopeOptions = [
            { value: 'all_pages', label: 'Все страницы' },
            { value: 'template', label: 'Только указанные шаблоны' },
            { value: 'except_template', label: 'Все, кроме указанных шаблонов' },
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
        const scopeLabel = (scope) => scopeOptions.find((option) => option.value === scope)?.label || 'Все страницы';

        const renderGroupSelect = () => {
            const currentValue = groupSelect.value;
            groupSelect.innerHTML = '<option value="">Произвольный набор</option>' + availableGroups().map((group) =>
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
                : `Соберите повторно используемый пресет из текущего набора полей или примените уже созданный. Текущий шаблон: ${currentTemplate()}`;
            applyGroupButton.disabled = !group;
            updateGroupButton.disabled = !group;
            deleteGroupButton.disabled = !group;
        };

        const askScopeConfig = (existingScope = 'all_pages', existingRules = {}) => {
            const scopePrompt = window.prompt(
                'Область действия пресета: all_pages | template | except_template',
                existingScope
            );

            if (!scopePrompt) {
                return null;
            }

            const normalizedScope = scopePrompt.trim();
            let templates = [];

            if (normalizedScope === 'template' || normalizedScope === 'except_template') {
                const templatesPrompt = window.prompt(
                    'Список шаблонов через запятую',
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
                return `<label class="inline-flex items-center gap-2 text-sm text-[var(--vc-text)]"><input type="checkbox" data-index="${index}" data-prop="value" ${field.value ? 'checked' : ''} class="rounded border-slate-300"> <span>Включено</span></label>`;
            }

            const inputType = field.type === 'number' ? 'number' : (field.type === 'url' ? 'url' : 'text');

            return `<input type="${inputType}" data-index="${index}" data-prop="value" value="${escapeHtml(field.value)}" class="vc-input text-sm">`;
        };

        const render = () => {
            if (state.length === 0) {
                list.innerHTML = '<div class="rounded-md border border-dashed border-[var(--vc-border-strong)] bg-[var(--vc-surface-strong)] px-4 py-6 text-center text-sm text-[var(--vc-text-soft)]">Пока нет ни одного пользовательского поля</div>';
                sync();
                return;
            }

            list.innerHTML = state.map((field, index) => `
                <div class="rounded-lg border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-medium text-[var(--vc-text)]">${escapeHtml(field.label || field.key || 'Поле')}</div>
                        <button type="button" data-remove="${index}" class="text-sm text-rose-500 hover:text-rose-400">Удалить</button>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-[var(--vc-text-soft)]">Ключ</span>
                            <input type="text" data-index="${index}" data-prop="key" value="${escapeHtml(field.key)}" class="vc-input text-sm">
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-[var(--vc-text-soft)]">Название</span>
                            <input type="text" data-index="${index}" data-prop="label" value="${escapeHtml(field.label)}" class="vc-input text-sm">
                        </label>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-[var(--vc-text-soft)]">Тип</span>
                            <select data-index="${index}" data-prop="type" class="vc-select text-sm">
                                ${fieldTypes.map((type) => `<option value="${type.value}"${type.value === (field.type || 'text') ? ' selected' : ''}>${type.label}</option>`).join('')}
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-[var(--vc-text-soft)]">Описание</span>
                            <input type="text" data-index="${index}" data-prop="description" value="${escapeHtml(field.description)}" class="vc-input text-sm">
                        </label>
                    </div>
                    <div>
                        <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-[var(--vc-text-soft)]">Значение</span>
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
                throw new Error(data.message || data.error || 'Не удалось выполнить операцию с группой полей');
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
                alert('Сначала выберите пресет.');
                return;
            }

            const shouldMerge = confirm('Объединить поля пресета с текущим набором? Нажмите "Отмена", чтобы полностью заменить текущий набор.');
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
            const name = window.prompt('Название пресета');
            if (!name) return;

            const description = window.prompt('Описание пресета (необязательно)') || '';
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
                alert('Сначала выберите пресет для обновления.');
                return;
            }

            const name = window.prompt('Название пресета', group.name);
            if (!name) return;

            const description = window.prompt('Описание пресета', group.description || '') || '';
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
            if (!group || !confirm(`Удалить пресет "${group.name}"?`)) {
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
                    throw new Error(data.message || data.error || 'Не удалось удалить пресет');
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
