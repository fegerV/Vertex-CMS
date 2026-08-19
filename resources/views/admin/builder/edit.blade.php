<!-- resources/views/admin/builder/edit.blade.php -->
@extends('admin.layouts.app')

@section('title', 'Конструктор страниц - ' . $page->title . ' - VertexCMS')
@section('page_title', 'Конструктор страниц')
@section('page_subtitle', $page->title)

@section('breadcrumbs')
    <a href="{{ route('admin.pages.index') }}" class="hover:text-slate-900">Страницы</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900">{{ $page->title }}</span>
@endsection

@section('page_actions')
    <div class="flex items-center gap-3">
        <button 
            @click="previewContent"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all shadow-sm"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Предпросмотр
        </button>
        <button 
            @click="saveContent"
            :disabled="saving || !hasChanges"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2 text-sm font-medium text-white hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg"
        >
            <svg v-if="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span v-if="!saving">Сохранить</span>
            <span v-else>Сохранение...</span>
        </button>
    </div>
@endsection

@section('content')
<div id="page-builder" class="flex h-[calc(100vh-180px)] min-h-[600px] gap-4" x-data="pageBuilder()" x-init="init()">
    <!-- Left Sidebar: Blocks Library -->
    <aside class="w-72 flex-shrink-0 border border-slate-200 rounded-xl bg-white shadow-sm flex flex-col overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <h3 class="font-semibold text-slate-800">Библиотека блоков</h3>
            </div>
            <p class="text-xs text-slate-500 mt-1">Нажмите на блок для добавления</p>
        </div>
        
        <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
            <template v-for="(block, type) in availableBlocks" :key="type">
                <div 
                    @click="addBlock(type)"
                    class="group block-item p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-blue-400 hover:shadow-md hover:shadow-blue-500/10 transition-all duration-200 active:scale-[0.98]"
                    :class="{'border-blue-300 bg-blue-50/50': hoveredBlockType === type}"
                    @mouseenter="hoveredBlockType = type"
                    @mouseleave="hoveredBlockType = null"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center group-hover:from-blue-100 group-hover:to-indigo-100 transition-colors">
                            <svg class="w-5 h-5 text-slate-600 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <template v-if="type === 'heading'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                                </template>
                                <template v-else-if="type === 'text'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h10"/>
                                </template>
                                <template v-else-if="type === 'button'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                                </template>
                                <template v-else-if="type === 'image'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </template>
                                <template v-else-if="type === 'divider'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"/>
                                </template>
                                <template v-else-if="type === 'faq'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </template>
                                <template v-else-if="type === 'html'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </template>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm text-slate-800">{{ block.name }}</div>
                            <div class="text-xs text-slate-500 truncate">Блок {{ type }}</div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                </div>
            </template>
        </div>
        
        <div class="p-3 border-t border-slate-100 bg-slate-50">
            <button 
                @click="clearBlocks"
                :disabled="content.length === 0"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Очистить все
            </button>
        </div>
    </aside>

    <!-- Center: Canvas Area -->
    <main class="flex-1 flex flex-col min-w-0 border border-slate-200 rounded-xl bg-slate-50 shadow-sm overflow-hidden">
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
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div :class="{
                'max-w-7xl mx-auto': viewMode === 'desktop',
                'max-w-3xl mx-auto': viewMode === 'tablet',
                'max-w-xs mx-auto': viewMode === 'mobile'
            }" class="transition-all duration-300">
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

                <!-- Blocks List with Drag & Drop -->
                <div class="space-y-3 blocks-container">
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
                                :disabled="index === 0"
                                class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded disabled:opacity-30 disabled:cursor-not-allowed"
                                title="Вверх"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                            </button>
                            <button 
                                @click.stop="moveBlockDown(index)"
                                :disabled="index === content.length - 1"
                                class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded disabled:opacity-30 disabled:cursor-not-allowed"
                                title="Вниз"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <button 
                                @click.stop="duplicateBlock(index)"
                                class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded"
                                title="Копировать"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                            <button 
                                @click.stop="deleteBlock(index)"
                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded"
                                title="Удалить"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Block Type Badge -->
                        <div class="absolute top-3 right-4 z-10">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                <template v-if="block.type === 'heading'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                                    </svg>
                                    Заголовок
                                </template>
                                <template v-else-if="block.type === 'text'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h10"/>
                                    </svg>
                                    Текст
                                </template>
                                <template v-else-if="block.type === 'button'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                                    </svg>
                                    Кнопка
                                </template>
                                <template v-else-if="block.type === 'image'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Изображение
                                </template>
                                <template v-else>{{ block.type }}</template>
                            </span>
                        </div>

                        <!-- Block Preview Content -->
                        <div class="p-5 pt-8">
                            <!-- Heading Block -->
                            <template v-if="block.type === 'heading'">
                                <component 
                                    :is="block.settings.level || 'h2'"
                                    class="vc-heading font-bold"
                                    :style="headingStyle(block.settings)"
                                >
                                    {{ block.settings.text || 'Заголовок' }}
                                </component>
                            </template>

                            <!-- Text Block -->
                            <template v-else-if="block.type === 'text'">
                                <div class="vc-text prose prose-slate max-w-none" :style="textStyle(block.settings)">
                                    {{ block.settings.text || 'Текстовый блок...' }}
                                </div>
                            </template>

                            <!-- Button Block -->
                            <template v-else-if="block.type === 'button'">
                                <div class="inline-block">
                                    <a 
                                        class="vc-button inline-flex items-center gap-2 px-6 py-2.5 rounded-lg font-medium transition-all"
                                        :class="{
                                            'bg-blue-600 text-white hover:bg-blue-700': block.settings.style === 'primary',
                                            'bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-300': block.settings.style === 'secondary'
                                        }"
                                        :href="block.settings.url || '#'""
                                        :target="block.settings.target || '_self'"
                                        @click.prevent
                                    >
                                        {{ block.settings.text || 'Кнопка' }}
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                        </svg>
                                    </a>
                                </div>
                            </template>

                            <!-- Divider Block -->
                            <template v-else-if="block.type === 'divider'">
                                <hr class="vc-divider my-4 border-t-2 border-slate-200">
                            </template>

                            <!-- FAQ Block -->
                            <template v-else-if="block.type === 'faq'">
                                <div class="vc-faq space-y-3">
                                    <details 
                                        v-for="(item, i) in block.settings.items || []" 
                                        :key="i"
                                        class="vc-faq-item group border border-slate-200 rounded-lg overflow-hidden"
                                    >
                                        <summary class="cursor-pointer font-medium p-4 bg-slate-50 group-hover:bg-slate-100 transition-colors flex items-center justify-between">
                                            {{ item.question || 'Вопрос?' }}
                                            <svg class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </summary>
                                        <div class="mt-2 text-slate-600 p-4 bg-white">{{ item.answer || 'Ответ...' }}</div>
                                    </details>
                                </div>
                            </template>

                            <!-- HTML Block -->
                            <template v-else-if="block.type === 'html'">
                                <div class="vc-html p-4 bg-slate-50 rounded-lg border border-slate-200 font-mono text-xs text-slate-500">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                        </svg>
                                        <span>HTML код</span>
                                    </div>
                                    <code class="block bg-white p-3 rounded border border-slate-200 overflow-x-auto">
                                        {{ block.settings.html || '<!-- HTML код -->' }}
                                    </code>
                                </div>
                            </template>

                            <!-- Image Block -->
                            <template v-else-if="block.type === 'image'">
                                <div class="vc-image border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-slate-500 text-sm">Изображение</p>
                                    <p class="text-slate-400 text-xs mt-1">ID: {{ block.settings.media_id || 'Не указан' }}</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Save Status Bar -->
        <div class="flex-shrink-0 px-4 py-2 border-t border-slate-200 bg-white flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <span :class="hasChanges ? 'text-amber-600' : 'text-emerald-600'" class="flex items-center gap-1.5">
                    <span :class="hasChanges ? 'w-2 h-2 bg-amber-500 rounded-full animate-pulse' : 'w-2 h-2 bg-emerald-500 rounded-full'"></span>
                    {{ hasChanges ? 'Есть несохраненные изменения' : 'Все сохранено' }}
                </span>
            </div>
            <div class="text-slate-400">
                Последнее сохранение: {{ lastSaved ? lastSaved.toLocaleTimeString() : 'не сохранялось' }}
            </div>
        </div>
    </main>

    <!-- Right Sidebar: Settings Panel -->
    <aside class="w-80 flex-shrink-0 border border-slate-200 rounded-xl bg-white shadow-sm flex flex-col overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h3 class="font-semibold text-slate-800">Настройки блока</h3>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <div v-if="selectedBlock !== null" class="p-4 space-y-5">
                <!-- Block Info Header -->
                <div class="pb-3 border-b border-slate-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Тип блока</span>
                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded">{{ selectedBlock.type }}</span>
                    </div>
                </div>

                <!-- Heading Settings -->
                <div v-if="selectedBlock.type === 'heading'" class="space-y-4">
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Текст заголовка
                        </label>
                        <input 
                            v-model="selectedBlock.settings.text"
                            @input="markAsChanged"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
                            placeholder="Введите текст заголовка"
                        >
                    </div>
                    
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Уровень заголовка
                        </label>
                        <select 
                            v-model="selectedBlock.settings.level"
                            @change="markAsChanged"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all bg-white"
                        >
                            <option value="h1">H1 - Главный заголовок</option>
                            <option value="h2">H2 - Подзаголовок</option>
                            <option value="h3">H3 - Раздел</option>
                            <option value="h4">H4 - Подраздел</option>
                            <option value="h5">H5 - Малый раздел</option>
                            <option value="h6">H6 - Минимальный</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                                Цвет
                            </label>
                            <div class="flex items-center gap-2">
                                <input 
                                    v-model="selectedBlock.settings.color"
                                    @input="markAsChanged"
                                    type="color"
                                    class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer"
                                >
                                <span class="text-xs text-slate-500 font-mono">{{ selectedBlock.settings.color || '#111827' }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                Выравнивание
                            </label>
                            <select 
                                v-model="selectedBlock.settings.align"
                                @change="markAsChanged"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all bg-white"
                            >
                                <option value="left">Слева</option>
                                <option value="center">По центру</option>
                                <option value="right">Справа</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Text Settings -->
                <div v-else-if="selectedBlock.type === 'text'" class="space-y-4">
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Текст
                        </label>
                        <textarea 
                            v-model="selectedBlock.settings.text"
                            @input="markAsChanged"
                            rows="5"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all resize-none"
                            placeholder="Введите текст..."
                        ></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">Цвет текста</label>
                            <div class="flex items-center gap-2">
                                <input 
                                    v-model="selectedBlock.settings.color"
                                    @input="markAsChanged"
                                    type="color"
                                    class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer"
                                >
                                <span class="text-xs text-slate-500 font-mono">{{ selectedBlock.settings.color || '#374151' }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">Выравнивание</label>
                            <select 
                                v-model="selectedBlock.settings.align"
                                @change="markAsChanged"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all bg-white"
                            >
                                <option value="left">Слева</option>
                                <option value="center">По центру</option>
                                <option value="right">Справа</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Button Settings -->
                <div v-else-if="selectedBlock.type === 'button'" class="space-y-4">
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">Текст кнопки</label>
                        <input 
                            v-model="selectedBlock.settings.text"
                            @input="markAsChanged"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
                            placeholder="Например: Узнать больше"
                        >
                    </div>
                    
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">URL ссылки</label>
                        <input 
                            v-model="selectedBlock.settings.url"
                            @input="markAsChanged"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
                            placeholder="/about или https://example.com"
                        >
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">Цель ссылки</label>
                            <select 
                                v-model="selectedBlock.settings.target"
                                @change="markAsChanged"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all bg-white"
                            >
                                <option value="_self">Текущая вкладка</option>
                                <option value="_blank">Новая вкладка</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">Стиль</label>
                            <select 
                                v-model="selectedBlock.settings.style"
                                @change="markAsChanged"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all bg-white"
                            >
                                <option value="primary">Основной</option>
                                <option value="secondary">Вторичный</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- FAQ Settings -->
                <div v-else-if="selectedBlock.type === 'faq'" class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-sm font-medium text-slate-700">Вопросы и ответы</span>
                        <button 
                            @click="addFaqItem"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Добавить
                        </button>
                    </div>
                    
                    <div v-for="(item, i) in selectedBlock.settings.items || []" :key="i" class="group p-3 bg-slate-50 rounded-lg border border-slate-200 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-xs font-medium text-slate-500">Вопрос #{{ i + 1 }}</span>
                            <button 
                                @click="removeFaqItem(i)"
                                class="p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                title="Удалить вопрос"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <input 
                            v-model="item.question"
                            @input="markAsChanged"
                            placeholder="Введите вопрос"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
                        >
                        <textarea 
                            v-model="item.answer"
                            @input="markAsChanged"
                            placeholder="Введите ответ"
                            rows="2"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all resize-none"
                        ></textarea>
                    </div>
                    
                    <div v-if="!selectedBlock.settings.items || selectedBlock.settings.items.length === 0" class="text-center py-6 text-slate-400">
                        <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm">Нет вопросов</p>
                        <p class="text-xs mt-1">Нажмите "Добавить" для создания</p>
                    </div>
                </div>

                <!-- HTML Settings -->
                <div v-else-if="selectedBlock.type === 'html'" class="space-y-4">
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            HTML код
                        </label>
                        <textarea 
                            v-model="selectedBlock.settings.html"
                            @input="markAsChanged"
                            rows="8"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-mono focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all resize-none"
                            placeholder="<div>Ваш HTML код...</div>"
                        ></textarea>
                        <p class="text-xs text-slate-500 mt-1">⚠️ Будьте осторожны при вставке кода из ненадежных источников</p>
                    </div>
                </div>

                <!-- Image Settings -->
                <div v-else-if="selectedBlock.type === 'image'" class="space-y-4">
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            ID медиафайла
                        </label>
                        <input 
                            v-model="selectedBlock.settings.media_id"
                            @input="markAsChanged"
                            type="number"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
                            placeholder="Например: 123"
                        >
                    </div>
                    
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Alt текст (описание)
                        </label>
                        <input 
                            v-model="selectedBlock.settings.alt"
                            @input="markAsChanged"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
                            placeholder="Описание изображения для доступности"
                        >
                    </div>
                </div>

                <!-- Common Actions -->
                <div class="pt-4 mt-4 border-t border-slate-100 space-y-2">
                    <button 
                        @click="duplicateBlock(selectedIndex)"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Копировать блок
                    </button>
                </div>
            </div>

            <div v-else class="p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-slate-700 mb-1">Блок не выбран</h3>
                <p class="text-xs text-slate-500">Кликните на блок на странице,<br>чтобы редактировать его настройки</p>
            </div>
        </div>
    </aside>
</div>

<!-- Preview Modal -->
<div 
    x-show="showPreview" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    style="display: none;"
    @keydown.escape.window="showPreview = false"
>
    <div 
        class="bg-white rounded-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden shadow-2xl"
        @click.away="showPreview = false"
    >
        <div class="p-5 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800">Предпросмотр страницы</h3>
                    <p class="text-xs text-slate-500">{{ $page->title }}</p>
                </div>
            </div>
            <button 
                @click="showPreview = false"
                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-100px)] bg-slate-50">
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
                <iframe 
                    v-if="previewHtml"
                    :srcdoc="previewHtml"
                    class="w-full min-h-[600px] border-0"
                    title="Preview"
                ></iframe>
                <div v-else class="py-20 text-center">
                    <svg class="w-12 h-12 mx-auto text-slate-300 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <p class="text-slate-500 mt-4">Загрузка превью...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications -->
<div 
    x-show="toast.visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed bottom-6 right-6 z-50"
    style="display: none;"
>
    <div 
        :class="{
            'bg-emerald-600': toast.type === 'success',
            'bg-red-600': toast.type === 'error',
            'bg-amber-600': toast.type === 'warning'
        }"
        class="rounded-lg shadow-lg px-4 py-3 text-white flex items-center gap-3"
    >
        <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <svg v-else-if="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span class="text-sm font-medium">{{ toast.message }}</span>
        <button @click="toast.visible = false" class="ml-2 hover:opacity-80">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endsection

@push('styles')
<style>
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
    
    .vc-heading {
        transition: all 0.2s ease;
    }
    
    .vc-text {
        line-height: 1.7;
    }
    
    /* SortableJS styles */
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
    
    .blocks-container {
        min-height: 100px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="{{ mix('js/app.js') }}"></script>
<script>
    const { createApp, ref, reactive, computed, onMounted, watch, nextTick } = Vue;
    
    function pageBuilder() {
        const page = @json($page);
        const content = reactive(@json($page->content_json['sections'][0]['blocks'] ?? []));
        const availableBlocks = ref({});
        const selectedIndex = ref(-1);
        const showPreview = ref(false);
        const previewHtml = ref('');
        const saving = ref(false);
        const hasChanges = ref(false);
        const lastSaved = ref(null);
        const viewMode = ref('desktop');
        const hoveredBlockType = ref(null);
        const draggingIndex = ref(null);
        const dragOverIndex = ref(null);
        let sortableInstance = null;
        
        // Undo/Redo history
        const history = ref([]);
        const historyIndex = ref(-1);
        const maxHistory = 50;
        
        // Toast notifications
        const toast = reactive({
            visible: false,
            message: '',
            type: 'success'
        });
        
        let saveTimeout = null;

        const selectedBlock = computed(() => {
            if (selectedIndex.value === -1) return null;
            return content[selectedIndex.value] || null;
        });
        
        const canUndo = computed(() => historyIndex.value > 0);
        const canRedo = computed(() => historyIndex.value < history.value.length - 1);

        function showToast(message, type = 'success') {
            toast.message = message;
            toast.type = type;
            toast.visible = true;
            setTimeout(() => { toast.visible = false; }, 3000);
        }

        function saveToHistory() {
            // Remove any future history if we're in the middle
            if (historyIndex.value < history.value.length - 1) {
                history.value = history.value.slice(0, historyIndex.value + 1);
            }
            
            // Add current state
            history.value.push(JSON.stringify(content));
            if (history.value.length > maxHistory) {
                history.value.shift();
            } else {
                historyIndex.value++;
            }
        }

        function undo() {
            if (!canUndo.value) return;
            historyIndex.value--;
            const previousState = JSON.parse(history.value[historyIndex.value]);
            content.splice(0, content.length, ...previousState);
            hasChanges.value = true;
        }

        function redo() {
            if (!canRedo.value) return;
            historyIndex.value++;
            const nextState = JSON.parse(history.value[historyIndex.value]);
            content.splice(0, content.length, ...nextState);
            hasChanges.value = true;
        }

        function markAsChanged() {
            hasChanges.value = true;
            saveToHistory();
            
            // Auto-save after 30 seconds of inactivity
            if (saveTimeout) clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                if (hasChanges.value) {
                    saveContent(true);
                }
            }, 30000);
        }

        function setViewMode(mode) {
            viewMode.value = mode;
        }

        onMounted(() => {
            fetchAvailableBlocks();
            saveToHistory(); // Initial state
            
            // Initialize SortableJS for drag-and-drop
            nextTick(() => {
                const container = document.querySelector('.blocks-container');
                if (container) {
                    sortableInstance = new Sortable(container, {
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
                                saveToHistory();
                                const draggedItem = content[oldIndex];
                                content.splice(oldIndex, 1);
                                content.splice(newIndex, 0, draggedItem);
                                selectedIndex.value = newIndex;
                                hasChanges.value = true;
                                showToast('Блок перемещён', 'success');
                            }
                        }
                    });
                }
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', handleKeyboard);
        });

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
            // Ctrl+S for save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                if (hasChanges.value) {
                    saveContent();
                }
            }
            // Delete key to remove selected block
            if (e.key === 'Delete' && selectedIndex.value !== -1) {
                e.preventDefault();
                deleteBlock(selectedIndex.value);
            }
        }

        async function fetchAvailableBlocks() {
            try {
                const response = await fetch('/admin/api/builder/blocks');
                const data = await response.json();
                availableBlocks.value = data.blocks || {};
            } catch (e) {
                console.error('Failed to load blocks:', e);
                showToast('Не удалось загрузить блоки', 'error');
            }
        }

        function addBlock(type) {
            const defaultBlock = availableBlocks.value?.[type]?.default;
            if (!defaultBlock) return;

            const newBlock = {
                type,
                settings: JSON.parse(JSON.stringify(defaultBlock.settings || defaultBlock)),
                _id: Date.now().toString()
            };

            saveToHistory();

            if (selectedIndex.value === -1) {
                content.push(newBlock);
                selectedIndex.value = content.length - 1;
            } else {
                content.splice(selectedIndex.value + 1, 0, newBlock);
                selectedIndex.value++;
            }
            
            hasChanges.value = true;
            showToast(`Блок "${type}" добавлен`);
        }

        function selectBlock(index) {
            selectedIndex.value = index;
        }

        function deleteBlock(index) {
            saveToHistory();
            content.splice(index, 1);
            if (selectedIndex.value >= index) {
                selectedIndex.value = Math.max(-1, selectedIndex.value - 1);
            }
            hasChanges.value = true;
            showToast('Блок удален');
        }

        function moveBlockUp(index) {
            if (index > 0) {
                saveToHistory();
                const temp = content[index];
                content[index] = content[index - 1];
                content[index - 1] = temp;
                selectedIndex.value = index - 1;
                hasChanges.value = true;
            }
        }

        function moveBlockDown(index) {
            if (index < content.length - 1) {
                saveToHistory();
                const temp = content[index];
                content[index] = content[index + 1];
                content[index + 1] = temp;
                selectedIndex.value = index + 1;
                hasChanges.value = true;
            }
        }

        function duplicateBlock(index) {
            if (index === -1) return;
            saveToHistory();
            const copy = JSON.parse(JSON.stringify(content[index]));
            copy._id = Date.now().toString();
            content.splice(index + 1, 0, copy);
            selectedIndex.value = index + 1;
            hasChanges.value = true;
            showToast('Блок скопирован');
        }

        function addFaqItem() {
            if (!selectedBlock.value?.settings?.items) return;
            selectedBlock.value.settings.items.push({
                question: 'Новый вопрос',
                answer: 'Новый ответ'
            });
            hasChanges.value = true;
        }

        function removeFaqItem(index) {
            if (!selectedBlock.value?.settings?.items) return;
            selectedBlock.value.settings.items.splice(index, 1);
            hasChanges.value = true;
        }

        function clearBlocks() {
            if (content.length === 0) return;
            if (confirm('Вы уверены, что хотите удалить все блоки? Это действие нельзя отменить.')) {
                saveToHistory();
                content.splice(0, content.length);
                selectedIndex.value = -1;
                hasChanges.value = true;
                showToast('Все блоки удалены', 'warning');
            }
        }

        // Drag and Drop handlers (kept for backward compatibility, but SortableJS is now primary)
        function startDrag(index) {
            draggingIndex.value = index;
        }

        function handleDragStart(event, index) {
            draggingIndex.value = index;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', index);
        }

        function handleDragOver(event, index) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            dragOverIndex.value = index;
        }

        function handleDrop(event, dropIndex) {
            event.preventDefault();
            if (draggingIndex.value === null || draggingIndex.value === dropIndex) return;
            
            saveToHistory();
            const draggedItem = content[draggingIndex.value];
            content.splice(draggingIndex.value, 1);
            content.splice(dropIndex, 0, draggedItem);
            selectedIndex.value = dropIndex;
            hasChanges.value = true;
            
            draggingIndex.value = null;
            dragOverIndex.value = null;
        }

        function handleDragEnd() {
            draggingIndex.value = null;
            dragOverIndex.value = null;
        }

        async function previewContent() {
            const blocks = content.map(block => {
                if (['heading', 'text', 'button', 'divider', 'faq', 'html', 'image'].includes(block.type)) {
                    return {
                        type: block.type,
                        settings: { ...block.settings }
                    };
                }
                return null;
            }).filter(Boolean);

            try {
                const response = await fetch('/admin/api/builder/render-preview', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({ content: [{ settings: {}, blocks }] })
                });
                const data = await response.json();
                previewHtml.value = data.html;
                showPreview.value = true;
            } catch (e) {
                showToast('Ошибка предпросмотра: ' + e.message, 'error');
            }
        }

        async function saveContent(isAutoSave = false) {
            if (!hasChanges.value) return;
            
            saving.value = true;
            try {
                const response = await fetch(`/admin/pages/${page.id}/builder`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({ 
                        content_json: JSON.stringify({ 
                            version: '1.0', 
                            layout: 'default', 
                            sections: [{ settings: {}, blocks: content }] 
                        }) 
                    })
                });
                
                if (response.ok) {
                    hasChanges.value = false;
                    lastSaved.value = new Date();
                    historyIndex.value = history.value.length - 1; // Reset redo stack
                    
                    if (!isAutoSave) {
                        showToast('Страница успешно сохранена!', 'success');
                    }
                } else {
                    const data = await response.json();
                    throw new Error(data.message || 'Не удалось сохранить');
                }
            } catch (e) {
                showToast('Ошибка сохранения: ' + e.message, 'error');
            } finally {
                saving.value = false;
                if (saveTimeout) clearTimeout(saveTimeout);
            }
        }

        function headingStyle(settings) {
            return {
                color: settings.color || '#111827',
                textAlign: settings.align || 'left',
                fontSize: {
                    h1: '2.25rem', h2: '1.875rem', h3: '1.5rem',
                    h4: '1.25rem', h5: '1.125rem', h6: '1rem'
                }[settings.level || 'h2'],
                fontWeight: settings.font_weight || (settings.level === 'h1' ? '700' : '600'),
                lineHeight: '1.3',
                marginBottom: '0.75rem'
            };
        }

        function textStyle(settings) {
            return {
                color: settings.color || '#374151',
                textAlign: settings.align || 'left',
                lineHeight: '1.7',
                fontSize: '1rem'
            };
        }

        return {
            page,
            content,
            availableBlocks,
            selectedIndex,
            selectedBlock,
            showPreview,
            previewHtml,
            saving,
            hasChanges,
            lastSaved,
            viewMode,
            hoveredBlockType,
            draggingIndex,
            toast,
            canUndo,
            canRedo,
            addBlock,
            selectBlock,
            deleteBlock,
            moveBlockUp,
            moveBlockDown,
            duplicateBlock,
            addFaqItem,
            removeFaqItem,
            clearBlocks,
            previewContent,
            saveContent,
            headingStyle,
            textStyle,
            setViewMode,
            markAsChanged,
            undo,
            redo,
            startDrag,
            handleDragStart,
            handleDragOver,
            handleDrop,
            handleDragEnd,
            showToast
        };
    }
    
    createApp(pageBuilder).mount('#page-builder');
</script>
@endpush
