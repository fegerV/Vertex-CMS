<template>
    <div class="space-y-4">
        <div v-if="filteredFieldPacks.length" class="space-y-5">
            <div
                v-for="pack in filteredFieldPacks"
                :key="pack.key"
                class="vc-builder-pack"
            >
                <div v-if="pack.label" class="vc-builder-pack-header">
                    <span class="vc-builder-pack-icon">{{ rowFamilyGlyph({ familyIcon: pack.icon }) }}</span>
                    <div>
                        <div class="vc-builder-pack-label">{{ pack.label }}</div>
                        <div v-if="pack.description" class="vc-builder-pack-description">{{ pack.description }}</div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="row in pack.rows"
                        :key="row.key"
                        class="vc-builder-form-row"
                        :class="rowClass(row)"
                    >
                        <div v-if="row.familyLabel" class="vc-builder-row-family">
                            <span class="vc-builder-row-family-icon">{{ rowFamilyGlyph(row) }}</span>
                            <div>
                                <div class="vc-builder-row-family-label">{{ row.familyLabel }}</div>
                                <div v-if="row.familyHint" class="vc-builder-row-family-hint">{{ row.familyHint }}</div>
                            </div>
                        </div>
                        <div
                            v-for="[key, field] in row.fields"
                            :key="key"
                            class="vc-builder-form-section"
                            :class="[
                                fieldCardClass(field),
                                { 'vc-builder-form-section-highlighted': isHighlightedField(key) },
                            ]"
                            :data-field-key="key"
                        >
                            <div class="vc-builder-field">
                                <div class="flex items-center gap-2">
                                    <label>{{ field.label }}</label>
                                    <span v-if="isPrimaryField(field) || isHighlightedField(key)" class="vc-builder-badge vc-builder-badge-active">Primary</span>
                                </div>
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
                        <p class="text-sm font-semibold text-[var(--vc-text)]">{{ isGalleryImagesField(key) ? 'Gallery is empty' : 'No items yet' }}</p>
                        <p class="mt-1 text-xs text-[var(--vc-text-soft)]">{{ isGalleryImagesField(key) ? 'Pick images from the media library or add a manual image row.' : 'Add entries to populate this repeater field.' }}</p>
                    </div>

                    <div v-else class="vc-builder-repeater-list">
                        <div
                            v-for="(item, itemIndex) in localSettings[key]"
                            :key="item.id || itemIndex"
                            class="vc-builder-repeater-card"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <div v-if="isGalleryImagesField(key) && galleryItemPreview(item)" class="vc-builder-gallery-thumb">
                                        <img :src="galleryItemPreview(item)" :alt="item.alt || item.caption || 'Gallery image'">
                                    </div>
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
                                    <span class="vc-builder-badge">{{ isGalleryImagesField(key) ? `Image ${itemIndex + 1}` : `Item ${itemIndex + 1}` }}</span>
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

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            v-if="isGalleryImagesField(key)"
                            type="button"
                            class="vc-button vc-button-primary px-3 py-2"
                            @click="$emit('open-media-picker', { key, field, blockType: type, mode: 'append-gallery-images' })"
                        >
                            Add image from library
                        </button>
                        <button type="button" class="vc-button vc-button-secondary px-3 py-2" @click="addRepeaterItem(key, field)">
                            {{ isGalleryImagesField(key) ? 'Add manual image' : 'Add item' }}
                        </button>
                    </div>
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

                    <div v-else-if="isColorControl(field)" class="vc-builder-color-control">
                        <input
                            :value="normalizeColorValue(localSettings[key])"
                            type="color"
                            class="vc-input h-12 w-16 shrink-0 p-1"
                            @input="updateColorValue(key, $event.target.value)"
                        >
                        <input
                            :value="localSettings[key]"
                            type="text"
                            :placeholder="field.placeholder || '#000000'"
                            class="vc-input"
                            @input="localSettings[key] = $event.target.value"
                        >
                    </div>

                    <div v-else-if="isSpacingControl(field)" class="vc-builder-spacing-control">
                        <input
                            class="vc-builder-range"
                            type="range"
                            :min="fieldControl(field).min ?? 0"
                            :max="fieldControl(field).max ?? 240"
                            :step="fieldControl(field).step ?? 1"
                            :value="numericControlValue(key, field)"
                            @input="updateSpacingValue(key, field, $event.target.value)"
                        >
                        <div class="vc-builder-spacing-value">
                            <input
                                class="vc-input"
                                :value="numericControlValue(key, field)"
                                type="number"
                                :min="fieldControl(field).min ?? 0"
                                :max="fieldControl(field).max ?? 240"
                                :step="fieldControl(field).step ?? 1"
                                @input="updateSpacingValue(key, field, $event.target.value)"
                            >
                            <span class="vc-builder-badge">{{ fieldControl(field).unit || '' }}</span>
                        </div>
                    </div>

                    <div v-else-if="isSegmentedSelectControl(field)" class="vc-builder-segmented-control">
                        <button
                            v-for="opt in field.options"
                            :key="opt.value"
                            type="button"
                            class="vc-builder-segmented-option"
                            :class="{ 'vc-builder-segmented-option-active': localSettings[key] === opt.value }"
                            @click="localSettings[key] = opt.value"
                        >
                            {{ opt.label }}
                        </button>
                    </div>

                    <div v-else-if="isLinkControl(field)" class="vc-builder-link-control">
                        <span class="vc-builder-link-icon" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="none">
                                <path d="M8.5 11.5l3-3M6.1 13.9l-1.5 1.5a2.5 2.5 0 103.5 3.5l1.6-1.6M13.9 6.1l1.5-1.5a2.5 2.5 0 10-3.5-3.5L10.3 2.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <input
                            v-model="localSettings[key]"
                            type="url"
                            :placeholder="field.placeholder || 'https://example.com'"
                            class="vc-input"
                        >
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
        highlightFields: {
            type: Array,
            default: () => [],
        },
    },
    emits: ['update', 'open-media-picker'],
    data() {
        return {
            localSettings: { ...this.settings },
            draggedRepeater: null,
            repeaterDropTarget: null,
            syncingFromProps: false,
            emitTimer: null,
        };
    },
    computed: {
        blockDef() {
            return this.registry?.[this.type] || window.availableBlocks?.[this.type];
        },
        sortedFields() {
            return Object.entries(this.blockDef?.fields || {})
                .sort(([, leftField], [, rightField]) => {
                    const leftPriority = Number(leftField?.priority ?? 999);
                    const rightPriority = Number(rightField?.priority ?? 999);

                    if (leftPriority === rightPriority) {
                        return String(leftField?.label || '').localeCompare(String(rightField?.label || ''));
                    }

                    return leftPriority - rightPriority;
                });
        },
        filteredFields() {
            const fields = this.sortedFields;
            return fields.filter(([key, field]) => this.belongsToGroup(key, field));
        },
        filteredFieldRows() {
            const rows = [];
            const grouped = new Map();

            for (const entry of this.filteredFields) {
                const [key, field] = entry;
                const rowKey = field?.layout?.row || `field-${key}`;

                if (!grouped.has(rowKey)) {
                    const row = {
                        key: rowKey,
                        fields: [],
                        family: this.fieldControl(field)?.family || null,
                        familyLabel: this.fieldControl(field)?.family_label || null,
                        familyIcon: this.fieldControl(field)?.family_icon || null,
                    };
                    grouped.set(rowKey, row);
                    rows.push(row);
                }

                grouped.get(rowKey).fields.push(entry);
            }

            return rows;
        },
        filteredFieldPacks() {
            const packs = [];
            const grouped = new Map();

            for (const row of this.filteredFieldRows) {
                const firstField = row.fields?.[0]?.[1] || {};
                const control = this.fieldControl(firstField);
                const packKey = control?.pack || 'content-pack';

                if (!grouped.has(packKey)) {
                    const recipe = this.blockPackRecipe(packKey);
                    const pack = {
                        key: packKey,
                        label: recipe?.label || control?.pack_label || null,
                        description: recipe?.description || control?.pack_description || null,
                        icon: recipe?.icon || control?.pack_icon || null,
                        rows: [],
                    };
                    grouped.set(packKey, pack);
                    packs.push(pack);
                }

                grouped.get(packKey).rows.push(row);
            }

            return packs;
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
                if (newSettings === this.localSettings) {
                    return;
                }
                if (this.emitTimer) {
                    clearTimeout(this.emitTimer);
                    this.emitTimer = null;
                }
                this.syncingFromProps = true;
                this.localSettings = { ...(newSettings || {}) };
                this.$nextTick(() => {
                    this.syncingFromProps = false;
                });
            },
        },
        highlightFields: {
            deep: true,
            immediate: true,
            handler() {
                this.scrollHighlightedFieldIntoView();
            },
        },
        localSettings: {
            deep: true,
            handler() {
                if (this.syncingFromProps) {
                    return;
                }
                this.scheduleSettingsUpdate();
            },
        },
    },
    beforeUnmount() {
        if (!this.emitTimer) {
            return;
        }

        clearTimeout(this.emitTimer);
        this.emitTimer = null;
        this.emitSettingsUpdate();
    },
    methods: {
        scheduleSettingsUpdate() {
            if (this.syncingFromProps) {
                return;
            }

            if (this.emitTimer) {
                clearTimeout(this.emitTimer);
            }

            this.emitTimer = setTimeout(() => {
                this.emitTimer = null;
                this.emitSettingsUpdate();
            }, 120);
        },
        emitSettingsUpdate() {
            if (this.syncingFromProps) {
                return;
            }

            this.$emit('update', this.localSettings);
        },
        isPrimaryField(field) {
            return String(field?.importance || '').toLowerCase() === 'primary';
        },
        rowClass(row) {
            const hasCompactPair = row.fields.length > 1 && row.fields.every(([, field]) => this.fieldSpan(field) === 'half');

            return {
                'vc-builder-form-row-two-up': hasCompactPair,
                [`vc-builder-form-row-family-${row.family}`]: Boolean(row.family),
            };
        },
        rowFamilyGlyph(row) {
            const icon = String(row?.familyIcon || '').toLowerCase();

            return {
                image: '◫',
                link: '↗',
                toggle: '◉',
                text: 'T',
                palette: '◌',
                spacing: '⋮⋮',
                sparkles: '✦',
                layers: '▣',
            }[icon] || '▣';
        },
        rowFamilyHint(row) {
            return {
                media: 'Asset selection and preview controls',
                link: 'Destination and navigation behavior',
                behavior: 'Interactive and runtime behavior',
                typography: 'Text hierarchy, sizing and alignment',
                surface: 'Colors, corners and visual treatments',
                spacing: 'Dimensions, gaps and responsive spacing',
                appearance: 'Visual mode and sizing presets',
                content: 'Primary content inputs',
            }[row?.family] || '';
        },
        fieldCardClass(field) {
            return {
                'vc-builder-form-section-half': this.fieldSpan(field) === 'half',
                'vc-builder-form-section-full': this.fieldSpan(field) !== 'half',
                'vc-builder-form-section-compact': this.fieldVariant(field) === 'compact',
                'vc-builder-form-section-media': this.fieldVariant(field) === 'media',
            };
        },
        fieldSpan(field) {
            return String(field?.layout?.span || 'full').toLowerCase();
        },
        fieldVariant(field) {
            return String(field?.layout?.variant || 'stacked').toLowerCase();
        },
        fieldControl(field) {
            return field?.control || {};
        },
        blockPackRecipe(packKey) {
            return this.blockDef?.editor?.packs?.[packKey] || null;
        },
        controlVariant(field) {
            return String(this.fieldControl(field)?.variant || 'default').toLowerCase();
        },
        isColorControl(field) {
            return this.controlVariant(field) === 'color-swatch';
        },
        isSpacingControl(field) {
            return this.controlVariant(field) === 'spacing-slider';
        },
        isSegmentedSelectControl(field) {
            return this.controlVariant(field) === 'segmented-select' && Array.isArray(field?.options);
        },
        isLinkControl(field) {
            return this.controlVariant(field) === 'link-composer';
        },
        isHighlightedField(key) {
            return Array.isArray(this.highlightFields) && this.highlightFields.includes(key);
        },
        normalizeColorValue(value) {
            const normalized = String(value || '').trim();
            return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(normalized) ? normalized : '#0f172a';
        },
        updateColorValue(key, value) {
            this.localSettings[key] = value;
        },
        numericControlValue(key, field) {
            const currentValue = this.localSettings[key];
            const fallback = Number(this.fieldControl(field)?.min ?? 0);

            if (typeof currentValue === 'number') {
                return Number.isFinite(currentValue) ? currentValue : fallback;
            }

            const parsed = parseFloat(String(currentValue ?? '').replace(',', '.'));
            return Number.isFinite(parsed) ? parsed : fallback;
        },
        updateSpacingValue(key, field, value) {
            const numeric = Number.parseFloat(String(value).replace(',', '.'));

            if (!Number.isFinite(numeric)) {
                this.localSettings[key] = '';
                return;
            }

            const unit = String(this.fieldControl(field)?.unit || '').trim();
            const precision = String(this.fieldControl(field)?.step || '').includes('.') ? 1 : 0;
            const normalized = precision > 0 ? numeric.toFixed(precision) : Math.round(numeric).toString();

            this.localSettings[key] = unit ? `${normalized}${unit}` : numeric;
        },
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
        isGalleryImagesField(key) {
            return this.type === 'gallery' && key === 'images';
        },
        galleryItemPreview(item) {
            if (!item || typeof item !== 'object') {
                return '';
            }

            if (item.url) {
                return item.url;
            }

            const mediaId = Number(item.media_id);
            if (Number.isFinite(mediaId) && mediaId > 0) {
                return this.mediaLookup?.[mediaId]?.url || `/api/media/${mediaId}`;
            }

            return '';
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

            if (last === 'media_id' && normalizedPath.length === 3 && normalizedPath[0] === 'images') {
                const item = this.getValueAtPath(normalizedPath.slice(0, -1));
                if (item && typeof item === 'object') {
                    item.media_id = null;
                    item.url = '';
                }
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
        scrollHighlightedFieldIntoView() {
            if (!Array.isArray(this.highlightFields) || this.highlightFields.length === 0) {
                return;
            }

            this.$nextTick(() => {
                const firstVisibleHighlighted = this.highlightFields.find((key) =>
                    this.filteredFields.some(([fieldKey]) => fieldKey === key)
                );

                if (!firstVisibleHighlighted) {
                    return;
                }

                const element = this.$el?.querySelector?.(`[data-field-key="${firstVisibleHighlighted}"]`);
                element?.scrollIntoView?.({ block: 'nearest', behavior: 'smooth' });
            });
        },
    },
};
</script>
