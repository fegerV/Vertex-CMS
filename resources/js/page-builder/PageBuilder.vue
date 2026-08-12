<template>
<div class="page-builder-container h-full flex flex-col">
    <!-- Main Builder Area -->
    <div class="flex-1 flex gap-4 overflow-hidden p-4">
        <!-- Left Sidebar: Block Library & Design Library -->
        <aside class="w-80 flex-shrink-0 flex flex-col bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <!-- Tabs -->
            <div class="flex border-b border-slate-200">
                <button 
                    @click="activeTab = 'blocks'"
                    :class="activeTab === 'blocks' ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-600' : 'text-slate-600 hover:bg-slate-50'"
                    class="flex-1 px-4 py-3 text-sm font-medium transition-colors"
                >
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Блоки
                    </div>
                </button>
                <button 
                    @click="activeTab = 'library'"
                    :class="activeTab === 'library' ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-600' : 'text-slate-600 hover:bg-slate-50'"
                    class="flex-1 px-4 py-3 text-sm font-medium transition-colors"
                >
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        Шаблоны
                    </div>
                </button>
                <button 
                    @click="activeTab = 'styles'"
                    :class="activeTab === 'styles' ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-600' : 'text-slate-600 hover:bg-slate-50'"
                    class="flex-1 px-4 py-3 text-sm font-medium transition-colors"
                >
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        Стили
                    </div>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="flex-1 overflow-y-auto p-3">
                <!-- Blocks Tab -->
                <div v-if="activeTab === 'blocks'" class="space-y-4">
                    <!-- Search -->
                    <div class="relative">
                        <input 
                            v-model="blockSearchQuery"
                            type="text" 
                            placeholder="Поиск блоков..."
                            class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Block Categories -->
                    <div v-for="(blocks, category) in filteredBlocksByCategory" :key="category" class="space-y-2">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ getCategoryName(category) }}</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div 
                                v-for="(block, type) in blocks" 
                                :key="type"
                                @click="addBlock(type)"
                                @mouseenter="hoveredBlockType = type"
                                @mouseleave="hoveredBlockType = null"
                                class="group p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-blue-400 hover:shadow-md hover:shadow-blue-500/10 transition-all duration-200 active:scale-[0.98]"
                                :class="{'border-blue-300 bg-blue-50/50': hoveredBlockType === type}"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center group-hover:from-blue-100 group-hover:to-indigo-100 transition-colors">
                                        <span class="text-lg">{{ block.icon || '📦' }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-sm text-slate-800">{{ block.name }}</div>
                                        <div class="text-xs text-slate-500 truncate">{{ block.description || '' }}</div>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Design Library Tab -->
                <div v-if="activeTab === 'library'" class="space-y-4">
                    <div class="text-sm text-slate-600 mb-3">
                        Выберите готовый шаблон или компонент для вставки
                    </div>
                    <div class="grid grid-cols-1 gap-3">
                        <div 
                            v-for="template in designLibrary" 
                            :key="template.id"
                            @click="importTemplate(template)"
                            class="group p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-blue-400 hover:shadow-md transition-all"
                        >
                            <div class="aspect-video bg-slate-100 rounded mb-2 overflow-hidden">
                                <img v-if="template.thumbnail" :src="template.thumbnail" :alt="template.name" class="w-full h-full object-cover"/>
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="font-medium text-sm text-slate-800">{{ template.name }}</div>
                            <div class="text-xs text-slate-500">{{ template.category || 'Шаблон' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Styles Tab -->
                <div v-if="activeTab === 'styles'" class="space-y-4">
                    <!-- Global Colors -->
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-2">Глобальные цвета</h4>
                        <div class="space-y-2">
                            <div v-for="(color, index) in colorPalette" :key="index" class="flex items-center gap-2">
                                <input 
                                    type="color" 
                                    :value="color.value"
                                    @input="updateColor(index, $event.target.value)"
                                    class="w-8 h-8 rounded border border-slate-300 cursor-pointer"
                                />
                                <input 
                                    type="text" 
                                    :value="color.name"
                                    @input="updateColorName(index, $event.target.value)"
                                    class="flex-1 px-2 py-1.5 text-sm border border-slate-300 rounded"
                                    placeholder="Название цвета"
                                />
                            </div>
                            <button 
                                @click="addColor"
                                class="w-full py-2 border-2 border-dashed border-slate-300 rounded-lg text-slate-400 hover:border-blue-400 hover:text-blue-500 transition-colors text-sm"
                            >
                                + Добавить цвет
                            </button>
                        </div>
                    </div>

                    <!-- Typography Presets -->
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-2">Типографика</h4>
                        <div class="space-y-2">
                            <div v-for="preset in typographyPresets" :key="preset.id" class="p-2 bg-slate-50 rounded border border-slate-200">
                                <div class="font-medium text-sm">{{ preset.name }}</div>
                                <div class="text-xs text-slate-500">{{ preset.settings?.font_family || 'Default' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Button Presets -->
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-2">Стили кнопок</h4>
                        <div class="space-y-2">
                            <div v-for="preset in buttonPresets" :key="preset.id" class="p-2 bg-slate-50 rounded border border-slate-200">
                                <div class="font-medium text-sm">{{ preset.name }}</div>
                                <button 
                                    :style="getButtonPresetStyle(preset)"
                                    class="mt-1 px-3 py-1.5 rounded text-xs"
                                >
                                    Пример
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Center: Canvas Area -->
        <main class="flex-1 flex flex-col min-w-0 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <!-- Canvas Toolbar -->
            <div class="flex-shrink-0 px-4 py-3 border-b border-slate-200 bg-white flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-slate-600">Блоков:</span>
                        <span class="px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">{{ content.length }}</span>
                    </div>
                    <div class="h-4 w-px bg-slate-200"></div>
                    <div class="flex items-center gap-1">
                        <button 
                            @click="undo"
                            :disabled="!canUndo"
                            class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                            title="Отменить (Ctrl+Z)"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                        </button>
                        <button 
                            @click="redo"
                            :disabled="!canRedo"
                            class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                            title="Повторить (Ctrl+Y)"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <button 
                        @click="setViewMode('desktop')"
                        :class="viewMode === 'desktop' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-100'"
                        class="p-2 rounded-lg transition-colors"
                        title="Десктоп"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </button>
                    <button 
                        @click="setViewMode('tablet')"
                        :class="viewMode === 'tablet' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-100'"
                        class="p-2 rounded-lg transition-colors"
                        title="Планшет"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </button>
                    <button 
                        @click="setViewMode('mobile')"
                        :class="viewMode === 'mobile' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-100'"
                        class="p-2 rounded-lg transition-colors"
                        title="Мобильный"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Canvas Content -->
            <div class="flex-1 overflow-y-auto p-6 bg-slate-50">
                <div 
                    :class="{
                        'max-w-7xl': viewMode === 'desktop',
                        'max-w-3xl': viewMode === 'tablet',
                        'max-w-xs': viewMode === 'mobile'
                    }"
                    class="mx-auto transition-all duration-300"
                >
                    <!-- Empty State -->
                    <div 
                        v-if="content.length === 0"
                        class="text-center py-20 bg-white rounded-xl border-2 border-dashed border-slate-300 hover:border-blue-400 transition-colors"
                    >
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-700 mb-2">Начните создание страницы</h3>
                        <p class="text-slate-500 text-sm mb-4">Выберите блок из библиотеки слева,<br>чтобы добавить его на страницу</p>
                        <div class="flex items-center justify-center gap-2 text-xs text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Совет: Блоки можно перетаскивать мышкой</span>
                        </div>
                    </div>

                    <!-- Blocks List -->
                    <div ref="blocksContainer" class="space-y-3 blocks-container">
                        <div 
                            v-for="(block, index) in content" 
                            :key="block._id"
                            :data-index="index"
                            class="group relative bg-white rounded-xl border-2 transition-all duration-200 hover:shadow-lg"
                            :class="{
                                'border-blue-500 shadow-lg shadow-blue-500/20 ring-2 ring-blue-500/20': selectedIndex === index,
                                'border-slate-200 hover:border-slate-300': selectedIndex !== index,
                                'opacity-50 scale-[0.98]': draggingIndex === index
                            }"
                            @click="selectBlock(index)"
                        >
                            <!-- Drag Handle & Actions -->
                            <div 
                                class="absolute -top-3 left-4 z-20 flex items-center gap-1 bg-white border border-slate-200 rounded-lg shadow-sm px-2 py-1.5 opacity-0 group-hover:opacity-100 transition-opacity"
                                :class="{'opacity-100': selectedIndex === index}"
                            >
                                <button 
                                    class="drag-handle p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded cursor-grab active:cursor-grabbing"
                                    title="Перетащить"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                    </svg>
                                </button>
                                <div class="w-px h-4 bg-slate-200"></div>
                                <button 
                                    @click.stop="moveBlockUp(index)"
                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors"
                                    title="Вверх"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                </button>
                                <button 
                                    @click.stop="moveBlockDown(index)"
                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors"
                                    title="Вниз"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <button 
                                    @click.stop="duplicateBlock(index)"
                                    class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors"
                                    title="Дублировать"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                                <button 
                                    @click.stop="deleteBlock(index)"
                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                    title="Удалить"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Block Preview -->
                            <div class="p-4 pt-8">
                                <component 
                                    :is="getBlockComponent(block.type)"
                                    :block="block"
                                    :settings="block.settings"
                                    :index="index"
                                    @update-setting="updateBlockSetting"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Right Sidebar: Settings Panel -->
        <aside class="w-80 flex-shrink-0 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                <h3 class="font-semibold text-slate-800">Настройки блока</h3>
                <p class="text-xs text-slate-500 mt-0.5">Редактирование параметров</p>
            </div>

            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="selectedBlock" class="space-y-4">
                    <!-- Block Info -->
                    <div class="pb-4 border-b border-slate-200">
                        <div class="text-sm font-medium text-slate-700">{{ getBlockName(selectedBlock.type) }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">ID: {{ selectedBlock._id }}</div>
                    </div>

                    <!-- Dynamic Settings Fields -->
                    <div v-for="(fieldConfig, fieldKey) in getBlockFields(selectedBlock.type)" :key="fieldKey" class="space-y-1">
                        <label class="block text-sm font-medium text-slate-600">{{ fieldConfig.label }}</label>
                        
                        <textarea 
                            v-if="fieldConfig.type === 'textarea'"
                            :value="getNestedValue(selectedBlock.settings, fieldKey)"
                            @input="updateBlockSetting(fieldKey, $event.target.value)"
                            :rows="fieldConfig.rows || 3"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        
                        <select 
                            v-else-if="fieldConfig.type === 'select'"
                            :value="getNestedValue(selectedBlock.settings, fieldKey)"
                            @change="updateBlockSetting(fieldKey, $event.target.value)"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option v-for="(label, value) in fieldConfig.options" :key="value" :value="value">{{ label }}</option>
                        </select>
                        
                        <input 
                            v-else-if="fieldConfig.type === 'color'"
                            type="color"
                            :value="getNestedValue(selectedBlock.settings, fieldKey) || '#000000'"
                            @input="updateBlockSetting(fieldKey, $event.target.value)"
                            class="w-full h-10 rounded border border-slate-300 cursor-pointer"
                        />
                        
                        <input 
                            v-else-if="fieldConfig.type === 'checkbox'"
                            type="checkbox"
                            :checked="getNestedValue(selectedBlock.settings, fieldKey)"
                            @change="updateBlockSetting(fieldKey, $event.target.checked)"
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        />
                        
                        <input 
                            v-else
                            :type="fieldConfig.type || 'text'"
                            :value="getNestedValue(selectedBlock.settings, fieldKey) || ''"
                            @input="updateBlockSetting(fieldKey, $event.target.value)"
                            :placeholder="fieldConfig.placeholder"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <!-- Common Settings -->
                    <div class="pt-4 border-t border-slate-200 space-y-3">
                        <h4 class="text-sm font-semibold text-slate-700">Общие настройки</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-600">CSS класс</label>
                            <input 
                                type="text"
                                :value="selectedBlock.settings?.css_class || ''"
                                @input="updateBlockSetting('css_class', $event.target.value)"
                                placeholder="custom-class"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600">Цвет фона</label>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="color"
                                    :value="selectedBlock.settings?.background_color || '#ffffff'"
                                    @input="updateBlockSetting('background_color', $event.target.value)"
                                    class="w-10 h-10 rounded border border-slate-300 cursor-pointer"
                                />
                                <input 
                                    type="text"
                                    :value="selectedBlock.settings?.background_color || '#ffffff'"
                                    @input="updateBlockSetting('background_color', $event.target.value)"
                                    class="flex-1 px-3 py-2 text-sm border border-slate-300 rounded-lg font-mono"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Save as Preset -->
                    <div class="pt-4 border-t border-slate-200">
                        <button 
                            @click="saveAsPreset"
                            class="w-full py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition-colors"
                        >
                            Сохранить как пресет
                        </button>
                    </div>
                </div>

                <div v-else class="text-center py-12 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                    </svg>
                    <p class="text-sm">Выберите блок для редактирования</p>
                </div>
            </div>
        </aside>
    </div>

    <!-- Toast Notification -->
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div 
            v-if="toast.visible"
            class="fixed bottom-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg"
            :class="{
                'bg-green-500 text-white': toast.type === 'success',
                'bg-red-500 text-white': toast.type === 'error',
                'bg-yellow-500 text-white': toast.type === 'warning',
                'bg-blue-500 text-white': toast.type === 'info'
            }"
        >
            <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm font-medium">{{ toast.message }}</span>
            <button @click="toast.visible = false" class="ml-2 hover:opacity-80">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </transition>
</div>
</template>

<script>
import { ref, computed, reactive, onMounted, nextTick } from 'vue';
import { createPageBuilderStore } from './stores/pageBuilderStore';
import Sortable from 'sortablejs';

// Import block components
import HeadingBlock from './components/blocks/HeadingBlock.vue';
import TextBlock from './components/blocks/TextBlock.vue';
import ButtonBlock from './components/blocks/ButtonBlock.vue';
import ImageBlock from './components/blocks/ImageBlock.vue';
import DividerBlock from './components/blocks/DividerBlock.vue';
import SpacerBlock from './components/blocks/SpacerBlock.vue';
import AccordionBlock from './components/blocks/AccordionBlock.vue';
import TabsBlock from './components/blocks/TabsBlock.vue';
import CounterBlock from './components/blocks/CounterBlock.vue';
import GalleryBlock from './components/blocks/GalleryBlock.vue';
import FaqBlock from './components/blocks/FaqBlock.vue';
import HtmlBlock from './components/blocks/HtmlBlock.vue';

export default {
    name: 'PageBuilder',
    
    components: {
        HeadingBlock,
        TextBlock,
        ButtonBlock,
        ImageBlock,
        DividerBlock,
        SpacerBlock,
        AccordionBlock,
        TabsBlock,
        CounterBlock,
        GalleryBlock,
        FaqBlock,
        HtmlBlock
    },
    
    props: {
        pageData: {
            type: Object,
            default: () => ({})
        },
        availableBlocks: {
            type: Object,
            default: () => ({})
        },
        initialContent: {
            type: Array,
            default: () => []
        },
        onSave: {
            type: Function,
            default: () => {}
        },
        onPreview: {
            type: Function,
            default: () => {}
        },
        apiEndpoint: {
            type: String,
            default: '/admin/api/builder'
        },
        enableAI: {
            type: Boolean,
            default: true
        },
        aiEndpoint: {
            type: String,
            default: '/admin/api/ai/edit'
        },
        designLibrary: {
            type: Array,
            default: () => []
        },
        globalStyles: {
            type: Object,
            default: () => ({})
        },
        colorPalette: {
            type: Array,
            default: () => []
        },
        typographyPresets: {
            type: Array,
            default: () => []
        },
        buttonPresets: {
            type: Array,
            default: () => []
        }
    },
    
    setup(props) {
        // Create store
        const store = createPageBuilderStore({
            content: props.initialContent,
            globalStyles: props.globalStyles,
            colorPalette: props.colorPalette,
            typographyPresets: props.typographyPresets,
            buttonPresets: props.buttonPresets
        });

        // Local state
        const activeTab = ref('blocks');
        const blockSearchQuery = ref('');
        const hoveredBlockType = ref(null);
        const draggingIndex = ref(null);
        const dragOverIndex = ref(null);
        const blocksContainer = ref(null);
        let sortableInstance = null;

        // Computed
        const { 
            state, 
            content, 
            selectedIndex, 
            viewMode, 
            toast,
            canUndo,
            canRedo
        } = store;

        const selectedBlock = computed(() => {
            if (selectedIndex.value === -1) return null;
            return content.value[selectedIndex.value] || null;
        });

        const filteredBlocksByCategory = computed(() => {
            const blocks = props.availableBlocks;
            if (!blocks) return {};

            const filtered = {};
            const query = blockSearchQuery.value.toLowerCase();

            Object.entries(blocks).forEach(([type, config]) => {
                const name = (config.name || type).toLowerCase();
                const description = (config.description || '').toLowerCase();
                
                if (name.includes(query) || description.includes(query)) {
                    const category = config.category || 'other';
                    if (!filtered[category]) {
                        filtered[category] = {};
                    }
                    filtered[category][type] = config;
                }
            });

            return filtered;
        });

        // Methods
        function getCategoryName(category) {
            const names = {
                content: 'Контент',
                media: 'Медиа',
                layout: 'Макет',
                dynamic: 'Динамические',
                interactive: 'Интерактивные',
                ecommerce: 'E-commerce',
                utility: 'Утилиты',
                seo: 'SEO',
                basic: 'Базовые',
                advanced: 'Расширенные'
            };
            return names[category] || category;
        }

        function addBlock(type) {
            const blockConfig = props.availableBlocks[type];
            if (!blockConfig) {
                showToast(`Блок "${type}" не найден`, 'error');
                return;
            }

            store.addBlock(type, blockConfig);
            showToast(`Блок "${blockConfig.name || type}" добавлен`);
        }

        function selectBlock(index) {
            store.selectBlock(index);
        }

        function deleteBlock(index) {
            store.deleteBlock(index);
            showToast('Блок удален');
        }

        function moveBlockUp(index) {
            if (store.moveBlock(index, index - 1)) {
                showToast('Блок перемещён вверх');
            }
        }

        function moveBlockDown(index) {
            if (store.moveBlock(index, index + 1)) {
                showToast('Блок перемещён вниз');
            }
        }

        function duplicateBlock(index) {
            store.duplicateBlock(index);
            showToast('Блок скопирован');
        }

        function undo() {
            if (store.undo()) {
                showToast('Действие отменено', 'info');
            }
        }

        function redo() {
            if (store.redo()) {
                showToast('Действие повторено', 'info');
            }
        }

        function setViewMode(mode) {
            store.setViewMode(mode);
        }

        function updateBlockSetting(key, value) {
            store.updateBlockSetting(key, value);
        }

        function getNestedValue(obj, path) {
            if (!obj) return undefined;
            return path.split('.').reduce((current, key) => current?.[key], obj);
        }

        function getBlockComponent(type) {
            const componentMap = {
                heading: 'HeadingBlock',
                text: 'TextBlock',
                button: 'ButtonBlock',
                image: 'ImageBlock',
                divider: 'DividerBlock',
                spacer: 'SpacerBlock',
                accordion: 'AccordionBlock',
                tabs: 'TabsBlock',
                counter: 'CounterBlock',
                gallery: 'GalleryBlock',
                faq: 'FaqBlock',
                html: 'HtmlBlock'
            };
            return componentMap[type] || 'TextBlock';
        }

        function getBlockName(type) {
            const config = props.availableBlocks[type];
            return config?.name || type;
        }

        function getBlockFields(type) {
            const config = props.availableBlocks[type];
            return config?.fields || {};
        }

        function saveAsPreset() {
            if (!selectedBlock.value) return;
            const name = prompt('Введите название пресета:');
            if (name) {
                store.savePreset(name, selectedBlock.value.type, selectedBlock.value.settings);
                showToast('Пресет сохранён', 'success');
            }
        }

        function importTemplate(template) {
            if (template.content) {
                store.setContent(template.content);
                showToast(`Шаблон "${template.name}" применён`, 'success');
            }
        }

        function addColor() {
            state.colorPalette.push({ name: '', value: '#000000' });
        }

        function updateColor(index, value) {
            if (state.colorPalette[index]) {
                state.colorPalette[index].value = value;
            }
        }

        function updateColorName(index, name) {
            if (state.colorPalette[index]) {
                state.colorPalette[index].name = name;
            }
        }

        function getButtonPresetStyle(preset) {
            const settings = preset?.settings || {};
            return {
                backgroundColor: settings.background_color || '#3b82f6',
                color: settings.text_color || '#ffffff',
                borderRadius: settings.border_radius || '4px',
                padding: `${settings.padding_y || 8}px ${settings.padding_x || 16}px`,
                fontSize: settings.font_size || '14px',
                fontWeight: settings.font_weight || '500'
            };
        }

        function handleKeyboard(e) {
            // Ctrl+Z for undo
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
                e.preventDefault();
                undo();
            }
            // Ctrl+Y or Ctrl+Shift+Z for redo
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) {
                e.preventDefault();
                redo();
            }
            // Delete key to remove selected block
            if (e.key === 'Delete' && selectedIndex.value !== -1) {
                e.preventDefault();
                deleteBlock(selectedIndex.value);
            }
        }

        // Initialize SortableJS
        function initSortable() {
            nextTick(() => {
                if (blocksContainer.value && !sortableInstance) {
                    sortableInstance = new Sortable(blocksContainer.value, {
                        animation: 150,
                        handle: '.drag-handle',
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                        onEnd: (evt) => {
                            const oldIndex = evt.oldIndex;
                            const newIndex = evt.newIndex;
                            
                            if (oldIndex !== newIndex && oldIndex !== null && newIndex !== null) {
                                store.moveBlock(oldIndex, newIndex);
                                showToast('Блок перемещён', 'success');
                            }
                        }
                    });
                }
            });
        }

        onMounted(() => {
            initSortable();
            document.addEventListener('keydown', handleKeyboard);
        });

        return {
            // State
            activeTab,
            blockSearchQuery,
            hoveredBlockType,
            draggingIndex,
            dragOverIndex,
            blocksContainer,
            
            // Store state
            content,
            selectedIndex,
            selectedBlock,
            viewMode,
            toast,
            canUndo,
            canRedo,
            filteredBlocksByCategory,
            
            // Methods
            getCategoryName,
            addBlock,
            selectBlock,
            deleteBlock,
            moveBlockUp,
            moveBlockDown,
            duplicateBlock,
            undo,
            redo,
            setViewMode,
            updateBlockSetting,
            getNestedValue,
            getBlockComponent,
            getBlockName,
            getBlockFields,
            saveAsPreset,
            importTemplate,
            addColor,
            updateColor,
            updateColorName,
            getButtonPresetStyle
        };
    }
};
</script>

<style scoped>
.page-builder-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.blocks-container {
    min-height: 100px;
}

.sortable-ghost {
    opacity: 0.4;
    background: #f1f5f9;
    border: 2px dashed #94a3b8;
}

.sortable-chosen {
    border-color: #3b82f6;
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
}

.sortable-drag {
    opacity: 1;
    transform: scale(1.02);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
