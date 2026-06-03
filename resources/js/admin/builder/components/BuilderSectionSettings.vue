<template>
    <div class="space-y-4">
        <div v-if="panel === 'content'" class="vc-builder-form-section">
            <div class="vc-builder-field-hint">
                Section content is arranged directly on the canvas. Use this panel to adjust the section surface, spacing and technical hooks.
            </div>

            <div v-if="presets.length" class="mt-4 space-y-3">
                <div class="vc-builder-form-title">Section presets</div>
                <div class="grid gap-3">
                    <button
                        v-for="preset in presets"
                        :key="preset.id"
                        type="button"
                        class="vc-builder-template-card text-left"
                        @click="applyPreset(preset)"
                    >
                        <span class="block text-sm font-semibold text-[var(--vc-text)]">{{ preset.label }}</span>
                        <span class="mt-1 block text-xs text-[var(--vc-text-soft)]">{{ preset.description }}</span>
                    </button>
                </div>
            </div>
        </div>

        <template v-if="panel === 'all' || panel === 'style'">
            <div class="vc-builder-form-section">
                <div class="vc-builder-form-title">Surface</div>
                <div v-if="surfaceTokens.length" class="mb-4">
                    <div class="vc-builder-field">
                        <label>Surface tokens</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="token in surfaceTokens"
                                :key="token.id"
                                type="button"
                                class="vc-builder-chip"
                                :class="{ 'vc-builder-chip-active': localSettings.background_color === token.color || (!localSettings.background_color && token.color === 'transparent') }"
                                @click="applySurfaceToken(token)"
                            >
                                {{ token.label }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="vc-builder-field">
                    <label>Background color</label>
                    <div class="vc-builder-inline-actions">
                        <div class="vc-builder-swatch-row">
                            <div class="vc-builder-swatch">
                                <input v-model="localSettings.background_color" type="color">
                            </div>
                            <span class="vc-builder-field-hint">{{ localSettings.background_color || 'transparent' }}</span>
                        </div>

                        <button
                            v-if="localSettings.background_color"
                            type="button"
                            class="text-xs font-semibold text-[var(--vc-danger)]"
                            @click="localSettings.background_color = null"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <div class="vc-builder-form-section">
                <div class="vc-builder-form-title">Spacing</div>
                <div class="vc-builder-field">
                    <label>Top padding</label>
                    <input
                        v-model.number="localSettings.padding_top"
                        type="range"
                        min="0"
                        max="160"
                        class="vc-builder-range w-full"
                    >
                    <span class="vc-builder-range-value">{{ localSettings.padding_top || 16 }}px</span>
                </div>
                <div class="vc-builder-field">
                    <label>Bottom padding</label>
                    <input
                        v-model.number="localSettings.padding_bottom"
                        type="range"
                        min="0"
                        max="160"
                        class="vc-builder-range w-full"
                    >
                    <span class="vc-builder-range-value">{{ localSettings.padding_bottom || 16 }}px</span>
                </div>
            </div>
        </template>

        <template v-if="panel === 'all' || panel === 'advanced'">
            <div class="vc-builder-form-section">
                <div class="vc-builder-form-title">Advanced</div>
                <div class="vc-builder-field">
                    <label>CSS class</label>
                    <input
                        v-model="localSettings.css_class"
                        type="text"
                        placeholder="for example hero-section"
                        class="vc-input"
                    >
                </div>
            </div>
        </template>
    </div>
</template>

<script>
export default {
    name: 'BuilderSectionSettings',
    props: {
        settings: {
            type: Object,
            default: () => ({}),
        },
        config: {
            type: Object,
            default: () => ({}),
        },
        panel: {
            type: String,
            default: 'all',
        },
    },
    emits: ['update'],
    data() {
        return {
            localSettings: { ...this.settings },
            syncingFromProps: false,
            emitTimer: null,
        };
    },
    computed: {
        presets() {
            return Array.isArray(this.config?.presets) ? this.config.presets : [];
        },
        surfaceTokens() {
            return Array.isArray(this.config?.surface_tokens) ? this.config.surface_tokens : [];
        },
        defaultSettings() {
            return {
                padding_top: 16,
                padding_bottom: 16,
                background_color: null,
                ...(this.config?.default_settings || {}),
            };
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
        applyPreset(preset) {
            this.localSettings = {
                ...this.defaultSettings,
                ...this.localSettings,
                ...(preset?.settings || {}),
            };
        },
        applySurfaceToken(token) {
            this.localSettings = {
                ...this.localSettings,
                background_color: token?.color === 'transparent' ? null : token?.color,
            };
        },
    },
};
</script>
