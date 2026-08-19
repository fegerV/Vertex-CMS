<template>
    <div class="vc-builder-shell vc-builder-shell-modern">
        <header class="vc-builder-appbar">
            <div class="vc-builder-appbar-brand">
                <span class="vc-builder-appbar-logo">V</span>
                <span class="vc-builder-appbar-title-wrap">
                    <span class="vc-builder-appbar-title">Vertex Builder</span>
                    <span class="vc-builder-appbar-subtitle">{{ page.title || 'Страница без названия' }}</span>
                </span>
            </div>

            <div class="vc-builder-appbar-device-group" aria-label="Responsive preview">
                <button
                    v-for="bp in breakpoints"
                    :key="bp.name"
                    type="button"
                    class="vc-builder-appbar-device"
                    :class="{ 'vc-builder-appbar-device-active': activeBreakpoint === bp.name }"
                    @click="activeBreakpoint = bp.name"
                >
                    {{ bp.label }}
                </button>
            </div>

            <div class="vc-builder-appbar-selection">
                <span>{{ selectionSummary.title }}</span>
                <small>{{ selectionSummary.path }}</small>
            </div>

            <div class="vc-builder-appbar-actions">
                <span class="vc-builder-status-chip vc-builder-appbar-status">
                    <span class="vc-builder-status-dot" :class="`vc-builder-status-dot-${autoSaveStatus}`"></span>
                    {{ autoSaveStatusText }}
                </span>
                <button type="button" class="vc-builder-appbar-button" :disabled="!canUndo" @click="undo">Назад</button>
                <button type="button" class="vc-builder-appbar-button" :disabled="!canRedo" @click="redo">Вперёд</button>
                <button type="button" class="vc-builder-appbar-button" :class="{ 'vc-builder-appbar-button-active': canvasMode === 'live' }" @click="canvasMode = 'live'">Сайт</button>
                <button type="button" class="vc-builder-appbar-button" :class="{ 'vc-builder-appbar-button-active': canvasMode === 'edit' }" @click="canvasMode = 'edit'">Редактор</button>
                <button type="button" class="vc-builder-appbar-button" @click="previewContent">Просмотр</button>
                <button type="button" class="vc-builder-appbar-button" @click="openDesignLibrary">Библиотека</button>
                <button type="button" class="vc-builder-appbar-save" :disabled="saving" @click="saveContent">
                    {{ saving ? 'Сохраняю…' : 'Сохранить' }}
                </button>
            </div>
        </header>

        <aside class="vc-builder-sidebar vc-builder-shell-pane vc-builder-shell-pane-left vc-builder-scroll" :class="`vc-builder-sidebar-mode-${sidebarMode}`">
            <div class="vc-builder-sidebar-stack">
                <section class="vc-builder-surface-card">
                    <div class="vc-builder-sidebar-header">
                        <div>
                            <p class="vc-builder-eyebrow">UX Builder</p>
                            <h2 class="vc-builder-sidebar-title">{{ page.title || 'Страница без названия' }}</h2>
                            <p class="vc-builder-sidebar-copy">{{ page.uri || ('/' + (page.slug || 'page')) }}</p>
                        </div>
                        <div class="vc-builder-sidebar-meta">
                            <span class="vc-builder-stat-pill">{{ sections.length }} секц.</span>
                            <span class="vc-builder-stat-pill">{{ totalCanvasBlocks }} блоков</span>
                        </div>
                    </div>

                    <div class="vc-builder-sidebar-mode-switch">
                        <button
                            v-for="mode in sidebarModes"
                            :key="mode.id"
                            type="button"
                            class="vc-builder-segment"
                            :class="{ 'vc-builder-segment-active': sidebarMode === mode.id }"
                            @click="sidebarMode = mode.id"
                        >
                            {{ mode.label }}
                        </button>
                    </div>
                </section>

                <section v-if="sidebarMode === 'structure'" class="vc-builder-surface-card">
                    <div class="vc-builder-panel-heading">
                        <div>
                            <p class="vc-builder-eyebrow">Навигатор</p>
                            <h3 class="vc-builder-panel-subtitle">Структура страницы</h3>
                        </div>
                        <button type="button" class="vc-builder-chip" @click="sidebarMode = 'library'">Добавить</button>
                    </div>

                    <div v-if="sections.length === 0" class="vc-builder-empty p-5 text-sm">
                        Добавьте первую секцию, чтобы начать собирать страницу.
                    </div>

                    <div v-else class="vc-builder-structure-list">
                        <div class="vc-builder-structure-root">
                            <span class="vc-builder-structure-root-icon">P</span>
                            <span class="vc-builder-structure-root-copy">
                                <strong>{{ page.title || 'Страница без названия' }}</strong>
                                <span>{{ page.uri || ('/' + (page.slug || 'page')) }}</span>
                            </span>
                        </div>

                        <article
                            v-for="(section, sIndex) in sections"
                            :key="section.id"
                            class="vc-builder-structure-card"
                            :class="{
                                'vc-builder-structure-card-active': selectedSection === sIndex,
                                'vc-builder-structure-card-dragging': draggedSectionIndex === sIndex,
                                'vc-builder-structure-card-drop': dropSectionIndex === sIndex && draggedSectionIndex !== null && draggedSectionIndex !== sIndex,
                            }"
                            @dragover.prevent="onSectionDragOver(sIndex)"
                            @drop.prevent="onSectionDrop(sIndex)"
                        >
                            <div class="vc-builder-structure-section">
                                <span
                                    class="vc-builder-structure-drag"
                                    draggable="true"
                                    title="Перетащить секцию"
                                    aria-label="Перетащить секцию"
                                    @dragstart="onSectionDragStart(sIndex, $event)"
                                    @dragend="onSectionDragEnd"
                                >⋮⋮</span>
                                <button
                                    type="button"
                                    class="vc-builder-structure-toggle"
                                    :class="{ 'vc-builder-structure-toggle-open': isSectionExpanded(sIndex) }"
                                    @click="toggleSectionExpanded(sIndex)"
                                >
                                    <svg viewBox="0 0 20 20" fill="none">
                                        <path d="M7 4l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="vc-builder-structure-section-main"
                                    @click="selectSection(sIndex)"
                                >
                                    <span class="vc-builder-structure-title-wrap">
                                        <span class="vc-builder-technical-label">SEC {{ String(sIndex + 1).padStart(2, '0') }}</span>
                                        <span class="vc-builder-structure-title">{{ sectionStructureLabel(section, sIndex) }}</span>
                                    </span>
                                    <span class="vc-builder-structure-meta">
                                        <span class="vc-builder-structure-count">{{ section.blocks.length }}</span>
                                        <span>блоков</span>
                                    </span>
                                </button>
                            </div>

                            <div v-if="isSectionExpanded(sIndex)" class="vc-builder-structure-children">
                                <button
                                    v-for="(block, bIndex) in section.blocks"
                                    :key="block.id"
                                    type="button"
                                    class="vc-builder-structure-block"
                                    :class="{
                                        'vc-builder-structure-block-active': selectedSection === sIndex && selectedBlock === bIndex,
                                        'vc-builder-structure-block-dragging': isDraggedBlock(sIndex, bIndex),
                                        'vc-builder-structure-block-drop': isBlockDropTarget(sIndex, bIndex),
                                    }"
                                    draggable="true"
                                    @click="selectBlock(sIndex, bIndex)"
                                    @dragstart.stop="onBlockDragStart(sIndex, bIndex, $event)"
                                    @dragend="onBlockDragEnd"
                                    @dragover.prevent.stop="onBlockDragOver(sIndex, bIndex)"
                                    @drop.prevent.stop="onBlockDrop(sIndex, bIndex)"
                                >
                                    <span class="vc-builder-structure-node-rail"></span>
                                    <span class="vc-builder-library-card-mark" aria-hidden="true">
                                        <svg viewBox="0 0 20 20" fill="none" v-html="blockIconPath(block.type)"></svg>
                                    </span>
                                    <span class="min-w-0 flex-1 text-left">
                                        <span class="block text-sm font-semibold text-[var(--vc-text)]">{{ blockLabel(block.type) }}</span>
                                        <span class="block text-xs text-[var(--vc-text-soft)]">{{ block.type }}</span>
                                    </span>
                                    <span class="vc-builder-structure-inline-meta">{{ String(bIndex + 1).padStart(2, '0') }}</span>
                                </button>

                                <button
                                    type="button"
                                    class="vc-builder-structure-add"
                                    :class="{ 'vc-builder-structure-add-drop': isInsertTarget(sIndex, section.blocks.length) }"
                                    @click="openQuickAdd(sIndex, section.blocks.length)"
                                    @dragover.prevent.stop="onInsertDragOver(sIndex, section.blocks.length)"
                                    @drop.prevent.stop="onInsertDrop(sIndex, section.blocks.length)"
                                >
                                    Добавить блок
                                </button>
                            </div>
                        </article>
                    </div>
                </section>

                <section v-else-if="sidebarMode === 'library'" class="vc-builder-surface-card">
                    <div class="vc-builder-panel-heading">
                        <div>
                            <p class="vc-builder-eyebrow">Добавление</p>
                            <h3 class="vc-builder-panel-subtitle">Блоки страницы</h3>
                        </div>
                        <span class="vc-builder-stat-pill">{{ totalBlockTypes }} типов</span>
                    </div>

                    <div class="vc-builder-tab-row mt-4">
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
                            placeholder="Поиск блоков"
                            class="vc-input"
                        >
                    </div>

                    <div class="vc-builder-library-filters mt-3">
                        <select v-model="librarySourceFilter" class="vc-select">
                            <option value="all">Все источники</option>
                            <option value="native">Native Vertex</option>
                            <option value="breakdance-native">Breakdance-native</option>
                            <option value="breakdance-reference">Breakdance-reference</option>
                        </select>
                        <select v-model="libraryCategoryFilter" class="vc-select">
                            <option value="all">Все категории</option>
                            <option v-for="category in libraryCategoryOptions" :key="category" :value="category">{{ categoryLabel(category) }}</option>
                        </select>
                    </div>

                    <div class="mt-4 space-y-3">
                        <button
                            v-for="[type, block] in filteredBlockEntries"
                            :key="type"
                            type="button"
                            class="vc-builder-library-card"
                            @click="addLibraryBlock(type)"
                        >
                            <span class="vc-builder-library-card-mark" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none" v-html="blockIconPath(type)"></svg>
                            </span>
                            <span class="min-w-0 flex-1 text-left">
                                <span class="block text-sm font-semibold text-[var(--vc-text)]">{{ block.name }}</span>
                                <span class="mt-1 block text-xs text-[var(--vc-text-soft)]">{{ block.description || 'Добавьте блок в выбранную секцию или начните новую структуру страницы.' }}</span>
                                <span class="mt-2 flex flex-wrap gap-2 text-[11px] text-[var(--vc-text-soft)]">
                                    <span class="vc-builder-library-pill">{{ categoryLabel(block.category) }}</span>
                                    <span class="vc-builder-library-pill">{{ blockSourceLabel(block) }}</span>
                                </span>
                            </span>
                            <span class="vc-builder-library-card-add">+</span>
                        </button>

                        <div v-if="filteredBlockEntries.length === 0" class="vc-builder-empty p-5 text-center text-sm">
                            Ничего не найдено по этому фильтру.
                        </div>
                    </div>
                </section>

                <section v-else class="vc-builder-surface-card">
                    <div class="vc-builder-panel-heading">
                        <div>
                            <p class="vc-builder-eyebrow">Шаблоны</p>
                            <h3 class="vc-builder-panel-subtitle">Секции и стартовые макеты</h3>
                        </div>
                        <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': showTemplates }" @click="showTemplates = !showTemplates">
                            {{ showTemplates ? 'Скрыть' : 'Показать' }}
                        </button>
                    </div>

                    <div v-if="showTemplates" class="mt-4 space-y-4">
                        <div class="vc-builder-tab-row">
                            <button type="button" class="vc-builder-tab" :class="{ 'vc-builder-tab-active': templateLibraryScope === 'all' }" @click="templateLibraryScope = 'all'">Все</button>
                            <button type="button" class="vc-builder-tab" :class="{ 'vc-builder-tab-active': templateLibraryScope === 'mine' }" @click="templateLibraryScope = 'mine'">Мои</button>
                            <button type="button" class="vc-builder-tab" :class="{ 'vc-builder-tab-active': templateLibraryScope === 'shared' }" @click="templateLibraryScope = 'shared'">Общие</button>
                            <button type="button" class="vc-builder-tab" :class="{ 'vc-builder-tab-active': templateLibraryScope === 'builtin' }" @click="templateLibraryScope = 'builtin'">Встроенные</button>
                        </div>

                        <div class="space-y-3">
                            <input
                                v-model="templateSearchQuery"
                                type="text"
                                placeholder="Поиск шаблонов"
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
                                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--vc-text-soft)]">{{ group.items.length }} шт.</div>
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
                                            <span class="mt-2 block text-xs text-[var(--vc-text-soft)]">{{ tpl.description || 'Готовый стартовый макет или секционный шаблон для текущего JSON-first builder.' }}</span>
                                            <span class="mt-3 flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--vc-text-soft)]">
                                                <span>{{ tpl.sections_count || 0 }} секций</span>
                                                <span>&middot;</span>
                                                <span>{{ tpl.blocks_count || 0 }} блоков</span>
                                                <span v-if="tpl.owner">&middot;</span>
                                                <span v-if="tpl.owner">{{ tpl.owner }}</span>
                                            </span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="vc-builder-empty p-5 text-center text-sm">
                            Шаблоны по этому фильтру не найдены.
                        </div>

                        <div class="space-y-2">
                            <input v-model="templateDraftName" type="text" class="vc-input" placeholder="Название шаблона">
                            <div class="grid grid-cols-2 gap-2">
                                <input v-model="templateDraftCategory" type="text" class="vc-input" placeholder="Категория">
                                <select v-model="templateVisibility" class="vc-select">
                                    <option value="shared">Общий</option>
                                    <option value="private">Приватный</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" class="vc-button vc-button-secondary flex-1" @click="saveSelectedSectionAsTemplate">Сохранить секцию</button>
                                <button type="button" class="vc-button vc-button-secondary" @click="openLibraryManager('templates')">Управлять</button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </aside>

        <main class="vc-builder-shell-pane vc-builder-shell-pane-main">
            <div class="vc-builder-toolbar vc-builder-toolbar-modern">
                <div class="vc-builder-toolbar-main vc-builder-toolbar-main-editor">
                    <div class="vc-builder-toolbar-group vc-builder-toolbar-group-selection">
                        <p class="vc-builder-eyebrow">Редактор</p>
                    <div class="vc-builder-selection-summary">
                        <div class="vc-builder-selection-title">{{ selectionSummary.title }}</div>
                        <div class="vc-builder-selection-copy">{{ selectionSummary.copy }}</div>
                        <div class="vc-builder-selection-path">{{ selectionSummary.path }}</div>
                    </div>
                </div>

                    <div class="vc-builder-toolbar-group vc-builder-toolbar-group-breakpoints">
                        <p class="vc-builder-eyebrow">Устройство</p>
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

                    <div class="vc-builder-toolbar-group vc-builder-toolbar-group-history">
                        <p class="vc-builder-eyebrow">Навигация</p>
                        <div class="mt-2 flex items-center gap-2">
                            <button type="button" class="vc-button vc-button-secondary" :disabled="!canUndo" @click="undo">Назад</button>
                            <button type="button" class="vc-button vc-button-secondary" :disabled="!canRedo" @click="redo">Вперёд</button>
                        </div>
                    </div>

                    <div class="vc-builder-toolbar-group vc-builder-toolbar-group-search">
                        <p class="vc-builder-eyebrow">Поиск по странице</p>
                        <input
                            v-model="contentSearchQuery"
                            type="text"
                            placeholder="Текст, тип блока или структура"
                            class="vc-input mt-2"
                        >
                    </div>
                </div>

                <div class="vc-builder-toolbar-actions">
                    <span class="vc-builder-status-chip">
                        <span class="vc-builder-status-dot" :class="`vc-builder-status-dot-${autoSaveStatus}`"></span>
                        {{ autoSaveStatusText }}
                    </span>
                    <span class="vc-builder-history-chip">{{ currentHistoryLabel || 'Текущее состояние' }}</span>
                    <span class="vc-builder-history-chip vc-builder-history-chip-quiet">{{ selectionSummary.path }}</span>
                    <div class="vc-builder-toolbar-action-group">
                        <button type="button" class="vc-button vc-button-secondary vc-builder-toolbar-tool" :class="{ 'vc-builder-toolbar-tool-active': canvasMode === 'live' }" @click="canvasMode = 'live'">Live</button>
                        <button type="button" class="vc-button vc-button-secondary vc-builder-toolbar-tool" :class="{ 'vc-builder-toolbar-tool-active': canvasMode === 'edit' }" @click="canvasMode = 'edit'">Редактор</button>
                        <button type="button" class="vc-button vc-button-secondary vc-builder-toolbar-tool" @click="showRevisions = true">Ревизии</button>
                        <button type="button" class="vc-button vc-button-secondary vc-builder-toolbar-tool" @click="exportCurrentSections">Экспорт</button>
                        <button type="button" class="vc-button vc-button-secondary vc-builder-toolbar-tool" @click="importSectionsPrompt">Импорт</button>
                        <button type="button" class="vc-button vc-button-secondary vc-builder-toolbar-tool" @click="previewContent">Preview</button>
                        <button type="button" class="vc-button vc-button-secondary vc-builder-toolbar-tool" @click="openDesignLibrary">Библиотека</button>
                    </div>
                    <button type="button" class="vc-button vc-button-primary vc-builder-save-action vc-builder-toolbar-tool-primary" :disabled="saving" @click="saveContent">
                        {{ saving ? 'Сохранение...' : 'Сохранить страницу' }}
                    </button>
                </div>
            </div>

            <div class="vc-builder-canvas vc-builder-canvas-modern">
                <div class="vc-builder-viewport">
                    <div class="vc-builder-page-frame" :style="pageFrameStyle">
                        <header class="vc-builder-page-hero">
                            <div>
                                <div class="vc-builder-page-kicker">
                                    <span class="vc-builder-technical-label vc-builder-technical-label-active">PAGE</span>
                                    <p class="vc-builder-eyebrow">Холст</p>
                                </div>
                                <h1 class="vc-builder-page-title">{{ page.title || 'Страница без названия' }}</h1>
                                <p class="vc-builder-page-meta">{{ page.uri || ('/' + (page.slug || 'page')) }}</p>
                            </div>
                            <div class="vc-builder-page-stats">
                                <span class="vc-builder-page-status">{{ autoSaveStatusText }}</span>
                                <span class="vc-builder-stat-pill">{{ sections.length }} секций</span>
                                <span class="vc-builder-stat-pill">{{ totalCanvasBlocks }} блоков</span>
                            </div>
                        </header>

                        <div v-if="sections.length === 0" class="vc-builder-empty-state">
                            <div class="vc-builder-empty-state-card">
                                <div class="vc-builder-empty-state-grid">
                                    <div class="vc-builder-empty-state-main">
                                        <p class="vc-builder-eyebrow">Старт</p>
                                        <h2 class="text-2xl font-semibold text-[var(--vc-text)]">Соберите первую секцию</h2>
                                        <p class="mt-3 text-sm text-[var(--vc-text-soft)]">Начните с базового блока или выберите готовый шаблон секции. Всё сохранится в <code>content_json</code> без смены контракта страницы.</p>
                                        <div class="mt-5 flex flex-wrap gap-3">
                                            <button type="button" class="vc-button vc-button-primary" @click="createStarterPage">Создать стартовую секцию</button>
                                            <button type="button" class="vc-button vc-button-secondary" @click="sidebarMode = 'templates'; showTemplates = true">Открыть шаблоны</button>
                                        </div>
                                    </div>
                                    <div class="vc-builder-empty-state-aside">
                                        <div class="vc-builder-empty-state-note">
                                            <span class="vc-builder-badge vc-builder-badge-active">Быстрый старт</span>
                                            <div class="mt-3 space-y-3 text-sm text-[var(--vc-text-soft)]">
                                                <p><strong class="text-[var(--vc-text)]">1.</strong> Выберите блок слева, чтобы сразу добавить его на страницу.</p>
                                                <p><strong class="text-[var(--vc-text)]">2.</strong> Используйте шаблоны для hero, CTA и готовых секций.</p>
                                                <p><strong class="text-[var(--vc-text)]">3.</strong> После добавления настройте контент и стили в inspector справа.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="canvasMode === 'live'" class="vc-builder-live-stage">
                            <div class="vc-builder-live-toolbar">
                                <span class="vc-builder-technical-label vc-builder-technical-label-active">LIVE HTML</span>
                                <span>{{ livePreviewLoading ? 'Обновление...' : 'Рендер страницы' }}</span>
                                <button type="button" class="vc-builder-chip" @click="refreshLivePreview">Обновить</button>
                            </div>
                            <div v-if="livePreviewError" class="vc-builder-live-error">
                                Ошибка live-preview: {{ livePreviewError }}
                            </div>
                            <div v-else-if="livePreviewLoading && !livePreviewDocument" class="vc-builder-live-loading">
                                Собираю живой preview страницы...
                            </div>
                            <iframe
                                ref="livePreviewFrame"
                                v-else
                                class="vc-builder-live-frame"
                                title="Live page preview"
                                :srcdoc="livePreviewSrcdoc"
                                sandbox="allow-same-origin allow-scripts allow-forms allow-popups"
                                @load="syncLivePreviewSelection()"
                            ></iframe>
                        </div>

                        <div v-else class="vc-builder-stage vc-builder-stage-modern">
                            <section
                                v-for="(section, sIndex) in sections"
                                :key="section.id"
                                class="vc-section vc-builder-section vc-builder-section-modern group"
                                :class="{
                                    'vc-builder-section-active': selectedSection === sIndex,
                                    'vc-builder-dragging': draggedSectionIndex === sIndex,
                                    'vc-builder-drop-target': dropSectionIndex === sIndex && draggedSectionIndex !== null && draggedSectionIndex !== sIndex,
                                    'vc-builder-controls-always-visible': sectionToolbarVisibility === 'always',
                                }"
                                :style="sectionCanvasStyle(section)"
                                @click="selectSection(sIndex)"
                                @contextmenu.prevent="openSectionContextMenu(sIndex, $event)"
                                @dragover.prevent="onSectionDragOver(sIndex)"
                                @drop.prevent="onSectionDrop(sIndex)"
                            >
                                <div class="vc-builder-section-toolbar">
                                    <div class="vc-builder-section-labels">
                                        <span class="vc-builder-technical-label vc-builder-technical-label-active">SEC {{ String(sIndex + 1).padStart(2, '0') }}</span>
                                        <span class="vc-builder-toolbar-tag">BLK {{ String(section.blocks.length).padStart(2, '0') }}</span>
                                        <span v-if="sectionPresetLabel(section)" class="vc-builder-toolbar-tag">{{ sectionPresetLabel(section) }}</span>
                                        <span v-else-if="section.settings?.background_color" class="vc-builder-toolbar-tag">CUSTOM BG</span>
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

                                <div class="vc-builder-section-body vc-builder-section-body-modern vc-container">
                                    <div class="vc-builder-insert-slot vc-builder-insert-slot-modern" :class="{ 'vc-builder-insert-slot-active': isInsertTarget(sIndex, 0) }" @dragover.prevent="onInsertDragOver(sIndex, 0)" @drop.prevent="onInsertDrop(sIndex, 0)">
                                        <button type="button" class="vc-builder-insert-button" @click.stop="openQuickAdd(sIndex, 0)">
                                            <span class="vc-builder-insert-button-icon">+</span>
                                            <span>Добавить первый блок</span>
                                        </button>
                                    </div>

                                    <div
                                        v-if="quickAddSectionIndex === sIndex && quickAddInsertIndex === 0"
                                        class="vc-builder-quick-add vc-builder-quick-add-modern"
                                    >
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'blocks' }" @click="quickAddMode = 'blocks'">Блоки</button>
                                            <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'presets' }" @click="quickAddMode = 'presets'">Пресеты</button>
                                            <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'templates' }" @click="quickAddMode = 'templates'">Шаблоны</button>
                                            <button type="button" class="vc-builder-chip" @click="closeQuickAdd">Закрыть</button>
                                        </div>
                                        <input v-model="quickAddQuery" type="text" class="vc-input" placeholder="Найти блок, пресет или шаблон">
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
                                            @dblclick.stop="inlineEditingEnabled(block.type) ? openInlineEdit(sIndex, bIndex) : undefined"
                                            @contextmenu.prevent="openBlockContextMenu(sIndex, bIndex, $event)"
                                            @dragstart="onBlockDragStart(sIndex, bIndex, $event)"
                                            @dragend="onBlockDragEnd"
                                            @dragover.prevent="onBlockDragOver(sIndex, bIndex)"
                                            @drop.prevent="onBlockDrop(sIndex, bIndex)"
                                        >
                                            <div class="vc-builder-block-head">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <span class="vc-builder-block-mark" aria-hidden="true">
                                                        <svg viewBox="0 0 20 20" fill="none" v-html="blockIconPath(block.type)"></svg>
                                                    </span>
                                                    <div class="min-w-0 vc-builder-block-copy">
                                                        <div class="vc-builder-block-kicker">
                                                            <span class="vc-builder-technical-label">BLK {{ String(bIndex + 1).padStart(2, '0') }}</span>
                                                        </div>
                                                        <div class="vc-builder-block-title">{{ blockLabel(block.type) }}</div>
                                                        <div class="vc-builder-meta truncate">{{ block.type }}</div>
                                                    </div>
                                                </div>

                                                <div class="vc-builder-action-cluster vc-builder-floating-controls vc-builder-block-actions" :class="{ 'vc-builder-controls-always-visible': blockToolbarVisibility(block.type) === 'always' }">
                                                    <button
                                                        v-if="inlineEditingEnabled(block.type)"
                                                        type="button"
                                                        class="vc-builder-action-button"
                                                        :title="inlineEditingLabel(block.type)"
                                                        :aria-label="inlineEditingLabel(block.type)"
                                                        @click.stop="openInlineEdit(sIndex, bIndex)"
                                                    >
                                                        <svg viewBox="0 0 20 20" fill="none">
                                                            <path d="M5 15l1.2-4.2L14.4 2.6a1.6 1.6 0 012.3 2.3L8.5 13.1 5 15z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                                            <path d="M12.9 4.1l3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                                                        </svg>
                                                    </button>
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

                                            <div
                                                v-if="isInlineTextEditorVisible(sIndex, bIndex, block)"
                                                class="vc-builder-inline-text-editor"
                                                @click.stop
                                            >
                                                <textarea
                                                    v-if="inlinePrimaryField(block.type) === 'content'"
                                                    class="vc-builder-inline-textarea"
                                                    :value="inlineEditorValue(block)"
                                                    rows="5"
                                                    @input="updateInlineEditorValue(sIndex, bIndex, block, $event.target.value)"
                                                ></textarea>
                                                <input
                                                    v-else
                                                    class="vc-builder-inline-input"
                                                    :value="inlineEditorValue(block)"
                                                    type="text"
                                                    @input="updateInlineEditorValue(sIndex, bIndex, block, $event.target.value)"
                                                >
                                            </div>

                                            <div class="vc-builder-block-preview">
                                                <BuilderBlockRenderer :type="block.type" :settings="block.settings" :registry="allBlocks" :editable="true" />
                                            </div>
                                        </article>

                                        <div class="vc-builder-insert-slot vc-builder-insert-slot-modern" :class="{ 'vc-builder-insert-slot-active': isInsertTarget(sIndex, bIndex + 1) }" @dragover.prevent="onInsertDragOver(sIndex, bIndex + 1)" @drop.prevent="onInsertDrop(sIndex, bIndex + 1)">
                                            <button type="button" class="vc-builder-insert-button" @click.stop="openQuickAdd(sIndex, bIndex + 1)">
                                                <span class="vc-builder-insert-button-icon">+</span>
                                                <span>Вставить сюда</span>
                                            </button>
                                        </div>

                                        <div
                                            v-if="quickAddSectionIndex === sIndex && quickAddInsertIndex === bIndex + 1"
                                            class="vc-builder-quick-add vc-builder-quick-add-modern"
                                        >
                                            <div class="flex flex-wrap items-center gap-2">
                                                <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'blocks' }" @click="quickAddMode = 'blocks'">Блоки</button>
                                                <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'presets' }" @click="quickAddMode = 'presets'">Пресеты</button>
                                                <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': quickAddMode === 'templates' }" @click="quickAddMode = 'templates'">Шаблоны</button>
                                                <button type="button" class="vc-builder-chip" @click="closeQuickAdd">Закрыть</button>
                                            </div>
                                            <input v-model="quickAddQuery" type="text" class="vc-input" placeholder="Найти блок, пресет или шаблон">
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
                    <p class="vc-builder-panel-title">Инспектор</p>
                    <h3 class="mt-3 text-xl font-semibold text-[var(--vc-text)]">{{ inspectorTitle }}</h3>
                    <p class="mt-2 text-sm text-[var(--vc-text-soft)]">{{ inspectorDescription }}</p>
                </div>
                <button type="button" class="vc-builder-chip" :class="{ 'vc-builder-chip-active': inspectorPinned }" @click="toggleInspectorPinned">
                    {{ inspectorPinned ? 'Закреплён' : 'Закрепить' }}
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
                        <p class="vc-builder-eyebrow">Выбор</p>
                        <div class="mt-3 flex items-center gap-3">
                            <span class="vc-builder-block-mark">{{ blockMark(selectedBlockData.type) }}</span>
                            <div>
                                <div class="text-sm font-semibold text-[var(--vc-text)]">{{ blockLabel(selectedBlockData.type) }}</div>
                                <div class="text-xs text-[var(--vc-text-soft)]">{{ selectedBlockData.type }}</div>
                            </div>
                        </div>
                        <div v-if="inlineEditingEnabled(selectedBlockData.type)" class="mt-4 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-3 text-sm text-[var(--vc-text-soft)]">
                            <div class="font-semibold text-[var(--vc-text)]">{{ inlineEditingLabel(selectedBlockData.type) }}</div>
                            <div class="mt-1">{{ inlineEditingDescription(selectedBlockData.type) }}</div>
                        </div>
                    </section>

                    <section v-if="selectedSection !== null" class="vc-builder-surface-card vc-builder-inspector-outline">
                        <p class="vc-builder-eyebrow">Контекст</p>
                        <div class="mt-3 space-y-2 text-sm">
                            <div class="vc-builder-inspector-outline-row">
                                <span>Секция</span>
                                <strong>{{ selectedSection + 1 }}</strong>
                            </div>
                            <div class="vc-builder-inspector-outline-row">
                                <span>Блоков</span>
                                <strong>{{ sections[selectedSection]?.blocks?.length || 0 }}</strong>
                            </div>
                            <div v-if="selectedBlockData" class="vc-builder-inspector-outline-row">
                                <span>Активный блок</span>
                                <strong>{{ blockLabel(selectedBlockData.type) }}</strong>
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
                        :highlight-fields="inlineEditingHighlightFields"
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
                        <p class="text-sm font-semibold text-[var(--vc-text)]">Выберите секцию или блок</p>
                        <p class="mt-2 text-sm text-[var(--vc-text-soft)]">Здесь появятся настройки контента, стилей и расширенных параметров для выбранного элемента.</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" class="vc-button vc-button-primary" @click="createStarterPage">Создать секцию</button>
                            <button type="button" class="vc-button vc-button-secondary" @click="sidebarMode = 'templates'; showTemplates = true">Шаблоны</button>
                        </div>
                        <div class="mt-4 grid gap-3">
                            <div class="vc-builder-shortcut-card">
                                <span class="vc-builder-shortcut-label">Ctrl + S</span>
                                <span class="vc-builder-shortcut-copy">Сохранить изменения</span>
                            </div>
                            <div class="vc-builder-shortcut-card">
                                <span class="vc-builder-shortcut-label">A</span>
                                <span class="vc-builder-shortcut-copy">Открыть быстрое добавление</span>
                            </div>
                            <div class="vc-builder-shortcut-card">
                                <span class="vc-builder-shortcut-label">R</span>
                                <span class="vc-builder-shortcut-copy">Открыть ревизии</span>
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
                        <p class="vc-builder-eyebrow">Предпросмотр</p>
                        <h3 class="text-lg font-semibold text-[var(--vc-text)]">{{ page.title }}</h3>
                    </div>
                    <button type="button" class="vc-button vc-button-secondary" @click="showPreview = false">Закрыть</button>
                </div>
                <div class="max-h-[75vh] overflow-auto bg-white p-6" v-html="previewHtml"></div>
            </div>
        </div>

        <div v-if="showRevisions" class="vc-builder-modal fixed inset-0 z-50 flex items-center justify-center p-6" @click.self="showRevisions = false">
            <div class="vc-builder-modal-card w-full max-w-4xl overflow-hidden">
                <div class="flex items-center justify-between border-b border-[var(--vc-border)] px-6 py-4">
                    <div>
                        <p class="vc-builder-eyebrow">История</p>
                        <h3 class="text-lg font-semibold text-[var(--vc-text)]">Ревизии страницы</h3>
                    </div>
                    <button type="button" class="vc-button vc-button-secondary" @click="showRevisions = false">Закрыть</button>
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
                            <button type="button" class="vc-button vc-button-secondary" @click="restoreRevision(rev)">Восстановить</button>
                        </div>
                        <div v-if="revisions.length === 0" class="vc-builder-empty p-6 text-center">Ревизий пока нет.</div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showCommandPalette" class="vc-builder-modal fixed inset-0 z-50 flex items-start justify-center p-6 pt-16" @click.self="closeCommandPalette">
            <div class="vc-builder-modal-card w-full max-w-2xl overflow-hidden">
                <div class="border-b border-[var(--vc-border)] p-4">
                    <input ref="commandPaletteInput" v-model="commandQuery" type="text" class="vc-input" placeholder="Поиск команды, действия или сочетания">
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
                        <h3 class="text-lg font-semibold text-[var(--vc-text)]">Выбрать медиа</h3>
                    </div>
                    <button type="button" class="vc-button vc-button-secondary" @click="closeMediaPicker">Закрыть</button>
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

const cloneBuilderValue = (value) => {
    if (typeof structuredClone === 'function') {
        try {
            return structuredClone(value);
        } catch (error) {
            // Vue proxies and some browser objects cannot be cloned natively.
        }
    }

    return JSON.parse(JSON.stringify(value));
};

const sections = ref(cloneBuilderValue(props.initialSections || []));
const activeBreakpoint = ref('desktop');
const searchQuery = ref('');
const templateSearchQuery = ref('');
const contentSearchQuery = ref('');
const librarySourceFilter = ref('all');
const libraryCategoryFilter = ref('all');
const selectedSection = ref(null);
const selectedBlock = ref(null);
const selectedBlockData = ref(null);
const showTemplates = ref(true);
const autoSaveTimer = ref(null);
const livePreviewTimer = ref(null);
const contentSearchTimer = ref(null);
const contentSearchIndexTimer = ref(null);
const contentRevision = ref(0);
const contentSearchIndex = ref([]);
const livePreviewFrame = ref(null);
const canvasMode = ref('live');
const sidebarMode = ref('structure');
const expandedSections = ref([]);
const libraryTab = ref('content');
const inspectorTab = ref('content');

const breakpoints = ref(props.config.breakpoints || [
    { name: 'desktop', label: 'Компьютер', width: '100%', maxWidth: 'none' },
    { name: 'tablet', label: 'Планшет', width: '860px', maxWidth: '860px' },
    { name: 'mobile', label: 'Телефон', width: '430px', maxWidth: '430px' },
]);
const sectionConfig = computed(() => props.config.sections || {});
const allBlocks = ref(props.config.blocks || window.availableBlocks || {});

const totalBlockTypes = computed(() => Object.keys(allBlocks.value || {}).length);
const totalCanvasBlocks = computed(() => sections.value.reduce((sum, section) => sum + (section.blocks?.length || 0), 0));
const libraryCategoryOptions = computed(() => {
    const categories = new Set();

    Object.entries(allBlocks.value || {}).forEach(([type, block]) => {
        if (resolveLibraryTab(type, block) !== libraryTab.value) return;
        if (block?.category) categories.add(String(block.category));
    });

    return Array.from(categories).sort((left, right) => left.localeCompare(right));
});

const libraryTabs = [
    { id: 'content', label: 'Контент' },
    { id: 'media', label: 'Медиа' },
    { id: 'layout', label: 'Сетка' },
    { id: 'dynamic', label: 'Динамика' },
];

const sidebarModes = [
    { id: 'structure', label: 'Структура' },
    { id: 'library', label: 'Добавить' },
    { id: 'templates', label: 'Шаблоны' },
];

const templateCategoryLabel = (value) => {
    if (!value) return 'Общее';

    return String(value)
        .replace(/[-_]+/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
};

const templateScopeLabel = (template) => {
    if (template.source === 'builtin') return 'Встроенный';
    if (template.visibility === 'private') return 'Приватный';
    return 'Общий';
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

const categoryLabel = (value) => {
    if (!value) return 'General';

    return String(value)
        .replace(/[-_]+/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
};

const blockSource = (block = {}) => {
    return block?.editor?.source || (String(block?.type || '').startsWith('breakdance-') ? 'breakdance-reference' : 'native');
};

const blockSourceLabel = (block = {}) => {
    return {
        native: 'Vertex',
        'breakdance-native': 'Breakdance native',
        'breakdance-reference': 'Breakdance ref',
    }[blockSource(block)] || 'Catalog';
};

const filteredBlockEntries = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    return Object.entries(allBlocks.value || {})
        .filter(([type, block]) => resolveLibraryTab(type, block) === libraryTab.value)
        .filter(([, block]) => librarySourceFilter.value === 'all' || blockSource(block) === librarySourceFilter.value)
        .filter(([, block]) => libraryCategoryFilter.value === 'all' || String(block.category || '') === libraryCategoryFilter.value)
        .filter(([type, block]) => {
            if (!query) return true;
            return type.toLowerCase().includes(query)
                || String(block.name || '').toLowerCase().includes(query)
                || String(block.description || '').toLowerCase().includes(query);
        })
        .sort((left, right) => {
            const leftBlock = left[1] || {};
            const rightBlock = right[1] || {};
            const sourceCompare = blockSource(leftBlock).localeCompare(blockSource(rightBlock));
            if (sourceCompare !== 0) return sourceCompare;
            const categoryCompare = String(leftBlock.category || '').localeCompare(String(rightBlock.category || ''));
            if (categoryCompare !== 0) return categoryCompare;

            return String(leftBlock.name || left[0]).localeCompare(String(rightBlock.name || right[0]));
        });
});

const defaultBlockIconPath = '<rect x="4" y="4" width="12" height="12" rx="2.4" stroke="currentColor" stroke-width="1.7"/><path d="M7 8h6M7 12h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>';
const blockIconPaths = {
    heading: '<path d="M4 5v10M16 5v10M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M11.5 15h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
    text: '<path d="M5 6h10M5 10h8M5 14h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
    list: '<path d="M8 6h8M8 10h8M8 14h8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 6h.01M4 10h.01M4 14h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>',
    button: '<rect x="4" y="7" width="12" height="6" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M8 10h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
    divider: '<path d="M4 10h12" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>',
    faq: '<path d="M7.5 7.2a2.6 2.6 0 115 1.1c-.7.6-1.5 1-1.5 2.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M10 15h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>',
    image: '<rect x="4" y="5" width="12" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M6.5 13l2.8-3 2 2 1.2-1.2L15 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12.8 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    video: '<rect x="4" y="5" width="12" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M9 8l4 2-4 2V8z" fill="currentColor"/>',
    gallery: '<rect x="3.8" y="6.5" width="9.8" height="8" rx="1.7" stroke="currentColor" stroke-width="1.5"/><path d="M6.4 6.5V5.8A1.8 1.8 0 018.2 4h6a1.8 1.8 0 011.8 1.8v5.1" stroke="currentColor" stroke-width="1.5"/><path d="M5.7 13l2.1-2.2 1.5 1.4 1-1L12 13" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>',
    icon: '<path d="M10 3.8l1.7 3.4 3.8.6-2.8 2.7.7 3.8-3.4-1.8-3.4 1.8.7-3.8-2.8-2.7 3.8-.6L10 3.8z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
    columns: '<rect x="4" y="5" width="5" height="10" rx="1.4" stroke="currentColor" stroke-width="1.6"/><rect x="11" y="5" width="5" height="10" rx="1.4" stroke="currentColor" stroke-width="1.6"/>',
    container: '<rect x="3.8" y="5" width="12.4" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M7 8h6M7 12h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
    spacer: '<path d="M10 4v12M7.5 6.5L10 4l2.5 2.5M7.5 13.5L10 16l2.5-2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
    accordion: '<path d="M5 6h10M5 10h10M5 14h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M14 5l1 1-1 1M14 9l1 1-1 1M14 13l1 1-1 1" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>',
    tabs: '<path d="M4 7h4l1 2h7v6H4V7z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8 7h4l1 2" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
    collapse: '<path d="M5 7h10M7 11h6M9 15h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
    modal: '<rect x="4" y="5" width="12" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M7 8h6M7 11h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
    form: '<rect x="5" y="4" width="10" height="12" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M7.5 8h5M7.5 11h5M7.5 14h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
    'news-feed': '<path d="M5 5h10v10H5z" stroke="currentColor" stroke-width="1.6"/><path d="M7 8h6M7 11h6M7 14h3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>',
    testimonials: '<path d="M5 6h10v6H8l-3 3V6z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 9h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
    counter: '<path d="M5 14V9M10 14V6M15 14v-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4 16h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
    'pricing-table': '<path d="M6 5h8a2 2 0 012 2v8H4V7a2 2 0 012-2z" stroke="currentColor" stroke-width="1.7"/><path d="M7 9h6M7 12h6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
    'product-card': '<path d="M5 7h10l-1 8H6L5 7z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 7a2 2 0 014 0" stroke="currentColor" stroke-width="1.5"/>',
    'product-list': '<path d="M5 6h3v3H5zM5 11h3v3H5zM10 7h5M10 12h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
    alert: '<path d="M10 4l7 12H3L10 4z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M10 8.5v3M10 14h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
    'progress-bar': '<rect x="4" y="8" width="12" height="4" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M6 10h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    breadcrumbs: '<path d="M4 10h3M9 10h3M14 10h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M7 7l3 3-3 3M12 7l3 3-3 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>',
    tooltip: '<path d="M5 6h10v6H9l-3 3v-3H5V6z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 9h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
    'seo-meta': '<path d="M4 6h12M4 10h8M4 14h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M14 10l2 2-2 2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>',
    hero: '<path d="M4 14l3.5-4 2.5 2.5L12 10l4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 6h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
    cart: '<path d="M4 5h2l1 7h8l2-5H7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 15h.01M15 15h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>',
    'breakdance-rich-text': '<path d="M5 6h10M5 10h8M5 14h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
    'breakdance-icon-list': '<path d="M8 6h8M8 10h8M8 14h8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 6l1 1 2-2M4 10l1 1 2-2M4 14l1 1 2-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
    'breakdance-social-icons': '<path d="M5 10a2.5 2.5 0 015 0 2.5 2.5 0 005 0 2.5 2.5 0 015 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M5 10a2.5 2.5 0 005 0 2.5 2.5 0 005 0 2.5 2.5 0 005 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
    'breakdance-logo-list': '<rect x="4" y="5" width="4" height="4" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="12" y="5" width="4" height="4" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="4" y="11" width="4" height="4" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="12" y="11" width="4" height="4" rx="1" stroke="currentColor" stroke-width="1.5"/>',
    'breakdance-search-form': '<circle cx="9" cy="9" r="4" stroke="currentColor" stroke-width="1.7"/><path d="M12.5 12.5L16 16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
};

const blockIconPath = (type) => blockIconPaths[type] || defaultBlockIconPath;

const sectionStructureLabel = (section, index) => sectionPresetLabel(section)
    || section.settings?.css_class
    || section.blocks?.[0]?.settings?.text
    || section.blocks?.[0]?.settings?.title
    || `Секция ${index + 1}`;

const rebuildContentSearchIndex = () => {
    contentSearchIndex.value = sections.value.flatMap((section, sectionIndex) =>
        (section.blocks || []).map((block, blockIndex) => ({
            sectionIndex,
            blockIndex,
            haystack: [
                sectionStructureLabel(section, sectionIndex),
                block.type,
                blockLabel(block.type),
                JSON.stringify(block.settings || {}),
            ].join(' ').toLowerCase(),
        }))
    );
};

const scheduleContentSearchIndexRebuild = (delay = 220) => {
    if (contentSearchIndexTimer.value) {
        clearTimeout(contentSearchIndexTimer.value);
    }

    contentSearchIndexTimer.value = setTimeout(() => {
        rebuildContentSearchIndex();
        contentSearchIndexTimer.value = null;
    }, delay);
};

const isSectionExpanded = (index) => expandedSections.value.includes(index);

const toggleSectionExpanded = (index) => {
    expandedSections.value = isSectionExpanded(index)
        ? expandedSections.value.filter((item) => item !== index)
        : [...expandedSections.value, index];
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
const inlineEditingConfig = (type) => allBlocks.value?.[type]?.editor?.inline_editing || { enabled: false, target_tab: 'content', trigger: null };
const inlineEditingEnabled = (type) => Boolean(inlineEditingConfig(type)?.enabled);
const inlineEditingTriggerLabel = (type) => {
    const trigger = inlineEditingConfig(type)?.trigger;
    if (trigger === 'double-click') return 'Двойной клик';
    if (trigger === 'click') return 'Клик для редактирования';
    return 'Быстрое редактирование';
};
const inlineEditingLabel = (type) => inlineEditingConfig(type)?.label || 'Редактировать контент блока';
const inlineEditingDescription = (type) => inlineEditingConfig(type)?.description || 'Откройте основные поля контента для этого блока.';

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
const inlineEditingHighlightFields = ref([]);

const markContentChanged = () => {
    contentRevision.value++;
};

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
    markContentChanged,
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
    markContentChanged,
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
            meta: `Пресет: ${baseCanvasState.blockLabel(preset.type)}`,
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
            meta: `${templateCategoryLabel(item.category || item.source || 'template')} - ${item.visibility === 'private' ? 'приватный' : 'общий'}`,
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
        meta: blockDef.editor?.quick_add?.hint || blockDef.category || 'Блок',
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
    markContentChanged,
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
    openInlineEdit,
});

closeContextMenuImpl = commandsState.closeContextMenu;

function scheduleLivePreviewRefresh(delay = 900) {
    if (livePreviewTimer.value) clearTimeout(livePreviewTimer.value);
    livePreviewTimer.value = setTimeout(() => {
        if (canvasMode.value === 'live') {
            persistenceState.refreshLivePreview();
        }
    }, delay);
}

function openSectionContextMenu(sIndex, event) {
    baseCanvasState.selectSection(sIndex);
    inlineEditingHighlightFields.value = [];
    commandsState.openSectionContextMenu(sIndex, event);
}

function openBlockContextMenu(sIndex, bIndex, event) {
    baseCanvasState.selectBlock(sIndex, bIndex);
    inlineEditingHighlightFields.value = [];
    commandsState.openBlockContextMenu(sIndex, bIndex, event);
}

function handleLivePreviewMessage(event) {
    if (event.data?.source !== 'vertexcms-builder-live-preview') {
        return;
    }

    if (event.data.type === 'select-section' && Number.isInteger(event.data.sectionIndex)) {
        const index = event.data.sectionIndex;
        if (!sections.value[index]) {
            return;
        }

        baseCanvasState.selectSection(index);
        selectedBlock.value = null;
        selectedBlockData.value = null;
        inspectorTab.value = 'style';
    }

    if (
        (event.data.type === 'select-block' || event.data.type === 'edit-block')
        && Number.isInteger(event.data.sectionIndex)
        && Number.isInteger(event.data.blockIndex)
    ) {
        const sectionIndex = event.data.sectionIndex;
        const blockIndex = event.data.blockIndex;
        if (!sections.value[sectionIndex]?.blocks?.[blockIndex]) {
            return;
        }

        baseCanvasState.selectBlock(sectionIndex, blockIndex);
        inspectorTab.value = 'content';
        if (event.data.type === 'edit-block' && inlineEditingEnabled(sections.value[sectionIndex].blocks[blockIndex].type)) {
            openInlineEdit(sectionIndex, blockIndex);
        }
    }

    if (
        event.data.type === 'open-add'
        && Number.isInteger(event.data.sectionIndex)
        && Number.isInteger(event.data.insertIndex)
    ) {
        const sectionIndex = event.data.sectionIndex;
        const insertIndex = Math.max(0, event.data.insertIndex);
        if (!sections.value[sectionIndex]) {
            return;
        }

        baseCanvasState.openQuickAdd(sectionIndex, Math.min(insertIndex, sections.value[sectionIndex].blocks.length));
        expandedSections.value = [...new Set([...expandedSections.value, sectionIndex])];
        sidebarMode.value = 'library';
    }

    if (
        event.data.type === 'section-action'
        && Number.isInteger(event.data.sectionIndex)
    ) {
        const sectionIndex = event.data.sectionIndex;
        if (!sections.value[sectionIndex]) {
            return;
        }

        baseCanvasState.selectSection(sectionIndex);
        switch (event.data.action) {
            case 'quick-add':
                baseCanvasState.openQuickAdd(sectionIndex, sections.value[sectionIndex].blocks.length);
                expandedSections.value = [...new Set([...expandedSections.value, sectionIndex])];
                sidebarMode.value = 'library';
                break;
            case 'add-section':
                insertSectionAfter(sectionIndex);
                break;
            case 'duplicate':
                duplicateSection(sectionIndex);
                break;
            case 'move-up':
                moveSectionUp(sectionIndex);
                break;
            case 'move-down':
                moveSectionDown(sectionIndex);
                break;
            case 'delete':
                if (window.confirm('Удалить эту секцию?')) {
                    deleteSection(sectionIndex);
                }
                break;
            default:
                break;
        }
    }

    if (
        event.data.type === 'block-action'
        && Number.isInteger(event.data.sectionIndex)
        && Number.isInteger(event.data.blockIndex)
    ) {
        const sectionIndex = event.data.sectionIndex;
        const blockIndex = event.data.blockIndex;
        if (!sections.value[sectionIndex]?.blocks?.[blockIndex]) {
            return;
        }

        baseCanvasState.selectBlock(sectionIndex, blockIndex);
        switch (event.data.action) {
            case 'edit':
                if (inlineEditingEnabled(sections.value[sectionIndex].blocks[blockIndex].type)) {
                    openInlineEdit(sectionIndex, blockIndex);
                }
                break;
            case 'duplicate':
                duplicateBlock(sectionIndex, blockIndex);
                break;
            case 'move-up':
                moveBlockUp(sectionIndex, blockIndex);
                break;
            case 'move-down':
                moveBlockDown(sectionIndex, blockIndex);
                break;
            case 'delete':
                if (window.confirm('Удалить этот блок?')) {
                    deleteBlock(sectionIndex, blockIndex);
                }
                break;
            default:
                break;
        }
    }
}

function openInlineEdit(sIndex, bIndex) {
    const type = sections.value[sIndex]?.blocks?.[bIndex]?.type;
    baseCanvasState.selectBlock(sIndex, bIndex);
    const config = inlineEditingConfig(type);
    inspectorTab.value = config?.target_tab || 'content';
    inlineEditingHighlightFields.value = Array.isArray(config?.fields) ? config.fields : [];
}

const pageFrameStyle = computed(() => {
    const breakpoint = breakpoints.value.find((item) => item.name === activeBreakpoint.value);
    return {
        width: breakpoint?.width || '100%',
        maxWidth: breakpoint?.maxWidth || breakpoint?.width || '1240px',
    };
});

const livePreviewSrcdoc = computed(() => {
    if (!livePreviewDocument.value) {
        return '';
    }

    const bridge = `
        <style>
            [data-vc-live-selected],
            [data-vc-live-block-selected] {
                outline: 2px solid #00a0d2 !important;
                outline-offset: -2px !important;
                box-shadow: inset 0 0 0 1px rgba(255,255,255,.75) !important;
            }
            [data-vc-live-block-selected] {
                outline-color: #00c2a8 !important;
            }
            .vc-section {
                cursor: default;
                transition: outline-color .12s ease, box-shadow .12s ease;
            }
            .vc-live-block {
                position: relative;
                cursor: pointer;
                min-height: 1px;
                transition: outline-color .12s ease, box-shadow .12s ease;
            }
            .vc-live-block[data-vc-block-depth="0"]:hover::before,
            .vc-live-block[data-vc-live-block-selected]::before {
                content: attr(data-vc-block-type);
                position: absolute;
                top: -22px;
                left: 0;
                z-index: 9999;
                border-radius: 999px;
                background: #00a0d2;
                color: #fff;
                padding: 4px 9px;
                font: 700 11px/1.1 Arial, sans-serif;
                letter-spacing: .03em;
                text-transform: uppercase;
                pointer-events: none;
            }
            .vc-live-add-control {
                position: fixed;
                z-index: 10000;
                display: none;
                align-items: center;
                gap: 6px;
                border: 0;
                border-radius: 999px;
                background: #00a0d2;
                color: #fff;
                padding: 7px 11px;
                font: 700 12px/1 Arial, sans-serif;
                box-shadow: 0 10px 24px rgba(0,0,0,.25);
                cursor: pointer;
            }
            .vc-live-add-control.is-visible {
                display: inline-flex;
            }
            .vc-live-action-toolbar {
                position: fixed;
                z-index: 10001;
                display: none;
                overflow: hidden;
                border-radius: 999px;
                background: #1f2933;
                box-shadow: 0 12px 30px rgba(0,0,0,.28);
            }
            .vc-live-action-toolbar.is-visible {
                display: inline-flex;
            }
            .vc-live-action-toolbar button {
                border: 0;
                border-left: 1px solid rgba(255,255,255,.12);
                background: transparent;
                color: #fff;
                padding: 8px 10px;
                font: 700 11px/1 Arial, sans-serif;
                cursor: pointer;
            }
            .vc-live-action-toolbar button:first-child {
                border-left: 0;
            }
            .vc-live-action-toolbar button:hover {
                background: rgba(0,160,210,.35);
            }
            .vc-live-action-toolbar button[data-action="delete"] {
                color: #fecdd3;
            }
            .vc-live-section-toolbar {
                position: fixed;
                z-index: 10002;
                display: none;
                overflow: hidden;
                border-radius: 999px;
                background: #0f766e;
                box-shadow: 0 14px 34px rgba(0,0,0,.3);
            }
            .vc-live-section-toolbar.is-visible {
                display: inline-flex;
            }
            .vc-live-section-toolbar button {
                border: 0;
                border-left: 1px solid rgba(255,255,255,.16);
                background: transparent;
                color: #ecfeff;
                padding: 8px 10px;
                font: 700 11px/1 Arial, sans-serif;
                cursor: pointer;
            }
            .vc-live-section-toolbar button:first-child {
                border-left: 0;
            }
            .vc-live-section-toolbar button:hover {
                background: rgba(255,255,255,.16);
            }
            .vc-live-section-toolbar button[data-action="delete"] {
                color: #fecdd3;
            }
            .vc-live-block:hover {
                outline: 1px dashed rgba(0,194,168,.78);
                outline-offset: 2px;
            }
            .vc-section:hover {
                outline: 1px dashed rgba(0,160,210,.72);
                outline-offset: -1px;
            }
        </style>
        <script>
            (() => {
                const addControl = document.createElement('button');
                addControl.type = 'button';
                addControl.className = 'vc-live-add-control';
                addControl.innerHTML = '<span>+</span><span>Add</span>';
                document.body.appendChild(addControl);
                const actionToolbar = document.createElement('div');
                actionToolbar.className = 'vc-live-action-toolbar';
                actionToolbar.innerHTML = [
                    ['edit', 'Edit'],
                    ['duplicate', 'Copy'],
                    ['move-up', '↑'],
                    ['move-down', '↓'],
                    ['delete', '×']
                ].map(([action, label]) => '<button type="button" data-action="' + action + '">' + label + '</button>').join('');
                document.body.appendChild(actionToolbar);
                const sectionToolbar = document.createElement('div');
                sectionToolbar.className = 'vc-live-section-toolbar';
                sectionToolbar.innerHTML = [
                    ['quick-add', '+ Block'],
                    ['add-section', '+ Sec'],
                    ['duplicate', 'Copy'],
                    ['move-up', '↑'],
                    ['move-down', '↓'],
                    ['delete', '×']
                ].map(([action, label]) => '<button type="button" data-action="' + action + '">' + label + '</button>').join('');
                document.body.appendChild(sectionToolbar);
                let pendingAdd = null;
                let pendingAction = null;
                let pendingSectionAction = null;
                const placeAddControl = (target, payload) => {
                    if (!target || !payload) {
                        addControl.classList.remove('is-visible');
                        actionToolbar.classList.remove('is-visible');
                        sectionToolbar.classList.remove('is-visible');
                        pendingAdd = null;
                        pendingAction = null;
                        pendingSectionAction = null;
                        return;
                    }

                    const rect = target.getBoundingClientRect();
                    pendingAdd = payload;
                    addControl.style.left = Math.max(12, rect.left + rect.width / 2 - 34) + 'px';
                    addControl.style.top = Math.max(12, rect.bottom + 8) + 'px';
                    addControl.classList.add('is-visible');
                };
                const placeActionToolbar = (target, payload) => {
                    if (!target || !payload) {
                        actionToolbar.classList.remove('is-visible');
                        pendingAction = null;
                        return;
                    }

                    const rect = target.getBoundingClientRect();
                    pendingAction = payload;
                    actionToolbar.style.left = Math.max(12, rect.right - 204) + 'px';
                    actionToolbar.style.top = Math.max(12, rect.top - 38) + 'px';
                    actionToolbar.classList.add('is-visible');
                };
                const placeSectionToolbar = (target, payload) => {
                    if (!target || !payload) {
                        sectionToolbar.classList.remove('is-visible');
                        pendingSectionAction = null;
                        return;
                    }

                    const rect = target.getBoundingClientRect();
                    pendingSectionAction = payload;
                    sectionToolbar.style.left = Math.max(12, rect.left + 12) + 'px';
                    sectionToolbar.style.top = Math.max(12, rect.top + 10) + 'px';
                    sectionToolbar.classList.add('is-visible');
                };
                const applySelection = (sectionIndex, blockIndex = null) => {
                    const sections = Array.from(document.querySelectorAll('.vc-section'));
                    const section = Number.isInteger(sectionIndex) ? sections[sectionIndex] : null;
                    sections.forEach((item) => item.removeAttribute('data-vc-live-selected'));
                    document.querySelectorAll('[data-vc-live-block-selected]').forEach((item) => item.removeAttribute('data-vc-live-block-selected'));

                    if (!section) {
                        placeAddControl(null, null);
                        return;
                    }

                    section.setAttribute('data-vc-live-selected', 'true');
                    placeSectionToolbar(section, { sectionIndex });
                    if (Number.isInteger(blockIndex)) {
                        const block = section.querySelector('.vc-live-block[data-vc-block-depth="0"][data-vc-block-index="' + blockIndex + '"]');
                        if (block) {
                            block.setAttribute('data-vc-live-block-selected', 'true');
                            placeAddControl(block, { sectionIndex, insertIndex: blockIndex + 1 });
                            placeActionToolbar(block, { sectionIndex, blockIndex });
                            return;
                        }
                    }

                    const blockCount = section.querySelectorAll(':scope > .vc-container > .vc-live-block[data-vc-block-depth="0"]').length;
                    placeAddControl(section, { sectionIndex, insertIndex: blockCount });
                    placeActionToolbar(null, null);
                };
                const selectSection = (section) => {
                    const sections = Array.from(document.querySelectorAll('.vc-section'));
                    const sectionIndex = sections.indexOf(section);
                    sections.forEach((item) => item.removeAttribute('data-vc-live-selected'));
                    document.querySelectorAll('[data-vc-live-block-selected]').forEach((item) => item.removeAttribute('data-vc-live-block-selected'));
                    if (sectionIndex >= 0) {
                        section.setAttribute('data-vc-live-selected', 'true');
                        window.parent.postMessage({
                            source: 'vertexcms-builder-live-preview',
                            type: 'select-section',
                            sectionIndex
                        }, '*');
                        const blockCount = section.querySelectorAll(':scope > .vc-container > .vc-live-block[data-vc-block-depth="0"]').length;
                        placeAddControl(section, { sectionIndex, insertIndex: blockCount });
                        placeSectionToolbar(section, { sectionIndex });
                        placeActionToolbar(null, null);
                    }
                };
                const selectBlock = (block) => {
                    const topLevelBlock = block.getAttribute('data-vc-block-depth') === '0'
                        ? block
                        : block.closest('.vc-live-block[data-vc-block-depth="0"]');
                    if (!topLevelBlock) return;

                    const section = topLevelBlock.closest('.vc-section');
                    if (!section) return;

                    const sections = Array.from(document.querySelectorAll('.vc-section'));
                    const sectionIndex = sections.indexOf(section);
                    const blockIndex = Number(topLevelBlock.getAttribute('data-vc-block-index'));
                    sections.forEach((item) => item.removeAttribute('data-vc-live-selected'));
                    document.querySelectorAll('[data-vc-live-block-selected]').forEach((item) => item.removeAttribute('data-vc-live-block-selected'));
                    section.setAttribute('data-vc-live-selected', 'true');
                    topLevelBlock.setAttribute('data-vc-live-block-selected', 'true');

                    window.parent.postMessage({
                        source: 'vertexcms-builder-live-preview',
                        type: 'select-block',
                        sectionIndex,
                        blockIndex
                    }, '*');
                    placeAddControl(topLevelBlock, { sectionIndex, insertIndex: blockIndex + 1 });
                    placeActionToolbar(topLevelBlock, { sectionIndex, blockIndex });
                    placeSectionToolbar(section, { sectionIndex });
                };
                const requestEdit = (block) => {
                    const topLevelBlock = block.getAttribute('data-vc-block-depth') === '0'
                        ? block
                        : block.closest('.vc-live-block[data-vc-block-depth="0"]');
                    if (!topLevelBlock) return;

                    const section = topLevelBlock.closest('.vc-section');
                    if (!section) return;

                    const sections = Array.from(document.querySelectorAll('.vc-section'));
                    window.parent.postMessage({
                        source: 'vertexcms-builder-live-preview',
                        type: 'edit-block',
                        sectionIndex: sections.indexOf(section),
                        blockIndex: Number(topLevelBlock.getAttribute('data-vc-block-index'))
                    }, '*');
                };

                document.addEventListener('click', (event) => {
                    const block = event.target.closest('.vc-live-block');
                    if (block) {
                        event.preventDefault();
                        event.stopPropagation();
                        selectBlock(block);
                        return;
                    }

                    const section = event.target.closest('.vc-section');
                    if (!section) return;
                    event.preventDefault();
                    event.stopPropagation();
                    selectSection(section);
                }, true);

                document.addEventListener('dblclick', (event) => {
                    const block = event.target.closest('.vc-live-block');
                    if (!block) return;
                    event.preventDefault();
                    event.stopPropagation();
                    requestEdit(block);
                }, true);
                addControl.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    if (!pendingAdd) return;

                    window.parent.postMessage({
                        source: 'vertexcms-builder-live-preview',
                        type: 'open-add',
                        sectionIndex: pendingAdd.sectionIndex,
                        insertIndex: pendingAdd.insertIndex
                    }, '*');
                });
                actionToolbar.addEventListener('click', (event) => {
                    const button = event.target.closest('button[data-action]');
                    if (!button || !pendingAction) return;
                    event.preventDefault();
                    event.stopPropagation();

                    window.parent.postMessage({
                        source: 'vertexcms-builder-live-preview',
                        type: 'block-action',
                        action: button.getAttribute('data-action'),
                        sectionIndex: pendingAction.sectionIndex,
                        blockIndex: pendingAction.blockIndex
                    }, '*');
                });
                sectionToolbar.addEventListener('click', (event) => {
                    const button = event.target.closest('button[data-action]');
                    if (!button || !pendingSectionAction) return;
                    event.preventDefault();
                    event.stopPropagation();

                    window.parent.postMessage({
                        source: 'vertexcms-builder-live-preview',
                        type: 'section-action',
                        action: button.getAttribute('data-action'),
                        sectionIndex: pendingSectionAction.sectionIndex
                    }, '*');
                });
                window.addEventListener('scroll', () => {
                    if (!pendingAdd) return;
                    const section = document.querySelector('.vc-section[data-vc-section-index="' + pendingAdd.sectionIndex + '"]');
                    const target = pendingAdd.insertIndex > 0
                        ? section?.querySelector('.vc-live-block[data-vc-block-depth="0"][data-vc-block-index="' + (pendingAdd.insertIndex - 1) + '"]')
                        : section;
                    placeAddControl(target || section, pendingAdd);
                    if (pendingAction) {
                        const actionTarget = section?.querySelector('.vc-live-block[data-vc-block-depth="0"][data-vc-block-index="' + pendingAction.blockIndex + '"]');
                        placeActionToolbar(actionTarget, pendingAction);
                    }
                    if (pendingSectionAction) {
                        const sectionTarget = document.querySelector('.vc-section[data-vc-section-index="' + pendingSectionAction.sectionIndex + '"]');
                        placeSectionToolbar(sectionTarget, pendingSectionAction);
                    }
                }, { passive: true });
                window.addEventListener('message', (event) => {
                    if (event.data?.source !== 'vertexcms-builder-parent' || event.data.type !== 'sync-selection') {
                        return;
                    }
                    applySelection(event.data.sectionIndex, event.data.blockIndex);
                });
            })();
        <\/script>
    `;

    return livePreviewDocument.value.includes('</body>')
        ? livePreviewDocument.value.replace('</body>', `${bridge}</body>`)
        : `${livePreviewDocument.value}${bridge}`;
});

function syncLivePreviewSelection({ scroll = false } = {}) {
    const frameDocument = livePreviewFrame.value?.contentDocument;
    const frameWindow = livePreviewFrame.value?.contentWindow;
    if (!frameDocument || !frameWindow) {
        return;
    }

    const sectionIndex = Number.isInteger(selectedSection.value) ? selectedSection.value : null;
    const blockIndex = Number.isInteger(selectedBlock.value) ? selectedBlock.value : null;

    frameWindow.postMessage({
        source: 'vertexcms-builder-parent',
        type: 'sync-selection',
        sectionIndex,
        blockIndex,
    }, '*');

    frameDocument.querySelectorAll('[data-vc-live-selected]').forEach((item) => item.removeAttribute('data-vc-live-selected'));
    frameDocument.querySelectorAll('[data-vc-live-block-selected]').forEach((item) => item.removeAttribute('data-vc-live-block-selected'));

    if (!Number.isInteger(sectionIndex)) {
        return;
    }

    const section = frameDocument.querySelector(`.vc-section[data-vc-section-index="${sectionIndex}"]`);
    if (!section) {
        return;
    }

    section.setAttribute('data-vc-live-selected', 'true');
    let target = section;

    if (Number.isInteger(blockIndex)) {
        const block = section.querySelector(`.vc-live-block[data-vc-block-depth="0"][data-vc-block-index="${blockIndex}"]`);
        if (block) {
            block.setAttribute('data-vc-live-block-selected', 'true');
            target = block;
        }
    }

    if (scroll) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

const selectionSummary = computed(() => {
    if (selectedBlockData.value && selectedSection.value !== null) {
        return {
            title: `${blockLabel(selectedBlockData.value.type)} в секции ${selectedSection.value + 1}`,
            copy: selectedBlockData.value.type,
            path: `Страница / Секция ${selectedSection.value + 1} / ${blockLabel(selectedBlockData.value.type)}`,
        };
    }

    if (selectedSection.value !== null) {
        return {
            title: `Секция ${selectedSection.value + 1}`,
            copy: `${sections.value[selectedSection.value]?.blocks?.length || 0} блоков внутри`,
            path: `Страница / Секция ${selectedSection.value + 1}`,
        };
    }

    return {
        title: 'Рабочий холст страницы',
        copy: 'Выберите секцию или блок, чтобы редактировать контент и стили.',
        path: 'Страница',
    };
});

const inspectorTabs = computed(() => {
    if (selectedBlockData.value) {
        const blockTabs = allBlocks.value?.[selectedBlockData.value.type]?.editor?.tabs;

        if (Array.isArray(blockTabs) && blockTabs.length) {
            return blockTabs.map((tab) => ({
                id: tab,
                label: translateInspectorTab(tab),
            }));
        }
    }

    if (selectedSection.value !== null) {
        const sectionTabs = sectionConfig.value?.tabs;

        if (Array.isArray(sectionTabs) && sectionTabs.length) {
            return sectionTabs.map((tab) => ({
                id: tab,
                label: translateInspectorTab(tab),
            }));
        }
    }

    return [
        { id: 'content', label: 'Контент' },
        { id: 'style', label: 'Стиль' },
        { id: 'advanced', label: 'Дополнительно' },
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
                text: props.page.title || 'Новый заголовок страницы',
                align: 'left',
                color: '#0f172a',
            },
        }],
    }];

    saveToHistory('Создать стартовую секцию');
    baseCanvasState.selectBlock(0, 0);
};

const insertSectionAfter = (sectionIndex) => {
    const defaults = sectionConfig.value?.default_settings || {};
    const nextIndex = Math.min(sectionIndex + 1, sections.value.length);

    sections.value.splice(nextIndex, 0, {
        id: baseCanvasState.generateId(),
        settings: {
            padding_top: defaults.padding_top ?? 48,
            padding_bottom: defaults.padding_bottom ?? 48,
            background_color: defaults.background_color ?? null,
            css_class: defaults.css_class ?? null,
        },
        blocks: [],
    });

    saveToHistory('Add section');
    markContentChanged();
    baseCanvasState.selectSection(nextIndex);
    baseCanvasState.openQuickAdd(nextIndex, 0);
    expandedSections.value = [...new Set([...expandedSections.value, nextIndex])];
    sidebarMode.value = 'library';
};

const openDesignLibrary = () => {
    const url = props.config?.design_library_url || '/admin/pages/builder/design-library';
    if (!props.page?.id) {
        window.location.href = url;
        return;
    }

    const separator = url.includes('?') ? '&' : '?';
    window.location.href = `${url}${separator}page_id=${encodeURIComponent(props.page.id)}`;
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

const translateInspectorTab = (tab) => {
    const labels = {
        content: 'Контент',
        style: 'Стиль',
        advanced: 'Дополнительно',
        media: 'Медиа',
        layout: 'Сетка',
        seo: 'SEO',
    };

    return labels[tab] || tab.charAt(0).toUpperCase() + tab.slice(1);
};

watch(contentRevision, () => {
    scheduleContentSearchIndexRebuild(contentSearchQuery.value ? 120 : 220);
    if (autoSaveTimer.value) clearTimeout(autoSaveTimer.value);
    autoSaveTimer.value = setTimeout(() => {
        persistenceState.autoSave();
    }, 120000);
    scheduleLivePreviewRefresh();
});

watch(sections, () => {
    markContentChanged();
});

watch(canvasMode, (mode) => {
    if (mode === 'live' && !persistenceState.livePreviewDocument.value) {
        scheduleLivePreviewRefresh(0);
    }
});

watch(contentSearchQuery, (query) => {
    if (contentSearchTimer.value) {
        clearTimeout(contentSearchTimer.value);
    }

    if (!query) {
        selectedSection.value = null;
        selectedBlock.value = null;
        return;
    }

    contentSearchTimer.value = setTimeout(() => {
        const normalizedQuery = query.toLowerCase();
        if (!contentSearchIndex.value.length) {
            rebuildContentSearchIndex();
        }

        const match = contentSearchIndex.value.find((item) => item.haystack.includes(normalizedQuery));
        if (match) {
            selectedSection.value = match.sectionIndex;
            selectedBlock.value = match.blockIndex;
        }
    }, 180);
});

watch([selectedSection, selectedBlock, selectedBlockData], () => {
    if (selectedBlockData.value) {
        inspectorTab.value = 'content';
        if (canvasMode.value === 'live') {
            requestAnimationFrame(() => syncLivePreviewSelection({ scroll: true }));
        }
        return;
    }

    if (selectedSection.value !== null) {
        inspectorTab.value = 'style';
        if (canvasMode.value === 'live') {
            requestAnimationFrame(() => syncLivePreviewSelection({ scroll: true }));
        }
        return;
    }

    inspectorTab.value = 'content';
});

onMounted(() => {
    inspectorState.restoreInspectorState();
    templatesState.restorePresetCache();
    rebuildContentSearchIndex();
    saveToHistory('Начальное состояние');
    persistenceState.loadRevisions();
    templatesState.loadSharedPresets();
    templatesState.loadSharedTemplates();
    mediaState.hydrateMediaLookup(mediaState.collectReferencedMediaIds());
    scheduleLivePreviewRefresh(0);
    document.addEventListener('keydown', commandsState.handleKeydown);
    document.addEventListener('click', commandsState.handleGlobalPointer);
    window.addEventListener('message', handleLivePreviewMessage);
    window.addEventListener('beforeunload', handleBeforeUnload);

    if (!Object.keys(allBlocks.value || {}).length) {
        const registryRequest = window.__vertexBuilderBlocksPromise
            || fetch('/admin/api/builder/blocks')
                .then((response) => response.json())
                .then((data) => {
                    return (data.blocks && !Array.isArray(data.blocks))
                        ? data.blocks
                        : Object.fromEntries((data.entries || data.blocks || []).map((block) => [block.type, block]));
                });

        window.__vertexBuilderBlocksPromise = registryRequest;

        registryRequest
            .then((blocks) => {
                allBlocks.value = blocks || {};
                window.availableBlocks = allBlocks.value;
            })
            .catch((error) => {
                console.error('Builder registry bootstrap error:', error);
            });
    } else {
        window.availableBlocks = allBlocks.value;
    }
});

onBeforeUnmount(() => {
    if (autoSaveTimer.value) clearTimeout(autoSaveTimer.value);
    if (livePreviewTimer.value) clearTimeout(livePreviewTimer.value);
    if (contentSearchTimer.value) clearTimeout(contentSearchTimer.value);
    if (contentSearchIndexTimer.value) clearTimeout(contentSearchIndexTimer.value);
    document.removeEventListener('keydown', commandsState.handleKeydown);
    document.removeEventListener('click', commandsState.handleGlobalPointer);
    window.removeEventListener('message', handleLivePreviewMessage);
    window.removeEventListener('beforeunload', handleBeforeUnload);
    mediaState.closeMediaPicker();
});

const {
    page,
    config,
} = props;

const {
    showPreview,
    showRevisions,
    previewHtml,
    livePreviewDocument,
    livePreviewLoading,
    livePreviewError,
    autoSaveStatus,
    autoSaveStatusText,
    hasPendingChanges,
    revisions,
    saveContent,
    previewContent,
    refreshLivePreview,
    restoreRevision,
    applyTemplate,
    exportCurrentSections,
    importSectionsPrompt,
    formatDate,
    saving,
} = persistenceState;

function handleBeforeUnload(event) {
    if (!hasPendingChanges.value) {
        return;
    }

    event.preventDefault();
    event.returnValue = '';
}

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
    selectSection: baseSelectSection,
    selectBlock: baseSelectBlock,
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

const selectSection = (...args) => {
    const [sectionIndex] = args;
    if (typeof sectionIndex === 'number' && !isSectionExpanded(sectionIndex)) {
        expandedSections.value = [...expandedSections.value, sectionIndex];
    }
    inlineEditingHighlightFields.value = [];
    return baseSelectSection(...args);
};

const selectBlock = (...args) => {
    const event = args[2] ?? null;
    if (!event || event.detail < 2) {
        inlineEditingHighlightFields.value = [];
    }

    return baseSelectBlock(...args);
};

const addLibraryBlock = (type) => {
    if (quickAddSectionIndex.value !== null) {
        baseCanvasState.runQuickAddItem(
            quickAddSectionIndex.value,
            quickAddInsertIndex.value,
            {
                id: `block-${type}`,
                kind: 'block',
                type,
                name: blockLabel(type),
            },
        );
        sidebarMode.value = 'structure';
        return;
    }

    addBlock(type);
};

const inlinePrimaryField = (type) => {
    const fields = inlineEditingConfig(type)?.fields;
    return Array.isArray(fields) && fields.length ? fields[0] : 'content';
};

const isInlineTextEditorVisible = (sIndex, bIndex, block) => {
    return selectedSection.value === sIndex
        && selectedBlock.value === bIndex
        && ['text', 'heading'].includes(block.type)
        && inlineEditingEnabled(block.type);
};

const inlineEditorValue = (block) => {
    const field = inlinePrimaryField(block.type);
    return block.settings?.[field] ?? '';
};

const updateInlineEditorValue = (sIndex, bIndex, block, value) => {
    if (selectedSection.value !== sIndex || selectedBlock.value !== bIndex) {
        baseSelectBlock(sIndex, bIndex);
    }

    const field = inlinePrimaryField(block.type);
    inspectorTab.value = inlineEditingConfig(block.type)?.target_tab || 'content';
    inlineEditingHighlightFields.value = [field];
    updateBlockSettings({
        ...(block.settings || {}),
        [field]: value,
    });
};
</script>
