<div class="vc-form-section">
<div class="vc-panel vc-panel-muted p-5">
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Основные параметры</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Заполните ключевые поля страницы. Заголовок, статус и шаблон определяют дальнейший сценарий публикации и рендера.</p>
    </div>

    <div class="vc-form-grid vc-form-grid-2">
    <label class="vc-field sm:col-span-2">
        <span class="vc-field-label">Заголовок</span>
        <span class="vc-field-help">Название страницы в админке и основной источник для автогенерации slug.</span>
        <input
            type="text"
            name="title"
            value="{{ old('title', $page->title) }}"
            required
            class="vc-input"
        >
        @error('title')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <label class="vc-field">
        <span class="vc-field-label">Slug</span>
        <span class="vc-field-help">Можно оставить пустым, тогда slug будет создан автоматически из заголовка.</span>
        <input
            type="text"
            name="slug"
            value="{{ old('slug', $page->slug) }}"
            placeholder="avto-iz-zagolovka"
            class="vc-input"
        >
        @error('slug')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <label class="vc-field">
        <span class="vc-field-label">Родительская страница</span>
        <span class="vc-field-help">Используйте иерархию, если страница должна быть дочерней и наследовать структуру URL.</span>
        <select name="parent_id" class="vc-select">
            <option value="">Без родителя</option>
            @foreach ($parentPages as $parentPage)
                <option value="{{ $parentPage->id }}" @selected((string) old('parent_id', $page->parent_id) === (string) $parentPage->id)>
                    {{ $parentPage->title }} ({{ $parentPage->uri }})
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <label class="vc-field">
        <span class="vc-field-label">Статус</span>
        <span class="vc-field-help">Черновик скрыт от публичного URL, опубликованная страница доступна посетителям.</span>
        <select name="status" class="vc-select">
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $page->status) === $status)>
                    {{ $status }}
                </option>
            @endforeach
        </select>
        @error('status')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <label class="vc-field">
        <span class="vc-field-label">Шаблон</span>
        <span class="vc-field-help">Имя шаблона влияет на рендер и набор доступных custom fields.</span>
        <input
            type="text"
            name="template"
            value="{{ old('template', $page->template ?: 'default') }}"
            class="vc-input"
        >
        @error('template')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>
</div>
</div>

<section class="vc-panel vc-panel-muted p-5 vc-form-section">
    <div>
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Page Builder</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">The visual builder now syncs directly with the backend block registry. Use the raw JSON editor only for advanced/manual adjustments.</p>
    </div>

    <div
        data-vc-page-builder-prototype
        data-input-target="content_json_input"
        data-initial-value="{{ e(json_encode($page->content_json, JSON_UNESCAPED_UNICODE)) }}"
        class="mt-5"
    ></div>

    <details class="mt-5 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface)] p-4">
        <summary class="cursor-pointer text-sm font-semibold text-[var(--vc-text)]">Raw content JSON</summary>

        <label class="vc-field mt-4">
            <span class="vc-field-label">Page content payload</span>
            <textarea
                id="content_json_input"
                name="content_json"
                rows="12"
                class="vc-textarea font-mono text-sm"
            >{{ old('content_json', json_encode($page->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
            @error('content_json')
                <span class="vc-field-error">{{ $message }}</span>
            @enderror
        </label>
    </details>
</section>

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
    $selectedTermIds = collect(old('term_ids', $page->terms?->pluck('id')->all() ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();
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

@if (($taxonomies ?? collect())->isNotEmpty())
    <section class="vc-panel vc-panel-muted p-5 vc-form-section">
        <div>
            <h2 class="text-lg font-semibold text-[var(--vc-text)]">Таксономии</h2>
            <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Привяжите категории и теги, чтобы страница попала в архивы терминов и в публичный taxonomy API.</p>
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

<section class="vc-panel vc-panel-muted p-5 vc-form-section">
    <div>
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">SEO</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Метаданные страницы для поисковых систем, социальных превью и sitemap.</p>
    </div>

    <label class="vc-field">
        <span class="vc-field-label">SEO title</span>
        <span class="vc-field-help">Заголовок сниппета в поисковой выдаче. Если не заполнить, можно использовать основной заголовок страницы.</span>
        <input
            type="text"
            name="seo_title"
            value="{{ old('seo_title', $seo?->title) }}"
            class="vc-input"
        >
        @error('seo_title')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <label class="vc-field">
        <span class="vc-field-label">SEO description</span>
        <span class="vc-field-help">Краткое описание для поисковой выдачи и предпросмотра страницы.</span>
        <textarea
            name="seo_description"
            rows="3"
            class="vc-textarea"
        >{{ old('seo_description', $seo?->description) }}</textarea>
        @error('seo_description')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <div class="vc-form-grid vc-form-grid-2">
        <label class="vc-field">
            <span class="vc-field-label">Canonical URL</span>
            <input
                type="url"
                name="seo_canonical_url"
                value="{{ old('seo_canonical_url', $seo?->canonical_url) }}"
                class="vc-input"
            >
            @error('seo_canonical_url')
                <span class="vc-field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Robots</span>
            <select name="seo_robots" class="vc-select">
                @foreach ($robotsOptions as $robots)
                    <option value="{{ $robots }}" @selected(old('seo_robots', $seo?->robots ?? 'index, follow') === $robots)>
                        {{ $robots }}
                    </option>
                @endforeach
            </select>
            @error('seo_robots')
                <span class="vc-field-error">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="vc-form-grid vc-form-grid-2">
        <label class="vc-field">
            <span class="vc-field-label">OG title</span>
            <input
                type="text"
                name="seo_og_title"
                value="{{ old('seo_og_title', $seo?->og_title) }}"
                class="vc-input"
            >
        </label>

        <label class="vc-field">
            <span class="vc-field-label">OG image media ID</span>
            <input
                type="number"
                name="seo_og_image"
                value="{{ old('seo_og_image', $seo?->og_image) }}"
                class="vc-input"
            >
        </label>
    </div>

    <label class="vc-field">
        <span class="vc-field-label">OG description</span>
        <textarea
            name="seo_og_description"
            rows="3"
            class="vc-textarea"
        >{{ old('seo_og_description', $seo?->og_description) }}</textarea>
    </label>

    <label class="vc-field">
        <span class="vc-field-label">Schema JSON</span>
        <span class="vc-field-help">Оставьте пустым, если страница не требует ручной schema.org разметки.</span>
        <textarea
            name="seo_schema_json"
            rows="6"
            class="vc-textarea font-mono text-sm"
        >{{ old('seo_schema_json', $seo?->schema_json ? json_encode($seo->schema_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        @error('seo_schema_json')
            <span class="vc-field-error">{{ $message }}</span>
        @enderror
    </label>

    <label class="vc-checkbox-row text-sm text-[var(--vc-text)]">
        <input
            type="checkbox"
            name="seo_include_in_sitemap"
            value="1"
            @checked(old('seo_include_in_sitemap', $seo?->include_in_sitemap ?? true))
            class="rounded border-slate-300"
        >
        <span>Включать страницу в sitemap.xml</span>
    </label>
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

