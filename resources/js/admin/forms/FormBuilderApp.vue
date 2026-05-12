<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';

const props = defineProps({
    registryUrl: { type: String, required: true },
    storeUrl: { type: String, required: true },
    updateUrlTemplate: { type: String, required: true },
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

const registry = ref([]);
const registryCategories = ref([]);
const registryLoaded = ref(false);
const loadingRegistry = ref(false);
const saveState = ref('idle');
const saveMessage = ref('Draft');
const errorMessage = ref('');

const ui = reactive({
    workspace: 'build',
    inspectorTab: 'field',
    selectedFieldId: null,
    paletteSearch: '',
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
        theme: 'default',
        honeypot_enabled: true,
        turnstile_enabled: false,
        notify_admin_emails: '',
        autoresponder_enabled: false,
        require_login: false,
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

watch(() => [form.name, form.description, form.type, form.is_active, form.settings, form.fields], () => {
    isDirty.value = true;
    if (saveState.value !== 'saving') {
        saveState.value = 'dirty';
        saveMessage.value = 'Unsaved changes';
    }
}, { deep: true });

watch(() => form.name, (value, oldValue) => {
    if (!form.slug || form.slug === slugify(oldValue)) {
        form.slug = slugify(value);
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
    if (prop === 'conditional') return 'json';
    if (config.type === 'boolean') return 'checkbox';
    if (config.type === 'integer' || config.type === 'number') return 'number';
    if (config.type === 'select') return 'select';
    if (config.type === 'array') return 'textarea';
    if (prop === 'formula' || prop === 'help_text' || prop === 'content') return 'textarea';

    return 'text';
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
        registry.value = payload.fields ?? [];
        registryCategories.value = payload.categories ?? [];
        form.fields = (props.initialForm.fields ?? []).map((field) => normalizeField(field));
        ui.selectedFieldId = form.fields[0]?.id ?? null;
        registryLoaded.value = true;
    } catch (error) {
        errorMessage.value = 'Failed to load form field registry.';
    } finally {
        loadingRegistry.value = false;
    }
}

async function saveForm(publish = false) {
    saveState.value = 'saving';
    saveMessage.value = publish ? 'Publishing...' : 'Saving...';
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
        ui.selectedFieldId = form.fields[0]?.id ?? null;
        isDirty.value = false;
        saveState.value = 'saved';
        saveMessage.value = publish ? 'Published' : 'Saved';

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

onMounted(loadRegistry);
</script>

<template>
    <div class="flex min-h-[calc(100vh-9rem)] flex-col rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface)] shadow-sm">
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
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="vc-button vc-button-secondary" @click="openPreview">
                    Preview
                </button>
                <button type="button" class="vc-button vc-button-secondary" @click="saveForm(false)">
                    Save Draft
                </button>
                <button type="button" class="vc-button vc-button-primary" @click="saveForm(true)">
                    {{ form.is_active ? 'Publish Changes' : 'Publish' }}
                </button>
                <a :href="exitUrl" class="vc-button vc-button-secondary">
                    Exit
                </a>
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
                {{ workspace.charAt(0).toUpperCase() + workspace.slice(1) }}
            </button>
        </div>

        <div v-if="errorMessage" class="border-b border-rose-200 bg-rose-50 px-6 py-3 text-sm text-rose-700">
            {{ errorMessage }}
        </div>

        <div v-if="ui.workspace === 'build'" class="grid flex-1 xl:grid-cols-[290px_minmax(0,1fr)_360px]">
            <aside class="border-b border-[var(--vc-border)] px-4 py-5 xl:border-b-0 xl:border-r">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-[var(--vc-text-soft)]">Fields</h3>
                    <input
                        v-model="ui.paletteSearch"
                        type="search"
                        placeholder="Search fields"
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
                            class="block w-full rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-3 py-3 text-left transition hover:border-[var(--vc-primary)] hover:bg-white"
                            @click="addField(field.type)"
                        >
                            <span class="block text-sm font-medium text-[var(--vc-text)]">{{ field.label }}</span>
                            <span class="mt-1 block text-xs text-[var(--vc-text-soft)]">{{ field.description }}</span>
                        </button>
                    </section>
                </div>
            </aside>

            <main class="border-b border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-5 py-6 xl:border-b-0">
                <div class="mx-auto max-w-3xl space-y-5">
                    <div class="rounded-3xl border border-[var(--vc-border)] bg-white p-5">
                        <input
                            v-model="form.description"
                            type="text"
                            placeholder="Describe what this form does"
                            class="w-full border-0 bg-transparent px-0 text-base text-[var(--vc-text)] focus:outline-none focus:ring-0"
                        >
                    </div>

                    <div v-if="form.fields.length === 0" class="rounded-3xl border border-dashed border-[var(--vc-border)] bg-white px-6 py-16 text-center text-[var(--vc-text-soft)]">
                        Insert your first field from the left palette.
                    </div>

                    <article
                        v-for="(field, index) in form.fields"
                        :key="field.id"
                        class="rounded-3xl border bg-white p-5 shadow-sm transition"
                        :class="String(ui.selectedFieldId) === String(field.id) ? 'border-[var(--vc-primary)] ring-2 ring-sky-100' : 'border-[var(--vc-border)]'"
                        @click="selectField(field.id)"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold text-[var(--vc-text)]">{{ field.label || 'Untitled field' }}</h3>
                                    <span class="rounded-full bg-[var(--vc-surface-strong)] px-2 py-0.5 text-xs text-[var(--vc-text-soft)]">{{ field.type }}</span>
                                    <span v-if="field.required" class="text-xs font-medium text-rose-500">Required</span>
                                </div>
                                <p class="mt-1 text-sm text-[var(--vc-text-soft)]">{{ field.name }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" class="vc-button vc-button-secondary px-3 py-2 text-sm" @click.stop="moveField(index, -1)">Up</button>
                                <button type="button" class="vc-button vc-button-secondary px-3 py-2 text-sm" @click.stop="moveField(index, 1)">Down</button>
                                <button type="button" class="vc-button vc-button-secondary px-3 py-2 text-sm" @click.stop="duplicateField(index)">Copy</button>
                                <button type="button" class="vc-button vc-button-danger px-3 py-2 text-sm" @click.stop="removeField(index)">Delete</button>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] px-4 py-3 text-sm text-[var(--vc-text-soft)]">
                            <template v-if="field.type === 'page_break'">
                                Starts a new page. Next page title: {{ field.options.page_title || 'Untitled page' }}
                            </template>
                            <template v-else-if="field.type === 'heading'">
                                Heading block rendered in the form.
                            </template>
                            <template v-else-if="field.type === 'html'">
                                Custom HTML block.
                            </template>
                            <template v-else>
                                {{ field.placeholder || field.help_text || 'No helper text yet.' }}
                            </template>
                        </div>
                    </article>
                </div>
            </main>

            <aside class="px-5 py-6">
                <div v-if="currentField" class="space-y-5">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="tab in ['field', 'validation', 'logic', 'appearance', 'advanced']"
                            :key="tab"
                            type="button"
                            class="rounded-full px-3 py-1.5 text-sm transition"
                            :class="ui.inspectorTab === tab
                                ? 'bg-[var(--vc-primary)] text-white'
                                : 'bg-[var(--vc-surface-strong)] text-[var(--vc-text-soft)]'"
                            @click="ui.inspectorTab = tab"
                        >
                            {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
                        </button>
                    </div>

                    <div class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] p-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-[var(--vc-text-soft)]">
                            {{ currentField.label || currentField.type }}
                        </h3>
                        <div class="mt-4 space-y-4">
                            <template
                                v-for="prop in (getRegistryEntry(currentField.type)?.editor?.tabs?.[ui.inspectorTab] ?? [])"
                                :key="`${currentField.id}-${ui.inspectorTab}-${prop}`"
                            >
                                <label class="block">
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
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-3xl border border-dashed border-[var(--vc-border)] px-5 py-10 text-center text-sm text-[var(--vc-text-soft)]">
                    Select a field to edit its properties.
                </div>
            </aside>
        </div>

        <div v-else class="grid gap-6 px-6 py-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] p-5">
                <h3 class="text-lg font-semibold text-[var(--vc-text)]">
                    {{ ui.workspace.charAt(0).toUpperCase() + ui.workspace.slice(1) }}
                </h3>
                <p class="mt-2 text-sm text-[var(--vc-text-soft)]">
                    This workspace is now mounted in Vue and ready for the next production pass.
                </p>

                <div class="mt-5 space-y-4">
                    <label v-if="ui.workspace === 'appearance'" class="block">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Submit button label</span>
                        <input v-model="form.settings.submit_label" type="text" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <label v-if="ui.workspace === 'appearance'" class="block">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Success message</span>
                        <textarea v-model="form.settings.success_message" rows="3" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm"></textarea>
                    </label>

                    <label v-if="ui.workspace === 'behavior'" class="inline-flex items-center gap-2 rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                        <input v-model="form.settings.honeypot_enabled" type="checkbox" class="rounded border-slate-300">
                        <span>Enable honeypot</span>
                    </label>

                    <label v-if="ui.workspace === 'behavior'" class="inline-flex items-center gap-2 rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                        <input v-model="form.settings.require_login" type="checkbox" class="rounded border-slate-300">
                        <span>Require login to submit</span>
                    </label>

                    <label v-if="ui.workspace === 'emails'" class="block">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Admin notification emails</span>
                        <input v-model="form.settings.notify_admin_emails" type="text" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <label v-if="ui.workspace === 'visibility'" class="block">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Available from</span>
                        <input v-model="form.settings.available_from" type="datetime-local" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>

                    <label v-if="ui.workspace === 'visibility'" class="block">
                        <span class="mb-1 block text-sm font-medium text-[var(--vc-text)]">Available to</span>
                        <input v-model="form.settings.available_to" type="datetime-local" class="w-full rounded-xl border border-[var(--vc-border)] bg-white px-3 py-2 text-sm">
                    </label>
                </div>
            </section>

            <section class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] p-5">
                <h3 class="text-lg font-semibold text-[var(--vc-text)]">Summary</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-[var(--vc-text-soft)]">Fields</dt>
                        <dd class="font-medium text-[var(--vc-text)]">{{ form.fields.length }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[var(--vc-text-soft)]">Type</dt>
                        <dd class="font-medium text-[var(--vc-text)]">{{ form.type }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[var(--vc-text-soft)]">Status</dt>
                        <dd class="font-medium text-[var(--vc-text)]">{{ form.is_active ? 'Published' : 'Draft' }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</template>
