<!-- resources/views/admin/builder/advanced.blade.php -->
@extends('admin.layouts.app')

@section('title', 'Advanced Builder - ' . $page->title)
@section('page_title', 'Advanced Page Builder')
@section('page_subtitle', $page->title)

@section('content')
<div id="advanced-builder" class="vc-builder-shell">
    <!-- Left Sidebar: Block Library & Templates -->
    <aside class="vc-builder-sidebar vc-builder-scroll flex w-80 flex-col overflow-hidden border-r">
        <!-- Block Categories Tabs -->
        <div class="border-b border-[var(--vc-border)]">
            <div class="flex overflow-x-auto">
                <button 
                    v-for="cat in categories" 
                    :key="cat"
                    @click="activeCategory = cat"
                    :class="['vc-builder-chip m-2 whitespace-nowrap',
                             activeCategory === cat ? 'vc-builder-chip-active' : '']"
                >
                    {{ cat }}
                </button>
            </div>
        </div>

        <!-- Block Search -->
        <div class="border-b border-[var(--vc-border)] p-3">
            <input 
                v-model="searchQuery"
                type="text"
                placeholder="Search blocks..."
                class="vc-input"
            >
        </div>

        <!-- Block List -->
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            <div 
                v-for="(block, type) in filteredBlocks" 
                :key="type"
                @click="addBlock(type)"
                class="vc-builder-card block-item cursor-pointer p-3 group"
            >
                <div class="font-medium text-sm text-[var(--vc-text)]">{{ block.name }}</div>
                <div class="mt-1 text-xs text-[var(--vc-text-muted)]">{{ block.description || 'Drag to add' }}</div>
            </div>
            
            <div v-if="Object.keys(filteredBlocks).length === 0" class="py-8 text-center text-[var(--vc-text-soft)]">
                No blocks found
            </div>
        </div>

        <!-- Templates Panel Toggle -->
        <div class="border-t border-[var(--vc-border)] p-3">
            <button 
                @click="showTemplates = !showTemplates"
                class="flex w-full items-center justify-between text-sm font-semibold text-[var(--vc-text)]"
            >
                <span>Templates</span>
                <svg class="w-4 h-4" :class="{ 'rotate-180': showTemplates }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <div v-if="showTemplates" class="mt-3 space-y-2 max-h-40 overflow-y-auto">
                <div 
                    v-for="tpl in templates"
                    :key="tpl.id"
                    @click="applyTemplate(tpl)"
                    class="vc-builder-card cursor-pointer p-2 text-xs"
                >
                    {{ tpl.name }}
                </div>
            </div>
        </div>
    </aside>

    <!-- Center Canvas -->
    <main class="flex flex-1 flex-col overflow-hidden">
        <!-- Top Toolbar -->
        <div class="vc-builder-toolbar flex min-h-14 items-center justify-between px-4">
            <div class="flex items-center gap-4">
                <!-- Responsive Preview Toggle -->
                <div class="flex items-center gap-1 border-r border-[var(--vc-border)] pr-4">
                    <button 
                        v-for="bp in breakpoints" 
                        :key="bp.name"
                        @click="activeBreakpoint = bp.name"
                        class="vc-builder-chip"
                        :class="activeBreakpoint === bp.name ? 'vc-builder-chip-active' : ''"
                    >
                        {{ bp.label }}
                    </button>
                </div>

                <!-- Undo/Redo -->
                <div class="flex items-center gap-1">
                    <button 
                        @click="undo"
                        :disabled="!canUndo"
                        class="vc-button vc-button-secondary px-3 py-2 disabled:opacity-50"
                    >
                        Undo
                    </button>
                    <button 
                        @click="redo"
                        :disabled="!canRedo"
                        class="vc-button vc-button-secondary px-3 py-2 disabled:opacity-50"
                    >
                        Redo
                    </button>
                </div>

                <!-- Search -->
                <div class="relative">
                    <input 
                        v-model="contentSearchQuery"
                        type="text"
                        placeholder="Search content..."
                        class="vc-input w-40 pl-8 pr-3 py-2 text-xs"
                    >
                    <svg class="absolute left-2 top-3 h-3 w-3 text-[var(--vc-text-soft)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Auto-save status -->
                <div class="flex items-center gap-1 text-xs text-[var(--vc-text-muted)]">
                    <span v-if="autoSaveStatus === 'saved'" class="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span v-if="autoSaveStatus === 'saving'" class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                    <span>{{ autoSaveStatusText }}</span>
                </div>

                <button 
                    @click="showRevisions = true"
                    class="vc-button vc-button-secondary px-3 py-2"
                >
                    Revisions
                </button>

                <button 
                    @click="exportCurrentSections"
                    class="vc-button vc-button-secondary px-3 py-2"
                >
                    Export
                </button>

                <button 
                    @click="importSectionsPrompt"
                    class="vc-button vc-button-secondary px-3 py-2"
                >
                    Import
                </button>

                <!-- Preview Button -->
                <button 
                    @click="previewContent"
                    class="vc-button vc-button-secondary px-3 py-2"
                >
                    Preview
                </button>

                <!-- Save Button -->
                <button 
                    @click="saveContent"
                    :disabled="saving"
                    class="vc-button vc-button-primary px-3 py-2 disabled:opacity-50"
                >
                    {{ saving ? 'Saving...' : 'Save' }}
                </button>
            </div>
        </div>

        <!-- Canvas Area -->
        <div class="vc-builder-canvas flex-1 overflow-y-auto p-6" :class="canvasClass">
            <div class="vc-panel vc-panel-strong mx-auto min-h-full">
                <!-- Empty State -->
                <div 
                    v-if="sections.length === 0"
                    class="vc-builder-empty flex h-96 flex-col items-center justify-center"
                >
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-lg text-[var(--vc-text)]">No content yet</p>
                    <p class="mt-1 text-sm">Drag blocks from the left or click to add</p>
                </div>

                <!-- Sections -->
                <div v-else class="space-y-4 p-4">
                    <div 
                        v-for="(section, sIndex) in sections" 
                        :key="section.id"
                        class="relative group"
                        :class="{ 'ring-2 ring-blue-500': selectedSection === sIndex }"
                        @click="selectSection(sIndex)"
                    >
                        <!-- Section Controls -->
                        <div class="vc-builder-floating-controls absolute -top-2 -right-2 z-10 hidden gap-1 group-hover:flex">
                            <button @click.stop="addBlockToSection(sIndex)" class="p-1" title="Add block">
                                +
                            </button>
                            <button @click.stop="moveSectionUp(sIndex)" class="p-1" title="Move up">
                                ↑
                            </button>
                            <button @click.stop="moveSectionDown(sIndex)" class="p-1" title="Move down">
                                ↓
                            </button>
                            <button @click.stop="duplicateSection(sIndex)" class="p-1" title="Duplicate">
                                📋
                            </button>
                            <button @click.stop="deleteSection(sIndex)" class="p-1 text-rose-300" title="Delete">
                                ✕
                            </button>
                        </div>

                        <!-- Section Background -->
                        <div class="p-4 rounded-lg border-2 border-transparent group-hover:border-slate-200">
                            <div v-if="section.settings?.background_color" 
                                 class="rounded" 
                                 :style="{ backgroundColor: section.settings.background_color }">
                                <div class="p-4">
                                    <div class="space-y-4">
                                        <div 
                                            v-for="(block, bIndex) in section.blocks" 
                                            :key="block.id"
                                            class="relative"
                                            :class="{ 'ring-2 ring-blue-400': selectedBlock === bIndex && selectedSection === sIndex }"
                                            @click.stop="selectBlock(sIndex, bIndex)"
                                        >
                                            <BlockRenderer 
                                                :type="block.type" 
                                                :settings="block.settings"
                                                :editable="false"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <div class="space-y-4">
                                    <div 
                                        v-for="(block, bIndex) in section.blocks" 
                                        :key="block.id"
                                        class="relative"
                                        :class="{ 'ring-2 ring-blue-400': selectedBlock === bIndex && selectedSection === sIndex }"
                                        @click.stop="selectBlock(sIndex, bIndex)"
                                    >
                                        <BlockRenderer 
                                            :type="block.type" 
                                            :settings="block.settings"
                                            :editable="false"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Right Sidebar: Block Settings -->
    <aside class="vc-builder-sidebar vc-builder-scroll w-96 overflow-y-auto border-l">
        <div v-if="selectedBlockData" class="p-6">
            <h3 class="mb-4 text-lg font-semibold text-[var(--vc-text)]">Block Settings</h3>
            <BlockSettings 
                :type="selectedBlockData.type"
                :settings="selectedBlockData.settings"
                @update="updateBlockSettings"
            />
        </div>
        
        <div v-else-if="selectedSection !== null" class="p-6">
            <h3 class="mb-4 text-lg font-semibold text-[var(--vc-text)]">Section Settings</h3>
            <SectionSettings 
                :settings="sections[selectedSection].settings"
                @update="updateSectionSettings"
            />
        </div>

        <div v-else class="p-6 text-center text-[var(--vc-text-muted)]">
            <p class="text-sm">Select a block or section to edit</p>
        </div>
    </aside>

    <!-- Revision History Panel -->
    <div v-if="showRevisions" class="vc-builder-modal fixed inset-0 z-50 flex justify-end">
        <div class="vc-builder-modal-card flex w-96 flex-col overflow-hidden rounded-none border-l">
        <div class="flex items-center justify-between border-b border-[var(--vc-border)] p-4">
            <h3 class="font-semibold text-[var(--vc-text)]">Revision History</h3>
            <button @click="showRevisions = false" class="text-[var(--vc-text-soft)] hover:text-[var(--vc-text)]">✕</button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <div 
                v-for="rev in revisions" 
                :key="rev.id"
                @click="restoreRevision(rev)"
                class="vc-builder-card cursor-pointer p-3"
            >
                <div class="flex justify-between items-start">
                    <span class="text-sm font-medium text-[var(--vc-text)]">{{ rev.title }}</span>
                    <span class="text-xs text-[var(--vc-text-soft)]">{{ formatDate(rev.created_at) }}</span>
                </div>
                <p class="mt-1 text-xs text-[var(--vc-text-muted)]">{{ rev.action }}</p>
                <div class="mt-2 text-xs text-[var(--vc-text-soft)]">
                    {{ countBlocks(rev.content_json) }} blocks
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div v-if="showPreview" class="vc-builder-modal fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="vc-builder-modal-card flex h-full w-full max-w-6xl flex-col overflow-hidden">
            <div class="flex items-center justify-between border-b border-[var(--vc-border)] p-4">
                <h3 class="font-semibold text-[var(--vc-text)]">Preview: {{ page.title }}</h3>
                <div class="flex items-center gap-2">
                    <select v-model="previewBreakpoint" class="vc-select w-auto px-3 py-2 text-sm">
                        <option value="100%">Full Width</option>
                        <option value="1200px">Desktop (1200px)</option>
                        <option value="768px">Tablet (768px)</option>
                        <option value="480px">Mobile (480px)</option>
                    </select>
                    <button @click="showPreview = false" class="text-[var(--vc-text-soft)] hover:text-[var(--vc-text)]">✕</button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-8" :style="{ maxWidth: previewBreakpoint, margin: '0 auto' }">
                <iframe 
                    v-if="previewHtml"
                    :srcdoc="previewHtml"
                    class="w-full min-h-[500px] border-0"
                    style="background: white;"
                ></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    import { createApp, ref, reactive, computed, onMounted, watch } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js'
    
    createApp({
        components: {
            BlockRenderer: {
                props: ['type', 'settings', 'editable'],
                template: `
                    <div class="block-renderer">
                        <!-- Dynamic block rendering will be injected here -->
                        <div v-html="renderedHtml"></div>
                    </div>
                `,
                computed: {
                    renderedHtml() {
                        return this.renderBlock(this.type, this.settings);
                    }
                },
                methods: {
                    renderBlock(type, settings) {
                        const block = window.availableBlocks?.[type];
                        if (!block) return '<div>Unknown block</div>';
                        
                        return block.render ? block.render(settings) : this.defaultRender(type, settings);
                    },
                    defaultRender(type, settings) {
                        switch(type) {
                            case 'heading':
                                return \`<h2 style="color: \${settings.color || '#111'}; text-align: \${settings.align || 'left'}">\${settings.text || 'Heading'}</h2>\`;
                            case 'text':
                                return \`<div style="color: \${settings.color || '#333'}; text-align: \${settings.align || 'left'}">\${settings.content || ''}</div>\`;
                            case 'button':
                                return \`<a href="\${settings.url || '#'}" class="btn" style="background: \${settings.style === 'primary' ? '#3b82f6' : '#6b7280'}; color: white; padding: 0.5rem 1rem; border-radius: 0.25rem; text-decoration: none; display: inline-block;">\${settings.text || 'Button'}</a>\`;
                            case 'divider':
                                return '<hr class="my-4">';
                            case 'faq':
                                return \`<div class="faq">\${(settings.items || []).map(item => \`<details><summary>\${item.question || 'Question'}</summary><div>\${item.answer || 'Answer'}</div></details>\`).join('')}</div>\`;
                            case 'image':
                                return \`<img src="\${settings.url || ''}" alt="\${settings.alt || ''}" style="max-width: 100%; height: auto;">\`;
                            case 'html':
                                return settings.html || '';
                            default:
                                return \`<div>[\${type}]</div>\`;
                        }
                    }
                }
            },
            BlockSettings: {
                props: ['type', 'settings'],
                emits: ['update'],
                data() {
                    return {
                        localSettings: { ...this.settings }
                    };
                },
                watch: {
                    settings: {
                        deep: true,
                        immediate: true,
                        handler(newSettings) {
                            this.localSettings = { ...(newSettings || {}) };
                        }
                    },
                    localSettings: {
                        deep: true,
                        handler() {
                            this.$emit('update', this.localSettings);
                        }
                    }
                },
                template: `
                    <div class="space-y-4">
                        <div v-if="blockDef?.fields" class="space-y-4">
                            <div v-for="(field, key) in blockDef.fields" :key="key" class="space-y-2">
                                <label class="block text-sm font-medium text-slate-700">{{ field.label }}</label>
                                <input 
                                    v-if="field.type === 'text' || field.type === 'color' || field.type === 'number'"
                                    :type="field.type"
                                    v-model="localSettings[key]"
                                    :placeholder="field.placeholder"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                                >
                                <textarea 
                                    v-else-if="field.type === 'textarea'"
                                    v-model="localSettings[key]"
                                    :rows="field.rows || 3"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                                ></textarea>
                                <select 
                                    v-else-if="field.type === 'select'"
                                    v-model="localSettings[key]"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                                >
                                    <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <div 
                                    v-else-if="field.type === 'toggle'"
                                    @click="localSettings[key] = !localSettings[key]"
                                    class="w-12 h-6 rounded-full cursor-pointer transition-colors"
                                    :class="localSettings[key] ? 'bg-blue-500' : 'bg-slate-300'"
                                >
                                    <div class="w-5 h-5 bg-white rounded-full transform transition-transform" :class="localSettings[key] ? 'translate-x-6' : 'translate-x-0.5'"></div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-slate-500">No settings available for this block type</div>
                    </div>
                `,
                computed: {
                    blockDef() {
                        return window.availableBlocks?.[this.type];
                    }
                }
            },
            SectionSettings: {
                props: ['settings'],
                emits: ['update'],
                data() {
                    return {
                        localSettings: { ...this.settings }
                    };
                },
                watch: {
                    settings: {
                        deep: true,
                        immediate: true,
                        handler(newSettings) {
                            this.localSettings = { ...(newSettings || {}) };
                        }
                    },
                    localSettings: {
                        deep: true,
                        handler() {
                            this.$emit('update', this.localSettings);
                        }
                    }
                },
                template: `
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Background Color</label>
                            <input 
                                type="color"
                                v-model="localSettings.background_color"
                                class="w-12 h-8 rounded cursor-pointer"
                            >
                            <button 
                                v-if="localSettings.background_color"
                                @click="localSettings.background_color = null"
                                class="ml-2 text-xs text-red-500"
                            >
                                Clear
                            </button>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Padding Top</label>
                            <input 
                                type="range" 
                                min="0" max="100" 
                                v-model.number="localSettings.padding_top"
                                class="w-full"
                            >
                            <span class="text-xs text-slate-500">{{ localSettings.padding_top || 16 }}px</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Padding Bottom</label>
                            <input 
                                type="range" 
                                min="0" max="100" 
                                v-model.number="localSettings.padding_bottom"
                                class="w-full"
                            >
                            <span class="text-xs text-slate-500">{{ localSettings.padding_bottom || 16 }}px</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">CSS Class</label>
                            <input 
                                type="text"
                                v-model="localSettings.css_class"
                                placeholder="e.g., my-custom-class"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                            >
                        </div>
                    </div>
                `
            }
        },
        setup() {
            const page = @json($page);
            const config = @json($config);
            const initialSections = @json($page->content_json['sections'] ?? []);

            // State
            const sections = ref(initialSections);
            const activeCategory = ref(config.categories?.[0] || '');
            const activeBreakpoint = ref('desktop');
            const searchQuery = ref('');
            const contentSearchQuery = ref('');
            const selectedSection = ref(null);
            const selectedBlock = ref(null);
            const selectedBlockData = ref(null);
            const saving = ref(false);
            const showPreview = ref(false);
            const showRevisions = ref(false);
            const showTemplates = ref(false);
            const previewHtml = ref('');
            const previewBreakpoint = ref('100%');
            const autoSaveTimer = ref(null);
            const autoSaveStatus = ref('saved');
            const revisions = ref([]);

            // History for undo/redo
            const history = ref([]);
            const historyIndex = ref(-1);

            // Templates
            const templates = ref([]);

            // Breakpoints
            const breakpoints = ref(config.breakpoints || [
                { name: 'desktop', label: 'Desktop', width: '100%', maxWidth: '1200px' },
                { name: 'tablet', label: 'Tablet', width: '768px' },
                { name: 'mobile', label: 'Mobile', width: '480px' }
            ]);

            // Available blocks (from window.availableBlocks set in Blade)
            const allBlocks = ref(window.availableBlocks || {});
            const categories = ref(config.categories || []);

            // Computed
            const filteredBlocks = computed(() => {
                const entries = Object.entries(allBlocks.value);
                const byCategory = activeCategory.value
                    ? entries.filter(([_, block]) => block.category === activeCategory.value)
                    : entries;

                if (!searchQuery.value) {
                    return Object.fromEntries(byCategory);
                }
                const query = searchQuery.value.toLowerCase();
                return Object.fromEntries(
                    byCategory.filter(([_, block]) =>
                        block.name.toLowerCase().includes(query) ||
                        (block.description || '').toLowerCase().includes(query)
                    )
                );
            });

            const canvasClass = computed(() => {
                const bp = breakpoints.value.find(b => b.name === activeBreakpoint.value);
                if (!bp) return '';
                if (activeBreakpoint.value === 'desktop') return '';
                return `simulate-${activeBreakpoint.value}`;
            });

            const canUndo = computed(() => historyIndex.value > 0);
            const canRedo = computed(() => historyIndex.value < history.value.length - 1);

            const autoSaveStatusText = computed(() => {
                switch(autoSaveStatus.value) {
                    case 'saved': return 'All changes saved';
                    case 'saving': return 'Saving...';
                    default: return '';
                }
            });

            // Methods
            const addBlock = (type) => {
                const block = allBlocks.value[type];
                if (!block) return;

                const newBlock = {
                    id: generateId(),
                    type,
                    settings: JSON.parse(JSON.stringify(block.default?.settings || block.default || {}))
                };

                if (selectedSection.value !== null) {
                    sections.value[selectedSection.value].blocks.push(newBlock);
                } else {
                    sections.value.push({
                        id: generateId(),
                        settings: {},
                        blocks: [newBlock]
                    });
                }

                saveToHistory();
            };

            const addBlockToSection = (sIndex) => {
                selectedSection.value = sIndex;
                // Could open a modal or dropdown here
            };

            const deleteSection = (sIndex) => {
                sections.value.splice(sIndex, 1);
                selectedSection.value = null;
                selectedBlock.value = null;
                saveToHistory();
            };

            const duplicateSection = (sIndex) => {
                const section = JSON.parse(JSON.stringify(sections.value[sIndex]));
                section.id = generateId();
                section.blocks = section.blocks.map(b => ({ ...b, id: generateId() }));
                sections.value.splice(sIndex + 1, 0, section);
                saveToHistory();
            };

            const moveSectionUp = (sIndex) => {
                if (sIndex > 0) {
                    const temp = sections.value[sIndex];
                    sections.value[sIndex] = sections.value[sIndex - 1];
                    sections.value[sIndex - 1] = temp;
                    saveToHistory();
                }
            };

            const moveSectionDown = (sIndex) => {
                if (sIndex < sections.value.length - 1) {
                    const temp = sections.value[sIndex];
                    sections.value[sIndex] = sections.value[sIndex + 1];
                    sections.value[sIndex + 1] = temp;
                    saveToHistory();
                }
            };

            const selectSection = (sIndex) => {
                selectedSection.value = sIndex;
                selectedBlock.value = null;
                selectedBlockData.value = null;
            };

            const selectBlock = (sIndex, bIndex) => {
                selectedSection.value = sIndex;
                selectedBlock.value = bIndex;
                selectedBlockData.value = {
                    type: sections.value[sIndex].blocks[bIndex].type,
                    settings: sections.value[sIndex].blocks[bIndex].settings
                };
            };

            const updateBlockSettings = (newSettings) => {
                if (selectedSection.value !== null && selectedBlock.value !== null) {
                    sections.value[selectedSection.value].blocks[selectedBlock.value].settings = newSettings;
                    saveToHistory();
                }
            };

            const updateSectionSettings = (newSettings) => {
                if (selectedSection.value !== null) {
                    sections.value[selectedSection.value].settings = newSettings;
                    saveToHistory();
                }
            };

            const saveToHistory = () => {
                // Remove any future states if we're not at the end
                history.value = history.value.slice(0, historyIndex.value + 1);
                // Add current state
                history.value.push(JSON.parse(JSON.stringify(sections.value)));
                // Limit history size
                if (history.value.length > 100) {
                    history.value.shift();
                } else {
                    historyIndex.value++;
                }
            };

            const undo = () => {
                if (canUndo.value) {
                    historyIndex.value--;
                    sections.value = JSON.parse(JSON.stringify(history.value[historyIndex.value]));
                }
            };

            const redo = () => {
                if (canRedo.value) {
                    historyIndex.value++;
                    sections.value = JSON.parse(JSON.stringify(history.value[historyIndex.value]));
                }
            };

            const saveContent = async () => {
                saving.value = true;
                try {
                    const response = await fetch(`/admin/pages/${page.id}/builder/advanced/save`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({
                            title: page.title,
                            content: sections.value,
                            create_revision: true
                        })
                    });
                    const data = await response.json();
                    if (data.ok) {
                        autoSaveStatus.value = 'saved';
                    } else {
                        alert('Error: ' + (data.error || 'Save failed'));
                    }
                } catch (e) {
                    console.error('Save error:', e);
                    alert('Network error');
                } finally {
                    saving.value = false;
                }
            };

            const exportCurrentSections = async () => {
                try {
                    const response = await fetch('/admin/pages/export-sections', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({ sections: sections.value })
                    });
                    const data = await response.json();
                    if (!data.ok) {
                        alert(data.error || 'Export failed');
                        return;
                    }
                    await navigator.clipboard.writeText(data.export);
                    alert(`Copied ${data.filename} to clipboard`);
                } catch (e) {
                    alert('Export error');
                }
            };

            const importSectionsPrompt = async () => {
                const importData = window.prompt('Paste exported sections JSON');
                if (!importData) return;

                try {
                    const response = await fetch('/admin/pages/import-sections', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({ import_data: importData, page_id: page.id })
                    });
                    const data = await response.json();
                    if (!data.ok) {
                        alert(data.error || 'Import failed');
                        return;
                    }
                    sections.value = data.sections || [];
                    saveToHistory();
                } catch (e) {
                    alert('Import error');
                }
            };

            const autoSave = async () => {
                autoSaveStatus.value = 'saving';
                try {
                    await fetch(`/admin/pages/${page.id}/builder/auto-save`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({ content: sections.value })
                    });
                    autoSaveStatus.value = 'saved';
                } catch (e) {
                    autoSaveStatus.value = 'error';
                }
            };

            const previewContent = async () => {
                try {
                    const response = await fetch(`/admin/pages/${page.id}/builder/preview`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({ content: sections.value })
                    });
                    const data = await response.json();
                    previewHtml.value = data.html;
                    showPreview.value = true;
                } catch (e) {
                    alert('Preview error');
                }
            };

            const restoreRevision = async (rev) => {
                if (!confirm('Restore this revision?')) return;
                
                try {
                    const response = await fetch(`/admin/pages/${page.id}/revisions/${rev.id}/restore`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
                    });
                    const data = await response.json();
                    if (data.ok) {
                        sections.value = data.page.content_json.sections;
                        showRevisions.value = false;
                        saveToHistory();
                    }
                } catch (e) {
                    alert('Restore error');
                }
            };

            const applyTemplate = (tpl) => {
                if (!confirm(`Apply template "${tpl.name}"?`)) return;
                
                fetch(`/admin/pages/${page.id}/builder/template`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({ template_id: tpl.id, merge: true })
                })
                .then(r => r.json())
                .then(data => {
                    sections.value = data.page.content_json.sections;
                    saveToHistory();
                });
            };

            const loadRevisions = async () => {
                try {
                    const response = await fetch(`/admin/pages/${page.id}/revisions`);
                    const data = await response.json();
                    revisions.value = data.data || [];
                } catch (e) {
                    console.error('Load revisions error:', e);
                }
            };

            const generateId = () => 'blk_' + Math.random().toString(36).substr(2, 9);
            const formatDate = (d) => new Date(d).toLocaleString();
            const countBlocks = (content) => {
                return (content?.sections ||[]).reduce((sum, s) => sum + (s.blocks?.length || 0), 0);
            };

            // Watchers
            watch(sections, () => {
                if (autoSaveTimer.value) clearTimeout(autoSaveTimer.value);
                autoSaveTimer.value = setTimeout(() => {
                    autoSave();
                }, 120000); // 2 minutes
            }, { deep: true });

            watch(contentSearchQuery, (query) => {
                if (!query) {
                    selectedSection.value = null;
                    selectedBlock.value = null;
                    return;
                }
                // Search through sections and highlight matches
                for (let s = 0; s < sections.value.length; s++) {
                    const section = sections.value[s];
                    for (let b = 0; b < section.blocks.length; b++) {
                        const block = section.blocks[b];
                        const str = JSON.stringify(block).toLowerCase();
                        if (str.includes(query.toLowerCase())) {
                            selectedSection.value = s;
                            selectedBlock.value = b;
                            return;
                        }
                    }
                }
            });

            // Lifecycle
            onMounted(() => {
                saveToHistory();
                loadRevisions();
                
                // Load available blocks from server
                fetch('/admin/api/builder/blocks')
                    .then(r => r.json())
                    .then(data => {
                        allBlocks.value = data.blocks || {};
                        window.availableBlocks = allBlocks.value;
                    });
                
                // Load templates
                fetch('/admin/pages/templates')
                    .then(r => r.json())
                    .then(data => {
                        templates.value = data.templates || [];
                    });
            });

            return {
                page, config, sections, activeCategory, activeBreakpoint,
                searchQuery, contentSearchQuery, selectedSection, selectedBlock,
                selectedBlockData, saving, showPreview, showRevisions, showTemplates, templates,
                breakpoints, revisions, canUndo, canRedo, canvasClass, categories,
                autoSaveStatus, autoSaveStatusText, previewHtml, previewBreakpoint,
                allBlocks, filteredBlocks,
                addBlock, addBlockToSection, deleteSection, duplicateSection, moveSectionUp, moveSectionDown,
                selectSection, selectBlock, updateBlockSettings, updateSectionSettings,
                saveContent, previewContent, undo, redo, restoreRevision, applyTemplate,
                exportCurrentSections, importSectionsPrompt, generateId, formatDate, countBlocks
            };
        }
    }).mount('#advanced-builder');
</script>
@endpush
