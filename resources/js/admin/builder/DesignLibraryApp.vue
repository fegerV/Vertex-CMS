<template>
    <div class="vc-design-library-shell">
        <section class="rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface)] p-5 shadow-sm">
            <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[var(--vc-text-muted)]">Design Library</p>
                    <h2 class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">Библиотека шаблонов, стартеров и пресетов</h2>
                    <p class="mt-2 text-sm text-[var(--vc-text-soft)]">Единый браузер backend-owned workspace: превью, категории, ownership и управление без изменения JSON-first контракта VertexCMS.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="/admin/pages" class="vc-button vc-button-secondary px-4 py-2">Страницы</a>
                    <a v-if="pageBuilderUrl" :href="pageBuilderUrl" class="vc-button vc-button-secondary px-4 py-2">Открыть Builder</a>
                    <button type="button" class="vc-button vc-button-secondary px-4 py-2" @click="loadWorkspace">Обновить</button>
                </div>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <div v-for="stat in stats" :key="stat.label" class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--vc-text-muted)]">{{ stat.label }}</div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ stat.value }}</div>
                </div>
            </div>
        </section>

        <section class="mt-6 rounded-3xl border border-[var(--vc-border)] bg-[var(--vc-surface)] p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="vc-builder-tab-row">
                    <button
                        v-for="collection in collections"
                        :key="collection.id"
                        type="button"
                        class="vc-builder-tab"
                        :class="{ 'vc-builder-tab-active': activeCollection === collection.id }"
                        @click="activeCollection = collection.id"
                    >
                        {{ collectionLabel(collection.id) }}
                    </button>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 lg:min-w-[440px]">
                    <input v-model="searchQuery" type="search" class="vc-input" placeholder="Поиск по названию, описанию, категории или автору">
                    <select v-model="categoryQuery" class="vc-select">
                        <option v-for="option in categoryOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
            </div>
        </section>

        <section v-if="loading" class="vc-builder-empty mt-6 p-8 text-center">
            Загрузка библиотеки...
        </section>

        <section v-else-if="error" class="rounded-3xl border border-rose-200 bg-rose-50 p-6 text-rose-900">
            {{ error }}
        </section>

        <section v-else class="mt-6">
            <div v-if="filteredItems.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="item in filteredItems"
                    :key="item.id"
                    class="vc-design-library-card"
                >
                    <button type="button" class="vc-design-library-thumb" @click="openPreview(item)">
                        <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="h-full w-full object-cover">
                        <span v-else class="vc-design-library-thumb-fallback">{{ item.name }}</span>
                    </button>

                    <div class="p-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="vc-builder-badge">{{ sourceLabel(item) }}</span>
                            <span class="vc-builder-badge vc-builder-badge-quiet">{{ visibilityLabel(item) }}</span>
                            <span v-if="item.can_edit" class="vc-builder-badge vc-builder-badge-active">Редактируемый</span>
                        </div>

                        <h3 class="mt-4 text-base font-semibold text-[var(--vc-text)]">{{ item.name }}</h3>
                        <p class="vc-design-library-card-copy mt-2 text-sm text-[var(--vc-text-soft)]">{{ item.description || item.meta || 'Без описания.' }}</p>

                        <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold uppercase tracking-[0.12em] text-[var(--vc-text-soft)]">
                            <span>{{ categoryLabel(item) }}</span>
                            <span v-if="item.sections_count !== undefined">&middot;</span>
                            <span v-if="item.sections_count !== undefined">{{ item.sections_count }} секц.</span>
                            <span v-if="item.blocks_count !== undefined">&middot;</span>
                            <span v-if="item.blocks_count !== undefined">{{ item.blocks_count }} блоков</span>
                            <span v-if="item.owner">&middot;</span>
                            <span v-if="item.owner">{{ item.owner }}</span>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <button type="button" class="vc-button vc-button-secondary flex-1" @click="openPreview(item)">Превью</button>
                            <button v-if="canApplyTemplate(item)" type="button" class="vc-button vc-button-primary flex-1" @click="applyTemplateToPage(item)">Применить</button>
                            <button type="button" class="vc-button vc-button-secondary" :aria-label="`Скопировать ID ${item.id}`" @click="copyItemId(item)">ID</button>
                            <button v-if="item.can_edit && activeCollection === 'templates'" type="button" class="vc-button vc-button-secondary" @click="startTemplateEdit(item)">Edit</button>
                            <button v-if="item.can_edit && activeCollection === 'presets'" type="button" class="vc-button vc-button-secondary" @click="startPresetEdit(item)">Edit</button>
                        </div>
                    </div>
                </article>
            </div>

            <div v-else class="vc-builder-empty p-8 text-center">
                <h3 class="text-base font-semibold text-[var(--vc-text)]">{{ emptyStateTitle }}</h3>
                <p class="mt-2 text-sm text-[var(--vc-text-soft)]">{{ emptyStateCopy }}</p>
            </div>
        </section>

        <div v-if="previewItem" class="vc-builder-modal fixed inset-0 z-50 flex items-center justify-center p-6" @click.self="closePreview" @keydown.esc="closePreview">
            <div class="vc-builder-modal-card w-full max-w-5xl overflow-hidden">
                <div class="flex items-start justify-between gap-4 border-b border-[var(--vc-border)] p-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[var(--vc-text-muted)]">{{ collectionLabel(previewItem.collectionId) }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-[var(--vc-text)]">{{ previewItem.name }}</h3>
                        <p class="mt-2 text-sm text-[var(--vc-text-soft)]">{{ previewItem.description || previewItem.meta || 'Без описания.' }}</p>
                    </div>
                    <button type="button" class="vc-button vc-button-secondary" @click="closePreview">Закрыть</button>
                </div>

                <div class="grid gap-0 lg:grid-cols-[minmax(0,1.4fr)_360px]">
                    <div class="bg-slate-950 p-6">
                        <img v-if="previewItem.thumbnail" :src="previewItem.thumbnail" :alt="previewItem.name" class="mx-auto max-h-[520px] rounded-3xl object-contain shadow-2xl">
                        <div v-else class="vc-design-library-thumb-preview">{{ previewItem.name }}</div>
                    </div>

                    <aside class="space-y-4 border-l border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-5">
                        <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface)] p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--vc-text-muted)]">Метаданные</div>
                            <dl class="mt-3 space-y-2 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--vc-text-soft)]">Source</dt>
                                    <dd class="font-semibold text-[var(--vc-text)]">{{ sourceLabel(previewItem) }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--vc-text-soft)]">Visibility</dt>
                                    <dd class="font-semibold text-[var(--vc-text)]">{{ visibilityLabel(previewItem) }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--vc-text-soft)]">Категория</dt>
                                    <dd class="font-semibold text-[var(--vc-text)]">{{ categoryLabel(previewItem) }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--vc-text-soft)]">Блоков</dt>
                                    <dd class="font-semibold text-[var(--vc-text)]">{{ previewItem.blocks_count || 0 }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--vc-text-soft)]">ID</dt>
                                    <dd class="break-all font-mono text-xs text-[var(--vc-text)]">{{ previewItem.id }}</dd>
                                </div>
                            </dl>
                        </div>

                        <button v-if="canApplyTemplate(previewItem)" type="button" class="vc-button vc-button-primary w-full justify-center" @click="applyTemplateToPage(previewItem)">Применить к странице</button>
                        <button type="button" class="vc-button vc-button-secondary w-full justify-center" @click="copyItemId(previewItem)">Скопировать ID</button>
                    </aside>
                </div>
            </div>
        </div>

        <div v-if="editingTemplate" class="vc-builder-modal fixed inset-0 z-50 flex items-center justify-center p-6" @click.self="editingTemplate = null" @keydown.esc="editingTemplate = null">
            <form class="vc-builder-modal-card w-full max-w-xl" @submit.prevent="saveTemplateMetadata">
                <div class="border-b border-[var(--vc-border)] p-5">
                    <h3 class="text-lg font-semibold text-[var(--vc-text)]">Редактировать шаблон</h3>
                    <p class="mt-1 text-sm text-[var(--vc-text-soft)]">Измените название, категорию и видимость без сброса секций.</p>
                </div>

                <div class="space-y-4 p-5">
                    <label class="vc-field">
                        <span class="vc-field-label">Название</span>
                        <input v-model="editingTemplate.name" type="text" class="vc-input" required>
                    </label>
                    <label class="vc-field">
                        <span class="vc-field-label">Категория</span>
                        <input v-model="editingTemplate.category" type="text" class="vc-input" required>
                    </label>
                    <label class="vc-field">
                        <span class="vc-field-label">Видимость</span>
                        <select v-model="editingTemplate.visibility" class="vc-select">
                            <option value="shared">Общий</option>
                            <option value="private">Приватный</option>
                        </select>
                    </label>
                </div>

                <div class="flex flex-wrap justify-between gap-2 border-t border-[var(--vc-border)] p-5">
                    <button v-if="editingTemplate.can_delete" type="button" class="vc-button vc-button-danger" @click="deleteTemplate">Удалить</button>
                    <span v-else></span>
                    <button type="button" class="vc-button vc-button-secondary" @click="editingTemplate = null">Отмена</button>
                    <button type="submit" class="vc-button vc-button-primary">Сохранить</button>
                </div>
            </form>
        </div>

        <div v-if="editingPreset" class="vc-builder-modal fixed inset-0 z-50 flex items-center justify-center p-6" @click.self="editingPreset = null" @keydown.esc="editingPreset = null">
            <form class="vc-builder-modal-card w-full max-w-xl" @submit.prevent="savePresetMetadata">
                <div class="border-b border-[var(--vc-border)] p-5">
                    <h3 class="text-lg font-semibold text-[var(--vc-text)]">Редактировать пресет</h3>
                    <p class="mt-1 text-sm text-[var(--vc-text-soft)]">Измените название и видимость пресета.</p>
                </div>

                <div class="space-y-4 p-5">
                    <label class="vc-field">
                        <span class="vc-field-label">Название</span>
                        <input v-model="editingPreset.name" type="text" class="vc-input" required>
                    </label>
                    <label class="vc-field">
                        <span class="vc-field-label">Видимость</span>
                        <select v-model="editingPreset.visibility" class="vc-select">
                            <option value="shared">Общий</option>
                            <option value="private">Приватный</option>
                        </select>
                    </label>
                </div>

                <div class="flex flex-wrap justify-between gap-2 border-t border-[var(--vc-border)] p-5">
                    <button v-if="editingPreset.can_delete" type="button" class="vc-button vc-button-danger" @click="deletePreset">Удалить</button>
                    <span v-else></span>
                    <button type="button" class="vc-button vc-button-secondary" @click="editingPreset = null">Отмена</button>
                    <button type="submit" class="vc-button vc-button-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    workspace: {
        type: Object,
        default: () => ({}),
    },
    apiUrl: {
        type: String,
        default: '/admin/api/pages/builder/design-library',
    },
});

const workspace = ref(props.workspace || {});
const activeCollection = ref('templates');
const searchQuery = ref('');
const categoryQuery = ref('all');
const loading = ref(false);
const error = ref('');
const previewItem = ref(null);
const editingTemplate = ref(null);
const editingPreset = ref(null);
const appliedTemplateId = ref('');

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const pageId = new URLSearchParams(window.location.search).get('page_id');

const collections = computed(() => workspace.value.collections || []);
const activeCollectionData = computed(() => collections.value.find((item) => item.id === activeCollection.value) || { id: activeCollection.value, items: [] });
const activeCollectionItems = computed(() => activeCollectionData.value.items || []);

const stats = computed(() => {
    const stats = workspace.value.stats || {};

    return [
        { label: 'Templates', value: stats.templates || 0 },
        { label: 'Starters', value: stats.starters || 0 },
        { label: 'Presets', value: stats.presets || 0 },
        { label: 'Editable', value: stats.editable_items || 0 },
        { label: 'Built-in', value: stats.builtin_templates || 0 },
    ];
});

const categoryOptions = computed(() => {
    const raw = workspace.value.categories?.[activeCollection.value] || [];

    if (!raw.length) {
        return [{ value: 'all', label: 'Все категории' }];
    }

    return [
        { value: 'all', label: 'Все категории' },
        ...raw.map((category) => ({
            value: category.id,
            label: `${category.label} (${category.count})`,
        })),
    ];
});

const pageBuilderUrl = computed(() => {
    if (!pageId) return '';
    return `/admin/pages/${encodeURIComponent(pageId)}/builder`;
});

const filteredItems = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    const category = categoryQuery.value;

    return activeCollectionItems.value.filter((item) => {
        if (category !== 'all' && categoryField(item) !== category) {
            return false;
        }

        if (!query) {
            return true;
        }

        const haystack = [
            item.id,
            item.name,
            item.description,
            item.meta,
            item.category,
            item.type,
            item.source,
            item.visibility,
            item.owner,
        ].join(' ').toLowerCase();

        return haystack.includes(query);
    });
});

const emptyStateTitle = computed(() => {
    if (searchQuery.value || categoryQuery.value !== 'all') {
        return 'Ничего не найдено';
    }

    return workspace.value.empty_states?.[activeCollection.value] || 'Библиотека пока пуста';
});

const emptyStateCopy = computed(() => {
    if (searchQuery.value || categoryQuery.value !== 'all') {
        return 'Измените поисковый запрос или категорию.';
    }

    return 'Добавьте шаблон, пресет или стартовый набор, чтобы они появились здесь.';
});

const collectionLabel = (id) => {
    const labels = {
        templates: 'Шаблоны',
        starters: 'Стартеры',
        presets: 'Пресеты',
    };

    return labels[id] || id;
};

const sourceLabel = (item) => {
    if (item.source === 'builtin') return 'Встроенный';
    if (item.source === 'private') return 'Приватный';
    return 'Общий';
};

const visibilityLabel = (item) => {
    if (item.visibility === 'private') return 'Приватный';
    return 'Общий';
};

const categoryField = (item) => {
    if (activeCollection.value === 'presets') {
        return item.type || 'uncategorized';
    }

    return item.category || item.source || 'uncategorized';
};

const categoryLabel = (item) => {
    return String(categoryField(item))
        .replace(/[-_]+/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
};

const canApplyTemplate = (item) => {
    const collectionId = item?.collectionId || activeCollection.value;
    return Boolean(pageId && collectionId === 'templates' && item?.sections?.length);
};

const copyItemId = async (item) => {
    try {
        await navigator.clipboard.writeText(item.id);
    } catch (error) {
        console.error('Copy item id error:', error);
    }
};

const openPreview = (item) => {
    previewItem.value = {
        ...item,
        collectionId: activeCollection.value,
    };
};

const closePreview = () => {
    previewItem.value = null;
};

const loadWorkspace = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch(props.apiUrl, {
            headers: {
                Accept: 'application/json',
            },
        });
        if (!response.ok) {
            throw new Error('Не удалось загрузить дизайн-библиотеку.');
        }

        const payload = await response.json();
        workspace.value = payload.data || {};
    } catch (caught) {
        error.value = caught.message || 'Не удалось загрузить дизайн-библиотеку.';
    } finally {
        loading.value = false;
    }
};

const applyTemplateToPage = async (template) => {
    if (!pageId) return;

    try {
        const response = await fetch(`/admin/pages/${encodeURIComponent(pageId)}/builder/template`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                template_id: template.id,
                merge: true,
            }),
        });

        if (!response.ok) {
            throw new Error('Не удалось применить шаблон.');
        }

        appliedTemplateId.value = template.id;
        window.location.href = pageBuilderUrl.value;
    } catch (caught) {
        error.value = caught.message || 'Не удалось применить шаблон.';
    }
};

const startTemplateEdit = (template) => {
    editingTemplate.value = {
        id: template.id,
        name: template.name,
        category: template.category || 'custom',
        visibility: template.visibility || 'shared',
        can_delete: Boolean(template.can_delete),
    };
};

const saveTemplateMetadata = async () => {
    if (!editingTemplate.value) return;

    try {
        const response = await fetch(`/admin/pages/builder/shared-templates/${encodeURIComponent(editingTemplate.value.id)}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                name: editingTemplate.value.name,
                category: editingTemplate.value.category,
                visibility: editingTemplate.value.visibility,
            }),
        });

        if (!response.ok) {
            throw new Error('Не удалось сохранить шаблон.');
        }

        await loadWorkspace();
        editingTemplate.value = null;
    } catch (caught) {
        error.value = caught.message || 'Не удалось сохранить шаблон.';
    }
};

const startPresetEdit = (preset) => {
    editingPreset.value = {
        id: preset.id,
        name: preset.name,
        visibility: preset.visibility || 'shared',
        can_delete: Boolean(preset.can_delete),
    };
};

const savePresetMetadata = async () => {
    if (!editingPreset.value) return;

    try {
        const response = await fetch(`/admin/pages/builder/presets/${encodeURIComponent(editingPreset.value.id)}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                name: editingPreset.value.name,
                visibility: editingPreset.value.visibility,
            }),
        });

        if (!response.ok) {
            throw new Error('Не удалось сохранить пресет.');
        }

        await loadWorkspace();
        editingPreset.value = null;
    } catch (caught) {
        error.value = caught.message || 'Не удалось сохранить пресет.';
    }
};

const deleteTemplate = async () => {
    if (!editingTemplate.value?.can_delete) return;
    if (!window.confirm('Удалить этот шаблон из дизайн-библиотеки?')) return;

    try {
        const response = await fetch(`/admin/pages/builder/shared-templates/${encodeURIComponent(editingTemplate.value.id)}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        if (!response.ok) {
            throw new Error('Не удалось удалить шаблон.');
        }

        await loadWorkspace();
        editingTemplate.value = null;
    } catch (caught) {
        error.value = caught.message || 'Не удалось удалить шаблон.';
    }
};

const deletePreset = async () => {
    if (!editingPreset.value?.can_delete) return;
    if (!window.confirm('Удалить этот пресет из дизайн-библиотеки?')) return;

    try {
        const response = await fetch(`/admin/pages/builder/presets/${encodeURIComponent(editingPreset.value.id)}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        if (!response.ok) {
            throw new Error('Не удалось удалить пресет.');
        }

        await loadWorkspace();
        editingPreset.value = null;
    } catch (caught) {
        error.value = caught.message || 'Не удалось удалить пресет.';
    }
};

onMounted(loadWorkspace);
</script>
