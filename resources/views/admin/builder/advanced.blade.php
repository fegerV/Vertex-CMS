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
                    class="vc-builder-card p-2 text-xs"
                >
                    <button @click="applyTemplate(tpl)" class="block w-full text-left">
                        <span class="font-semibold text-[var(--vc-text)]">{{ tpl.name }}</span>
                        <span class="mt-1 block text-[var(--vc-text-soft)]">{{ tpl.category || tpl.source || 'template' }} · {{ tpl.visibility || 'shared' }}</span>
                    </button>
                    <div class="mt-2 flex items-center gap-2" v-if="tpl.can_edit || tpl.can_delete">
                        <button v-if="tpl.can_edit" @click="saveSelectedSectionAsTemplate(tpl)" class="vc-builder-icon-button" title="Update template">Upd</button>
                        <button v-if="tpl.can_delete" @click="deleteSharedTemplate(tpl.id)" class="vc-builder-icon-button text-rose-300" title="Delete template">Del</button>
                    </div>
                </div>
            </div>
            <div v-if="showTemplates" class="mt-3 space-y-2">
                <div class="flex items-center gap-2">
                    <select v-model="templateVisibility" class="vc-select text-xs">
                        <option value="shared">Shared</option>
                        <option value="private">Private</option>
                    </select>
                    <button @click="saveSelectedSectionAsTemplate()" class="vc-button vc-button-secondary px-3 py-2 text-xs">Save Section</button>
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
                    <span v-if="currentHistoryLabel" class="vc-builder-history-note">Last action: {{ currentHistoryLabel }}</span>
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
                <div v-else class="vc-builder-stage p-4">
                    <div 
                        v-for="(section, sIndex) in sections" 
                        :key="section.id"
                        class="vc-builder-section group"
                        :class="{
                            'vc-builder-section-active': selectedSection === sIndex,
                            'vc-builder-dragging': draggedSectionIndex === sIndex,
                            'vc-builder-drop-target': dropSectionIndex === sIndex && draggedSectionIndex !== null && draggedSectionIndex !== sIndex
                        }"
                        @click="selectSection(sIndex)"
                        @contextmenu.prevent="openSectionContextMenu(sIndex, $event)"
                        @dragover.prevent="onSectionDragOver(sIndex)"
                        @drop.prevent="onSectionDrop(sIndex)"
                    >
                        <!-- Section Controls -->
                        <div class="vc-builder-section-head">
                            <div class="vc-builder-section-meta">
                                <span class="vc-builder-badge" :class="{ 'vc-builder-badge-active': selectedSection === sIndex && selectedBlock === null }">
                                    Section {{ sIndex + 1 }}
                                </span>
                                <span class="vc-builder-badge">{{ section.blocks.length }} blocks</span>
                                <span v-if="section.settings?.background_color" class="vc-builder-badge">Tinted</span>
                                <div v-if="selectedCountForSection(sIndex) > 0" class="vc-builder-batch-bar">
                                    <span class="vc-builder-badge vc-builder-badge-active">{{ selectedCountForSection(sIndex) }} selected</span>
                                    <button @click.stop="duplicateSelectedBlocks(sIndex)" class="vc-builder-icon-button" title="Duplicate selection">Cp</button>
                                    <button @click.stop="deleteSelectedBlocks(sIndex)" class="vc-builder-icon-button text-rose-300" title="Delete selection">Del</button>
                                </div>
                            </div>

                            <div class="vc-builder-floating-controls flex gap-1 opacity-0 transition-opacity duration-150 group-hover:opacity-100">
                                <button
                                    draggable="true"
                                    @dragstart="onSectionDragStart(sIndex, $event)"
                                    @dragend="onSectionDragEnd"
                                    class="vc-builder-icon-button vc-builder-drag-handle p-1"
                                    title="Move section"
                                >
                                    ::
                                </button>
                                <button @click.stop="addBlockToSection(sIndex)" class="vc-builder-icon-button p-1" title="Add block">
                                    +
                                </button>
                            <button @click.stop="moveSectionUp(sIndex)" class="vc-builder-icon-button p-1" title="Move up">
                                ↑
                            </button>
                            <button @click.stop="moveSectionDown(sIndex)" class="vc-builder-icon-button p-1" title="Move down">
                                ↓
                            </button>
                            <button @click.stop="duplicateSection(sIndex)" class="vc-builder-icon-button p-1" title="Duplicate">
                                📋
                            </button>
                            <button @click.stop="deleteSection(sIndex)" class="vc-builder-icon-button p-1 text-rose-300" title="Delete">
                                ✕
                            </button>
                        </div>

                        </div>
                        <div class="vc-builder-section-body" :style="sectionCanvasStyle(section)" @dragover.prevent="onSectionBodyDragOver(sIndex)" @drop.prevent="onSectionBodyDrop(sIndex)">
                            <div class="vc-builder-insert-slot" :class="{ 'vc-builder-insert-slot-active': isInsertTarget(sIndex, 0) }" @dragover.prevent="onInsertDragOver(sIndex, 0)" @drop.prevent="onInsertDrop(sIndex, 0)">
                                <button @click.stop="openQuickAdd(sIndex, 0)" class="vc-builder-insert-button">Add block here</button>
                            </div>
                            <div v-if="section.blocks.length === 0" class="vc-builder-empty flex min-h-32 flex-col items-center justify-center text-center">
                                <p class="text-sm font-semibold text-[var(--vc-text)]">This section is empty</p>
                                <p class="mt-1 text-xs text-[var(--vc-text-soft)]">Use the add control or choose a block from the library.</p>
                            </div>
                            <div v-if="quickAddSectionIndex === sIndex" class="vc-builder-quick-add">
                                <div class="flex items-center gap-3">
                                    <input v-model="quickAddQuery" type="text" class="vc-input" placeholder="Find a block for this section...">
                                    <button @click.stop="closeQuickAdd" class="vc-button vc-button-secondary px-3 py-2">Close</button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button @click.stop="quickAddMode = 'blocks'" class="vc-builder-chip" :class="quickAddMode === 'blocks' ? 'vc-builder-chip-active' : ''">Blocks</button>
                                    <button @click.stop="quickAddMode = 'presets'" class="vc-builder-chip" :class="quickAddMode === 'presets' ? 'vc-builder-chip-active' : ''">Presets</button>
                                    <button @click.stop="quickAddMode = 'templates'" class="vc-builder-chip" :class="quickAddMode === 'templates' ? 'vc-builder-chip-active' : ''">Templates</button>
                                </div>
                                <div class="vc-builder-quick-grid">
                                    <button
                                        v-for="item in quickAddItems"
                                        :key="`quick-${sIndex}-${item.id}`"
                                        @click.stop="runQuickAddItem(sIndex, quickAddInsertIndex, item)"
                                        class="vc-builder-quick-card"
                                    >
                                        <div class="vc-builder-quick-preview" v-html="renderQuickAddPreview(item)"></div>
                                        <span class="vc-builder-quick-card-title">{{ item.name }}</span>
                                        <span class="vc-builder-quick-card-meta">{{ item.meta }}</span>
                                    </button>
                                </div>
                                <div v-if="!quickAddItems.length" class="vc-builder-field-hint">Nothing matched this query in the current library.</div>
                            </div>
                            <div class="space-y-4">
                                <div 
                                    v-for="(block, bIndex) in section.blocks" 
                                    :key="block.id"
                                    class="vc-builder-block-shell"
                                    :class="{
                                        'vc-builder-block-active': selectedBlock === bIndex && selectedSection === sIndex,
                                        'vc-builder-block-selected': isBlockSelected(block.id),
                                        'vc-builder-dragging': isDraggedBlock(sIndex, bIndex),
                                        'vc-builder-drop-target': isBlockDropTarget(sIndex, bIndex)
                                    }"
                                    @click.stop="selectBlock(sIndex, bIndex, $event)"
                                    @contextmenu.prevent.stop="openBlockContextMenu(sIndex, bIndex, $event)"
                                    @dragover.prevent="onBlockDragOver(sIndex, bIndex)"
                                    @drop.prevent="onBlockDrop(sIndex, bIndex)"
                                >
                                    <div class="vc-builder-block-head">
                                        <div class="vc-builder-section-meta">
                                            <button
                                                draggable="true"
                                                @dragstart="onBlockDragStart(sIndex, bIndex, $event)"
                                                @dragend="onBlockDragEnd"
                                                class="vc-builder-icon-button vc-builder-drag-handle"
                                                title="Move block"
                                            >
                                                ::
                                            </button>
                                            <span class="vc-builder-block-title">{{ blockLabel(block.type) }}</span>
                                            <span class="vc-builder-badge" :class="{ 'vc-builder-badge-active': selectedBlock === bIndex && selectedSection === sIndex }">
                                                Block {{ bIndex + 1 }}
                                            </span>
                                            <span v-if="isBlockSelected(block.id)" class="vc-builder-badge vc-builder-badge-active">Selected</span>
                                        </div>
                                        <div class="vc-builder-block-actions">
                                            <button @click.stop="toggleBlockSelection(block.id)" class="vc-builder-icon-button" title="Select block">Sel</button>
                                            <button @click.stop="moveBlockUp(sIndex, bIndex)" class="vc-builder-icon-button" title="Move up">Up</button>
                                            <button @click.stop="moveBlockDown(sIndex, bIndex)" class="vc-builder-icon-button" title="Move down">Dn</button>
                                            <button @click.stop="duplicateBlock(sIndex, bIndex)" class="vc-builder-icon-button" title="Duplicate">Cp</button>
                                            <button @click.stop="deleteBlock(sIndex, bIndex)" class="vc-builder-icon-button text-rose-300" title="Delete">Del</button>
                                        </div>
                                    </div>

                                    <BlockRenderer 
                                        :type="block.type" 
                                        :settings="block.settings"
                                        :editable="false"
                                    />
                                    <div class="vc-builder-insert-slot" :class="{ 'vc-builder-insert-slot-active': isInsertTarget(sIndex, bIndex + 1) }" @dragover.prevent="onInsertDragOver(sIndex, bIndex + 1)" @drop.prevent="onInsertDrop(sIndex, bIndex + 1)">
                                        <button @click.stop="openQuickAdd(sIndex, bIndex + 1)" class="vc-builder-insert-button">Insert here</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Right Sidebar: Inspector -->
    <aside class="vc-builder-sidebar vc-builder-scroll vc-builder-inspector w-96 overflow-y-auto border-l">
        <div class="vc-builder-inspector-head">
            <div>
                <div class="vc-builder-panel-title">Inspector</div>
                <h3 class="mt-2 text-lg font-semibold text-[var(--vc-text)]">{{ inspectorTitle }}</h3>
                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ inspectorDescription }}</p>
            </div>
            <button
                @click="toggleInspectorPinned"
                class="vc-builder-chip"
                :class="inspectorPinned ? 'vc-builder-chip-active' : ''"
                :title="inspectorPinned ? 'Disable sticky inspector state' : 'Keep inspector mode sticky'"
            >
                {{ inspectorPinned ? 'Pinned' : 'Pin' }}
            </button>
        </div>

        <div class="p-6 pt-4">
            <div v-if="selectedBlockData" class="space-y-5">
                <BlockSettings 
                    :type="selectedBlockData.type"
                    :settings="selectedBlockData.settings"
                    :media-lookup="mediaLookup"
                    @update="updateBlockSettings"
                    @open-media-picker="openMediaPicker"
                />

                <div class="vc-builder-form-section">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="vc-builder-form-title">Presets</div>
                            <p class="mt-1 text-xs text-[var(--vc-text-soft)]">Save reusable snippets for this block type.</p>
                        </div>
                        <span class="vc-builder-badge">{{ currentBlockPresets.length }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <input v-model="presetDraftName" type="text" class="vc-input" placeholder="Preset name">
                        <select v-model="presetVisibility" class="vc-select max-w-36">
                            <option value="shared">Shared</option>
                            <option value="private">Private</option>
                        </select>
                        <button @click="saveCurrentBlockPreset" class="vc-button vc-button-secondary px-3 py-2">Save</button>
                    </div>
                    <div v-if="currentBlockPresets.length" class="vc-builder-preset-list">
                        <div v-for="preset in currentBlockPresets" :key="preset.id" class="vc-builder-preset-card">
                            <div>
                                <div class="vc-builder-command-title">{{ preset.name }}</div>
                                <div class="vc-builder-command-meta">{{ formatPresetDate(preset.updated_at) }} · {{ preset.visibility }} · {{ preset.owner || 'system' }}</div>
                            </div>
                            <div class="vc-builder-inline-actions">
                                <button @click="applyBlockPreset(preset)" class="vc-builder-icon-button" title="Apply preset">Use</button>
                                <button @click="insertPresetAfterSelection(preset)" class="vc-builder-icon-button" title="Insert preset as new block">Add</button>
                                <button v-if="preset.can_delete" @click="deleteBlockPreset(preset.id)" class="vc-builder-icon-button text-rose-300" title="Delete preset">Del</button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="vc-builder-field-hint">No presets saved for this block yet.</div>
                </div>
            </div>
            
            <div v-else-if="selectedSection !== null" class="space-y-5">
                <SectionSettings 
                    :settings="sections[selectedSection].settings"
                    @update="updateSectionSettings"
                />
            </div>

            <div v-else-if="inspectorPinned" class="vc-builder-empty p-6 text-center text-[var(--vc-text-muted)]">
                <p class="text-sm font-semibold text-[var(--vc-text)]">Inspector stays pinned</p>
                <p class="mt-2 text-sm">Select a block or section to continue editing in the same inspector mode.</p>
            </div>

            <div v-else class="p-6 text-center text-[var(--vc-text-muted)]">
                <p class="text-sm">Select a block or section to edit</p>
            </div>
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

    <div v-if="showMediaPicker" class="vc-builder-modal fixed inset-0 z-60 flex items-center justify-center p-4" @click.self="closeMediaPicker">
        <div class="vc-builder-modal-card flex h-full max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden">
            <div class="flex items-center justify-between gap-4 border-b border-[var(--vc-border)] p-4">
                <div>
                    <h3 class="font-semibold text-[var(--vc-text)]">Media picker</h3>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Choose a media asset for the current block.</p>
                </div>
                <div class="flex items-center gap-3">
                    <input v-model="mediaPickerQuery" type="text" class="vc-input w-72" placeholder="Search media...">
                    <button @click="closeMediaPicker" class="vc-button vc-button-secondary px-3 py-2">Close</button>
                </div>
            </div>
            <div class="grid flex-1 min-h-0 gap-0 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div class="vc-builder-media-grid-wrap">
                    <div class="vc-builder-media-grid">
                        <button
                            v-for="item in mediaPickerItems"
                            :key="item.id"
                            @click="selectMediaItem(item)"
                            class="vc-builder-media-card"
                            :class="{ 'vc-builder-media-card-active': mediaPickerSelected?.id === item.id }"
                        >
                            <div class="vc-builder-media-thumb">
                                <img v-if="item.mime_type?.startsWith('image/')" :src="item.url" :alt="item.alt || item.original_filename || 'Media preview'">
                                <div v-else class="vc-builder-renderer-fallback h-full place-content-center">File</div>
                            </div>
                            <div class="space-y-1 text-left">
                                <div class="text-sm font-semibold text-[var(--vc-text)]">{{ item.title || item.original_filename }}</div>
                                <div class="text-xs text-[var(--vc-text-soft)]">#{{ item.id }} · {{ item.mime_type }}</div>
                            </div>
                        </button>
                    </div>
                    <div v-if="!mediaPickerLoading && !mediaPickerItems.length" class="vc-builder-empty m-6 flex min-h-48 items-center justify-center text-center">
                        <div>
                            <p class="text-sm font-semibold text-[var(--vc-text)]">No media found</p>
                            <p class="mt-1 text-sm text-[var(--vc-text-soft)]">Try another search query or upload media in the media library.</p>
                        </div>
                    </div>
                </div>
                <div class="border-l border-[var(--vc-border)] p-4">
                    <div v-if="mediaPickerSelected" class="space-y-4">
                        <div class="vc-builder-form-title">Selected asset</div>
                        <div class="vc-builder-media-preview">
                            <img v-if="mediaPickerSelected.mime_type?.startsWith('image/')" :src="mediaPickerSelected.url" :alt="mediaPickerSelected.alt || mediaPickerSelected.original_filename || 'Selected media'">
                            <div v-else class="vc-builder-renderer-fallback h-56 place-content-center">Preview unavailable</div>
                        </div>
                        <div class="vc-builder-settings-card">
                            <div class="text-sm font-semibold text-[var(--vc-text)]">{{ mediaPickerSelected.title || mediaPickerSelected.original_filename }}</div>
                            <div class="text-xs text-[var(--vc-text-soft)]">ID: {{ mediaPickerSelected.id }}</div>
                            <div class="text-xs text-[var(--vc-text-soft)]">{{ mediaPickerSelected.width || 'auto' }} × {{ mediaPickerSelected.height || 'auto' }}</div>
                            <div v-if="mediaPickerSelected.alt" class="text-xs text-[var(--vc-text-muted)]">{{ mediaPickerSelected.alt }}</div>
                        </div>
                        <button @click="applyPickedMedia" class="vc-button vc-button-primary w-full px-3 py-2">Use media</button>
                    </div>
                    <div v-else class="vc-builder-empty flex h-full items-center justify-center p-6 text-center">
                        <div>
                            <p class="text-sm font-semibold text-[var(--vc-text)]">Select an asset</p>
                            <p class="mt-1 text-sm text-[var(--vc-text-soft)]">Preview and metadata will appear here.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between gap-4 border-t border-[var(--vc-border)] p-4">
                <div class="text-sm text-[var(--vc-text-muted)]">Page {{ mediaPickerPage }} of {{ mediaPickerLastPage }}</div>
                <div class="flex items-center gap-2">
                    <button @click="changeMediaPickerPage(-1)" :disabled="mediaPickerPage <= 1 || mediaPickerLoading" class="vc-button vc-button-secondary px-3 py-2 disabled:opacity-50">Prev</button>
                    <button @click="changeMediaPickerPage(1)" :disabled="mediaPickerPage >= mediaPickerLastPage || mediaPickerLoading" class="vc-button vc-button-secondary px-3 py-2 disabled:opacity-50">Next</button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="showCommandPalette" class="vc-builder-modal fixed inset-0 z-60 flex items-start justify-center p-6" @click.self="closeCommandPalette">
        <div class="vc-builder-modal-card w-full max-w-2xl p-4">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <input ref="commandPaletteInput" v-model="commandQuery" type="text" class="vc-input" placeholder="Search commands...">
                    <button @click="closeCommandPalette" class="vc-button vc-button-secondary px-3 py-2">Close</button>
                </div>
                <div class="vc-builder-command-list">
                    <button
                        v-for="command in filteredCommandItems"
                        :key="command.id"
                        @click="executeCommand(command.id)"
                        class="vc-builder-command-item"
                    >
                        <span>
                            <span class="vc-builder-command-title">{{ command.label }}</span>
                            <span class="vc-builder-command-meta">{{ command.description }}</span>
                        </span>
                        <span v-if="command.shortcut" class="vc-builder-shortcut">{{ command.shortcut }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        v-if="contextMenu.visible"
        class="vc-builder-context-menu"
        :style="{ left: `${contextMenu.x}px`, top: `${contextMenu.y}px` }"
    >
        <div class="vc-builder-command-list">
            <button
                v-for="item in contextMenu.items"
                :key="item.id"
                @click="executeContextCommand(item)"
                class="vc-builder-command-item"
            >
                <span>
                    <span class="vc-builder-command-title">{{ item.label }}</span>
                    <span v-if="item.description" class="vc-builder-command-meta">{{ item.description }}</span>
                </span>
                <span v-if="item.shortcut" class="vc-builder-shortcut">{{ item.shortcut }}</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    import { createApp, ref, reactive, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js'
    
    createApp({
        components: {
            BlockRenderer: {
                props: ['type', 'settings', 'editable'],
                template: `
                    <div class="vc-builder-renderer">
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
                        if (!block) return '<div class="vc-builder-renderer-fallback"><strong>Unknown block</strong><span>This block type is not registered in the current builder config.</span></div>';
                        
                        return block.render ? block.render(settings) : this.defaultRender(type, settings);
                    },
                    defaultRender(type, settings) {
                        switch(type) {
                            case 'heading':
                                return \`<h2 style="color: \${settings.color || '#111'}; text-align: \${settings.align || 'left'}">\${settings.text || 'Heading'}</h2>\`;
                            case 'text':
                                return \`<div style="color: \${settings.color || '#333'}; text-align: \${settings.align || 'left'}">\${settings.content || settings.text || ''}</div>\`;
                            case 'button':
                                return \`<a href="\${settings.url || '#'}" class="btn" style="background: \${settings.style === 'primary' ? '#3b82f6' : '#6b7280'}; color: white; padding: 0.5rem 1rem; border-radius: 0.25rem; text-decoration: none; display: inline-block;">\${settings.text || 'Button'}</a>\`;
                            case 'divider':
                                return '<hr class="my-4">';
                            case 'faq':
                                return \`<div class="faq">\${(settings.items || []).map(item => \`<details><summary>\${item.question || 'Question'}</summary><div>\${item.answer || 'Answer'}</div></details>\`).join('')}</div>\`;
                            case 'image':
                                return settings.url
                                    ? \`<img src="\${settings.url}" alt="\${settings.alt || ''}" style="max-width: 100%; height: auto; border-radius: 16px;">\`
                                    : '<div class="vc-builder-renderer-fallback"><strong>Image placeholder</strong><span>Bind a media file or image URL in block settings.</span></div>';
                            case 'html':
                                return settings.html || '<div class="vc-builder-html-preview">HTML block</div>';
                            default:
                                return \`<div class="vc-builder-renderer-fallback"><strong>\${type}</strong><span>No default renderer is defined for this block.</span></div>\`;
                        }
                    }
                }
            },
            BlockSettings: {
                props: ['type', 'settings', 'mediaLookup'],
                emits: ['update', 'open-media-picker'],
                data() {
                    return {
                        localSettings: { ...this.settings },
                        draggedRepeater: null,
                        repeaterDropTarget: null,
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
                            <div v-for="(field, key) in blockDef.fields" :key="key" class="vc-builder-form-section">
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
                                        <div v-for="(item, itemIndex) in localSettings[key]" :key="item.id || itemIndex" class="vc-builder-repeater-card">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        draggable="true"
                                                        @dragstart="onRepeaterDragStart(key, itemIndex, $event)"
                                                        @dragend="onRepeaterDragEnd"
                                                        type="button"
                                                        class="vc-builder-icon-button vc-builder-drag-handle"
                                                        title="Move item"
                                                    >
                                                        ::
                                                    </button>
                                                    <span class="vc-builder-badge">Item {{ itemIndex + 1 }}</span>
                                                </div>
                                                <button @click="removeRepeaterItem(key, itemIndex)" type="button" class="vc-builder-icon-button text-rose-300" title="Remove item">Del</button>
                                            </div>
                                            <div
                                                class="space-y-3"
                                                :class="{ 'vc-builder-drop-target': isRepeaterDropTarget(key, itemIndex) }"
                                                @dragover.prevent="onRepeaterDragOver(key, itemIndex)"
                                                @drop.prevent="onRepeaterDrop(key, itemIndex)"
                                            >
                                                <div v-for="nestedField in field.fields || []" :key="nestedField.key" class="vc-builder-field">
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
                                                                <div class="text-sm font-semibold text-[var(--vc-text)]">{{ mediaPreview([key, itemIndex, nestedField.key]).title || mediaPreview([key, itemIndex, nestedField.key]).original_filename }}</div>
                                                                <div class="text-xs text-[var(--vc-text-soft)]">ID: {{ resolveMediaId([key, itemIndex, nestedField.key]) }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-3">
                                                            <button @click="$emit('open-media-picker', { path: [key, itemIndex, nestedField.key], field: nestedField, blockType: type })" type="button" class="vc-button vc-button-secondary px-3 py-2">
                                                                {{ resolveMediaId([key, itemIndex, nestedField.key]) ? 'Replace media' : 'Choose media' }}
                                                            </button>
                                                            <button v-if="resolveMediaId([key, itemIndex, nestedField.key])" @click="clearMediaField([key, itemIndex, nestedField.key])" type="button" class="vc-button vc-button-secondary px-3 py-2">
                                                                Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input
                                                        v-else-if="nestedField.type === 'text' || nestedField.type === 'number' || nestedField.type === 'color'"
                                                        :type="nestedField.type"
                                                        :value="item[nestedField.key]"
                                                        @input="updateRepeaterField(key, itemIndex, nestedField.key, $event.target.value)"
                                                        :class="nestedField.type === 'color' ? 'vc-input h-12' : 'vc-input'"
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
                                                        @click="updateRepeaterField(key, itemIndex, nestedField.key, !item[nestedField.key])"
                                                        class="vc-builder-toggle"
                                                        :class="item[nestedField.key] ? 'vc-builder-toggle-active' : ''"
                                                    >
                                                        <div class="vc-builder-toggle-knob"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button @click="addRepeaterItem(key, field)" type="button" class="vc-button vc-button-secondary px-3 py-2">Add item</button>
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
                                            <div class="text-sm font-semibold text-[var(--vc-text)]">{{ mediaPreview(key).title || mediaPreview(key).original_filename }}</div>
                                            <div class="text-xs text-[var(--vc-text-soft)]">ID: {{ resolveMediaId(key) }}</div>
                                            <div class="text-xs text-[var(--vc-text-soft)]">{{ mediaPreview(key).mime_type }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button @click="$emit('open-media-picker', { key, field, blockType: type })" type="button" class="vc-button vc-button-secondary px-3 py-2">
                                            {{ resolveMediaId(key) ? 'Replace media' : 'Choose media' }}
                                        </button>
                                        <button v-if="resolveMediaId(key)" @click="clearMediaField(key)" type="button" class="vc-button vc-button-secondary px-3 py-2">
                                            Remove
                                        </button>
                                    </div>
                                    <div class="vc-builder-field-hint">
                                        <span v-if="resolveMediaId(key)">Linked media ID: {{ resolveMediaId(key) }}</span>
                                        <span v-else>No media selected yet.</span>
                                    </div>
                                </div>
                                <input 
                                    v-else-if="field.type === 'text' || field.type === 'color' || field.type === 'number'"
                                    :type="field.type"
                                    v-model="localSettings[key]"
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
                                    @click="localSettings[key] = !localSettings[key]"
                                    class="vc-builder-toggle"
                                    :class="localSettings[key] ? 'vc-builder-toggle-active' : ''"
                                >
                                    <div class="vc-builder-toggle-knob"></div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="vc-builder-form-section">
                            <div class="vc-builder-field-hint">No settings available for this block type</div>
                        </div>
                    </div>
                `,
                computed: {
                    blockDef() {
                        return window.availableBlocks?.[this.type];
                    }
                },
                methods: {
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
                        if (!target || last === undefined) return;
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
                        <div class="vc-builder-form-section">
                            <div class="vc-builder-form-title">Surface</div>
                            <div class="vc-builder-field">
                                <label>Background Color</label>
                                <div class="vc-builder-inline-actions">
                                    <div class="vc-builder-swatch-row">
                                        <div class="vc-builder-swatch">
                                            <input 
                                                type="color"
                                                v-model="localSettings.background_color"
                                            >
                                        </div>
                                        <span class="vc-builder-field-hint">{{ localSettings.background_color || 'transparent' }}</span>
                                    </div>
                                    <button 
                                        v-if="localSettings.background_color"
                                        @click="localSettings.background_color = null"
                                        class="text-xs font-semibold text-[var(--vc-danger)]"
                                    >
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="vc-builder-form-section">
                            <div class="vc-builder-form-title">Spacing</div>
                            <div class="vc-builder-field">
                                <label>Padding Top</label>
                                <input 
                                    type="range" 
                                    min="0" max="100" 
                                    v-model.number="localSettings.padding_top"
                                    class="vc-builder-range w-full"
                                >
                                <span class="vc-builder-range-value">{{ localSettings.padding_top || 16 }}px</span>
                            </div>
                            <div class="vc-builder-field">
                                <label>Padding Bottom</label>
                                <input 
                                    type="range" 
                                    min="0" max="100" 
                                    v-model.number="localSettings.padding_bottom"
                                    class="vc-builder-range w-full"
                                >
                                <span class="vc-builder-range-value">{{ localSettings.padding_bottom || 16 }}px</span>
                            </div>
                        </div>
                        <div class="vc-builder-form-section">
                            <div class="vc-builder-form-title">Advanced</div>
                            <div class="vc-builder-field">
                                <label>CSS Class</label>
                                <input 
                                    type="text"
                                    v-model="localSettings.css_class"
                                    placeholder="e.g., my-custom-class"
                                    class="vc-input"
                                >
                            </div>
                        </div>
                    </div>
                `
            }
        },
        setup() {
            const page = @json($page);
            const config = @json($config);
            const initialSections = @json($page->content_json['sections'] ?? []);
            const INSPECTOR_STATE_KEY = 'vertexcms.builder.inspector';
            const PRESETS_STORAGE_KEY = 'vertexcms.builder.shared-presets-cache';

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
            const draggedSectionIndex = ref(null);
            const dropSectionIndex = ref(null);
            const draggedBlock = ref(null);
            const dropBlockTarget = ref(null);
            const selectedBlockIds = ref([]);
            const quickAddSectionIndex = ref(null);
            const quickAddInsertIndex = ref(0);
            const quickAddQuery = ref('');
            const quickAddMode = ref('blocks');
            const showCommandPalette = ref(false);
            const commandQuery = ref('');
            const commandPaletteInput = ref(null);
            const contextMenu = ref({ visible: false, x: 0, y: 0, items: [] });
            const inspectorPinned = ref(false);
            const inspectorMode = ref('block');
            const presetDraftName = ref('');
            const presetVisibility = ref('shared');
            const templateVisibility = ref('shared');
            const sharedPresets = ref([]);
            const mediaLookup = ref({});
            const showMediaPicker = ref(false);
            const mediaPickerQuery = ref('');
            const mediaPickerPage = ref(1);
            const mediaPickerLastPage = ref(1);
            const mediaPickerItems = ref([]);
            const mediaPickerLoading = ref(false);
            const mediaPickerSelected = ref(null);
            const mediaPickerTarget = ref(null);
            const mediaPickerSearchTimer = ref(null);

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

            const quickAddBlocks = computed(() => {
                const entries = Object.entries(allBlocks.value);
                const query = quickAddQuery.value.trim().toLowerCase();
                const filtered = !query
                    ? entries
                    : entries.filter(([_, block]) =>
                        block.name.toLowerCase().includes(query) ||
                        (block.description || '').toLowerCase().includes(query) ||
                        (block.category || '').toLowerCase().includes(query)
                    );

                return Object.fromEntries(filtered.slice(0, 8));
            });
            const quickAddPresetItems = computed(() => {
                const query = quickAddQuery.value.trim().toLowerCase();

                return sharedPresets.value
                    .filter((preset) => !query
                        || preset.name.toLowerCase().includes(query)
                        || preset.type.toLowerCase().includes(query)
                    )
                    .slice(0, 8)
                    .map((preset) => ({
                        id: `preset-${preset.id}`,
                        name: preset.name,
                        meta: `${blockLabel(preset.type)} preset`,
                        kind: 'preset',
                        preset,
                    }));
            });
            const quickAddTemplateItems = computed(() => {
                const library = [
                    {
                        id: 'template-hero-heading',
                        name: 'Hero heading',
                        meta: 'Template · heading + text + button',
                        kind: 'template',
                        blocks: [
                            { type: 'heading', settings: { level: 'h1', text: 'Launch a stronger headline', align: 'left', color: '#111827', font_size: '2rem' } },
                            { type: 'text', settings: { content: 'Add a concise supporting paragraph for the section intro.', align: 'left', color: '#4b5563' } },
                            { type: 'button', settings: { text: 'Primary action', url: '#', style: 'primary', size: 'md', target: '_self' } },
                        ],
                    },
                    {
                        id: 'template-image-feature',
                        name: 'Feature with image',
                        meta: 'Template · image + heading + text',
                        kind: 'template',
                        blocks: [
                            { type: 'image', settings: { media_id: null, url: '', alt: '', width: '100%', height: 'auto', radius: 'md', shadow: 'sm' } },
                            { type: 'heading', settings: { level: 'h3', text: 'Feature title', align: 'left', color: '#111827', font_size: '1.5rem' } },
                            { type: 'text', settings: { content: 'Describe this feature in one useful paragraph.', align: 'left', color: '#4b5563' } },
                        ],
                    },
                    {
                        id: 'template-faq-starter',
                        name: 'FAQ starter',
                        meta: 'Template · heading + faq',
                        kind: 'template',
                        blocks: [
                            { type: 'heading', settings: { level: 'h2', text: 'Frequently asked questions', align: 'left', color: '#111827', font_size: '1.5rem' } },
                            { type: 'faq', settings: { items: [{ question: 'Question one', answer: 'Answer one' }, { question: 'Question two', answer: 'Answer two' }] } },
                        ],
                    },
                ];
                const query = quickAddQuery.value.trim().toLowerCase();

                return library
                    .filter((item) => !query
                        || item.name.toLowerCase().includes(query)
                        || item.meta.toLowerCase().includes(query)
                    )
                    .slice(0, 8);
            });
            const sharedQuickAddTemplateItems = computed(() => {
                const query = quickAddQuery.value.trim().toLowerCase();

                return templates.value
                    .filter((item) => !query
                        || item.name.toLowerCase().includes(query)
                        || (item.category || item.source || '').toLowerCase().includes(query)
                    )
                    .slice(0, 8)
                    .map((item) => ({
                        ...item,
                        kind: 'template',
                        meta: `${item.category || item.source || 'template'} · ${item.visibility || 'shared'}`,
                    }));
            });
            const quickAddItems = computed(() => {
                if (quickAddMode.value === 'presets') return quickAddPresetItems.value;
                if (quickAddMode.value === 'templates') return sharedQuickAddTemplateItems.value;

                return Object.entries(quickAddBlocks.value).map(([type, blockDef]) => ({
                    id: `block-${type}`,
                    name: blockDef.name,
                    meta: blockDef.category || 'Block',
                    kind: 'block',
                    type,
                }));
            });
            const templateBlocksFromItem = (item) => {
                return (item.sections || [])
                    .flatMap((section) => section.blocks || [])
                    .map((block) => ({
                        type: block.type,
                        settings: JSON.parse(JSON.stringify(block.settings || {})),
                    }));
            };
            const renderPreviewBlocks = (blocks) => {
                if (!Array.isArray(blocks) || blocks.length === 0) {
                    return '<div class="vc-builder-renderer-fallback"><strong>Empty</strong><span>No preview available.</span></div>';
                }

                return blocks.slice(0, 2).map((block) => {
                    const definition = allBlocks.value?.[block.type];
                    if (!definition) {
                        return `<div class="vc-builder-html-preview">${block.type}</div>`;
                    }

                    try {
                        if (typeof definition.render === 'function') {
                            return definition.render(block.settings || {});
                        }
                    } catch (error) {
                        console.error('Quick preview render error:', error);
                    }

                    if (block.type === 'image' && block.settings?.url) {
                        return `<img src="${block.settings.url}" alt="${block.settings.alt || ''}" style="max-width:100%;height:88px;object-fit:cover;border-radius:12px;">`;
                    }

                    if (block.type === 'heading') {
                        return `<strong style="display:block;font-size:1rem;color:#111827;">${block.settings?.text || 'Heading'}</strong>`;
                    }

                    if (block.type === 'text') {
                        return `<div style="font-size:0.82rem;color:#64748b;">${(block.settings?.content || block.settings?.text || '').slice(0, 96)}</div>`;
                    }

                    return `<div class="vc-builder-html-preview">${blockLabel(block.type)}</div>`;
                }).join('');
            };
            const renderQuickAddPreview = (item) => {
                if (item.kind === 'preset') {
                    return renderPreviewBlocks([{ type: item.preset.type, settings: item.preset.settings || {} }]);
                }

                if (item.kind === 'template') {
                    return renderPreviewBlocks(templateBlocksFromItem(item));
                }

                const block = buildBlock(item.type);
                return block ? renderPreviewBlocks([block]) : '<div class="vc-builder-html-preview">Preview</div>';
            };

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

            const currentHistoryEntry = computed(() => history.value[historyIndex.value] || null);
            const currentHistoryLabel = computed(() => currentHistoryEntry.value?.label || '');
            const currentBlockPresets = computed(() => {
                if (!selectedBlockData.value) return [];

                return sharedPresets.value.filter((preset) => preset.type === selectedBlockData.value.type);
            });
            const inspectorTitle = computed(() => {
                if (selectedBlockData.value) {
                    return `${blockLabel(selectedBlockData.value.type)} settings`;
                }

                if (selectedSection.value !== null) {
                    return `Section ${selectedSection.value + 1} settings`;
                }

                return inspectorPinned.value
                    ? `${inspectorMode.value === 'section' ? 'Section' : 'Block'} inspector`
                    : 'Inspector';
            });
            const inspectorDescription = computed(() => {
                if (selectedBlockData.value) {
                    return 'Tune block content, appearance and reusable presets.';
                }

                if (selectedSection.value !== null) {
                    return 'Adjust section spacing, background and CSS hooks.';
                }

                return inspectorPinned.value
                    ? 'Inspector mode is pinned between selections.'
                    : 'Pick a section or block on the canvas to start editing.';
            });

            const blockLabel = (type) => {
                return allBlocks.value?.[type]?.name || type;
            };

            const persistInspectorState = () => {
                localStorage.setItem(INSPECTOR_STATE_KEY, JSON.stringify({
                    pinned: inspectorPinned.value,
                    mode: inspectorMode.value,
                }));
            };

            const persistBlockPresets = () => {
                localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(sharedPresets.value));
            };

            const collectReferencedMediaIds = () => {
                const ids = new Set();
                const visit = (value, key = null) => {
                    if (Array.isArray(value)) {
                        value.forEach((item) => visit(item));
                        return;
                    }

                    if (value && typeof value === 'object') {
                        Object.entries(value).forEach(([nestedKey, nestedValue]) => {
                            if ((nestedKey === 'media_id' || nestedKey === 'image') && Number.isFinite(Number(nestedValue)) && Number(nestedValue) > 0) {
                                ids.add(Number(nestedValue));
                            }
                            visit(nestedValue, nestedKey);
                        });
                        return;
                    }

                    if ((key === 'media_id' || key === 'image') && Number.isFinite(Number(value)) && Number(value) > 0) {
                        ids.add(Number(value));
                    }
                };

                visit(sections.value);

                return [...ids];
            };

            const hydrateMediaLookup = async (ids = []) => {
                const missingIds = ids
                    .map((id) => Number(id))
                    .filter((id) => id > 0 && !mediaLookup.value[id]);

                if (!missingIds.length) return;

                try {
                    const chunks = [];
                    for (let index = 0; index < missingIds.length; index += 50) {
                        chunks.push(missingIds.slice(index, index + 50));
                    }

                    for (const chunk of chunks) {
                        const response = await fetch(`/admin/api/media?ids[]=${chunk.join('&ids[]=')}&per_page=${chunk.length}`);
                        const data = await response.json();
                        const items = data.data || [];
                        mediaLookup.value = {
                            ...mediaLookup.value,
                            ...Object.fromEntries(items.map((item) => [Number(item.id), item])),
                        };
                    }
                } catch (error) {
                    console.error('Media hydrate error:', error);
                }
            };

            const sectionCanvasStyle = (section) => {
                return {
                    backgroundColor: section.settings?.background_color || '',
                    paddingTop: `${section.settings?.padding_top ?? 16}px`,
                    paddingBottom: `${section.settings?.padding_bottom ?? 16}px`,
                };
            };

            const isDraggedBlock = (sIndex, bIndex) => {
                return draggedBlock.value?.sectionIndex === sIndex && draggedBlock.value?.blockIndex === bIndex;
            };

            const isBlockDropTarget = (sIndex, bIndex) => {
                return dropBlockTarget.value?.sectionIndex === sIndex && dropBlockTarget.value?.blockIndex === bIndex;
            };

            const isInsertTarget = (sIndex, insertIndex) => {
                return dropBlockTarget.value?.sectionIndex === sIndex && dropBlockTarget.value?.blockIndex === insertIndex;
            };

            const isBlockSelected = (blockId) => {
                return selectedBlockIds.value.includes(blockId);
            };

            const selectedCountForSection = (sIndex) => {
                return sections.value[sIndex]?.blocks.filter((block) => selectedBlockIds.value.includes(block.id)).length || 0;
            };

            const commandItems = computed(() => {
                const hasBlock = selectedSection.value !== null && selectedBlock.value !== null;
                const hasSelection = selectedBlockIds.value.length > 0;

                return [
                    { id: 'save', label: 'Save changes', description: 'Persist current builder JSON', shortcut: 'Ctrl/Cmd+S' },
                    { id: 'preview', label: 'Open preview', description: 'Render current page preview', shortcut: 'Ctrl/Cmd+Shift+P' },
                    { id: 'undo', label: 'Undo last action', description: 'Step back one builder action', shortcut: 'Ctrl/Cmd+Z', disabled: !canUndo.value },
                    { id: 'redo', label: 'Redo action', description: 'Restore next builder action', shortcut: 'Ctrl/Cmd+Shift+Z', disabled: !canRedo.value },
                    { id: 'revisions', label: 'Open revisions', description: 'Browse saved revisions', shortcut: 'R' },
                    { id: 'quick-add', label: 'Quick add in selected section', description: 'Open inline block palette for the current section', shortcut: 'A', disabled: selectedSection.value === null },
                    { id: 'duplicate-selection', label: 'Duplicate selected blocks', description: 'Duplicate current multi-selection in this section', shortcut: 'Ctrl/Cmd+D', disabled: !hasSelection },
                    { id: 'delete-selection', label: 'Delete selected blocks', description: 'Delete current multi-selection', shortcut: 'Delete', disabled: !hasSelection },
                    { id: 'duplicate-block', label: 'Duplicate current block', description: 'Duplicate the focused block', shortcut: 'D', disabled: !hasBlock },
                    { id: 'delete-block', label: 'Delete current block', description: 'Remove the focused block', shortcut: 'Backspace/Delete', disabled: !hasBlock },
                ].filter((item) => !item.disabled);
            });

            const filteredCommandItems = computed(() => {
                const query = commandQuery.value.trim().toLowerCase();
                if (!query) return commandItems.value;
                return commandItems.value.filter((item) =>
                    item.label.toLowerCase().includes(query) ||
                    item.description.toLowerCase().includes(query) ||
                    (item.shortcut || '').toLowerCase().includes(query)
                );
            });

            // Methods
            const buildBlock = (type) => {
                const block = allBlocks.value[type];
                if (!block) return null;

                return {
                    id: generateId(),
                    type,
                    settings: JSON.parse(JSON.stringify(block.default?.settings || block.default || {}))
                };
            };

            const addBlock = (type) => {
                const newBlock = buildBlock(type);
                if (!newBlock) return;

                if (selectedSection.value !== null) {
                    sections.value[selectedSection.value].blocks.push(newBlock);
                } else {
                    sections.value.push({
                        id: generateId(),
                        settings: {},
                        blocks: [newBlock]
                    });
                }

                saveToHistory('Add block');
            };

            const toggleInspectorPinned = () => {
                inspectorPinned.value = !inspectorPinned.value;
                persistInspectorState();
            };

            const insertBlockAt = (sIndex, insertIndex, type) => {
                const newBlock = buildBlock(type);
                if (!newBlock) return;
                sections.value[sIndex].blocks.splice(insertIndex, 0, newBlock);
                selectBlock(sIndex, insertIndex);
                closeQuickAdd();
                saveToHistory('Insert block');
            };

            const buildPresetBlock = (preset) => ({
                id: generateId(),
                type: preset.type,
                settings: JSON.parse(JSON.stringify(preset.settings || {})),
            });

            const insertPresetAt = (sIndex, insertIndex, preset) => {
                sections.value[sIndex].blocks.splice(insertIndex, 0, buildPresetBlock(preset));
                selectBlock(sIndex, insertIndex);
                closeQuickAdd();
                saveToHistory('Insert preset block');
            };

            const insertTemplateBlocksAt = (sIndex, insertIndex, template) => {
                const blocks = templateBlocksFromItem(template).map((block) => ({
                    id: generateId(),
                    type: block.type,
                    settings: JSON.parse(JSON.stringify(block.settings || {})),
                }));

                sections.value[sIndex].blocks.splice(insertIndex, 0, ...blocks);
                selectBlock(sIndex, insertIndex);
                closeQuickAdd();
                saveToHistory('Insert quick template');
            };

            const runQuickAddItem = (sIndex, insertIndex, item) => {
                if (item.kind === 'preset') {
                    insertPresetAt(sIndex, insertIndex, item.preset);
                    return;
                }

                if (item.kind === 'template') {
                    insertTemplateBlocksAt(sIndex, insertIndex, item);
                    return;
                }

                insertBlockAt(sIndex, insertIndex, item.type);
            };

            const openQuickAdd = (sIndex, insertIndex) => {
                selectedSection.value = sIndex;
                quickAddSectionIndex.value = sIndex;
                quickAddInsertIndex.value = insertIndex;
                quickAddQuery.value = '';
                quickAddMode.value = 'blocks';
            };

            const closeQuickAdd = () => {
                quickAddSectionIndex.value = null;
                quickAddInsertIndex.value = 0;
                quickAddQuery.value = '';
                quickAddMode.value = 'blocks';
            };

            const addBlockToSection = (sIndex) => {
                openQuickAdd(sIndex, sections.value[sIndex].blocks.length);
            };

            const deleteSection = (sIndex) => {
                const removedIds = new Set((sections.value[sIndex]?.blocks || []).map((block) => block.id));
                sections.value.splice(sIndex, 1);
                selectedBlockIds.value = selectedBlockIds.value.filter((id) => !removedIds.has(id));
                selectedSection.value = null;
                selectedBlock.value = null;
                selectedBlockData.value = null;
                saveToHistory('Delete section');
            };

            const duplicateSection = (sIndex) => {
                const section = JSON.parse(JSON.stringify(sections.value[sIndex]));
                section.id = generateId();
                section.blocks = section.blocks.map(b => ({ ...b, id: generateId() }));
                sections.value.splice(sIndex + 1, 0, section);
                saveToHistory('Duplicate section');
            };

            const moveSectionUp = (sIndex) => {
                if (sIndex > 0) {
                    const temp = sections.value[sIndex];
                    sections.value[sIndex] = sections.value[sIndex - 1];
                    sections.value[sIndex - 1] = temp;
                    saveToHistory('Move section up');
                }
            };

            const moveSectionDown = (sIndex) => {
                if (sIndex < sections.value.length - 1) {
                    const temp = sections.value[sIndex];
                    sections.value[sIndex] = sections.value[sIndex + 1];
                    sections.value[sIndex + 1] = temp;
                    saveToHistory('Move section down');
                }
            };

            const moveBlockUp = (sIndex, bIndex) => {
                if (bIndex <= 0) return;
                const blocks = sections.value[sIndex].blocks;
                const temp = blocks[bIndex];
                blocks[bIndex] = blocks[bIndex - 1];
                blocks[bIndex - 1] = temp;
                selectBlock(sIndex, bIndex - 1);
                saveToHistory('Move block up');
            };

            const moveBlockDown = (sIndex, bIndex) => {
                const blocks = sections.value[sIndex].blocks;
                if (bIndex >= blocks.length - 1) return;
                const temp = blocks[bIndex];
                blocks[bIndex] = blocks[bIndex + 1];
                blocks[bIndex + 1] = temp;
                selectBlock(sIndex, bIndex + 1);
                saveToHistory('Move block down');
            };

            const duplicateBlock = (sIndex, bIndex) => {
                const source = sections.value[sIndex].blocks[bIndex];
                if (!source) return;
                const copy = JSON.parse(JSON.stringify(source));
                copy.id = generateId();
                sections.value[sIndex].blocks.splice(bIndex + 1, 0, copy);
                selectBlock(sIndex, bIndex + 1);
                saveToHistory('Duplicate block');
            };

            const toggleBlockSelection = (blockId) => {
                if (selectedBlockIds.value.includes(blockId)) {
                    selectedBlockIds.value = selectedBlockIds.value.filter((id) => id !== blockId);
                    return;
                }
                selectedBlockIds.value = [...selectedBlockIds.value, blockId];
            };

            const duplicateSelectedBlocks = (sIndex) => {
                const blocks = sections.value[sIndex].blocks;
                const selected = blocks
                    .map((block, index) => ({ block, index }))
                    .filter(({ block }) => selectedBlockIds.value.includes(block.id));

                if (!selected.length) return;

                let offset = 0;
                for (const { block, index } of selected) {
                    const copy = JSON.parse(JSON.stringify(block));
                    copy.id = generateId();
                    blocks.splice(index + 1 + offset, 0, copy);
                    offset++;
                }

                saveToHistory('Duplicate selected blocks');
            };

            const deleteSelectedBlocks = (sIndex) => {
                const idsToDelete = new Set(
                    sections.value[sIndex].blocks
                        .filter((block) => selectedBlockIds.value.includes(block.id))
                        .map((block) => block.id)
                );
                if (!idsToDelete.size) return;

                sections.value[sIndex].blocks = sections.value[sIndex].blocks.filter((block) => !idsToDelete.has(block.id));
                selectedBlockIds.value = selectedBlockIds.value.filter((id) => !idsToDelete.has(id));
                if (selectedSection.value === sIndex) {
                    selectedBlock.value = null;
                    selectedBlockData.value = null;
                }
                saveToHistory('Delete selected blocks');
            };

            const deleteBlock = (sIndex, bIndex) => {
                const removedId = sections.value[sIndex].blocks[bIndex]?.id;
                sections.value[sIndex].blocks.splice(bIndex, 1);
                if (removedId) {
                    selectedBlockIds.value = selectedBlockIds.value.filter((id) => id !== removedId);
                }
                if (selectedSection.value === sIndex && selectedBlock.value === bIndex) {
                    selectedBlock.value = null;
                    selectedBlockData.value = null;
                } else if (selectedSection.value === sIndex && selectedBlock.value > bIndex) {
                    selectedBlock.value--;
                    const nextBlock = sections.value[sIndex].blocks[selectedBlock.value];
                    selectedBlockData.value = nextBlock
                        ? { type: nextBlock.type, settings: nextBlock.settings }
                        : null;
                }
                saveToHistory('Delete block');
            };

            const onSectionDragStart = (sIndex, event) => {
                draggedSectionIndex.value = sIndex;
                dropSectionIndex.value = sIndex;
                if (event?.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', `section:${sIndex}`);
                }
            };

            const onSectionDragOver = (sIndex) => {
                if (draggedSectionIndex.value === null) return;
                dropSectionIndex.value = sIndex;
            };

            const onSectionDrop = (sIndex) => {
                const from = draggedSectionIndex.value;
                if (from === null || from === sIndex) {
                    onSectionDragEnd();
                    return;
                }
                const [section] = sections.value.splice(from, 1);
                sections.value.splice(sIndex, 0, section);
                selectSection(sIndex);
                saveToHistory('Reorder sections');
                onSectionDragEnd();
            };

            const onSectionDragEnd = () => {
                draggedSectionIndex.value = null;
                dropSectionIndex.value = null;
            };

            const onBlockDragStart = (sIndex, bIndex, event) => {
                draggedBlock.value = { sectionIndex: sIndex, blockIndex: bIndex };
                dropBlockTarget.value = { sectionIndex: sIndex, blockIndex: bIndex };
                if (event?.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', `block:${sIndex}:${bIndex}`);
                }
            };

            const onBlockDragOver = (sIndex, bIndex) => {
                if (!draggedBlock.value) return;
                dropBlockTarget.value = { sectionIndex: sIndex, blockIndex: bIndex };
            };

            const onInsertDragOver = (sIndex, insertIndex) => {
                if (!draggedBlock.value) return;
                dropBlockTarget.value = { sectionIndex: sIndex, blockIndex: insertIndex };
            };

            const moveDraggedBlock = (targetSectionIndex, targetBlockIndex = null) => {
                if (!draggedBlock.value) return;
                const { sectionIndex: fromSectionIndex, blockIndex: fromBlockIndex } = draggedBlock.value;
                const sourceBlocks = sections.value[fromSectionIndex]?.blocks;
                if (!sourceBlocks?.[fromBlockIndex]) {
                    onBlockDragEnd();
                    return;
                }

                const [block] = sourceBlocks.splice(fromBlockIndex, 1);
                let insertIndex = targetBlockIndex;

                if (insertIndex === null || insertIndex === undefined) {
                    insertIndex = sections.value[targetSectionIndex].blocks.length;
                }

                if (fromSectionIndex === targetSectionIndex && fromBlockIndex < insertIndex) {
                    insertIndex--;
                }

                sections.value[targetSectionIndex].blocks.splice(insertIndex, 0, block);
                selectBlock(targetSectionIndex, insertIndex);
                saveToHistory('Reorder blocks');
                onBlockDragEnd();
            };

            const onBlockDrop = (sIndex, bIndex) => {
                if (!draggedBlock.value) return;
                moveDraggedBlock(sIndex, bIndex);
            };

            const onInsertDrop = (sIndex, insertIndex) => {
                if (!draggedBlock.value) return;
                moveDraggedBlock(sIndex, insertIndex);
            };

            const onSectionBodyDragOver = (sIndex) => {
                if (!draggedBlock.value) return;
                dropBlockTarget.value = { sectionIndex: sIndex, blockIndex: sections.value[sIndex].blocks.length };
            };

            const onSectionBodyDrop = (sIndex) => {
                if (!draggedBlock.value) return;
                moveDraggedBlock(sIndex, sections.value[sIndex].blocks.length);
            };

            const onBlockDragEnd = () => {
                draggedBlock.value = null;
                dropBlockTarget.value = null;
            };

            const selectSection = (sIndex) => {
                selectedSection.value = sIndex;
                selectedBlock.value = null;
                selectedBlockData.value = null;
                inspectorMode.value = 'section';
                persistInspectorState();
                closeQuickAdd();
            };

            const selectBlock = (sIndex, bIndex, event = null) => {
                const block = sections.value[sIndex].blocks[bIndex];
                if (!block) return;

                if (event?.ctrlKey || event?.metaKey) {
                    toggleBlockSelection(block.id);
                } else {
                    selectedBlockIds.value = [block.id];
                }

                selectedSection.value = sIndex;
                selectedBlock.value = bIndex;
                inspectorMode.value = 'block';
                persistInspectorState();
                selectedBlockData.value = {
                    type: block.type,
                    settings: block.settings
                };
            };

            const updateBlockSettings = (newSettings) => {
                if (selectedSection.value !== null && selectedBlock.value !== null) {
                    sections.value[selectedSection.value].blocks[selectedBlock.value].settings = newSettings;
                    selectedBlockData.value = {
                        type: sections.value[selectedSection.value].blocks[selectedBlock.value].type,
                        settings: newSettings,
                    };
                    saveToHistory('Edit block settings', { mergeKey: `block-settings:${selectedSection.value}:${selectedBlock.value}` });
                }
            };

            const updateSectionSettings = (newSettings) => {
                if (selectedSection.value !== null) {
                    sections.value[selectedSection.value].settings = newSettings;
                    saveToHistory('Edit section settings', { mergeKey: `section-settings:${selectedSection.value}` });
                }
            };

            const applyHistoryEntry = (entry) => {
                sections.value = JSON.parse(JSON.stringify(entry.snapshot));
                selectedSection.value = null;
                selectedBlock.value = null;
                selectedBlockData.value = null;
                selectedBlockIds.value = [];
                closeQuickAdd();
                closeContextMenu();
            };

            const saveToHistory = (label = 'Edit builder', options = {}) => {
                const { mergeKey = null } = options;
                const snapshot = JSON.parse(JSON.stringify(sections.value));

                // Remove any future states if we're not at the end
                history.value = history.value.slice(0, historyIndex.value + 1);

                const lastEntry = history.value[history.value.length - 1];
                if (mergeKey && lastEntry?.mergeKey === mergeKey) {
                    history.value[history.value.length - 1] = { snapshot, label, mergeKey };
                    historyIndex.value = history.value.length - 1;
                    return;
                }

                history.value.push({ snapshot, label, mergeKey });
                // Limit history size
                if (history.value.length > 100) {
                    history.value.shift();
                    historyIndex.value = history.value.length - 1;
                } else {
                    historyIndex.value++;
                }
            };

            const undo = () => {
                if (canUndo.value) {
                    historyIndex.value--;
                    applyHistoryEntry(history.value[historyIndex.value]);
                }
            };

            const redo = () => {
                if (canRedo.value) {
                    historyIndex.value++;
                    applyHistoryEntry(history.value[historyIndex.value]);
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
                    saveToHistory('Import sections');
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
                        saveToHistory('Restore revision');
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
                    saveToHistory('Apply template');
                });
            };

            const closeContextMenu = () => {
                contextMenu.value = { visible: false, x: 0, y: 0, items: [] };
            };

            const openContextMenu = (items, event) => {
                contextMenu.value = {
                    visible: true,
                    x: event.clientX,
                    y: event.clientY,
                    items,
                };
            };

            const openSectionContextMenu = (sIndex, event) => {
                selectSection(sIndex);
                openContextMenu([
                    { id: 'quick-add', label: 'Quick add block', description: 'Open section palette', shortcut: 'A', sectionIndex: sIndex },
                    { id: 'duplicate-section', label: 'Duplicate section', shortcut: '[]', sectionIndex: sIndex },
                    { id: 'delete-section', label: 'Delete section', shortcut: 'x', sectionIndex: sIndex },
                ], event);
            };

            const openBlockContextMenu = (sIndex, bIndex, event) => {
                selectBlock(sIndex, bIndex);
                openContextMenu([
                    { id: 'duplicate-block', label: 'Duplicate block', shortcut: 'D', sectionIndex: sIndex, blockIndex: bIndex },
                    { id: 'delete-block', label: 'Delete block', shortcut: 'Delete', sectionIndex: sIndex, blockIndex: bIndex },
                    { id: 'move-block-up', label: 'Move block up', shortcut: 'Alt+Up', sectionIndex: sIndex, blockIndex: bIndex },
                    { id: 'move-block-down', label: 'Move block down', shortcut: 'Alt+Down', sectionIndex: sIndex, blockIndex: bIndex },
                ], event);
            };

            const closeCommandPalette = () => {
                showCommandPalette.value = false;
                commandQuery.value = '';
            };

            const loadSharedPresets = async () => {
                try {
                    const response = await fetch('/admin/pages/builder/presets');
                    const data = await response.json();
                    sharedPresets.value = data.data || [];
                    persistBlockPresets();
                } catch (error) {
                    console.error('Shared presets load error:', error);
                }
            };

            const loadSharedTemplates = async () => {
                try {
                    const response = await fetch('/admin/pages/builder/shared-templates');
                    const data = await response.json();
                    templates.value = data.data || [];
                } catch (error) {
                    console.error('Shared templates load error:', error);
                }
            };

            const saveCurrentBlockPreset = async () => {
                if (!selectedBlockData.value) return;

                const name = presetDraftName.value.trim() || `${blockLabel(selectedBlockData.value.type)} preset`;
                const existingPreset = sharedPresets.value.find((preset) =>
                    preset.type === selectedBlockData.value.type && preset.name.toLowerCase() === name.toLowerCase()
                );
                const payload = {
                    type: selectedBlockData.value.type,
                    name,
                    settings: JSON.parse(JSON.stringify(selectedBlockData.value.settings || {})),
                    visibility: presetVisibility.value,
                };
                const url = existingPreset
                    ? `/admin/pages/builder/presets/${existingPreset.id}`
                    : '/admin/pages/builder/presets';
                const method = existingPreset ? 'PUT' : 'POST';

                try {
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await response.json();
                    sharedPresets.value = data.presets || sharedPresets.value;
                    persistBlockPresets();
                } catch (error) {
                    console.error('Shared preset save error:', error);
                }

                presetDraftName.value = '';
            };

            const applyBlockPreset = (preset) => {
                if (!selectedBlockData.value || selectedSection.value === null || selectedBlock.value === null) return;

                const mergedSettings = {
                    ...sections.value[selectedSection.value].blocks[selectedBlock.value].settings,
                    ...JSON.parse(JSON.stringify(preset.settings || {})),
                };

                sections.value[selectedSection.value].blocks[selectedBlock.value].settings = mergedSettings;
                selectedBlockData.value = {
                    type: sections.value[selectedSection.value].blocks[selectedBlock.value].type,
                    settings: mergedSettings,
                };
                saveToHistory('Apply block preset');
            };

            const insertPresetAfterSelection = (preset) => {
                if (selectedSection.value === null) return;

                const insertIndex = selectedBlock.value !== null
                    ? selectedBlock.value + 1
                    : sections.value[selectedSection.value].blocks.length;

                const block = {
                    id: generateId(),
                    type: preset.type,
                    settings: JSON.parse(JSON.stringify(preset.settings || {})),
                };

                sections.value[selectedSection.value].blocks.splice(insertIndex, 0, block);
                selectBlock(selectedSection.value, insertIndex);
                saveToHistory('Insert block preset');
            };

            const deleteBlockPreset = async (presetId) => {
                try {
                    const response = await fetch(`/admin/pages/builder/presets/${presetId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                    });
                    const data = await response.json();
                    sharedPresets.value = data.presets || sharedPresets.value.filter((preset) => preset.id !== presetId);
                    persistBlockPresets();
                } catch (error) {
                    console.error('Shared preset delete error:', error);
                }
            };

            const saveSelectedSectionAsTemplate = async (template = null) => {
                if (selectedSection.value === null || !sections.value[selectedSection.value]) {
                    alert('Select a section first.');
                    return;
                }

                const name = window.prompt('Template name', template?.name || '');
                if (!name) return;

                const payload = {
                    name,
                    category: template?.category || 'custom',
                    sections: [JSON.parse(JSON.stringify(sections.value[selectedSection.value]))],
                    visibility: template?.visibility || templateVisibility.value,
                };
                const url = template?.id && template?.can_edit
                    ? `/admin/pages/builder/shared-templates/${template.id}`
                    : '/admin/pages/builder/shared-templates';
                const method = template?.id && template?.can_edit ? 'PUT' : 'POST';

                try {
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await response.json();
                    templates.value = data.templates || templates.value;
                } catch (error) {
                    console.error('Shared template save error:', error);
                }
            };

            const deleteSharedTemplate = async (templateId) => {
                try {
                    const response = await fetch(`/admin/pages/builder/shared-templates/${templateId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                    });
                    const data = await response.json();
                    templates.value = data.templates || templates.value.filter((template) => template.id !== templateId);
                } catch (error) {
                    console.error('Shared template delete error:', error);
                }
            };

            const formatPresetDate = (value) => {
                if (!value) return 'Recently updated';

                return new Date(value).toLocaleString();
            };

            const openMediaPicker = async (payload) => {
                mediaPickerTarget.value = payload;
                showMediaPicker.value = true;
                mediaPickerSelected.value = null;
                mediaPickerPage.value = 1;

                const resolvePathValue = (source, path) => {
                    const normalized = Array.isArray(path) ? path : [path];
                    return normalized.reduce((acc, segment) => acc?.[segment], source);
                };
                const currentValue = resolvePathValue(selectedBlockData.value?.settings || {}, payload?.path || payload?.key);

                if (currentValue) {
                    await hydrateMediaLookup([currentValue]);
                    mediaPickerSelected.value = mediaLookup.value[Number(currentValue)] || null;
                }

                await loadMediaPickerItems();
            };

            const closeMediaPicker = () => {
                showMediaPicker.value = false;
                mediaPickerSelected.value = null;
                mediaPickerTarget.value = null;
            };

            const loadMediaPickerItems = async () => {
                mediaPickerLoading.value = true;
                try {
                    const params = new URLSearchParams({
                        per_page: '18',
                        page: String(mediaPickerPage.value),
                        kind: 'image',
                    });
                    if (mediaPickerQuery.value.trim()) {
                        params.set('q', mediaPickerQuery.value.trim());
                    }

                    const response = await fetch(`/admin/api/media?${params.toString()}`);
                    const data = await response.json();
                    const items = data.data || [];
                    mediaPickerItems.value = items;
                    mediaPickerLastPage.value = data.last_page || 1;
                    mediaLookup.value = {
                        ...mediaLookup.value,
                        ...Object.fromEntries(items.map((item) => [Number(item.id), item])),
                    };
                } catch (error) {
                    console.error('Media picker error:', error);
                    mediaPickerItems.value = [];
                } finally {
                    mediaPickerLoading.value = false;
                }
            };

            const changeMediaPickerPage = async (step) => {
                const nextPage = mediaPickerPage.value + step;
                if (nextPage < 1 || nextPage > mediaPickerLastPage.value) return;
                mediaPickerPage.value = nextPage;
                await loadMediaPickerItems();
            };

            const selectMediaItem = (item) => {
                mediaPickerSelected.value = item;
            };

            const applyPickedMedia = () => {
                if (!mediaPickerSelected.value || selectedSection.value === null || selectedBlock.value === null) return;

                const block = sections.value[selectedSection.value].blocks[selectedBlock.value];
                const settings = {
                    ...block.settings,
                };
                const targetPath = mediaPickerTarget.value?.path || (mediaPickerTarget.value?.key ? [mediaPickerTarget.value.key] : []);
                const setAtPath = (targetValue, path, value) => {
                    const normalized = Array.isArray(path) ? path : [path];
                    const last = normalized.at(-1);
                    const target = normalized.slice(0, -1).reduce((acc, segment) => acc?.[segment], targetValue);
                    if (!target || last === undefined) return;
                    target[last] = value;
                };
                const lastKey = targetPath.at(-1);

                if (lastKey === 'media_id') {
                    setAtPath(settings, targetPath, mediaPickerSelected.value.id);
                    if (targetPath.length === 1) {
                        settings.url = mediaPickerSelected.value.url || settings.url || '';
                        if (!settings.alt && mediaPickerSelected.value.alt) {
                            settings.alt = mediaPickerSelected.value.alt;
                        }
                    }
                } else if (lastKey) {
                    setAtPath(settings, targetPath, mediaPickerSelected.value.id);
                    if (targetPath.length === 1 && (lastKey === 'image' || lastKey.endsWith('_image')) && !settings.url) {
                        settings.url = mediaPickerSelected.value.url || '';
                    }
                }

                sections.value[selectedSection.value].blocks[selectedBlock.value].settings = settings;
                selectedBlockData.value = {
                    type: sections.value[selectedSection.value].blocks[selectedBlock.value].type,
                    settings,
                };
                saveToHistory('Attach media');
                closeMediaPicker();
            };

            const openCommandPalette = async () => {
                showCommandPalette.value = true;
                commandQuery.value = '';
                await nextTick();
                commandPaletteInput.value?.focus();
            };

            const executeCommand = async (commandId, payload = {}) => {
                const targetSection = payload.sectionIndex ?? selectedSection.value;
                const targetBlock = payload.blockIndex ?? selectedBlock.value;

                switch (commandId) {
                    case 'save':
                        await saveContent();
                        break;
                    case 'preview':
                        await previewContent();
                        break;
                    case 'undo':
                        undo();
                        break;
                    case 'redo':
                        redo();
                        break;
                    case 'revisions':
                        showRevisions.value = true;
                        break;
                    case 'quick-add':
                        if (targetSection !== null) {
                            openQuickAdd(targetSection, sections.value[targetSection].blocks.length);
                        }
                        break;
                    case 'duplicate-selection':
                        if (targetSection !== null) {
                            duplicateSelectedBlocks(targetSection);
                        }
                        break;
                    case 'delete-selection':
                        if (targetSection !== null) {
                            deleteSelectedBlocks(targetSection);
                        }
                        break;
                    case 'duplicate-block':
                        if (targetSection !== null && targetBlock !== null) {
                            duplicateBlock(targetSection, targetBlock);
                        }
                        break;
                    case 'delete-block':
                        if (targetSection !== null && targetBlock !== null) {
                            deleteBlock(targetSection, targetBlock);
                        }
                        break;
                    case 'move-block-up':
                        if (targetSection !== null && targetBlock !== null) {
                            moveBlockUp(targetSection, targetBlock);
                        }
                        break;
                    case 'move-block-down':
                        if (targetSection !== null && targetBlock !== null) {
                            moveBlockDown(targetSection, targetBlock);
                        }
                        break;
                    case 'duplicate-section':
                        if (targetSection !== null) {
                            duplicateSection(targetSection);
                        }
                        break;
                    case 'delete-section':
                        if (targetSection !== null) {
                            deleteSection(targetSection);
                        }
                        break;
                }

                closeContextMenu();
                closeCommandPalette();
            };

            const executeContextCommand = (item) => executeCommand(item.id, item);

            const handleKeydown = async (event) => {
                const target = event.target;
                const typingTarget = target && (
                    ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) ||
                    target.isContentEditable
                );

                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                    event.preventDefault();
                    await openCommandPalette();
                    return;
                }

                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
                    event.preventDefault();
                    await saveContent();
                    return;
                }

                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'z') {
                    event.preventDefault();
                    if (event.shiftKey) {
                        redo();
                    } else {
                        undo();
                    }
                    return;
                }

                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'y') {
                    event.preventDefault();
                    redo();
                    return;
                }

                if ((event.ctrlKey || event.metaKey) && event.shiftKey && event.key.toLowerCase() === 'p') {
                    event.preventDefault();
                    await previewContent();
                    return;
                }

                if (event.key === 'Escape') {
                    closeQuickAdd();
                    closeContextMenu();
                    closeCommandPalette();
                    showPreview.value = false;
                    showRevisions.value = false;
                    return;
                }

                if (typingTarget) return;

                if (event.key.toLowerCase() === 'a' && selectedSection.value !== null) {
                    event.preventDefault();
                    openQuickAdd(selectedSection.value, sections.value[selectedSection.value].blocks.length);
                    return;
                }

                if (event.key.toLowerCase() === 'r') {
                    event.preventDefault();
                    showRevisions.value = true;
                    return;
                }

                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'd') {
                    event.preventDefault();
                    if (selectedBlockIds.value.length && selectedSection.value !== null) {
                        duplicateSelectedBlocks(selectedSection.value);
                    } else if (selectedSection.value !== null && selectedBlock.value !== null) {
                        duplicateBlock(selectedSection.value, selectedBlock.value);
                    }
                    return;
                }

                if ((event.key === 'Delete' || event.key === 'Backspace') && selectedSection.value !== null) {
                    event.preventDefault();
                    if (selectedBlockIds.value.length) {
                        deleteSelectedBlocks(selectedSection.value);
                    } else if (selectedBlock.value !== null) {
                        deleteBlock(selectedSection.value, selectedBlock.value);
                    }
                    return;
                }

                if (event.altKey && event.key === 'ArrowUp' && selectedSection.value !== null && selectedBlock.value !== null) {
                    event.preventDefault();
                    moveBlockUp(selectedSection.value, selectedBlock.value);
                    return;
                }

                if (event.altKey && event.key === 'ArrowDown' && selectedSection.value !== null && selectedBlock.value !== null) {
                    event.preventDefault();
                    moveBlockDown(selectedSection.value, selectedBlock.value);
                }
            };

            const handleGlobalPointer = () => {
                if (contextMenu.value.visible) {
                    closeContextMenu();
                }
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

            watch(mediaPickerQuery, () => {
                if (mediaPickerSearchTimer.value) clearTimeout(mediaPickerSearchTimer.value);
                mediaPickerSearchTimer.value = setTimeout(() => {
                    mediaPickerPage.value = 1;
                    loadMediaPickerItems();
                }, 220);
            });

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
                try {
                    const savedInspectorState = JSON.parse(localStorage.getItem(INSPECTOR_STATE_KEY) || '{}');
                    inspectorPinned.value = Boolean(savedInspectorState.pinned);
                    inspectorMode.value = savedInspectorState.mode === 'section' ? 'section' : 'block';
                    sharedPresets.value = JSON.parse(localStorage.getItem(PRESETS_STORAGE_KEY) || '[]');
                } catch (error) {
                    console.error('Builder local state restore error:', error);
                }

                saveToHistory('Initial state');
                loadRevisions();
                loadSharedPresets();
                loadSharedTemplates();
                hydrateMediaLookup(collectReferencedMediaIds());
                document.addEventListener('keydown', handleKeydown);
                document.addEventListener('click', handleGlobalPointer);
                
                // Load available blocks from server
                fetch('/admin/api/builder/blocks')
                    .then(r => r.json())
                    .then(data => {
                        allBlocks.value = data.blocks || {};
                        window.availableBlocks = allBlocks.value;
                    });
            });

            onBeforeUnmount(() => {
                document.removeEventListener('keydown', handleKeydown);
                document.removeEventListener('click', handleGlobalPointer);
                if (mediaPickerSearchTimer.value) clearTimeout(mediaPickerSearchTimer.value);
            });

            return {
                page, config, sections, activeCategory, activeBreakpoint,
                searchQuery, contentSearchQuery, selectedSection, selectedBlock,
                selectedBlockData, saving, showPreview, showRevisions, showTemplates, templates,
                showCommandPalette, commandQuery, commandPaletteInput, contextMenu,
                inspectorPinned, inspectorMode, inspectorTitle, inspectorDescription, toggleInspectorPinned,
                presetDraftName, presetVisibility, templateVisibility, currentBlockPresets, saveCurrentBlockPreset, applyBlockPreset, insertPresetAfterSelection, deleteBlockPreset, formatPresetDate,
                mediaLookup, showMediaPicker, mediaPickerQuery, mediaPickerPage, mediaPickerLastPage, mediaPickerItems, mediaPickerLoading, mediaPickerSelected,
                openMediaPicker, closeMediaPicker, selectMediaItem, applyPickedMedia, changeMediaPickerPage,
                breakpoints, revisions, canUndo, canRedo, canvasClass, categories,
                autoSaveStatus, autoSaveStatusText, currentHistoryLabel, previewHtml, previewBreakpoint,
                allBlocks, filteredBlocks, quickAddBlocks, quickAddItems, quickAddMode, blockLabel, sectionCanvasStyle, draggedSectionIndex, dropSectionIndex,
                quickAddSectionIndex, quickAddInsertIndex, quickAddQuery, runQuickAddItem, renderQuickAddPreview,
                addBlock, addBlockToSection, deleteSection, duplicateSection, moveSectionUp, moveSectionDown,
                moveBlockUp, moveBlockDown, duplicateBlock, deleteBlock,
                insertBlockAt, openQuickAdd, closeQuickAdd, toggleBlockSelection, duplicateSelectedBlocks, deleteSelectedBlocks,
                selectSection, selectBlock, updateBlockSettings, updateSectionSettings,
                openCommandPalette, closeCommandPalette, executeCommand, executeContextCommand,
                openSectionContextMenu, openBlockContextMenu,
                onSectionDragStart, onSectionDragOver, onSectionDrop, onSectionDragEnd,
                onBlockDragStart, onBlockDragOver, onBlockDrop, onBlockDragEnd,
                onSectionBodyDragOver, onSectionBodyDrop, onInsertDragOver, onInsertDrop,
                isDraggedBlock, isBlockDropTarget, isInsertTarget, isBlockSelected, selectedCountForSection, filteredCommandItems,
                saveContent, previewContent, undo, redo, restoreRevision, applyTemplate, saveSelectedSectionAsTemplate, deleteSharedTemplate,
                exportCurrentSections, importSectionsPrompt, generateId, formatDate, countBlocks
            };
        }
    }).mount('#advanced-builder');
</script>
@endpush
