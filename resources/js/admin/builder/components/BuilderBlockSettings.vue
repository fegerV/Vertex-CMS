<template>
    <div class="space-y-4">
        <div v-if="filteredFields.length" class="space-y-4">
            <div
                v-for="[key, field] in filteredFields"
                :key="key"
                class="vc-builder-form-section"
            >
                <div class="vc-builder-field">
                    <label>{{ field.label }}</label>
                    <span v-if="field.help" class="vc-builder-field-hint">{{ field.help }}</span>
                </div>

                <div v-if="field.type === 'repeater'" class="space-y-3">
                    <div
                        v-if="!Array.isArray(localSettings[key]) || localSettings[key].length === 0"
                        class="vc-builder-empty p-4 text-center"
                        :class="{ 'vc-builder-drop-target': repeaterDropTarget?.key === key }"
                        @dragover.prevent="onRepeaterDragOver(key, 0)"
                        @drop.prevent="onRepeaterDrop(key, 0)"
                    >
                        <p class="text-sm font-semibold text-[var(--vc-text)]">No items yet</p>
                        <p class="mt-1 text-xs text-[var(--vc-text-soft)]">Add entries to populate this repeater field.</p>
                    </div>

                    <div v-else class="vc-builder-repeater-list">
                        <div
                            v-for="(item, itemIndex) in localSettings[key]"
                            :key="item.id || itemIndex"
                            class="vc-builder-repeater-card"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <button
                                        draggable="true"
                                        type="button"
                                        class="vc-builder-icon-button vc-builder-drag-handle"
                                        title="Move item"
                                        aria-label="Move item"
                                        @dragstart="onRepeaterDragStart(key, itemIndex, $event)"
                                        @dragend="onRepeaterDragEnd"
                                    >
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                            <path d="M7 5h.01M13 5h.01M7 10h.01M13 10h.01M7 15h.01M13 15h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                    <span class="vc-builder-badge">Item {{ itemIndex + 1 }}</span>
                                </div>

                                <button
                                    type="button"
                                    class="vc-builder-icon-button text-rose-300"
                                    title="Delete item"
                                    aria-label="Delete item"
                                    @click="removeRepeaterItem(key, itemIndex)"
                                >
                                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>

                            <div
                                class="space-y-3"
                                :class="{ 'vc-builder-drop-target': isRepeaterDropTarget(key, itemIndex) }"
                                @dragover.prevent="onRepeaterDragOver(key, itemIndex)"
                                @drop.prevent="onRepeaterDrop(key, itemIndex)"
                            >
                                <div
                                    v-for="nestedField in field.fields || []"
                                    :key="nestedField.key"
                                    class="vc-builder-field"
                                >
                                    <label>{{ nestedField.label }}</label>

                                    <div v-if="isMediaField(nestedField, nestedField.key)" class="space-y-3">
                                        <div v-if="mediaPreview([key, itemIndex, nestedField.key])" class="vc-builder-media-inline">
                                            <div class="vc-builder-media-inline-thumb">
                                                <img
                                                    v-if="mediaPreview([key, itemIndex, nestedField.key]).mime_type?.startsWith('image/')"
                                                    :src="mediaPreview([key, itemIndex, nestedField.key]).url"
                                                    :alt="mediaPreview([key, itemIndex, nestedField.key]).alt || mediaPreview([key, itemIndex, nestedField.key]).original_filename || nestedField.label"
                                                >
                                                <div v-else class="vc-builder-renderer-fallback h-full place-content-center">File</div>
                                            </div>
                                            <div class="space-y-1">
                                                <div class="text-sm font-semibold text-[var(--vc-text)]">
                                                    {{ mediaPreview([key, itemIndex, nestedField.key]).title || mediaPreview([key, itemIndex, nestedField.key]).original_filename }}
                                                </div>
                                                <div class="text-xs text-[var(--vc-text-soft)]">
                                                    ID: {{ resolveMediaId([key, itemIndex, nestedField.key]) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <button
                                                type="button"
                                                class="vc-button vc-button-secondary px-3 py-2"
                                                @click="$emit('open-media-picker', { path: [key, itemIndex, nestedField.key], field: nestedField, blockType: type })"
                                            >
                                                {{ resolveMediaId([key, itemIndex, nestedField.key]) ? 'Replace media' : 'Choose media' }}
                                            </button>
                                            <button
                                                v-if="resolveMediaId([key, itemIndex, nestedField.key])"
                                                type="button"
                                                class="vc-button vc-button-secondary px-3 py-2"
                                                @click="clearMediaField([key, itemIndex, nestedField.key])"
                                            >
                                                Clear
                                            </button>
                                        </div>
                                    </div>

                                    <input
                                        v-else-if="nestedField.type === 'text' || nestedField.type === 'number' || nestedField.type === 'color'"
                                        :type="nestedField.type"
                                        :value="item[nestedField.key]"
                                        :class="nestedField.type === 'color' ? 'vc-input h-12' : 'vc-input'"
                                        @input="updateRepeaterField(key, itemIndex, nestedField.key, $event.target.value)"
                                    >

                                    <textarea
                                        v-else-if="nestedField.type === 'textarea'"
                                        :rows="nestedField.rows || 3"
                                        class="vc-textarea"
                                        :value="item[nestedField.key]"
                                        @input="updateRepeaterField(key, itemIndex, nestedField.key, $event.target.value)"
                                    ></textarea>

                                    <select
                                        v-else-if="nestedField.type === 'select'"
                                        class="vc-select"
                                        :value="item[nestedField.key]"
                                        @change="updateRepeaterField(key, itemIndex, nestedField.key, $event.target.value)"
                                    >
                                        <option v-for="opt in nestedField.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>

                                    <div
                                        v-else-if="nestedField.type === 'toggle'"
                                        class="vc-builder-toggle"
                                        :class="item[nestedField.key] ? 'vc-builder-toggle-active' : ''"
                                        @click="updateRepeaterField(key, itemIndex, nestedField.key, !item[nestedField.key])"
                                    >
                                        <div class="vc-builder-toggle-knob"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="vc-button vc-button-secondary px-3 py-2" @click="addRepeaterItem(key, field)">
                        Add item
                    </button>
                </div>

                <div v-else-if="isMediaField(field, key)" class="space-y-3">
                    <div v-if="mediaPreview(key)" class="vc-builder-media-inline">
                        <div class="vc-builder-media-inline-thumb">
                            <img
                                v-if="mediaPreview(key).mime_type?.startsWith('image/')"
                                :src="mediaPreview(key).url"
                                :alt="mediaPreview(key).alt || mediaPreview(key).original_filename || field.label"
                            >
                            <div v-else class="vc-builder-renderer-fallback h-full place-content-center">File</div>
                        </div>

                        <div class="space-y-1">
                            <div class="text-sm font-semibold text-[var(--vc-text)]">
                                {{ mediaPreview(key).title || mediaPreview(key).original_filename }}
                            </div>
                            <div class="text-xs text-[var(--vc-text-soft)]">ID: {{ resolveMediaId(key) }}</div>
                            <div class="text-xs text-[var(--vc-text-soft)]">{{ mediaPreview(key).mime_type }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="vc-button vc-button-secondary px-3 py-2"
                            @click="$emit('open-media-picker', { key, field, blockType: type })"
                        >
                            {{ resolveMediaId(key) ? 'Replace media' : 'Choose media' }}
                        </button>
                        <button
                            v-if="resolveMediaId(key)"
                            type="button"
                            class="vc-button vc-button-secondary px-3 py-2"
                            @click="clearMediaField(key)"
                        >
                            Clear
                        </button>
                    </div>

                    <div class="vc-builder-field-hint">
                        <span v-if="resolveMediaId(key)">Linked media ID: {{ resolveMediaId(key) }}</span>
                        <span v-else>No media selected yet.</span>
                    </div>
                </div>

                <input
                    v-else-if="field.type === 'text' || field.type === 'color' || field.type === 'number'"
                    v-model="localSettings[key]"
                    :type="field.type"
                    :placeholder="field.placeholder"
                    :class="field.type === 'color' ? 'vc-input h-12' : 'vc-input'"
                >

                <textarea
                    v-else-if="field.type === 'textarea'"
                    v-model="localSettings[key]"
                    :rows="field.rows || 3"
                    class="vc-textarea"
                ></textarea>

                <select
                    v-else-if="field.type === 'select'"
                    v-model="localSettings[key]"
                    class="vc-select"
                >
                    <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>

                <div
                    v-else-if="field.type === 'toggle'"
                    class="vc-builder-toggle"
                    :class="localSettings[key] ? 'vc-builder-toggle-active' : ''"
                    @click="localSettings[key] = !localSettings[key]"
                >
                    <div class="vc-builder-toggle-knob"></div>
                </div>
            </div>
        </div>

        <div v-else class="vc-builder-form-section">
            <div class="vc-builder-field-hint">{{ emptyMessage }}</div>
        </div>
    </div>
</template>

<script>
const STYLE_KEYWORDS = ['color', 'background', 'align', 'size', 'width', 'height', 'padding', 'margin', 'gap', 'radius', 'shadow', 'border', 'font', 'opacity'];
const ADVANCED_KEYWORDS = ['class', 'id', 'target', 'rel', 'loading', 'anchor', 'lazy', 'role', 'schema', 'aria', 'hook'];
const STYLE_GROUPS = ['style', 'appearance', 'surface', 'spacing', 'layout', 'design'];
const ADVANCED_GROUPS = ['advanced', 'technical', 'meta', 'system'];
const CONTENT_GROUPS = ['content', 'copy', 'data', 'general'];

export default {
    name: 'BuilderBlockSettings',
    props: {
        type: {
            type: String,
            required: true,
        },
        settings: {
            type: Object,
            default: () => ({}),
        },
        mediaLookup: {
            type: Object,
            default: () => ({}),
        },
        registry: {
            type: Object,
            default: () => ({}),
        },
        fieldGroup: {
            type: String,
            default: 'all',
        },
    },
    emits: ['update', 'open-media-picker'],
    data() {
        return {
            localSettings: { ...this.settings },
            draggedRepeater: null,
            repeaterDropTarget: null,
        };
    },
    computed: {
        blockDef() {
            return this.registry?.[this.type] || window.availableBlocks?.[this.type];
        },
        filteredFields() {
            const fields = Object.entries(this.blockDef?.fields || {});
            return fields.filter(([key, field]) => this.belongsToGroup(key, field));
        },
        emptyMessage() {
            if (this.fieldGroup === 'style') return 'No visual controls are registered for this block yet.';
            if (this.fieldGroup === 'advanced') return 'No advanced controls are registered for this block yet.';
            return 'No editable fields are registered for this block yet.';
        },
    },
    watch: {
        settings: {
            deep: true,
            immediate: true,
            handler(newSettings) {
                this.localSettings = { ...(newSettings || {}) };
            },
        },
        localSettings: {
            deep: true,
            handler() {
                this.$emit('update', this.localSettings);
            },
        },
    },
    methods: {
        resolveFieldGroup(key, field) {
            const explicit = String(field?.group || field?.tab || field?.panel || field?.section || '').toLowerCase().trim();

            if (STYLE_GROUPS.includes(explicit)) return 'style';
            if (ADVANCED_GROUPS.includes(explicit)) return 'advanced';
            if (CONTENT_GROUPS.includes(explicit)) return 'content';

            if (field?.type === 'media' || key === 'media_id') {
                return 'content';
            }

            const normalizedKey = String(key).toLowerCase();

            if (ADVANCED_KEYWORDS.some((keyword) => normalizedKey.includes(keyword))) {
                return 'advanced';
            }

            if (STYLE_KEYWORDS.some((keyword) => normalizedKey.includes(keyword))) {
                return 'style';
            }

            return 'content';
        },
        belongsToGroup(key, field) {
            if (this.fieldGroup === 'all') return true;
            return this.resolveFieldGroup(key, field) === this.fieldGroup;
        },
        isMediaField(field, key) {
            return field?.type === 'media' || key === 'media_id';
        },
        normalizePath(path) {
            return Array.isArray(path) ? path : [path];
        },
        getValueAtPath(path) {
            return this.normalizePath(path).reduce((acc, segment) => acc?.[segment], this.localSettings);
        },
        setValueAtPath(path, value) {
            const normalizedPath = this.normalizePath(path);
            const last = normalizedPath.at(-1);
            const target = normalizedPath.slice(0, -1).reduce((acc, segment) => acc?.[segment], this.localSettings);
            if (!target || last === undefined) {
                return;
            }
            target[last] = value;
        },
        resolveMediaId(path) {
            return this.getValueAtPath(path) || null;
        },
        mediaPreview(path) {
            return this.mediaLookup?.[this.resolveMediaId(path)] || null;
        },
        clearMediaField(path) {
            const normalizedPath = this.normalizePath(path);
            const last = normalizedPath.at(-1);

            if (last === 'media_id' && normalizedPath.length === 1) {
                this.localSettings.media_id = null;
                this.localSettings.url = '';
                return;
            }

            this.setValueAtPath(normalizedPath, null);
        },
        addRepeaterItem(key, field) {
            if (!Array.isArray(this.localSettings[key])) {
                this.localSettings[key] = [];
            }

            const template = Object.fromEntries(
                (field.fields || []).map((nestedField) => {
                    if (nestedField.type === 'toggle') return [nestedField.key, false];
                    if (nestedField.type === 'number') return [nestedField.key, null];
                    return [nestedField.key, nestedField.type === 'media' ? null : ''];
                })
            );

            this.localSettings[key].push(template);
        },
        removeRepeaterItem(key, itemIndex) {
            if (!Array.isArray(this.localSettings[key])) return;
            this.localSettings[key].splice(itemIndex, 1);
        },
        updateRepeaterField(key, itemIndex, nestedKey, value) {
            if (!Array.isArray(this.localSettings[key])) return;
            this.localSettings[key][itemIndex][nestedKey] = value;
        },
        onRepeaterDragStart(key, itemIndex, event) {
            this.draggedRepeater = { key, itemIndex };
            this.repeaterDropTarget = { key, itemIndex };
            if (event?.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
            }
        },
        onRepeaterDragOver(key, itemIndex) {
            if (!this.draggedRepeater) return;
            this.repeaterDropTarget = { key, itemIndex };
        },
        onRepeaterDrop(key, itemIndex) {
            if (!this.draggedRepeater || !Array.isArray(this.localSettings[key]) || !Array.isArray(this.localSettings[this.draggedRepeater.key])) {
                this.onRepeaterDragEnd();
                return;
            }

            const sourceKey = this.draggedRepeater.key;
            const fromIndex = this.draggedRepeater.itemIndex;
            if (sourceKey === key && fromIndex === itemIndex) {
                this.onRepeaterDragEnd();
                return;
            }

            const [item] = this.localSettings[sourceKey].splice(fromIndex, 1);
            this.localSettings[key].splice(itemIndex, 0, item);
            this.onRepeaterDragEnd();
        },
        onRepeaterDragEnd() {
            this.draggedRepeater = null;
            this.repeaterDropTarget = null;
        },
        isRepeaterDropTarget(key, itemIndex) {
            return this.repeaterDropTarget?.key === key && this.repeaterDropTarget?.itemIndex === itemIndex;
        },
    },
};
</script>
