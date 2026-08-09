<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';

const props = defineProps({
    registryUrl: { type: String, required: true },
    storeUrl: { type: String, required: true },
    updateUrlTemplate: { type: String, required: true },
    submissionsUrlTemplate: { type: String, default: '' },
    analyticsUrlTemplate: { type: String, default: '' },
    builderRouteTemplate: { type: String, required: true },
    publicPreviewUrl: { type: String, default: '' },
    exitUrl: { type: String, required: true },
    csrfToken: { type: String, default: '' },
    initialForm: {
        type: Object,
        default: () => ({}),
    },
});

const CORE_KEYS = ['id', 'name', 'label', 'type', 'sort_order', 'required', 'visible', 'default_value', 'placeholder', 'help_text', 'css_class'];
const WORKSPACES = ['build', 'appearance', 'behavior', 'emails', 'integrations', 'visibility', 'submissions', 'reports'];
const INSPECTOR_TABS = ['field', 'validation', 'logic', 'appearance', 'advanced'];
const WORKSPACE_LABELS = {
    build: 'Сборка',
    appearance: 'Внешний вид',
    behavior: 'Поведение',
    emails: 'Email',
    integrations: 'Интеграции',
    visibility: 'Доступ',
    submissions: 'Заявки',
    reports: 'Отчёты',
};
const INSPECTOR_LABELS = {
    field: 'Поле',
    validation: 'Проверка',
    logic: 'Логика',
    appearance: 'Вид',
    advanced: 'Дополнительно',
};
const FIELD_ICONS = {
    text: 'T',
    email: '@',
    tel: '☎',
    number: '#',
    textarea: '¶',
    select: '⌄',
    radio: '○',
    checkbox: '✓',
    checkbox_group: '☑',
    file: '↑',
    date: '◷',
    time: 'TM',
    url: '↗',
    name: 'NM',
    address: 'AD',
    consent: '✓',
    rating: '★',
    hidden: 'H',
    calculator: '=',
    heading: 'H2',
    divider: '—',
    html: '</>',
    page_break: '↳',
};
const FORM_TEMPLATES = [
    {
        id: 'contact',
        label: 'Контактная форма',
        type: 'standard',
        description: 'Имя, email, телефон и сообщение для быстрых заявок.',
        settings: {
            submit_label: 'Отправить заявку',
            success_message: 'Спасибо! Мы скоро свяжемся с вами.',
        },
        fields: [
            { type: 'name', label: 'Ваше имя', name: 'name', required: true },
            { type: 'email', label: 'Email', name: 'email', placeholder: 'mail@example.com', required: true },
            { type: 'tel', label: 'Телефон', name: 'phone', placeholder: '+7 900 000-00-00' },
            { type: 'textarea', label: 'Сообщение', name: 'message', placeholder: 'Коротко опишите задачу', required: true, options: { rows: 5 } },
            { type: 'consent', label: 'Согласие', name: 'privacy_consent', options: { consent_text: 'Я согласен на обработку персональных данных.' } },
        ],
    },
    {
        id: 'lead',
        label: 'Лид-форма',
        type: 'standard',
        description: 'Минимальная форма для посадочных страниц и квизов.',
        settings: {
            submit_label: 'Получить консультацию',
            success_message: 'Заявка принята. Команда уже получила уведомление.',
        },
        fields: [
            { type: 'heading', label: 'Получите расчёт за 15 минут', name: 'lead_heading', options: { level: 'h2' } },
            { type: 'name', label: 'Имя', name: 'name', required: true },
            { type: 'tel', label: 'Телефон', name: 'phone', required: true },
            { type: 'select', label: 'Интересующая услуга', name: 'service', required: true, options: { choices: [
                { value: 'site', label: 'Сайт' },
                { value: 'shop', label: 'Интернет-магазин' },
                { value: 'support', label: 'Поддержка' },
            ] } },
        ],
    },
    {
        id: 'calculator',
        label: 'Калькулятор',
        type: 'calculator',
        description: 'Числовые поля и итоговая формула для расчётов.',
        settings: {
            submit_label: 'Отправить расчёт',
            success_message: 'Расчёт сохранён, мы уточним детали.',
        },
        fields: [
            { type: 'number', label: 'Площадь', name: 'area', placeholder: '100', required: true, options: { min: 1, step: 1 } },
            { type: 'number', label: 'Цена за единицу', name: 'price', placeholder: '2500', required: true, options: { min: 0, step: 100 } },
            { type: 'calculator', label: 'Итого', name: 'total', options: { formula: '{area} * {price}', depends_on: ['area', 'price'], prefix: '₽ ', precision: 0, live: true, readonly: true } },
            { type: 'email', label: 'Email для отправки расчёта', name: 'email', required: true },
        ],
    },
    {
        id: 'booking',
        label: 'Бронирование',
        type: 'standard',
        description: 'Дата, контакты и пожелания клиента.',
        settings: {
            submit_label: 'Забронировать',
            success_message: 'Бронирование отправлено. Мы подтвердим время.',
        },
        fields: [
            { type: 'name', label: 'Имя', name: 'name', required: true },
            { type: 'tel', label: 'Телефон', name: 'phone', required: true },
            { type: 'date', label: 'Желаемая дата', name: 'date', required: true },
            { type: 'time', label: 'Желаемое время', name: 'time' },
            { type: 'textarea', label: 'Комментарий', name: 'comment', placeholder: 'Количество гостей, пожелания, детали' },
        ],
    },
    {
        id: 'wizard',
        label: 'Многошаговая форма',
        type: 'standard',
        description: 'Форма с разделением на шаги через Page Break.',
        settings: {
            submit_label: 'Завершить',
            success_message: 'Спасибо! Все шаги успешно отправлены.',
        },
        fields: [
            { type: 'heading', label: 'Шаг 1. Контакты', name: 'step_contacts' },
            { type: 'text', label: 'Имя', name: 'name', required: true },
            { type: 'email', label: 'Email', name: 'email', required: true },
            { type: 'page_break', label: 'Следующий шаг', name: 'page_break_1', options: { page_title: 'Детали проекта' } },
            { type: 'select', label: 'Тип проекта', name: 'project_type', options: { choices: [
                { value: 'landing', label: 'Лендинг' },
                { value: 'cms', label: 'CMS' },
                { value: 'shop', label: 'Магазин' },
            ] } },
            { type: 'textarea', label: 'Описание задачи', name: 'brief', required: true },
        ],
    },
];

const registry = ref([]);
const registryCategories = ref([]);
const registryLoaded = ref(false);
const loadingRegistry = ref(false);
const saveState = ref('idle');
const saveMessage = ref('Draft');
const errorMessage = ref('');
const loadingSubmissions = ref(false);
const loadingAnalytics = ref(false);
const submissionsError = ref('');
const analyticsError = ref('');
const submissions = ref([]);
const submissionsPagination = ref(null);
const analytics = ref(null);
const selectedSubmissionIds = ref([]);
const draggedFieldIndex = ref(null);
const dragOverFieldIndex = ref(null);

const ui = reactive({
    workspace: 'build',
    inspectorTab: 'field',
    selectedFieldId: null,
    paletteSearch: '',
    showTemplates: false,
    submissionsSearch: '',
    submissionsStatus: '',
    analyticsDays: 30,
});

const form = reactive({
    id: props.initialForm.id ?? null,
    name: props.initialForm.name ?? '',
    slug: props.initialForm.slug ?? '',
    type: props.initialForm.type ?? 'standard',
    description: props.initialForm.description ?? '',
    is_active: props.initialForm.is_active ?? false,
    settings: {
        submit_label: 'Submit',
        success_message: 'Thank you!',
        error_message: 'Проверьте поля и попробуйте ещё раз.',
        redirect_url: '',
        layout_density: 'comfortable',
        button_style: 'solid',
        theme: 'default',
        honeypot_enabled: true,
        turnstile_enabled: false,
        notify_admin_emails: '',
        notify_admin_subject: 'Новая заявка: {form_name}',
        autoresponder_enabled: false,
        autoresponder_subject: 'Мы получили вашу заявку',
        autoresponder_body: 'Здравствуйте! Спасибо за обращение, мы скоро ответим.',
        webhooks: [],
        require_login: false,
        retention_days: 365,
        save_resume_enabled: false,
        resume_days: 30,
        ...(props.initialForm.settings ?? {}),
    },
    fields: [],
});

function slugify(value) {
    return String(value ?? '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function getRegistryEntry(type) {
    return registry.value.find((item) => item.type === type) ?? null;
}

function isCoreProp(prop) {
    return CORE_KEYS.includes(prop);
}

function deepClone(value) {
    return JSON.parse(JSON.stringify(value));
}

function normalizeChoices(choices) {
    if (!Array.isArray(choices)) {
        return [];
    }

    return choices
        .map((choice) => ({
            value: String(choice?.value ?? ''),
            label: String(choice?.label ?? ''),
        }))
        .filter((choice) => choice.value !== '' || choice.label !== '');
}

function normalizeField(field) {
    const entry = getRegistryEntry(field.type);
    const base = deepClone(entry?.default_field ?? {
        id: 'fld_' + Math.random().toString(36).slice(2, 10),
        type: field.type ?? 'text',
        name: 'field_' + Math.random().toString(36).slice(2, 8),
        label: 'Field',
        sort_order: 0,
        required: false,
        visible: true,
        options: {},
    });

    const normalized = {
        ...base,
        ...field,
        options: {
            ...(base.options ?? {}),
            ...(field.options ?? {}),
        },
    };

    Object.entries(field).forEach(([key, value]) => {
        if (!isCoreProp(key) && key !== 'options' && value !== undefined) {
            normalized.options[key] = value;
        }
    });

    Object.entries(normalized).forEach(([key, value]) => {
        if (!isCoreProp(key) && key !== 'options' && value !== undefined && normalized.options[key] === undefined) {
            normalized.options[key] = value;
        }
    });

    if (Array.isArray(normalized.options.choices)) {
        normalized.options.choices = normalizeChoices(normalized.options.choices);
    }

    normalized.id = normalized.id ?? 'fld_' + Math.random().toString(36).slice(2, 10);

    return normalized;
}

function selectedField() {
    return form.fields.find((field) => String(field.id) === String(ui.selectedFieldId)) ?? null;
}

const currentField = computed(() => selectedField());
const currentFieldIndex = computed(() => form.fields.findIndex((field) => String(field.id) === String(ui.selectedFieldId)));
const selectedRegistryEntry = computed(() => currentField.value ? getRegistryEntry(currentField.value.type) : null);
const buildSummary = computed(() => {
    const required = form.fields.filter((field) => field.required).length;
    const logic = form.fields.filter((field) => hasConditional(field)).length;
    const pages = Math.max(1, form.fields.filter((field) => field.type === 'page_break').length + 1);

    return { required, logic, pages };
});
const availableConditionalFields = computed(() => form.fields
    .filter((field) => String(field.id) !== String(ui.selectedFieldId))
    .filter((field) => !['heading', 'divider', 'html', 'page_break'].includes(field.type)));
const completionScore = computed(() => {
    let score = 0;
    if (form.name.trim()) score += 25;
    if (form.fields.length > 0) score += 35;
    if (form.settings.submit_label) score += 15;
    if (form.settings.success_message) score += 15;
    if (form.settings.notify_admin_emails) score += 10;

    return Math.min(score, 100);
});
const submissionsUrl = computed(() => {
    if (!form.id || !props.submissionsUrlTemplate) {
        return '';
    }

    return props.submissionsUrlTemplate.replace('__FORM_ID__', String(form.id));
});
const analyticsUrl = computed(() => {
    if (!form.id || !props.analyticsUrlTemplate) {
        return '';
    }

    return props.analyticsUrlTemplate.replace('__FORM_ID__', String(form.id));
});
const analyticsSummary = computed(() => analytics.value?.summary ?? analytics.value?.totals ?? analytics.value ?? {});
const conversionRate = computed(() => {
    const views = Number(analyticsSummary.value.views ?? analyticsSummary.value.total_views ?? 0);
    const totalSubmissions = Number(analyticsSummary.value.submissions ?? analyticsSummary.value.total_submissions ?? submissionsPagination.value?.total ?? 0);

    if (views <= 0) {
        return totalSubmissions > 0 ? '100%' : '0%';
    }

    return `${Math.round((totalSubmissions / views) * 1000) / 10}%`;
});

const filteredCategories = computed(() => {
    const search = ui.paletteSearch.trim().toLowerCase();

    return registryCategories.value
        .map((category) => {
            const fields = registry.value.filter((field) => field.category === category.id)
                .filter((field) => {
                    if (search === '') {
                        return true;
                    }

                    return field.label.toLowerCase().includes(search)
                        || field.type.toLowerCase().includes(search)
                        || String(field.description ?? '').toLowerCase().includes(search);
                });

            return {
                ...category,
                fields,
            };
        })
        .filter((category) => category.fields.length > 0);
});

const isDirty = ref(false);
const autosaveTimer = ref(null);
const suppressDirty = ref(false);
const hasLoadedInitialState = ref(false);
const undoStack = ref([]);
const redoStack = ref([]);
const lastHistoryState = ref('');
const historyTimer = ref(null);
const restoringHistory = ref(false);

function historyState() {
    return JSON.stringify({
        name: form.name,
        slug: form.slug,
        type: form.type,
        description: form.description,
        is_active: form.is_active,
        settings: form.settings,
        fields: form.fields,
    });
}

function restoreHistoryState(serialized) {
    const state = JSON.parse(serialized);
    restoringHistory.value = true;
    Object.assign(form, deepClone(state));
    ui.selectedFieldId = form.fields.find((field) => String(field.id) === String(ui.selectedFieldId))?.id
        ?? form.fields[0]?.id
        ?? null;
    window.setTimeout(() => { restoringHistory.value = false; }, 0);
}

function recordHistory() {
    if (!hasLoadedInitialState.value || restoringHistory.value) return;
    const nextState = historyState();
    if (nextState === lastHistoryState.value) return;

    undoStack.value.push(lastHistoryState.value);
    if (undoStack.value.length > 50) undoStack.value.shift();
    redoStack.value = [];
    lastHistoryState.value = nextState;
}

function scheduleHistory() {
    if (historyTimer.value) window.clearTimeout(historyTimer.value);
    historyTimer.value = window.setTimeout(recordHistory, 450);
}

function undo() {
    if (undoStack.value.length === 0) return;
    const previous = undoStack.value.pop();
    redoStack.value.push(historyState());
    restoreHistoryState(previous);
    lastHistoryState.value = previous;
}

function redo() {
    if (redoStack.value.length === 0) return;
    const next = redoStack.value.pop();
    undoStack.value.push(historyState());
    restoreHistoryState(next);
    lastHistoryState.value = next;
}

function handleHistoryShortcut(event) {
    if (!(event.ctrlKey || event.metaKey)) return;
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target?.tagName) || event.target?.isContentEditable) return;
    if (event.key.toLowerCase() !== 'z') return;
    event.preventDefault();
    event.shiftKey ? redo() : undo();
}

watch(() => [form.name, form.description, form.type, form.is_active, form.settings, form.fields], () => {
    if (suppressDirty.value || !hasLoadedInitialState.value) {
        return;
    }

    isDirty.value = true;
    if (saveState.value !== 'saving') {
        saveState.value = 'dirty';
        saveMessage.value = 'Unsaved changes';
    }

    scheduleAutosave();
    scheduleHistory();
}, { deep: true });

watch(() => form.name, (value, oldValue) => {
    if (!form.slug || form.slug === slugify(oldValue)) {
        form.slug = slugify(value);
    }
});

watch(() => ui.workspace, () => {
    refreshWorkspaceData();
});

watch(() => ui.analyticsDays, () => {
    if (ui.workspace === 'reports') {
        loadAnalytics();
    }
});

function getPropConfig(field, prop) {
    return getRegistryEntry(field.type)?.props?.[prop] ?? { type: 'string' };
}

function getFieldProp(field, prop) {
    if (isCoreProp(prop)) {
        return field[prop];
    }

    return field.options?.[prop];
}

function setFieldProp(field, prop, value) {
    if (isCoreProp(prop)) {
        field[prop] = value;
        return;
    }

    field.options = field.options ?? {};
    field.options[prop] = value;
}

function propInputKind(field, prop) {
    const config = getPropConfig(field, prop);

    if (prop === 'choices') return 'choices';
    if (prop === 'conditional') return 'conditional';
    if (config.type === 'boolean') return 'checkbox';
    if (config.type === 'integer' || config.type === 'number') return 'number';
    if (config.type === 'select') return 'select';
    if (config.type === 'array') return 'textarea';
    if (prop === 'formula' || prop === 'help_text' || prop === 'content') return 'textarea';

    return 'text';
}

function fieldIcon(type) {
    return FIELD_ICONS[type] ?? '•';
}

function workspaceLabel(workspace) {
    return WORKSPACE_LABELS[workspace] ?? workspace;
}

function inspectorLabel(tab) {
    return INSPECTOR_LABELS[tab] ?? tab;
}

function fieldPreviewText(field) {
    if (field.type === 'page_break') return `Новый шаг: ${field.options?.page_title || 'Без названия'}`;
    if (field.type === 'heading') return 'Заголовок раздела формы.';
    if (field.type === 'divider') return 'Визуальный разделитель.';
    if (field.type === 'html') return 'Пользовательский HTML-блок.';
    if (field.type === 'calculator') return field.options?.formula ? `Формула: ${field.options.formula}` : 'Добавьте формулу расчёта.';
    if (['select', 'radio', 'checkbox_group'].includes(field.type)) {
        const count = normalizeChoices(field.options?.choices).length;
        return count > 0 ? `${count} вариантов ответа` : 'Добавьте варианты ответа.';
    }

    return field.placeholder || field.help_text || 'Поле готово к настройке.';
}

function visibleInspectorProps(field) {
    const props = selectedRegistryEntry.value?.editor?.tabs?.[ui.inspectorTab] ?? [];

    return props.filter((prop) => {
        if (prop !== 'conditional') return true;
        return availableConditionalFields.value.length > 0;
    });
}

function ensureConditional(field) {
    field.options = field.options ?? {};
    field.options.conditional = {
        action: 'show',
        logic: 'all',
        rules: [
            {
                field: availableConditionalFields.value[0]?.name ?? '',
                operator: 'equals',
                value: '',
            },
        ],
        ...(field.options.conditional ?? {}),
    };

    if (!Array.isArray(field.options.conditional.rules)) {
        field.options.conditional.rules = [{
            field: field.options.conditional.depends_on ?? availableConditionalFields.value[0]?.name ?? '',
            operator: field.options.conditional.operator ?? 'equals',
            value: field.options.conditional.value ?? '',
        }];
    }

    if (field.options.conditional.rules.length === 0) {
        field.options.conditional.rules.push({
            field: availableConditionalFields.value[0]?.name ?? '',
            operator: 'equals',
            value: '',
        });
    }

    delete field.options.conditional.depends_on;
    delete field.options.conditional.operator;
    delete field.options.conditional.value;

    return field.options.conditional;
}

function hasConditional(field) {
    const conditional = field.options?.conditional;

    return Boolean(conditional?.depends_on || (Array.isArray(conditional?.rules) && conditional.rules.some((rule) => rule?.field)));
}

function addConditionalRule(field) {
    const conditional = ensureConditional(field);
    conditional.rules.push({
        field: availableConditionalFields.value[0]?.name ?? '',
        operator: 'equals',
        value: '',
    });
}

function removeConditionalRule(field, index) {
    const conditional = ensureConditional(field);
    conditional.rules.splice(index, 1);

    if (conditional.rules.length === 0) {
        clearConditional(field);
    }
}

function clearConditional(field) {
    if (field.options?.conditional) {
        delete field.options.conditional;
    }
}

function propOptions(field, prop) {
    return Object.entries(getPropConfig(field, prop)?.options ?? {}).map(([value, label]) => ({
        value,
        label,
    }));
}

function propTextareaValue(field, prop) {
    const value = getFieldProp(field, prop);

    if (prop === 'choices') {
        return normalizeChoices(value).map((choice) => `${choice.value}|${choice.label}`).join('\n');
    }

    if (Array.isArray(value)) {
        return value.join('\n');
    }

    if (typeof value === 'object' && value !== null) {
        return JSON.stringify(value, null, 2);
    }

    return String(value ?? '');
}

function updateTextareaProp(field, prop, rawValue) {
    if (prop === 'choices') {
        const choices = rawValue
            .split('\n')
            .map((line) => line.trim())
            .filter(Boolean)
            .map((line) => {
                const [value, ...labelParts] = line.split('|');
                return {
                    value: (value ?? '').trim(),
                    label: (labelParts.join('|') || value || '').trim(),
                };
            });
        setFieldProp(field, prop, choices);
        return;
    }

    const config = getPropConfig(field, prop);

    if (config.type === 'array') {
        setFieldProp(field, prop, rawValue.split('\n').map((line) => line.trim()).filter(Boolean));
        return;
    }

    if (prop === 'conditional') {
        try {
            setFieldProp(field, prop, rawValue ? JSON.parse(rawValue) : null);
            errorMessage.value = '';
        } catch (error) {
            errorMessage.value = 'Conditional JSON is invalid.';
        }
        return;
    }

    setFieldProp(field, prop, rawValue);
}

function addField(type) {
    const entry = getRegistryEntry(type);
    if (!entry) {
        return;
    }

    const field = normalizeField({
        ...deepClone(entry.default_field),
        id: 'fld_' + Math.random().toString(36).slice(2, 10),
        sort_order: form.fields.length,
    });

    form.fields.push(field);
    ui.selectedFieldId = field.id;
}

function applyTemplate(template) {
    suppressDirty.value = true;
    form.type = template.type ?? 'standard';
    form.settings = {
        ...form.settings,
        ...(template.settings ?? {}),
    };
    form.fields = template.fields.map((field, index) => normalizeField({
        ...field,
        id: 'fld_' + Math.random().toString(36).slice(2, 10),
        sort_order: index,
    }));
    ui.selectedFieldId = form.fields[0]?.id ?? null;
    ui.showTemplates = false;
    suppressDirty.value = false;
    isDirty.value = true;
    saveState.value = 'dirty';
    saveMessage.value = 'Template applied';
    scheduleAutosave();
}

function selectField(fieldId) {
    ui.selectedFieldId = fieldId;
}

function removeField(index) {
    form.fields.splice(index, 1);
    ui.selectedFieldId = form.fields[index]?.id ?? form.fields[index - 1]?.id ?? null;
    reindexFields();
}

function duplicateField(index) {
    const source = form.fields[index];
    if (!source) {
        return;
    }

    const copy = normalizeField({
        ...deepClone(source),
        id: 'fld_' + Math.random().toString(36).slice(2, 10),
        name: `${source.name}_copy`,
    });

    form.fields.splice(index + 1, 0, copy);
    ui.selectedFieldId = copy.id;
    reindexFields();
}

function moveField(index, direction) {
    const target = index + direction;
    if (target < 0 || target >= form.fields.length) {
        return;
    }

    const item = form.fields[index];
    form.fields.splice(index, 1);
    form.fields.splice(target, 0, item);
    reindexFields();
}

function startFieldDrag(index, event) {
    draggedFieldIndex.value = index;
    dragOverFieldIndex.value = index;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(index));
}

function dropField(targetIndex) {
    const sourceIndex = draggedFieldIndex.value;
    if (sourceIndex === null || sourceIndex === targetIndex) {
        endFieldDrag();
        return;
    }

    const [field] = form.fields.splice(sourceIndex, 1);
    // targetIndex refers to the card currently under the pointer. After the
    // source is removed, inserting at the same index places the field after a
    // lower target and before a higher target, which matches the visual cue.
    form.fields.splice(targetIndex, 0, field);
    reindexFields();
    ui.selectedFieldId = field.id;
    endFieldDrag();
}

function endFieldDrag() {
    draggedFieldIndex.value = null;
    dragOverFieldIndex.value = null;
}

function reindexFields() {
    form.fields = form.fields.map((field, index) => ({
        ...field,
        sort_order: index,
    }));
}

function serializeField(field) {
    const payload = {
        id: typeof field.id === 'number' ? field.id : undefined,
        name: field.name,
        label: field.label,
        type: field.type,
        sort_order: field.sort_order,
        required: !!field.required,
        visible: field.visible !== false,
        default_value: field.default_value ?? null,
        placeholder: field.placeholder ?? null,
        help_text: field.help_text ?? null,
        css_class: field.css_class ?? null,
        options: {
            ...(field.options ?? {}),
        },
    };

    return Object.fromEntries(Object.entries(payload).filter(([, value]) => value !== undefined));
}

async function loadRegistry() {
    loadingRegistry.value = true;

    try {
        const response = await fetch(props.registryUrl, {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Registry request failed with ${response.status}`);
        }

        const payload = await response.json();
        suppressDirty.value = true;
        registry.value = payload.fields ?? [];
        registryCategories.value = payload.categories ?? [];
        form.fields = (props.initialForm.fields ?? []).map((field) => normalizeField(field));
        ui.selectedFieldId = form.fields[0]?.id ?? null;
        registryLoaded.value = true;
        hasLoadedInitialState.value = true;
        lastHistoryState.value = historyState();
        suppressDirty.value = false;
        isDirty.value = false;
        saveState.value = 'idle';
        saveMessage.value = form.id ? 'Saved' : 'Draft';
    } catch (error) {
        errorMessage.value = 'Failed to load form field registry.';
    } finally {
        loadingRegistry.value = false;
    }
}

async function loadSubmissions(page = 1) {
    if (!submissionsUrl.value) {
        submissions.value = [];
        submissionsPagination.value = null;
        return;
    }

    loadingSubmissions.value = true;
    submissionsError.value = '';

    try {
        const url = new URL(submissionsUrl.value, window.location.origin);
        url.searchParams.set('page', String(page));
        url.searchParams.set('per_page', '8');
        if (ui.submissionsSearch.trim()) {
            url.searchParams.set('search', ui.submissionsSearch.trim());
        }
        if (ui.submissionsStatus) url.searchParams.set('status', ui.submissionsStatus);

        const response = await fetch(url.toString(), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Failed to load submissions.');
        }

        submissions.value = data.submissions?.data ?? data.submissions ?? [];
        submissionsPagination.value = data.pagination ?? null;
    } catch (error) {
        submissionsError.value = String(error.message || error);
    } finally {
        loadingSubmissions.value = false;
    }
}

async function bulkSubmissions(action) {
    if (!submissionsUrl.value || selectedSubmissionIds.value.length === 0) return;
    const response = await fetch(`${submissionsUrl.value}/bulk`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': props.csrfToken },
        credentials: 'same-origin',
        body: JSON.stringify({ ids: selectedSubmissionIds.value, action }),
    });
    const data = await response.json();
    if (!response.ok) {
        submissionsError.value = data.message || 'Bulk action failed.';
        return;
    }
    selectedSubmissionIds.value = [];
    await loadSubmissions(submissionsPagination.value?.current_page || 1);
}

function exportSubmissions() {
    if (!submissionsUrl.value) return;
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = submissionsUrl.value.replace(/\/submissions$/, '/export-submissions');
    Object.entries({ _token: props.csrfToken, status: ui.submissionsStatus, search: ui.submissionsSearch }).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value || '';
        exportForm.appendChild(input);
    });
    document.body.appendChild(exportForm);
    exportForm.submit();
    exportForm.remove();
}

async function loadAnalytics() {
    if (!analyticsUrl.value) {
        analytics.value = null;
        return;
    }

    loadingAnalytics.value = true;
    analyticsError.value = '';

    try {
        const url = new URL(analyticsUrl.value, window.location.origin);
        url.searchParams.set('days', String(ui.analyticsDays || 30));

        const response = await fetch(url.toString(), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Failed to load analytics.');
        }

        analytics.value = data.analytics ?? data;
    } catch (error) {
        analyticsError.value = String(error.message || error);
    } finally {
        loadingAnalytics.value = false;
    }
}

function refreshWorkspaceData() {
    if (ui.workspace === 'submissions') {
        loadSubmissions();
    }

    if (ui.workspace === 'reports') {
        loadAnalytics();
    }
}

function scheduleAutosave() {
    if (!form.id) {
        return;
    }

    if (autosaveTimer.value) {
        window.clearTimeout(autosaveTimer.value);
    }

    autosaveTimer.value = window.setTimeout(() => {
        saveForm(false, { autosave: true, preserveSelection: true });
    }, 1800);
}

async function saveForm(publish = false, options = {}) {
    if (autosaveTimer.value) {
        window.clearTimeout(autosaveTimer.value);
        autosaveTimer.value = null;
    }

    const selectedBeforeSave = ui.selectedFieldId;
    saveState.value = 'saving';
    saveMessage.value = options.autosave ? 'Autosaving...' : (publish ? 'Publishing...' : 'Saving...');
    errorMessage.value = '';

    const payload = {
        name: form.name,
        slug: form.slug || slugify(form.name),
        type: form.type,
        description: form.description,
        is_active: publish ? true : form.is_active,
        settings: form.settings,
        require_login: !!form.settings.require_login,
        available_from: form.settings.available_from || null,
        available_to: form.settings.available_to || null,
        entry_limit: form.settings.entry_limit || null,
        daily_limit: form.settings.daily_limit || null,
        fields: form.fields.map(serializeField),
    };

    const url = form.id
        ? props.updateUrlTemplate.replace('__FORM_ID__', String(form.id))
        : props.storeUrl;
    const method = form.id ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || JSON.stringify(data.errors || data));
        }

        form.id = data.form.id;
        form.slug = data.form.slug;
        form.is_active = !!data.form.is_active;
        form.fields = (data.form.fields ?? []).map((field) => normalizeField(field));
        ui.selectedFieldId = options.preserveSelection
            ? (form.fields.find((field) => String(field.id) === String(selectedBeforeSave))?.id ?? form.fields[0]?.id ?? null)
            : (form.fields[0]?.id ?? null);
        isDirty.value = false;
        saveState.value = 'saved';
        saveMessage.value = options.autosave ? 'Autosaved' : (publish ? 'Published' : 'Saved');
        refreshWorkspaceData();

        if (!props.initialForm.id) {
            window.location.href = props.builderRouteTemplate.replace('__FORM_ID__', String(form.id));
        }
    } catch (error) {
        saveState.value = 'error';
        saveMessage.value = 'Save failed';
        errorMessage.value = String(error.message || error);
    }
}

function openPreview() {
    if (props.publicPreviewUrl) {
        window.open(props.publicPreviewUrl, '_blank', 'noopener');
        return;
    }

    if (form.slug) {
        window.open(`/forms/${form.slug}`, '_blank', 'noopener');
        return;
    }

    errorMessage.value = 'Save the form first to open a public preview.';
}

function addWebhook() {
    form.settings.webhooks = Array.isArray(form.settings.webhooks) ? form.settings.webhooks : [];
    form.settings.webhooks.push({
        id: `webhook_${Date.now()}`,
        name: 'Webhook',
        url: '',
        secret: window.crypto?.randomUUID?.().replaceAll('-', '') || Math.random().toString(36).slice(2),
        enabled: true,
        headers: {},
    });
}

function removeWebhook(index) {
    form.settings.webhooks.splice(index, 1);
}

onMounted(() => {
    loadRegistry();
    window.addEventListener('keydown', handleHistoryShortcut);
});

onBeforeUnmount(() => {
    if (autosaveTimer.value) {
        window.clearTimeout(autosaveTimer.value);
    }
    if (historyTimer.value) window.clearTimeout(historyTimer.value);
    window.removeEventListener('keydown', handleHistoryShortcut);
});
</script>

<template>
    <div class="vc-form-builder-root flex min-h-[calc(100vh-9rem)] flex-col overflow-hidden rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface)] shadow-sm">
        <div class="flex flex-wrap items-center gap-4 border-b border-[var(--vc-border)] px-6 py-4">
            <div class="min-w-[280px] flex-1">
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Form name"
                    class="w-full border-0 bg-transparent px-0 text-2xl font-semibold text-[var(--vc-text)] focus:outline-none focus:ring-0"
                >
                <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-[var(--vc-text-soft)]">
                    <span>{{ form.slug || 'draft-form' }}</span>
                    <span class="rounded-full border border-[var(--vc-border)] px-2 py-0.5">
                        {{ form.is_active ? 'Published' : 'Draft' }}
                    </span>
                    <span>{{ saveMessage }}</span>
                    <span class="rounded-full bg-[var(--vc-surface-strong)] px-2 py-0.5">{{ completionScore }}% готово</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="vc-button vc-button-secondary" @click="ui.showTemplates = !ui.showTemplates">
                    Шаблоны
                </button>
                <button type="button" class="vc-button vc-button-secondary" :disabled="undoStack.length === 0" @click="undo">Отменить</button>
                <button type="button" class="vc-button vc-button-secondary" :disabled="redoStack.length === 0" @click="redo">Повторить</button>
                <button type="button" class="vc-button vc-button-secondary" @click="openPreview">
                    Предпросмотр
                </button>
                <button type="button" class="vc-button vc-button-secondary" @click="saveForm(false)">
                    Сохранить
                </button>
                <button type="button" class="vc-button vc-button-primary" @click="saveForm(true)">
                    {{ form.is_active ? 'Обновить' : 'Опубликовать' }}
                </button>
                <a :href="exitUrl" class="vc-button vc-button-secondary">
                    Выйти
                </a>
            </div>
        </div>

        <div v-if="ui.showTemplates" class="border-b border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-6 py-5">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-[var(--vc-text)]">Быстрый старт как в Forminator</h3>
                    <p class="mt-1 text-sm text-[var(--vc-text-soft)]">Шаблон заменит текущие поля, но сохранит общие настройки формы.</p>
                </div>
                <button type="button" class="text-sm font-medium text-[var(--vc-text-soft)] hover:text-[var(--vc-text)]" @click="ui.showTemplates = false">
                    Скрыть
                </button>
            </div>
            <div class="grid gap-3 lg:grid-cols-5">
                <button
                    v-for="template in FORM_TEMPLATES"
                    :key="template.id"
                    type="button"
                    class="rounded-2xl border border-[var(--vc-border)] bg-white p-4 text-left transition hover:-translate-y-0.5 hover:border-[var(--vc-primary)] hover:shadow-sm"
                    @click="applyTemplate(template)"
                >
                    <span class="block text-sm font-semibold text-[var(--vc-text)]">{{ template.label }}</span>
                    <span class="mt-2 block text-xs leading-5 text-[var(--vc-text-soft)]">{{ template.description }}</span>
                    <span class="mt-3 inline-flex rounded-full bg-[var(--vc-surface-strong)] px-2 py-1 text-xs text-[var(--vc-text-soft)]">{{ template.fields.length }} полей</span>
                </button>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 border-b border-[var(--vc-border)] px-6 py-3">
            <button
                v-for="workspace in WORKSPACES"
                :key="workspace"
                type="button"
                class="rounded-full px-3 py-1.5 text-sm transition"
                :class="ui.workspace === workspace
                    ? 'bg-[var(--vc-primary)] text-white'
                    : 'bg-[var(--vc-surface-strong)] text-[var(--vc-text-soft)] hover:text-[var(--vc-text)]'"
                @click="ui.workspace = workspace"
            >
                {{ workspaceLabel(workspace) }}
            </button>
        </div>

        <div v-if="errorMessage" class="border-b border-rose-200 bg-rose-50 px-6 py-3 text-sm text-rose-700">
            {{ errorMessage }}
        </div>

        <div v-if="ui.workspace === 'build'" class="grid flex-1 xl:grid-cols-[290px_minmax(0,1fr)_360px]">
            <aside class="border-b border-[var(--vc-border)] px-4 py-5 xl:border-b-0 xl:border-r">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-[var(--vc-text-soft)]">Поля</h3>
                    <input
                        v-model="ui.paletteSearch"
                        type="search"
                        placeholder="Найти поле"
                        class="mt-3 w-full rounded-xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-2 text-sm"
                    >
                </div>

                <div v-if="loadingRegistry" class="text-sm text-[var(--vc-text-soft)]">Loading registry...</div>
                <div v-else class="space-y-4">
                    <section v-for="category in filteredCategories" :key="category.id" class="space-y-2">
                        <h4 class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--vc-text-soft)]">
                            {{ category.label }}
                        </h4>
                        <button
                            v-for="field in category.fields"
                            :key="field.type"
                            type="button"
                            class="flex w-full items-start gap-3 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-3 text-left transition hover:border-[var(--vc-primary)] hover:bg-white"
                            @click="addField(field.type)"
                        >
                            <span class="inline-flex h-8 w-8 flex-none items-center justify-center rounded-xl bg-white text-xs font-black text-[var(--vc-primary)]">{{ fieldIcon(field.type) }}</span>
                            <span>
                                <span class="block text-sm font-medium text-[var(--vc-text)]">{{ field.label }}</span>
                                <span class="mt-1 block text-xs text-[var(--vc-text-soft)]">{{ field.description }}</span>
                            </span>
                        </button>
                    </section>
                </div>
            </aside>

            <main class="border-b border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-5 py-6 xl:border-b-0">
                <div class="mx-auto max-w-3xl space-y-5">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                            <span class="text-xs uppercase tracking-[0.18em] text-[var(--vc-text-soft)]">Поля</span>
                            <strong class="mt-2 block text-2xl text-[var(--vc-text)]">{{ form.fields.length }}</strong>
                        </div>
                        <div class="rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                            <span class="text-xs uppercase tracking-[0.18em] text-[var(--vc-text-soft)]">Обязательные</span>
                            <strong class="mt-2 block text-2xl text-[var(--vc-text)]">{{ buildSummary.required }}</strong>
                        </div>
                        <div class="rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                            <span class="text-xs uppercase tracking-[0.18em] text-[var(--vc-text-soft)]">Шаги / логика</span>
                            <strong class="mt-2 block text-2xl text-[var(--vc-text)]">{{ buildSummary.pages }} / {{ buildSummary.logic }}</strong>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-[var(--vc-border)] bg-white p-5">
                        <input
                            v-model="form.description"
                            type="text"
                            placeholder="Описание формы для команды"
                            class="w-full border-0 bg-transparent px-0 text-base text-[var(--vc-text)] focus:outline-none focus:ring-0"
                        >
                    </div>

                    <div v-if="form.fields.length === 0" class="rounded-3xl border border-dashed border-[var(--vc-border)] bg-white px-6 py-16 text-center text-[var(--vc-text-soft)]">
                        Добавьте первое поле слева или выберите готовый шаблон.
                    </div>

                    <article
                        v-for="(field, index) in form.fields"
                        :key="field.id"
                        class="rounded-3xl border bg-white p-5 shadow-sm transition"
                        :class="[
                            String(ui.selectedFieldId) === String(field.id) ? 'border-[var(--vc-primary)] ring-2 ring-sky-100' : 'border-[var(--vc-border)]',
                            dragOverFieldIndex === index ? 'translate-y-1 border-dashed border-[var(--vc-primary)]' : '',
                            draggedFieldIndex === index ? 'opacity-50' : '',
                        ]"
                        @dragover.prevent="dragOverFieldIndex = index"
                        @drop.prevent="dropField(index)"
                        @click="selectField(field.id)"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-xl bg-[var(--vc-surface-strong)] px-2 text-xs font-black text-[var(--vc-primary)]">{{ fieldIcon(field.type) }}</span>
                                    <h3 class="text-base font-semibold text-[var(--vc-text)]">{{ field.label || 'Untitled field' }}</h3>
                                    <span class="rounded-full bg-[var(--vc-surface-strong)] px-2 py-0.5 text-xs text-[var(--vc-text-soft)]">{{ field.type }}</span>
                                    <span v-if="field.required" class="text-xs font-medium text-rose-500">Required</span>
                                    <span v-if="hasConditional(field)" class="text-xs font-medium text-sky-600">Logic</span>
                                </div>
                                <p class="mt-1 text-sm text-[var(--vc-text-soft)]">{{ field.name }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    draggable="true"
                                    class="vc-button vc-button-secondary cursor-grab px-3 py-2 text-sm active:cursor-grabbing"
                                    title="Перетащить поле"
                                    aria-label="Перетащить поле"
                                    @dragstart.stop="startFieldDrag(index, $event)"
                                    @dragend="endFieldDrag"
                                >⋮⋮</button>
                                <button type="button" class="vc-button vc-button-secondary px-3 py-2 text-sm" @click.stop="moveField(index, -1)">↑</button>
                                <button type="button" class="vc-button vc-button-secondary px-3 py-2 text-sm" @click.stop="moveField(index, 1)">↓</button>
                                <button type="button" class="vc-button vc-button-secondary px-3 py-2 text-sm" @click.stop="duplicateField(index)">Копия</button>
                                <button type="button" class="vc-button vc-button-danger px-3 py-2 text-sm" @click.stop="removeField(index)">Удалить</button>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-4 py-3 text-sm text-[var(--vc-text-soft)]">
                            {{ fieldPreviewText(field) }}
                        </div>
                    </article>
                </div>
            </main>

            <aside class="px-5 py-6">
                <div v-if="currentField" class="space-y-5">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="tab in INSPECTOR_TABS"
                            :key="tab"
                            type="button"
                            class="rounded-full px-3 py-1.5 text-sm transition"
                            :class="ui.inspectorTab === tab
                                ? 'bg-[var(--vc-primary)] text-white'
                                : 'bg-[var(--vc-surface-strong)] text-[var(--vc-text-soft)]'"
                            @click="ui.inspectorTab = tab"
                        >
                            {{ inspectorLabel(tab) }}
                        </button>
                    </div>

                    <div class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] p-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-[var(--vc-text-soft)]">
                            {{ currentField.label || currentField.type }}
                        </h3>
                        <div class="mt-4 space-y-4">
                            <template
                                v-for="prop in visibleInspectorProps(currentField)"
                                :key="`${currentField.id}-${ui.inspectorTab}-${prop}`"
                            >
                                <div v-if="propInputKind(currentField, prop) === 'conditional'" class="rounded-2xl border border-[var(--vc-border)] bg-white p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <span class="block text-sm font-semibold text-[var(--vc-text)]">Условная логика</span>
                                            <p class="mt-1 text-xs text-[var(--vc-text-soft)]">Показывать поле, когда другое поле соответствует правилу.</p>
                                        </div>
                                        <button v-if="currentField.options?.conditional" type="button" class="text-xs font-semibold text-rose-500" @click="clearConditional(currentField)">
                                            Очистить
                                        </button>
                                    </div>
                                    <button
                                        v-if="!currentField.options?.conditional"
                                        type="button"
                                        class="mt-3 w-full rounded-xl border border-dashed border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-3 text-sm font-semibold text-[var(--vc-text)] transition hover:border-[var(--vc-primary)]"
                                        @click="ensureConditional(currentField)"
                                    >
                                        Добавить условие показа
                                    </button>
                                    <div v-else class="mt-3 grid gap-3">
                                        <div class="grid gap-2 sm:grid-cols-2">
                                            <select
                                                v-model="currentField.options.conditional.action"
                                                class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"
                                            >
                                                <option value="show">Show field when</option>
                                                <option value="hide">Hide field when</option>
                                            </select>
                                            <select
                                                v-model="currentField.options.conditional.logic"
                                                class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"
                                            >
                                                <option value="all">All rules match</option>
                                                <option value="any">Any rule matches</option>
                                            </select>
                                        </div>
                                        <div
                                            v-for="(rule, ruleIndex) in ensureConditional(currentField).rules"
                                            :key="`${currentField.id}-rule-${ruleIndex}`"
                                            class="grid gap-2 rounded-xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] p-3"
                                        >
                                            <select
                                                v-model="rule.field"
                                                class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"
                                            >
                                                <option v-for="field in availableConditionalFields" :key="field.id" :value="field.name">
                                                    {{ field.label }} ({{ field.name }})
                                                </option>
                                            </select>
                                            <select
                                                v-model="rule.operator"
                                                class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"
                                            >
                                            <option value="equals">равно</option>
                                            <option value="not_equals">не равно</option>
                                            <option value="contains">содержит</option>
                                            <option value="greater_than">больше чем</option>
                                            <option value="less_than">меньше чем</option>
                                            <option value="is_empty">пусто</option>
                                            <option value="is_not_empty">не пусто</option>
                                            </select>
                                        <input
                                            v-if="!['is_empty', 'is_not_empty'].includes(rule.operator)"
                                            v-model="rule.value"
                                            type="text"
                                            placeholder="Значение"
                                            class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"
                                        >
                                            <button
                                                v-if="ensureConditional(currentField).rules.length > 1"
                                                type="button"
                                                class="justify-self-start text-xs font-semibold text-rose-500"
                                                @click="removeConditionalRule(currentField, ruleIndex)"
                                            >
                                                Remove rule
                                            </button>
                                        </div>
                                        <button
                                            type="button"
                                            class="rounded-xl border border-dashed border-[var(--vc-border)] px-3 py-2 text-sm font-semibold text-[var(--vc-text)] hover:border-[var(--vc-primary)]"
                                            @click="addConditionalRule(currentField)"
                                        >
                                            Add another rule
                                        </button>
                                    </div>
                                </div>

                                <label v-else class="block">
                                    <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">{{ prop }}</span>

                                    <input
                                        v-if="propInputKind(currentField, prop) === 'text'"
                                        :value="getFieldProp(currentField, prop) ?? ''"
                                        type="text"
                                        class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"
                                        @input="setFieldProp(currentField, prop, $event.target.value)"
                                    >

                                    <input
                                        v-else-if="propInputKind(currentField, prop) === 'number'"
                                        :value="getFieldProp(currentField, prop) ?? ''"
                                        type="number"
                                        class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"
                                        @input="setFieldProp(currentField, prop, $event.target.value === '' ? null : Number($event.target.value))"
                                    >

                                    <select
                                        v-else-if="propInputKind(currentField, prop) === 'select'"
                                        :value="getFieldProp(currentField, prop) ?? ''"
                                        class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"
                                        @change="setFieldProp(currentField, prop, $event.target.value)"
                                    >
                                        <option v-for="option in propOptions(currentField, prop)" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>

                                    <textarea
                                        v-else-if="['textarea', 'choices', 'json'].includes(propInputKind(currentField, prop))"
                                        :value="propTextareaValue(currentField, prop)"
                                        :rows="prop === 'choices' ? 6 : 4"
                                        class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"
                                        @input="updateTextareaProp(currentField, prop, $event.target.value)"
                                    ></textarea>

                                    <label v-else-if="propInputKind(currentField, prop) === 'checkbox'" class="inline-flex items-center gap-2 rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm text-[var(--vc-text)]">
                                        <input
                                            :checked="!!getFieldProp(currentField, prop)"
                                            type="checkbox"
                                            class="rounded border-slate-300"
                                            @change="setFieldProp(currentField, prop, $event.target.checked)"
                                        >
                                        <span>Enabled</span>
                                    </label>
                                </label>
                            </template>
                            <div v-if="visibleInspectorProps(currentField).length === 0" class="rounded-2xl border border-dashed border-[var(--vc-border)] bg-white p-4 text-sm text-[var(--vc-text-soft)]">
                                Для этой вкладки нет дополнительных настроек.
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-3xl border border-dashed border-[var(--vc-border)] px-5 py-10 text-center text-sm text-[var(--vc-text-soft)]">
                    Выберите поле, чтобы настроить его свойства.
                </div>
            </aside>
        </div>

        <div v-else class="grid gap-6 px-6 py-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <section class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] p-5">
                <h3 class="text-lg font-semibold text-[var(--vc-text)]">
                    {{ workspaceLabel(ui.workspace) }}
                </h3>
                <p class="mt-2 text-sm text-[var(--vc-text-soft)]">
                    Настройки сохраняются в JSON-контракт формы и готовы для расширения интеграциями.
                </p>

                <div class="mt-5 grid gap-4">
                    <label v-if="ui.workspace === 'appearance'" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Текст кнопки отправки</span>
                        <input v-model="form.settings.submit_label" type="text" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <label v-if="ui.workspace === 'appearance'" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Сообщение успеха</span>
                        <textarea v-model="form.settings.success_message" rows="3" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"></textarea>
                    </label>

                    <label v-if="ui.workspace === 'appearance'" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Плотность формы</span>
                        <select v-model="form.settings.layout_density" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                            <option value="compact">Компактная</option>
                            <option value="comfortable">Комфортная</option>
                            <option value="spacious">Просторная</option>
                        </select>
                    </label>

                    <label v-if="ui.workspace === 'behavior'" class="inline-flex items-center gap-2 rounded-2xl border border-[var(--vc-border)] bg-white px-4 py-3 text-sm">
                        <input v-model="form.settings.honeypot_enabled" type="checkbox" class="rounded border-slate-300">
                        <span>Включить honeypot-защиту</span>
                    </label>

                    <label v-if="ui.workspace === 'behavior'" class="inline-flex items-center gap-2 rounded-2xl border border-[var(--vc-border)] bg-white px-4 py-3 text-sm">
                        <input v-model="form.settings.require_login" type="checkbox" class="rounded border-slate-300">
                        <span>Требовать авторизацию для отправки</span>
                    </label>

                    <label v-if="ui.workspace === 'behavior'" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">URL редиректа после отправки</span>
                        <input v-model="form.settings.redirect_url" type="url" placeholder="https://example.com/thanks" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <label v-if="ui.workspace === 'behavior'" class="inline-flex items-center gap-2 rounded-2xl border border-[var(--vc-border)] bg-white px-4 py-3 text-sm">
                        <input v-model="form.settings.save_resume_enabled" type="checkbox" class="rounded border-slate-300">
                        <span>Разрешить сохранить черновик и продолжить позже</span>
                    </label>

                    <label v-if="ui.workspace === 'behavior' && form.settings.save_resume_enabled" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Срок ссылки продолжения, дней</span>
                        <input v-model.number="form.settings.resume_days" type="number" min="1" max="365" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <label v-if="ui.workspace === 'behavior'" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Хранение заявок, дней</span>
                        <input v-model.number="form.settings.retention_days" type="number" min="0" max="3650" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                        <span class="mt-2 block text-xs text-[var(--vc-text-soft)]">0 — хранить бессрочно. Просроченные заявки и приватные файлы удаляются ежедневной задачей.</span>
                    </label>

                    <label v-if="ui.workspace === 'emails'" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Email администраторов</span>
                        <input v-model="form.settings.notify_admin_emails" type="text" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <label v-if="ui.workspace === 'emails'" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Тема уведомления</span>
                        <input v-model="form.settings.notify_admin_subject" type="text" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <label v-if="ui.workspace === 'emails'" class="inline-flex items-center gap-2 rounded-2xl border border-[var(--vc-border)] bg-white px-4 py-3 text-sm">
                        <input v-model="form.settings.autoresponder_enabled" type="checkbox" class="rounded border-slate-300">
                        <span>Включить автоответ пользователю</span>
                    </label>

                    <label v-if="ui.workspace === 'emails' && form.settings.autoresponder_enabled" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Тема автоответа</span>
                        <input v-model="form.settings.autoresponder_subject" type="text" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <label v-if="ui.workspace === 'emails' && form.settings.autoresponder_enabled" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Текст автоответа</span>
                        <textarea v-model="form.settings.autoresponder_body" rows="6" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"></textarea>
                        <span class="mt-2 block text-xs text-[var(--vc-text-soft)]">Получатель определяется по первому email-полю формы.</span>
                    </label>

                    <label v-if="ui.workspace === 'visibility'" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Доступна с</span>
                        <input v-model="form.settings.available_from" type="datetime-local" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <label v-if="ui.workspace === 'visibility'" class="block rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Доступна до</span>
                        <input v-model="form.settings.available_to" type="datetime-local" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <div v-if="ui.workspace === 'submissions'" class="grid gap-4">
                        <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                            <input
                                v-model="ui.submissionsSearch"
                                type="search"
                                placeholder="Search submission id or IP"
                                class="min-w-[220px] flex-1 rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"
                                @keydown.enter.prevent="loadSubmissions()"
                            >
                            <select v-model="ui.submissionsStatus" class="rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm" @change="loadSubmissions()">
                                <option value="">Все статусы</option>
                                <option value="unread">Новые</option>
                                <option value="read">Прочитанные</option>
                                <option value="spam">Спам</option>
                                <option value="trashed">Корзина</option>
                            </select>
                            <button type="button" class="vc-button vc-button-secondary" @click="loadSubmissions()">Refresh</button>
                            <button type="button" class="vc-button vc-button-secondary" @click="exportSubmissions">CSV</button>
                        </div>

                        <div v-if="selectedSubmissionIds.length" class="flex flex-wrap items-center gap-2 rounded-2xl border border-sky-700/30 bg-sky-950/30 p-3 text-sm">
                            <span>{{ selectedSubmissionIds.length }} выбрано</span>
                            <button type="button" class="vc-button vc-button-secondary" @click="bulkSubmissions('mark_read')">Прочитано</button>
                            <button type="button" class="vc-button vc-button-secondary" @click="bulkSubmissions('mark_spam')">Спам</button>
                            <button type="button" class="vc-button vc-button-danger" @click="bulkSubmissions('delete')">Удалить</button>
                        </div>

                        <div v-if="!form.id" class="rounded-2xl border border-dashed border-[var(--vc-border)] bg-white p-5 text-sm text-[var(--vc-text-soft)]">
                            Save the form first to collect submissions.
                        </div>
                        <div v-else-if="submissionsError" class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">{{ submissionsError }}</div>
                        <div v-else class="overflow-hidden rounded-2xl border border-[var(--vc-border)] bg-white">
                            <div v-if="loadingSubmissions" class="p-5 text-sm text-[var(--vc-text-soft)]">Loading submissions...</div>
                            <div v-else-if="submissions.length === 0" class="p-5 text-sm text-[var(--vc-text-soft)]">No submissions yet.</div>
                            <table v-else class="w-full text-left text-sm">
                                <thead class="bg-[var(--vc-surface-strong)] text-xs uppercase tracking-[0.16em] text-[var(--vc-text-soft)]">
                                    <tr>
                                        <th class="px-4 py-3"></th>
                                        <th class="px-4 py-3">ID</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">IP</th>
                                        <th class="px-4 py-3">Values</th>
                                        <th class="px-4 py-3">Created</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[var(--vc-border)]">
                                    <tr v-for="submission in submissions" :key="submission.id">
                                        <td class="px-4 py-3"><input v-model="selectedSubmissionIds" type="checkbox" :value="submission.id"></td>
                                        <td class="px-4 py-3 font-medium text-[var(--vc-text)]">{{ submission.submission_id || submission.id }}</td>
                                        <td class="px-4 py-3 text-[var(--vc-text-soft)]">{{ submission.status }}</td>
                                        <td class="px-4 py-3 text-[var(--vc-text-soft)]">{{ submission.ip_address }}</td>
                                        <td class="px-4 py-3 text-[var(--vc-text-soft)]">{{ submission.values_count }}</td>
                                        <td class="px-4 py-3 text-[var(--vc-text-soft)]">{{ submission.created_at }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="submissionsPagination" class="flex items-center justify-between border-t border-[var(--vc-border)] px-4 py-3 text-sm text-[var(--vc-text-soft)]">
                                <span>{{ submissionsPagination.total }} total</span>
                                <div class="flex gap-2">
                                    <button type="button" class="vc-button vc-button-secondary px-3 py-2 text-xs" :disabled="submissionsPagination.current_page <= 1" @click="loadSubmissions(submissionsPagination.current_page - 1)">Prev</button>
                                    <button type="button" class="vc-button vc-button-secondary px-3 py-2 text-xs" :disabled="submissionsPagination.current_page >= submissionsPagination.last_page" @click="loadSubmissions(submissionsPagination.current_page + 1)">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="ui.workspace === 'reports'" class="grid gap-4">
                        <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                            <select v-model="ui.analyticsDays" class="rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                                <option :value="7">7 days</option>
                                <option :value="30">30 days</option>
                                <option :value="90">90 days</option>
                            </select>
                            <button type="button" class="vc-button vc-button-secondary" @click="loadAnalytics()">Refresh</button>
                        </div>
                        <div v-if="!form.id" class="rounded-2xl border border-dashed border-[var(--vc-border)] bg-white p-5 text-sm text-[var(--vc-text-soft)]">
                            Save the form first to view reports.
                        </div>
                        <div v-else-if="analyticsError" class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">{{ analyticsError }}</div>
                        <div v-else class="grid gap-3 md:grid-cols-3">
                            <div class="rounded-2xl border border-[var(--vc-border)] bg-white p-5">
                                <span class="text-xs uppercase tracking-[0.18em] text-[var(--vc-text-soft)]">Views</span>
                                <strong class="mt-2 block text-3xl text-[var(--vc-text)]">{{ analyticsSummary.views ?? analyticsSummary.total_views ?? 0 }}</strong>
                            </div>
                            <div class="rounded-2xl border border-[var(--vc-border)] bg-white p-5">
                                <span class="text-xs uppercase tracking-[0.18em] text-[var(--vc-text-soft)]">Submissions</span>
                                <strong class="mt-2 block text-3xl text-[var(--vc-text)]">{{ analyticsSummary.submissions ?? analyticsSummary.total_submissions ?? submissionsPagination?.total ?? 0 }}</strong>
                            </div>
                            <div class="rounded-2xl border border-[var(--vc-border)] bg-white p-5">
                                <span class="text-xs uppercase tracking-[0.18em] text-[var(--vc-text-soft)]">Conversion</span>
                                <strong class="mt-2 block text-3xl text-[var(--vc-text)]">{{ conversionRate }}</strong>
                            </div>
                        </div>
                        <pre v-if="analytics && !loadingAnalytics" class="max-h-80 overflow-auto rounded-2xl border border-[var(--vc-border)] bg-slate-950 p-4 text-xs text-slate-100">{{ JSON.stringify(analytics, null, 2) }}</pre>
                        <div v-if="loadingAnalytics" class="rounded-2xl border border-[var(--vc-border)] bg-white p-5 text-sm text-[var(--vc-text-soft)]">Loading analytics...</div>
                    </div>

                    <div v-if="ui.workspace === 'integrations'" class="grid gap-4">
                        <div class="flex items-center justify-between rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                            <div>
                                <h4 class="font-semibold text-[var(--vc-text)]">Webhook integrations</h4>
                                <p class="mt-1 text-xs text-[var(--vc-text-soft)]">Подписанные события form.submitted отправляются через очередь с повторными попытками.</p>
                            </div>
                            <button type="button" class="vc-button vc-button-primary" @click="addWebhook">Добавить webhook</button>
                        </div>

                        <article v-for="(webhook, index) in form.settings.webhooks" :key="webhook.id || index" class="grid gap-3 rounded-2xl border border-[var(--vc-border)] bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <input v-model="webhook.name" type="text" placeholder="Название" class="flex-1 rounded-xl border border-[var(--vc-border)] px-3 py-2 text-sm">
                                <label class="inline-flex items-center gap-2 text-sm"><input v-model="webhook.enabled" type="checkbox"> Активен</label>
                                <button type="button" class="text-sm font-semibold text-rose-600" @click="removeWebhook(index)">Удалить</button>
                            </div>
                            <input v-model="webhook.url" type="url" placeholder="https://crm.example/webhooks/forms" class="rounded-xl border border-[var(--vc-border)] px-3 py-2 text-sm">
                            <label class="block">
                                <span class="mb-1 block text-xs font-medium text-[var(--vc-text-soft)]">Signing secret</span>
                                <input v-model="webhook.secret" type="password" autocomplete="new-password" class="w-full rounded-xl border border-[var(--vc-border)] px-3 py-2 font-mono text-sm">
                            </label>
                        </article>

                        <div v-if="!form.settings.webhooks?.length" class="rounded-2xl border border-dashed border-[var(--vc-border)] bg-white p-5 text-sm text-[var(--vc-text-soft)]">
                            Интеграции ещё не настроены.
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] p-5">
                <h3 class="text-lg font-semibold text-[var(--vc-text)]">Сводка</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-[var(--vc-text-soft)]">Поля</dt>
                        <dd class="font-medium text-[var(--vc-text)]">{{ form.fields.length }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[var(--vc-text-soft)]">Тип</dt>
                        <dd class="font-medium text-[var(--vc-text)]">{{ form.type }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[var(--vc-text-soft)]">Статус</dt>
                        <dd class="font-medium text-[var(--vc-text)]">{{ form.is_active ? 'Published' : 'Draft' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[var(--vc-text-soft)]">Готовность</dt>
                        <dd class="font-medium text-[var(--vc-text)]">{{ completionScore }}%</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</template>

<style scoped>
.vc-form-builder-root {
    position: relative;
    isolation: isolate;
    border-color: rgba(148, 163, 184, 0.16);
    background:
        radial-gradient(circle at 18% 0%, rgba(20, 184, 166, 0.12), transparent 30rem),
        linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(11, 18, 32, 0.98));
    color: #e5edf8;
    box-shadow: 0 28px 90px rgba(0, 0, 0, 0.28);
}

.vc-form-builder-root :deep(.bg-white) {
    border-color: rgba(148, 163, 184, 0.18) !important;
    background: linear-gradient(180deg, rgba(17, 24, 39, 0.96), rgba(13, 21, 36, 0.96)) !important;
    color: #e5edf8 !important;
    box-shadow: none !important;
}

.vc-form-builder-root :deep(.bg-\[var\(--vc-surface-strong\)\]) {
    background: rgba(9, 16, 30, 0.72) !important;
}

.vc-form-builder-root :deep(.text-\[var\(--vc-text\)\]) {
    color: #f8fafc !important;
}

.vc-form-builder-root :deep(.text-\[var\(--vc-text-soft\)\]) {
    color: #93a4bd !important;
}

.vc-form-builder-root :deep(input),
.vc-form-builder-root :deep(select),
.vc-form-builder-root :deep(textarea) {
    border-color: rgba(148, 163, 184, 0.18) !important;
    background: rgba(3, 7, 18, 0.62) !important;
    color: #e5edf8 !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.vc-form-builder-root :deep(input::placeholder),
.vc-form-builder-root :deep(textarea::placeholder) {
    color: #667792 !important;
}

.vc-form-builder-root :deep(input:focus),
.vc-form-builder-root :deep(select:focus),
.vc-form-builder-root :deep(textarea:focus) {
    border-color: rgba(45, 212, 191, 0.7) !important;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(45, 212, 191, 0.14) !important;
}

.vc-form-builder-root :deep(.vc-button) {
    min-height: 42px;
    border-radius: 14px;
    border-color: rgba(148, 163, 184, 0.18);
    background: rgba(15, 23, 42, 0.78);
    color: #e5edf8;
    box-shadow: none;
}

.vc-form-builder-root :deep(.vc-button-primary) {
    border-color: rgba(45, 212, 191, 0.72);
    background: linear-gradient(135deg, #2dd4bf, #14b8a6);
    color: #042f2e;
}

.vc-form-builder-root :deep(.vc-button-danger) {
    border-color: rgba(251, 113, 133, 0.32);
    background: rgba(251, 113, 133, 0.12);
    color: #fda4af;
}

.vc-form-builder-root > div:first-child {
    background: rgba(2, 6, 23, 0.32);
    backdrop-filter: blur(18px);
}

.vc-form-builder-root > div:nth-child(3) {
    background: rgba(2, 6, 23, 0.22);
}

.vc-form-builder-root :deep(.rounded-3xl) {
    border-radius: 22px;
}

.vc-form-builder-root :deep(.rounded-2xl) {
    border-radius: 16px;
}

.vc-form-builder-root :deep(.ring-sky-100) {
    --tw-ring-color: rgba(45, 212, 191, 0.24) !important;
}

.vc-form-builder-root :deep(.border-\[var\(--vc-primary\)\]) {
    border-color: rgba(45, 212, 191, 0.82) !important;
}

.vc-form-builder-root :deep(aside) {
    background: rgba(2, 6, 23, 0.18);
}

.vc-form-builder-root :deep(main) {
    background:
        linear-gradient(90deg, rgba(148, 163, 184, 0.045) 1px, transparent 1px),
        linear-gradient(180deg, rgba(148, 163, 184, 0.04) 1px, transparent 1px),
        rgba(7, 13, 25, 0.62) !important;
    background-size: 28px 28px;
}

.vc-form-builder-root :deep(article) {
    border-color: rgba(148, 163, 184, 0.18) !important;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.95), rgba(12, 19, 34, 0.95)) !important;
}

.vc-form-builder-root :deep(article:hover) {
    border-color: rgba(45, 212, 191, 0.54) !important;
}

.vc-form-builder-root :deep(table) {
    overflow: hidden;
}

.vc-form-builder-root :deep(pre) {
    border-color: rgba(45, 212, 191, 0.18) !important;
    background: #020617 !important;
}

@media (max-width: 1280px) {
    .vc-form-builder-root :deep(.xl\:grid-cols-\[290px_minmax\(0\,1fr\)_360px\]) {
        grid-template-columns: minmax(0, 1fr) !important;
    }
}
</style>
