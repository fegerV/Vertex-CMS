<template>
    <div class="vc-builder-shell vc-builder-shell-modern">
        <aside class="vc-builder-sidebar vc-builder-shell-pane vc-builder-shell-pane-left vc-builder-scroll">
            <div class="vc-builder-sidebar-stack">
                <section class="vc-builder-surface-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="vc-builder-eyebrow">Library</p>
                            <h2 class="vc-builder-sidebar-title">Page blocks</h2>
                            <p class="vc-builder-sidebar-copy">Build the page section by section with a UX Builder-inspired canvas, while keeping the JSON contract intact.</p>
                        </div>
                        <span class="vc-builder-stat-pill">{{ totalBlockTypes }} types</span>
                    </div>
                </section>

                <section class="vc-builder-surface-card">
                    <div class="vc-builder-tab-row">
                        <button
                            v-for="tab in libraryTabs"
                            :key="tab.id"
                            type="button"
                            class="vc-builder-tab"
                            :class="{ 'vc-builder-tab-active': libraryTab === tab.id }"
                            @click="libraryTab = tab.id"
                        >
                            {{ tab.label }}
                        </button>
                    </div>

                    <div class="mt-4">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search blocks"
                            class="vc-input"
                        >
                    </div>

                    <div class="mt-4 space-y-3">
                        <button
                            v-for="[type, block] in filteredBlockEntries"
                            :key="type"
                            type="button"
                            class="vc-builder-library-card"
                            @click="addBlock(type)"
                        >
                            <span class="vc-builder-library-card-mark">{{ blockMark(type) }}</span>
                            <span class="min-w-0 flex-1 text-left">
                                <span class="block text-sm font-semibold text-[var(--vc-text)]">{{ block.name }}</span>
                                <span class="mt-1 block text-xs text-[var(--vc-text-soft)]">{{ block.description || 'Insert into the selected section or start a new page structure.' }}</span>
                            </span>
                            <span class="vc-builder-library-card-add">+</span>
                        </button>

                        <div v-if="filteredBlockEntries.length === 0" class="vc-builder-empty p-5 text-center text-sm">
                            No blocks match this filter yet.
                        </div>
                    </div>
                </section>

                <section class="vc-builder-surface-card">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="vc-builder-eyebrow">Templates</p>
                            <h3 class="text-sm font-semibold text-[var(--vc-text)]">Sections and page starters</h3>
                        </div>
                        <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': showTemplates }" @click="showTemplates = !showTemplates">
                            {{ showTemplates ? 'Hide' : 'Show' }}
                        </button>
                    </div>

                    <div v-if="showTemplates" class="mt-4 space-y-4">
                        <div class="vc-builder-tab-row">
                            <button type="button" class="vc-builder-tab" :class="{ 'vc-builder-tab-active': templateLibraryScope === 'all' }" @click="templateLibraryScope = 'all'">All</button>
                            <button type="button" class="vc-builder-tab" :class="{ 'vc-builder-tab-active': templateLibraryScope === 'mine' }" @click="templateLibraryScope = 'mine'">Mine</button>
                            <button type="button" class="vc-builder-tab" :class="{ 'vc-builder-tab-active': templateLibraryScope === 'shared' }" @click="templateLibraryScope = 'shared'">Shared</button>
                            <button type="button" class="vc-builder-tab" :class="{ 'vc-builder-tab-active': templateLibraryScope === 'builtin' }" @click="templateLibraryScope = 'builtin'">Built-in</button>
                        </div>

                        <div class="space-y-3">
                            <input
                                v-model="templateSearchQuery"
                                type="text"
                                placeholder="Search templates"
                                class="vc-input"
                            >
                        </div>

                        <div v-if="filteredTemplates.length" class="space-y-5">
                            <div
                                v-for="group in groupedFilteredTemplates"
                                :key="group.key"
                                class="space-y-3"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="vc-builder-template-group-title">{{ group.label }}</div>
                                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--vc-text-soft)]">{{ group.items.length }} items</div>
                                </div>

                                <div class="space-y-3">
                                    <button
                                        v-for="tpl in group.items"
                                        :key="tpl.id"
                                        type="button"
                                        class="vc-builder-template-card vc-builder-template-card-rich"
                                        @click="applyTemplate(tpl)"
                                    >
                                        <div class="vc-builder-template-thumb-shell">
                                            <img
                                                v-if="tpl.thumbnail"
                                                :src="tpl.thumbnail"
                                                :alt="tpl.name"
                                                class="vc-builder-template-thumb"
                                            >
                                            <div v-else class="vc-builder-template-thumb vc-builder-template-thumb-fallback">
                                                {{ tpl.name }}
                                            </div>
                                        </div>

                                        <span class="min-w-0 flex-1 text-left">
                                            <span class="flex flex-wrap items-center gap-2">
                                                <span class="block text-sm font-semibold text-[var(--vc-text)]">{{ tpl.name }}</span>
                                                <span class="vc-builder-badge">{{ templateScopeLabel(tpl) }}</span>
                                            </span>
                                            <span class="mt-2 block text-xs text-[var(--vc-text-soft)]">{{ tpl.description || 'Reusable page starter or section preset for the current JSON-first builder.' }}</span>
                                            <span class="mt-3 flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--vc-text-soft)]">
                                                <span>{{ tpl.sections_count || 0 }} sections</span>
                                                <span>&middot;</span>
                                                <span>{{ tpl.blocks_count || 0 }} blocks</span>
                                                <span v-if="tpl.owner">&middot;</span>
                                                <span v-if="tpl.owner">{{ tpl.owner }}</span>
                                            </span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="vc-builder-empty p-5 text-center text-sm">
                            No templates found for this filter.
                        </div>

                        <div class="space-y-2">
                            <input v-model="templateDraftName" type="text" class="vc-input" placeholder="Template name">
                            <div class="grid grid-cols-2 gap-2">
                                <input v-model="templateDraftCategory" type="text" class="vc-input" placeholder="Category">
                                <select v-model="templateVisibility" class="vc-select">
                                    <option value="shared">Shared</option>
                                    <option value="private">Private</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" class="vc-button vc-button-secondary flex-1" @click="saveSelectedSectionAsTemplate">Save section</button>
                                <button type="button" class="vc-button vc-button-secondary" @click="openLibraryManager('templates')">Manage</button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </aside>

        <main class="vc-builder-shell-pane vc-builder-shell-pane-main">
            <div class="vc-builder-toolbar vc-builder-toolbar-modern">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="vc-builder-toolbar-group">
                        <p class="vc-builder-eyebrow">Canvas</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <button
                                v-for="bp in breakpoints"
                                :key="bp.name"
                                type="button"
                                class="vc-builder-chip"
                                :class="{ 'vc-builder-chip-active': activeBreakpoint === bp.name }"
                                @click="activeBreakpoint = bp.name"
                            >
                                {{ bp.label }}
                            </button>
                        </div>
                    </div>

                    <div class="vc-builder-toolbar-group">
                        <p class="vc-builder-eyebrow">History</p>
                        <div class="mt-2 flex items-center gap-2">
                            <button type="button" class="vc-button vc-button-secondary" :disabled="!canUndo" @click="undo">Undo</button>
                            <button type="button" class="vc-button vc-button-secondary" :disabled="!canRedo" @click="redo">Redo</button>
                        </div>
                    </div>

                    <div class="vc-builder-toolbar-group min-w-[220px] flex-1">
                        <p class="vc-builder-eyebrow">Page search</p>
                        <input
                            v-model="contentSearchQuery"
                            type="text"
                            placeholder="Find text, block labels or structure"
                            class="vc-input mt-2"
                        >
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <span class="vc-builder-status-chip">
                        <span class="vc-builder-status-dot" :class="`vc-builder-status-dot-${autoSaveStatus}`"></span>
                        {{ autoSaveStatusText }}
                    </span>
                    <button type="button" class="vc-button vc-button-secondary" @click="showRevisions = true">Revisions</button>
                    <button type="button" class="vc-button vc-button-secondary" @click="exportCurrentSections">Export</button>
                    <button type="button" class="vc-button vc-button-secondary" @click="importSectionsPrompt">Import</button>
                    <button type="button" class="vc-button vc-button-secondary" @click="previewContent">UX Preview</button>
                    <button type="button" class="vc-button vc-button-primary" :disabled="saving" @click="saveContent">
                        {{ saving ? 'Saving...' : 'Save page' }}
                    </button>
                </div>
            </div>

            <div class="vc-builder-canvas vc-builder-canvas-modern">
                <div class="vc-builder-viewport">
                    <div class="vc-builder-page-frame" :style="pageFrameStyle">
                        <header class="vc-builder-page-hero">
                            <div>
                                <p class="vc-builder-eyebrow">Page preview</p>
                                <h1 class="vc-builder-page-title">{{ page.title || 'Untitled page' }}</h1>
                                <p class="vc-builder-page-meta">{{ page.uri || ('/' + (page.slug || 'page')) }}</p>
                            </div>
                            <div class="vc-builder-page-stats">
                                <span class="vc-builder-stat-pill">{{ sections.length }} sections</span>
                                <span class="vc-builder-stat-pill">{{ totalCanvasBlocks }} blocks</span>
                            </div>
                        </header>

                        <div v-if="sections.length === 0" class="vc-builder-empty-state">
                            <div class="vc-builder-empty-state-card">
                                <p class="vc-builder-eyebrow">Start</p>
                                <h2 class="text-2xl font-semibold text-[var(--vc-text)]">Build the first section</h2>
                                <p class="mt-3 text-sm text-[var(--vc-text-soft)]">This builder already saves to <code>content_json</code>, so the visual UX can evolve without breaking the page contract.</p>
                                <div class="mt-5 flex flex-wrap gap-3">
                                    <button type="button" class="vc-button vc-button-primary" @click="createStarterPage">Create a starter section</button>
                                    <button type="button" class="vc-button vc-button-secondary" @click="showTemplates = true">Browse templates</button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="vc-builder-stage vc-builder-stage-modern">
                            <section
                                v-for="(section, sIndex) in sections"
                                :key="section.id"
                                class="vc-builder-section vc-builder-section-modern group"
                                :class="{
                                    'vc-builder-section-active': selectedSection === sIndex,
                                    'vc-builder-dragging': draggedSectionIndex === sIndex,
                                    'vc-builder-drop-target': dropSectionIndex === sIndex && draggedSectionIndex !== null && draggedSectionIndex !== sIndex,
                                    'vc-builder-controls-always-visible': sectionToolbarVisibility === 'always',
                                }"
                                @click="selectSection(sIndex)"
                                @contextmenu.prevent="openSectionContextMenu(sIndex, $event)"
                                @dragover.prevent="onSectionDragOver(sIndex)"
                                @drop.prevent="onSectionDrop(sIndex)"
                            >
                                <div class="vc-builder-section-toolbar">
                                    <div class="vc-builder-section-labels">
                                        <span class="vc-builder-badge vc-builder-badge-active">Section {{ sIndex + 1 }}</span>
                                        <span class="vc-builder-badge">{{ section.blocks.length }} blocks</span>
                                        <span v-if="sectionPresetLabel(section)" class="vc-builder-badge">{{ sectionPresetLabel(section) }}</span>
                                        <span v-else-if="section.settings?.background_color" class="vc-builder-badge">Custom surface</span>
                                    </div>

                                    <div class="vc-builder-action-cluster vc-builder-floating-controls vc-builder-section-actions" :class="{ 'vc-builder-controls-always-visible': sectionToolbarVisibility === 'always' }">
                                        <button
                                            v-for="action in sectionActions"
                                            :key="action.id"
                                            type="button"
                                            class="vc-builder-action-button"
                                            :class="{
                                                'vc-builder-action-danger': action.tone === 'danger',
                                                'vc-builder-drag-handle': action.id === 'move',
                                            }"
                                            :title="action.label"
                                            :aria-label="action.label"
                                            :draggable="action.id === 'move'"
                                            @click.stop="runSectionAction(action.id, sIndex)"
                                            @dragstart="action.id === 'move' ? onSectionDragStart(sIndex, $event) : undefined"
                                            @dragend="action.id === 'move' ? onSectionDragEnd() : undefined"
                                        >
                                            <svg viewBox="0 0 20 20" fill="none">
                                                <template v-for="(path, index) in actionIconPaths(action.icon)" :key="`${action.id}-${index}`">
                                                    <path
                                                        :d="path.d"
                                                        stroke="currentColor"
                                                        :stroke-width="path.strokeWidth || 1.8"
                                                        stroke-linecap="round"
                                                        :stroke-linejoin="path.strokeLinejoin || 'round'"
                                                    />
                                                </template>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="vc-builder-section-body vc-builder-section-body-modern" :style="sectionCanvasStyle(section)">
                                    <div class="vc-builder-insert-slot vc-builder-insert-slot-modern" :class="{ 'vc-builder-insert-slot-active': isInsertTarget(sIndex, 0) }" @dragover.prevent="onInsertDragOver(sIndex, 0)" @drop.prevent="onInsertDrop(sIndex, 0)">
                                        <button type="button" class="vc-builder-insert-button" @click.stop="openQuickAdd(sIndex, 0)">
                                            <span class="vc-builder-insert-button-icon">+</span>
                                            <span>Add first block</span>
                                        </button>
                                    </div>

                                    <div
                                        v-if="quickAddSectionIndex === sIndex && quickAddInsertIndex === 0"
                                        class="vc-builder-quick-add vc-builder-quick-add-modern"
                                    >
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'blocks' }" @click="quickAddMode = 'blocks'">Blocks</button>
                                            <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'presets' }" @click="quickAddMode = 'presets'">Presets</button>
                                            <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'templates' }" @click="quickAddMode = 'templates'">Templates</button>
                                            <button type="button" class="vc-builder-chip" @click="closeQuickAdd">Close</button>
                                        </div>
                                        <input v-model="quickAddQuery" type="text" class="vc-input" placeholder="Find a block or template">
                                        <div class="vc-builder-quick-grid">
                                            <button
                                                v-for="item in quickAddItems"
                                                :key="item.id"
                                                type="button"
                                                class="vc-builder-quick-card"
                                                @click="runQuickAddItem(sIndex, 0, item)"
                                            >
                                                <div class="vc-builder-quick-preview" v-html="renderQuickAddPreview(item)"></div>
                                                <div class="vc-builder-quick-card-title">{{ item.name }}</div>
                                                <div class="vc-builder-quick-card-meta">{{ item.meta }}</div>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <article
                                            v-for="(block, bIndex) in section.blocks"
                                            :key="block.id"
                                            class="vc-builder-block-shell vc-builder-block-shell-modern"
                                            :class="{
                                                'vc-builder-block-active': selectedSection === sIndex && selectedBlock === bIndex,
                                                'vc-builder-block-selected': isBlockSelected(block.id),
                                                'vc-builder-dragging': isDraggedBlock(sIndex, bIndex),
                                                'vc-builder-drop-target': isBlockDropTarget(sIndex, bIndex),
                                                'vc-builder-controls-always-visible': blockToolbarVisibility(block.type) === 'always',
                                                'vc-builder-block-multi-selectable': blockSelectionMode(block.type) === 'multi',
                                            }"
                                            draggable="true"
                                            @click.stop="selectBlock(sIndex, bIndex, $event)"
                                            @contextmenu.prevent="openBlockContextMenu(sIndex, bIndex, $event)"
                                            @dragstart="onBlockDragStart(sIndex, bIndex, $event)"
                                            @dragend="onBlockDragEnd"
                                            @dragover.prevent="onBlockDragOver(sIndex, bIndex)"
                                            @drop.prevent="onBlockDrop(sIndex, bIndex)"
                                        >
                                            <div class="vc-builder-block-head">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <span class="vc-builder-block-mark">{{ blockMark(block.type) }}</span>
                                                    <div class="min-w-0">
                                                        <div class="vc-builder-block-title">{{ blockLabel(block.type) }}</div>
                                                        <div class="vc-builder-meta truncate">{{ block.type }}</div>
                                                    </div>
                                                </div>

                                                <div class="vc-builder-action-cluster vc-builder-floating-controls vc-builder-block-actions" :class="{ 'vc-builder-controls-always-visible': blockToolbarVisibility(block.type) === 'always' }">
                                                    <button
                                                        v-for="action in blockActions(block.type)"
                                                        :key="`${block.id}-${action.id}`"
                                                        type="button"
                                                        class="vc-builder-action-button"
                                                        :class="{ 'vc-builder-action-danger': action.tone === 'danger' }"
                                                        :title="action.label"
                                                        :aria-label="action.label"
                                                        @click.stop="runBlockAction(action.id, sIndex, bIndex)"
                                                    >
                                                        <svg viewBox="0 0 20 20" fill="none">
                                                            <template v-for="(path, index) in actionIconPaths(action.icon)" :key="`${action.id}-${index}`">
                                                                <path
                                                                    :d="path.d"
                                                                    stroke="currentColor"
                                                                    :stroke-width="path.strokeWidth || 1.8"
                                                                    stroke-linecap="round"
                                                                    :stroke-linejoin="path.strokeLinejoin || 'round'"
                                                                />
                                                            </template>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="vc-builder-block-preview">
                                                <BuilderBlockRenderer :type="block.type" :settings="block.settings" :registry="allBlocks" :editable="true" />
                                            </div>
                                        </article>

                                        <div class="vc-builder-insert-slot vc-builder-insert-slot-modern" :class="{ 'vc-builder-insert-slot-active': isInsertTarget(sIndex, bIndex + 1) }" @dragover.prevent="onInsertDragOver(sIndex, bIndex + 1)" @drop.prevent="onInsertDrop(sIndex, bIndex + 1)">
                                            <button type="button" class="vc-builder-insert-button" @click.stop="openQuickAdd(sIndex, bIndex + 1)">
                                                <span class="vc-builder-insert-button-icon">+</span>
                                                <span>Insert here</span>
                                            </button>
                                        </div>

                                        <div
                                            v-if="quickAddSectionIndex === sIndex && quickAddInsertIndex === bIndex + 1"
                                            class="vc-builder-quick-add vc-builder-quick-add-modern"
                                        >
                                            <div class="flex flex-wrap items-center gap-2">
                                                <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'blocks' }" @click="quickAddMode = 'blocks'">Blocks</button>
                                                <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'presets' }" @click="quickAddMode = 'presets'">Presets</button>
                                                <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'templates' }" @click="quickAddMode = 'templates'">Templates</button>
                                                <button type="button" class="vc-builder-chip" @click="closeQuickAdd">Close</button>
                                            </div>
                                            <input v-model="quickAddQuery" type="text" class="vc-input" placeholder="Find a block or template">
                                            <div class="vc-builder-quick-grid">
                                                <button
                                                    v-for="item in quickAddItems"
                                                    :key="item.id"
                                                    type="button"
                                                    class="vc-builder-quick-card"
                                                    @click="runQuickAddItem(sIndex, bIndex + 1, item)"
                                                >
                                                    <div class="vc-builder-quick-preview" v-html="renderQuickAddPreview(item)"></div>
                                                    <div class="vc-builder-quick-card-title">{{ item.name }}</div>
                                                    <div class="vc-builder-quick-card-meta">{{ item.meta }}</div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <aside class="vc-builder-shell-pane vc-builder-shell-pane-right vc-builder-inspector vc-builder-scroll">
            <div class="vc-builder-inspector-head">
                <div>
                    <p class="vc-builder-panel-title">Inspector</p>
                    <h3 class="mt-3 text-xl font-semibold text-[var(--vc-text)]">{{ inspectorTitle }}</h3>
                    <p class="mt-2 text-sm text-[var(--vc-text-soft)]">{{ inspectorDescription }}</p>
                </div>
                <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': inspectorPinned }" @click="toggleInspectorPinned">
                    {{ inspectorPinned ? 'Pinned' : 'Pin' }}
                </button>
            </div>

            <div class="vc-builder-inspector-body">
                <div class="vc-builder-tab-row">
                    <button
                        v-for="tab in inspectorTabs"
                        :key="tab.id"
                        type="button"
                        class="vc-builder-tab"
                        :class="{ 'vc-builder-tab-active': inspectorTab === tab.id }"
                        @click="inspectorTab = tab.id"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div class="mt-6 space-y-5">
                    <section v-if="selectedBlockData" class="vc-builder-surface-card">
                        <p class="vc-builder-eyebrow">Selection</p>
                        <div class="mt-3 flex items-center gap-3">
                            <span class="vc-builder-block-mark">{{ blockMark(selectedBlockData.type) }}</span>
                            <div>
                                <div class="text-sm font-semibold text-[var(--vc-text)]">{{ blockLabel(selectedBlockData.type) }}</div>
                                <div class="text-xs text-[var(--vc-text-soft)]">{{ selectedBlockData.type }}</div>
                            </div>
                        </div>
                    </section>

                    <BuilderBlockSettings
                        v-if="selectedBlockData"
                        :type="selectedBlockData.type"
                        :settings="selectedBlockData.settings"
                        :media-lookup="mediaLookup"
                        :registry="allBlocks"
                        :field-group="inspectorTab"
                        @update="updateBlockSettings"
                        @open-media-picker="openMediaPicker"
                    />

                    <BuilderSectionSettings
                        v-else-if="selectedSection !== null"
                        :settings="sections[selectedSection]?.settings || {}"
                        :config="sectionConfig"
                        :panel="inspectorTab"
                        @update="updateSectionSettings"
                    />

                    <div v-else class="vc-builder-empty p-6 text-left">
                        <p class="text-sm font-semibold text-[var(--vc-text)]">Select a block or section</p>
                        <p class="mt-2 text-sm text-[var(--vc-text-soft)]">The inspector now switches between Content, Style and Advanced settings, so it stays useful instead of acting like an empty placeholder column.</p>
                        <div class="mt-4 grid gap-3">
                            <div class="vc-builder-shortcut-card">
                                <span class="vc-builder-shortcut-label">Ctrl + S</span>
                                <span class="vc-builder-shortcut-copy">Save changes</span>
                            </div>
                            <div class="vc-builder-shortcut-card">
                                <span class="vc-builder-shortcut-label">A</span>
                                <span class="vc-builder-shortcut-copy">Open quick add</span>
                            </div>
                            <div class="vc-builder-shortcut-card">
                                <span class="vc-builder-shortcut-label">R</span>
                                <span class="vc-builder-shortcut-copy">Open revisions</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div v-if="showPreview" class="vc-builder-modal fixed inset-0 z-50 flex items-center justify-center p-6" @click.self="showPreview = false">
            <div class="vc-builder-modal-card w-full max-w-6xl overflow-hidden">
                <div class="flex items-center justify-between border-b border-[var(--vc-border)] px-6 py-4">
                    <div>
                        <p class="vc-builder-eyebrow">UX Preview</p>
                        <h3 class="text-lg font-semibold text-[var(--vc-text)]">{{ page.title }}</h3>
                    </div>
                    <button type="button" class="vc-button vc-button-secondary" @click="showPreview = false">Close</button>
                </div>
                <div class="max-h-[75vh] overflow-auto bg-white p-6" v-html="previewHtml"></div>
            </div>
        </div>

        <div v-if="showRevisions" class="vc-builder-modal fixed inset-0 z-50 flex items-center justify-center p-6" @click.self="showRevisions = false">
            <div class="vc-builder-modal-card w-full max-w-4xl overflow-hidden">
                <div class="flex items-center justify-between border-b border-[var(--vc-border)] px-6 py-4">
                    <div>
                        <p class="vc-builder-eyebrow">History</p>
                        <h3 class="text-lg font-semibold text-[var(--vc-text)]">Page revisions</h3>
                    </div>
                    <button type="button" class="vc-button vc-button-secondary" @click="showRevisions = false">Close</button>
                </div>
                <div class="max-h-[75vh] overflow-auto p-6">
                    <div class="space-y-3">
                        <div
                            v-for="rev in revisions"
                            :key="rev.id"
                            class="vc-builder-preset-card"
                        >
                            <div>
                                <div class="text-sm font-semibold text-[var(--vc-text)]">{{ rev.title || page.title }}</div>
                                <div class="mt-1 text-xs text-[var(--vc-text-soft)]">{{ formatDate(rev.created_at) }}</div>
                            </div>
                            <button type="button" class="vc-button vc-button-secondary" @click="restoreRevision(rev)">Restore</button>
                        </div>
                        <div v-if="revisions.length === 0" class="vc-builder-empty p-6 text-center">No revisions yet.</div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showCommandPalette" class="vc-builder-modal fixed inset-0 z-50 flex items-start justify-center p-6 pt-16" @click.self="closeCommandPalette">
            <div class="vc-builder-modal-card w-full max-w-2xl overflow-hidden">
                <div class="border-b border-[var(--vc-border)] p-4">
                    <input ref="commandPaletteInput" v-model="commandQuery" type="text" class="vc-input" placeholder="Search command, action or shortcut">
                </div>
                <div class="max-h-[60vh] overflow-auto p-4">
                    <div class="vc-builder-command-list">
                        <button
                            v-for="item in filteredCommandItems"
                            :key="item.id"
                            type="button"
                            class="vc-builder-command-item"
                            @click="executeCommand(item.id)"
                        >
                            <span>
                                <span class="vc-builder-command-title">{{ item.label }}</span>
                                <span class="vc-builder-command-meta">{{ item.description }}</span>
                            </span>
                            <span class="vc-builder-shortcut">{{ item.shortcut }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="contextMenu.visible"
            class="vc-builder-context-menu"
            :style="{ top: `${contextMenu.y}px`, left: `${contextMenu.x}px` }"
        >
            <button
                v-for="item in contextMenu.items"
                :key="`${item.id}-${item.sectionIndex ?? 'na'}-${item.blockIndex ?? 'na'}`"
                type="button"
                class="vc-builder-command-item"
                @click="executeContextCommand(item)"
            >
                <span>
                    <span class="vc-builder-command-title">{{ item.label }}</span>
                    <span v-if="item.description" class="vc-builder-command-meta">{{ item.description }}</span>
                </span>
                <span v-if="item.shortcut" class="vc-builder-shortcut">{{ item.shortcut }}</span>
            </button>
        </div>

        <div v-if="showMediaPicker" class="vc-builder-modal fixed inset-0 z-50 flex items-center justify-center p-6" @click.self="closeMediaPicker">
            <div class="vc-builder-modal-card flex h-[85vh] w-full max-w-7xl flex-col overflow-hidden">
                <div class="flex items-center justify-between border-b border-[var(--vc-border)] px-6 py-4">
                    <div>
                        <p class="vc-builder-eyebrow">Media</p>
                        <h3 class="text-lg font-semibold text-[var(--vc-text)]">Choose media</h3>
                    </div>
                    <button type="button" class="vc-button vc-button-secondary" @click="closeMediaPicker">Close</button>
                </div>
                <div ref="mediaPickerMount" class="min-h-0 flex-1"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import BuilderBlockRenderer from './components/BuilderBlockRenderer.vue';
import BuilderBlockSettings from './components/BuilderBlockSettings.vue';
import BuilderSectionSettings from './components/BuilderSectionSettings.vue';
import { useBuilderHistory } from './useBuilderHistory';
import { useBuilderInspector } from './useBuilderInspector';
import { useBuilderTemplates } from './useBuilderTemplates';
import { useBuilderCommands } from './useBuilderCommands';
import { useBuilderCanvas } from './useBuilderCanvas';
import { useBuilderPersistence } from './useBuilderPersistence';
import { useBuilderMedia } from './useBuilderMedia';

const props = defineProps({
    page: {
        type: Object,
        default: () => ({}),
    },
    config: {
        type: Object,
        default: () => ({}),
    },
    initialSections: {
        type: Array,
        default: () => [],
    },
});

const INSPECTOR_STATE_KEY = 'vertexcms.builder.inspector';
const PRESETS_STORAGE_KEY = 'vertexcms.builder.shared-presets-cache';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

const sections = ref(JSON.parse(JSON.stringify(props.initialSections || [])));
const activeBreakpoint = ref('desktop');
const searchQuery = ref('');
const templateSearchQuery = ref('');
const contentSearchQuery = ref('');
const selectedSection = ref(null);
const selectedBlock = ref(null);
const selectedBlockData = ref(null);
const showTemplates = ref(true);
const autoSaveTimer = ref(null);
const libraryTab = ref('content');
const inspectorTab = ref('content');

const breakpoints = ref(props.config.breakpoints || [
    { name: 'desktop', label: 'Desktop', width: '100%', maxWidth: '1240px' },
    { name: 'tablet', label: 'Tablet', width: '860px', maxWidth: '860px' },
    { name: 'mobile', label: 'Mobile', width: '430px', maxWidth: '430px' },
]);
const sectionConfig = computed(() => props.config.sections || {});
const allBlocks = ref(window.availableBlocks || {});

const totalBlockTypes = computed(() => Object.keys(allBlocks.value || {}).length);
const totalCanvasBlocks = computed(() => sections.value.reduce((sum, section) => sum + (section.blocks?.length || 0), 0));

const libraryTabs = [
    { id: 'content', label: 'Content' },
    { id: 'media', label: 'Media' },
    { id: 'layout', label: 'Layout' },
    { id: 'dynamic', label: 'Dynamic' },
];

const templateCategoryLabel = (value) => {
    if (!value) return 'General';

    return String(value)
        .replace(/[-_]+/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
};

const templateScopeLabel = (template) => {
    if (template.source === 'builtin') return 'Built-in';
    if (template.visibility === 'private') return 'Private';
    return 'Shared';
};

const resolveLibraryTab = (type, block = {}) => {
    const layoutTypes = ['columns', 'container', 'spacer', 'divider', 'accordion', 'tabs', 'modal', 'collapse'];
    const mediaTypes = ['image', 'video', 'gallery', 'icon'];
    const dynamicTypes = ['news-feed', 'testimonials', 'counter', 'pricing-table', 'form', 'seo-meta', 'product-card', 'product-list', 'cart', 'alert', 'progress-bar', 'breadcrumbs'];

    if (layoutTypes.includes(type)) return 'layout';
    if (mediaTypes.includes(type)) return 'media';
    if (dynamicTypes.includes(type) || String(block.category || '').toLowerCase().includes('dynamic')) return 'dynamic';
    return 'content';
};

const filteredBlockEntries = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    return Object.entries(allBlocks.value || {})
        .filter(([type, block]) => resolveLibraryTab(type, block) === libraryTab.value)
        .filter(([type, block]) => {
            if (!query) return true;
            return type.toLowerCase().includes(query)
                || String(block.name || '').toLowerCase().includes(query)
                || String(block.description || '').toLowerCase().includes(query);
        });
});

const blockMark = (type) => {
    const registryMark = allBlocks.value?.[type]?.editor?.preview?.badge;
    if (registryMark) {
        return registryMark;
    }

    const map = {
        heading: 'H',
        text: 'T',
        button: 'B',
        image: 'I',
        video: 'V',
        gallery: 'G',
        columns: '2C',
        container: 'BX',
        faq: '?',
        form: 'F',
    };
    return map[type] || String(type || '?').slice(0, 1).toUpperCase();
};

const actionIconPaths = (icon) => {
    const icons = {
        plus: [
            { d: 'M10 4v12' },
            { d: 'M4 10h12' },
        ],
        drag: [
            { d: 'M7 5h.01M13 5h.01M7 10h.01M13 10h.01M7 15h.01M13 15h.01', strokeWidth: 2 },
        ],
        'arrow-up': [
            { d: 'M10 15V5' },
            { d: 'M10 5l-4 4' },
            { d: 'M10 5l4 4' },
        ],
        'arrow-down': [
            { d: 'M10 5v10' },
            { d: 'M10 15l-4-4' },
            { d: 'M10 15l4-4' },
        ],
        duplicate: [
            { d: 'M7 7h8v8H7z', strokeWidth: 1.7, strokeLinejoin: 'miter' },
            { d: 'M5 13H4a1 1 0 01-1-1V4a1 1 0 011-1h8a1 1 0 011 1v1', strokeWidth: 1.7 },
        ],
        close: [
            { d: 'M6 6l8 8' },
            { d: 'M14 6l-8 8' },
        ],
    };

    return icons[icon] || icons.close;
};

const sectionActions = computed(() => Array.isArray(sectionConfig.value?.actions) ? sectionConfig.value.actions : []);
const sectionToolbarVisibility = computed(() => sectionConfig.value?.presentation?.toolbar?.visibility || 'hover-or-selected');
const blockActions = (type) => allBlocks.value?.[type]?.editor?.actions || [];
const blockToolbarVisibility = (type) => allBlocks.value?.[type]?.editor?.presentation?.toolbar?.visibility || 'hover-or-selected';

const runSectionAction = (actionId, sIndex) => {
    switch (actionId) {
        case 'quick-add':
            addBlockToSection(sIndex);
            break;
        case 'move-up':
            moveSectionUp(sIndex);
            break;
        case 'move-down':
            moveSectionDown(sIndex);
            break;
        case 'duplicate':
            duplicateSection(sIndex);
            break;
        case 'delete':
            deleteSection(sIndex);
            break;
        default:
            break;
    }
};

const runBlockAction = (actionId, sIndex, bIndex) => {
    switch (actionId) {
        case 'move-up':
            moveBlockUp(sIndex, bIndex);
            break;
        case 'move-down':
            moveBlockDown(sIndex, bIndex);
            break;
        case 'duplicate':
            duplicateBlock(sIndex, bIndex);
            break;
        case 'delete':
            deleteBlock(sIndex, bIndex);
            break;
        default:
            break;
    }
};

let closeQuickAddImpl = () => {};
let closeContextMenuImpl = () => {};
const selectedBlockIds = ref([]);

const historyState = useBuilderHistory({
    sections,
    selectedSection,
    selectedBlock,
    selectedBlockData,
    selectedBlockIds,
    closeQuickAdd: () => closeQuickAddImpl(),
    closeContextMenu: () => closeContextMenuImpl(),
});

const {
    canUndo,
    canRedo,
    currentHistoryLabel,
    saveToHistory,
    undo,
    redo,
} = historyState;

const inspectorState = useBuilderInspector({
    storageKey: INSPECTOR_STATE_KEY,
    selectedSection,
    selectedBlockData,
    blockLabel: (type) => allBlocks.value?.[type]?.name || type,
});

const baseCanvasState = useBuilderCanvas({
    sections,
    allBlocks,
    sectionConfig,
    activeBreakpoint,
    selectedSection,
    selectedBlock,
    selectedBlockData,
    selectedBlockIds,
    inspectorMode: inspectorState.inspectorMode,
    persistInspectorState: inspectorState.persistInspectorState,
    saveToHistory,
});

closeQuickAddImpl = baseCanvasState.closeQuickAdd;

const templatesState = useBuilderTemplates({
    page: props.page,
    storageKey: PRESETS_STORAGE_KEY,
    selectedSection,
    selectedBlock,
    selectedBlockData,
    sections,
    saveToHistory,
    generateId: baseCanvasState.generateId,
    blockLabel: baseCanvasState.blockLabel,
    csrfToken,
    selectBlock: baseCanvasState.selectBlock,
});

const quickAddPresetItems = computed(() => {
    const query = baseCanvasState.quickAddQuery.value.trim().toLowerCase();

    return templatesState.sharedPresets.value
        .filter((preset) => !query
            || preset.name.toLowerCase().includes(query)
            || preset.type.toLowerCase().includes(query))
        .slice(0, 8)
        .map((preset) => ({
            id: `preset-${preset.id}`,
            name: preset.name,
            meta: `${baseCanvasState.blockLabel(preset.type)} preset`,
            kind: 'preset',
            preset,
        }));
});

const quickAddTemplateItems = computed(() => {
    const library = Array.isArray(props.config?.quick_add?.templates)
        ? props.config.quick_add.templates
        : [];
    const query = baseCanvasState.quickAddQuery.value.trim().toLowerCase();

    return library
        .filter((item) => !query
            || item.name.toLowerCase().includes(query)
            || String(item.meta || '').toLowerCase().includes(query)
            || String(item.description || '').toLowerCase().includes(query))
        .slice(0, 8);
});

const sharedQuickAddTemplateItems = computed(() => {
    const query = baseCanvasState.quickAddQuery.value.trim().toLowerCase();

    return templatesState.templates.value
        .filter((item) => !query
            || item.name.toLowerCase().includes(query)
            || (item.category || item.source || '').toLowerCase().includes(query))
        .slice(0, 8)
        .map((item) => ({
            ...item,
            kind: 'template',
            meta: `${item.category || item.source || 'template'} - ${item.visibility || 'shared'}`,
        }));
});

const quickAddItems = computed(() => {
    if (baseCanvasState.quickAddMode.value === 'presets') return quickAddPresetItems.value;
    if (baseCanvasState.quickAddMode.value === 'templates') {
        return sharedQuickAddTemplateItems.value.length ? sharedQuickAddTemplateItems.value : quickAddTemplateItems.value;
    }

    return Object.entries(baseCanvasState.quickAddBlocks.value).map(([type, blockDef]) => ({
        id: `block-${type}`,
        name: blockDef.name,
        meta: blockDef.editor?.quick_add?.hint || blockDef.category || 'Block',
        kind: 'block',
        type,
    }));
});

const persistenceState = useBuilderPersistence({
    page: props.page,
    sections,
    csrfToken,
    saveToHistory,
});

const mediaState = useBuilderMedia({
    config: props.config,
    sections,
    selectedSection,
    selectedBlock,
    selectedBlockData,
    saveToHistory,
});

const commandsState = useBuilderCommands({
    sections,
    allBlocks,
    sectionConfig,
    selectedSection,
    selectedBlock,
    selectedBlockIds,
    canUndo,
    canRedo,
    showRevisions: persistenceState.showRevisions,
    showPreview: persistenceState.showPreview,
    closeQuickAdd: baseCanvasState.closeQuickAdd,
    closeLibraryManager: templatesState.closeLibraryManager,
    closeMediaPicker: mediaState.closeMediaPicker,
    saveContent: persistenceState.saveContent,
    previewContent: persistenceState.previewContent,
    undo,
    redo,
    openQuickAdd: baseCanvasState.openQuickAdd,
    duplicateSelectedBlocks: baseCanvasState.duplicateSelectedBlocks,
    deleteSelectedBlocks: baseCanvasState.deleteSelectedBlocks,
    duplicateBlock: baseCanvasState.duplicateBlock,
    deleteBlock: baseCanvasState.deleteBlock,
    moveBlockUp: baseCanvasState.moveBlockUp,
    moveBlockDown: baseCanvasState.moveBlockDown,
    duplicateSection: baseCanvasState.duplicateSection,
    deleteSection: baseCanvasState.deleteSection,
});

closeContextMenuImpl = commandsState.closeContextMenu;

const openSectionContextMenu = (sIndex, event) => {
    baseCanvasState.selectSection(sIndex);
    commandsState.openSectionContextMenu(sIndex, event);
};

const openBlockContextMenu = (sIndex, bIndex, event) => {
    baseCanvasState.selectBlock(sIndex, bIndex);
    commandsState.openBlockContextMenu(sIndex, bIndex, event);
};

const pageFrameStyle = computed(() => {
    const breakpoint = breakpoints.value.find((item) => item.name === activeBreakpoint.value);
    return {
        width: breakpoint?.width || '100%',
        maxWidth: breakpoint?.maxWidth || breakpoint?.width || '1240px',
    };
});

const inspectorTabs = computed(() => {
    if (selectedBlockData.value) {
        const blockTabs = allBlocks.value?.[selectedBlockData.value.type]?.editor?.tabs;

        if (Array.isArray(blockTabs) && blockTabs.length) {
            return blockTabs.map((tab) => ({
                id: tab,
                label: tab.charAt(0).toUpperCase() + tab.slice(1),
            }));
        }
    }

    if (selectedSection.value !== null) {
        const sectionTabs = sectionConfig.value?.tabs;

        if (Array.isArray(sectionTabs) && sectionTabs.length) {
            return sectionTabs.map((tab) => ({
                id: tab,
                label: tab.charAt(0).toUpperCase() + tab.slice(1),
            }));
        }
    }

    return [
        { id: 'content', label: 'Content' },
        { id: 'style', label: 'Style' },
        { id: 'advanced', label: 'Advanced' },
    ];
});

watch(inspectorTabs, (tabs) => {
    if (!tabs.some((tab) => tab.id === inspectorTab.value)) {
        inspectorTab.value = tabs[0]?.id || 'content';
    }
}, { immediate: true });

const createStarterPage = () => {
    if (sections.value.length) return;

    const defaults = sectionConfig.value?.default_settings || {};

    sections.value = [{
        id: baseCanvasState.generateId(),
        settings: {
            padding_top: defaults.padding_top ?? 56,
            padding_bottom: defaults.padding_bottom ?? 56,
            background_color: defaults.background_color ?? null,
            css_class: defaults.css_class ?? null,
        },
        blocks: [{
            id: baseCanvasState.generateId(),
            type: 'heading',
            settings: {
                level: 'h1',
                text: props.page.title || 'New page heading',
                align: 'left',
                color: '#0f172a',
            },
        }],
    }];

    saveToHistory('Create starter section');
    baseCanvasState.selectBlock(0, 0);
};

const sectionPresetLabel = (section) => {
    const presets = Array.isArray(sectionConfig.value?.presets) ? sectionConfig.value.presets : [];
    const match = presets.find((preset) => {
        const settings = preset?.settings || {};

        return (settings.background_color ?? null) === (section.settings?.background_color ?? null)
            && (settings.padding_top ?? null) === (section.settings?.padding_top ?? null)
            && (settings.padding_bottom ?? null) === (section.settings?.padding_bottom ?? null)
            && (settings.css_class ?? null) === (section.settings?.css_class ?? null);
    });

    return match?.label || null;
};

watch(sections, () => {
    if (autoSaveTimer.value) clearTimeout(autoSaveTimer.value);
    autoSaveTimer.value = setTimeout(() => {
        persistenceState.autoSave();
    }, 120000);
}, { deep: true });

watch(contentSearchQuery, (query) => {
    if (!query) {
        selectedSection.value = null;
        selectedBlock.value = null;
        return;
    }

    for (let s = 0; s < sections.value.length; s++) {
        const section = sections.value[s];
        for (let b = 0; b < section.blocks.length; b++) {
            const block = section.blocks[b];
            const haystack = JSON.stringify(block).toLowerCase();
            if (haystack.includes(query.toLowerCase())) {
                selectedSection.value = s;
                selectedBlock.value = b;
                return;
            }
        }
    }
});

watch([selectedSection, selectedBlock, selectedBlockData], () => {
    if (selectedBlockData.value) {
        inspectorTab.value = 'content';
        return;
    }

    if (selectedSection.value !== null) {
        inspectorTab.value = 'style';
        return;
    }

    inspectorTab.value = 'content';
});

onMounted(() => {
    inspectorState.restoreInspectorState();
    templatesState.restorePresetCache();
    saveToHistory('Initial state');
    persistenceState.loadRevisions();
    templatesState.loadSharedPresets();
    templatesState.loadSharedTemplates();
    mediaState.hydrateMediaLookup(mediaState.collectReferencedMediaIds());
    document.addEventListener('keydown', commandsState.handleKeydown);
    document.addEventListener('click', commandsState.handleGlobalPointer);

    fetch('/admin/api/builder/blocks')
        .then((response) => response.json())
        .then((data) => {
            allBlocks.value = (data.blocks && !Array.isArray(data.blocks))
                ? data.blocks
                : Object.fromEntries((data.entries || data.blocks || []).map((block) => [block.type, block]));
            window.availableBlocks = allBlocks.value;
        });
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', commandsState.handleKeydown);
    document.removeEventListener('click', commandsState.handleGlobalPointer);
    mediaState.closeMediaPicker();
});

const {
    currentHistoryLabel: _currentHistoryLabel,
} = historyState;

const {
    page,
    config,
} = props;

const {
    showPreview,
    showRevisions,
    previewHtml,
    autoSaveStatus,
    autoSaveStatusText,
    revisions,
    saveContent,
    previewContent,
    restoreRevision,
    applyTemplate,
    exportCurrentSections,
    importSectionsPrompt,
    formatDate,
    saving,
} = persistenceState;

const {
    inspectorPinned,
    inspectorTitle,
    inspectorDescription,
    toggleInspectorPinned,
} = inspectorState;

const {
    presetDraftName,
    presetVisibility,
    templateDraftName,
    templateDraftCategory,
    templateVisibility,
    templateLibraryScope,
    filteredTemplates: availableTemplates,
    openLibraryManager,
    saveSelectedSectionAsTemplate,
} = templatesState;

const filteredTemplates = computed(() => {
    const query = templateSearchQuery.value.trim().toLowerCase();

    return availableTemplates.value.filter((template) => {
        if (!query) return true;

        return template.name.toLowerCase().includes(query)
            || String(template.category || '').toLowerCase().includes(query)
            || String(template.description || '').toLowerCase().includes(query)
            || String(template.owner || '').toLowerCase().includes(query)
            || String(template.source || '').toLowerCase().includes(query);
    });
});

const groupedFilteredTemplates = computed(() => {
    const groups = new Map();

    for (const template of filteredTemplates.value) {
        const key = String(template.category || template.source || 'general').toLowerCase();
        if (!groups.has(key)) {
            groups.set(key, {
                key,
                label: templateCategoryLabel(template.category || template.source || 'general'),
                items: [],
            });
        }

        groups.get(key).items.push(template);
    }

    return Array.from(groups.values());
});

const {
    mediaLookup,
    showMediaPicker,
    mediaPickerMount,
    openMediaPicker,
    closeMediaPicker,
} = mediaState;

const {
    showCommandPalette,
    commandQuery,
    commandPaletteInput,
    contextMenu,
    closeCommandPalette,
    executeCommand,
    executeContextCommand,
    filteredCommandItems,
} = commandsState;

const {
    canvasClass: _canvasClass,
    sectionCanvasStyle,
    draggedSectionIndex,
    dropSectionIndex,
    quickAddSectionIndex,
    quickAddInsertIndex,
    quickAddQuery,
    quickAddMode,
    blockLabel,
    renderQuickAddPreview,
    addBlock,
    addBlockToSection,
    moveSectionUp,
    moveSectionDown,
    duplicateSection,
    deleteSection,
    moveBlockUp,
    moveBlockDown,
    duplicateBlock,
    deleteBlock,
    openQuickAdd,
    closeQuickAdd,
    selectSection,
    selectBlock,
    updateBlockSettings,
    updateSectionSettings,
    onSectionDragStart,
    onSectionDragOver,
    onSectionDrop,
    onSectionDragEnd,
    onBlockDragStart,
    onBlockDragOver,
    onBlockDrop,
    onBlockDragEnd,
    onInsertDragOver,
    onInsertDrop,
    isDraggedBlock,
    isBlockDropTarget,
    isInsertTarget,
    isBlockSelected,
    blockSelectionMode,
} = baseCanvasState;
</script>
