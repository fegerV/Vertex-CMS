<!-- resources/views/admin/builder/edit.blade.php -->
@extends('admin.layouts.app')

@section('title', 'Page Builder - ' . $page->title . ' - VertexCMS')
@section('page_title', 'Page Builder')
@section('page_subtitle', $page->title)

@section('content')
<div id="page-builder" class="vc-builder-shell">
    <!-- Sidebar: Blocks Library -->
    <aside class="vc-builder-sidebar vc-builder-scroll flex w-80 flex-col border-r">
        <div class="border-b border-[var(--vc-border)] p-4">
            <h3 class="vc-builder-panel-title">Блоки</h3>
            <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Библиотека базовых блоков для быстрой сборки страницы.</p>
        </div>
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            <template v-for="(block, type) in availableBlocks" :key="type">
                <div 
                    @click="addBlock(type)"
                    class="vc-builder-card block-item cursor-pointer p-3"
                >
                    <div class="font-medium text-sm text-[var(--vc-text)]">@{{ block.name }}</div>
                    <div class="mt-1 text-xs text-[var(--vc-text-muted)]">Нажми чтобы добавить</div>
                </div>
            </template>
        </div>
        <div class="border-t border-[var(--vc-border)] p-3">
            <button 
                @click="clearBlocks"
                class="vc-button vc-button-danger w-full px-3 py-3"
            >
                Очистить все
            </button>
        </div>
    </aside>

    <!-- Canvas -->
    <main class="vc-builder-canvas flex-1 overflow-y-auto p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Page header info -->
            <div class="vc-panel vc-panel-strong mb-6 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--vc-text)]">@{{ page.title }}</h2>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">URI: @{{ page.uri }} | Статус: @{{ page.status }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="previewContent"
                            class="vc-button vc-button-secondary px-4 py-3"
                        >
                            Превью
                        </button>
                        <button 
                            @click="saveContent"
                            :disabled="saving"
                            class="vc-button vc-button-primary px-4 py-3 disabled:opacity-50"
                        >
                            <span v-if="!saving">Сохранить</span>
                            <span v-else>Сохранение...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Blocks Canvas -->
            <div class="space-y-4">
                <div 
                    v-if="content.length === 0"
                    class="vc-builder-empty text-center py-20"
                >
                    <p>Выберите блок слева, чтобы начать</p>
                </div>

                <div 
                    v-for="(block, index) in content" 
                    :key="block._id"
                    class="vc-builder-card relative"
                    :class="{ 'vc-builder-card-active': selectedIndex === index }"
                    @click="selectBlock(index)"
                >
                    <!-- Selected overlay -->
                    <div 
                        v-if="selectedIndex === index" 
                        class="vc-builder-floating-controls absolute -top-2 -right-2 z-10 flex gap-1"
                    >
                        <button 
                            @click.stop="moveBlockUp(index)"
                            class="rounded-l p-1"
                            title="Вверх"
                        >
                            ↑
                        </button>
                        <button 
                            @click.stop="moveBlockDown(index)"
                            class="p-1"
                            title="Вниз"
                        >
                            ↓
                        </button>
                        <button 
                            @click.stop="deleteBlock(index)"
                            class="rounded-r p-1 text-rose-300"
                            title="Удалить"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Block Content -->
                    <div class="p-4">
                        <!-- Heading Block -->
                        <template v-if="block.type === 'heading'">
                            <component 
                                :is="block.settings.level || 'h2'"
                                class="vc-heading"
                                :style="headingStyle(block.settings)"
                                contenteditable="false"
                            >
                                @{{ block.settings.text || 'Заголовок' }}
                            </component>
                            <div class="vc-builder-meta mt-2">
                                Заголовок (@{{ block.settings.level || 'h2' }})
                            </div>
                        </template>

                        <!-- Text Block -->
                        <template v-else-if="block.type === 'text'">
                            <div class="vc-text" :style="textStyle(block.settings)">
                                 @{{ block.settings.text || 'Текстовый блок...' }}
                            </div>
                            <div class="vc-builder-meta mt-2">Текст</div>
                        </template>

                        <!-- Button Block -->
                        <template v-else-if="block.type === 'button'">
                            <div class="inline-block">
                                <a 
                                    class="vc-button vc-button-@{{ block.settings.style || 'primary' }}"
                                    :href="block.settings.url || '#'"
                                    :target="block.settings.target || '_self'"
                                >
                                     @{{ block.settings.text || 'Кнопка' }}
                                </a>
                            </div>
                            <div class="vc-builder-meta mt-2">Кнопка</div>
                        </template>

                        <!-- Divider Block -->
                        <template v-else-if="block.type === 'divider'">
                            <hr class="vc-divider my-4">
                            <div class="vc-builder-meta">Разделитель</div>
                        </template>

                        <!-- FAQ Block -->
                        <template v-else-if="block.type === 'faq'">
                            <div class="vc-faq">
                                <details 
                                    v-for="(item, i) in block.settings.items || []" 
                                    :key="i"
                                    class="vc-faq-item"
                                >
                                     <summary class="cursor-pointer font-medium">@{{ item.question || 'Вопрос?' }}</summary>
                                     <div class="mt-2 text-[var(--vc-text-muted)]">@{{ item.answer || 'Ответ...' }}</div>
                                </details>
                            </div>
                            <div class="vc-builder-meta mt-2">FAQ</div>
                        </template>

                        <!-- HTML Block -->
                        <template v-else-if="block.type === 'html'">
                            <div class="vc-html">
                                <span class="vc-builder-meta">[ HTML код ]</span>
                            </div>
                            <div class="vc-builder-meta mt-2">HTML</div>
                        </template>

                        <!-- Image Block -->
                        <template v-else-if="block.type === 'image'">
                            <div class="vc-builder-empty rounded-lg p-8 text-center">
                                <span>Изображение</span>
                            </div>
                            <div class="vc-builder-meta mt-2">Изображение</div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Settings Panel -->
    <aside class="vc-builder-sidebar vc-builder-scroll w-96 overflow-y-auto border-l">
        <div v-if="selectedBlock !== null" class="p-6 space-y-4">
            <h3 class="mb-4 text-lg font-semibold text-[var(--vc-text)]">Настройки блока</h3>
            
            <!-- Heading Settings -->
            <div v-if="selectedBlock.type === 'heading'" class="vc-builder-form-section">
                <div class="vc-builder-form-title">Контент</div>
                <div class="vc-builder-field">
                    <label>Текст</label>
                    <input 
                        v-model="selectedBlock.settings.text"
                        type="text"
                        class="vc-input"
                    >
                </div>
                <div class="vc-builder-field">
                    <label>Уровень</label>
                    <select 
                        v-model="selectedBlock.settings.level"
                        class="vc-select"
                    >
                        <option value="h1">H1</option>
                        <option value="h2">H2</option>
                        <option value="h3">H3</option>
                        <option value="h4">H4</option>
                        <option value="h5">H5</option>
                        <option value="h6">H6</option>
                    </select>
                </div>
                <div class="vc-builder-field">
                    <label>Цвет</label>
                    <div class="vc-builder-swatch-row">
                        <div class="vc-builder-swatch">
                            <input 
                                v-model="selectedBlock.settings.color"
                                type="color"
                            >
                        </div>
                         <span class="vc-builder-field-hint">@{{ selectedBlock.settings.color || '#111827' }}</span>
                    </div>
                </div>
                <div class="vc-builder-field">
                    <label>Выравнивание</label>
                    <select 
                        v-model="selectedBlock.settings.align"
                        class="vc-select"
                    >
                        <option value="left">По левому краю</option>
                        <option value="center">По центру</option>
                        <option value="right">По правому краю</option>
                    </select>
                </div>
            </div>

            <!-- Text Settings -->
            <div v-else-if="selectedBlock.type === 'text'" class="vc-builder-form-section">
                <div class="vc-builder-form-title">Текстовый блок</div>
                <div class="vc-builder-field">
                    <label>Текст</label>
                    <textarea 
                        v-model="selectedBlock.settings.text"
                        rows="4"
                        class="vc-textarea"
                    ></textarea>
                </div>
                <div class="vc-builder-field">
                    <label>Цвет</label>
                    <div class="vc-builder-swatch-row">
                        <div class="vc-builder-swatch">
                            <input 
                                v-model="selectedBlock.settings.color"
                                type="color"
                            >
                        </div>
                         <span class="vc-builder-field-hint">@{{ selectedBlock.settings.color || '#374151' }}</span>
                    </div>
                </div>
                <div class="vc-builder-field">
                    <label>Выравнивание</label>
                    <select 
                        v-model="selectedBlock.settings.align"
                        class="vc-select"
                    >
                        <option value="left">По левому краю</option>
                        <option value="center">По центру</option>
                        <option value="right">По правому краю</option>
                    </select>
                </div>
            </div>

            <!-- Button Settings -->
            <div v-else-if="selectedBlock.type === 'button'" class="vc-builder-form-section">
                <div class="vc-builder-form-title">Кнопка</div>
                <div class="vc-builder-field">
                    <label>Текст</label>
                    <input 
                        v-model="selectedBlock.settings.text"
                        type="text"
                        class="vc-input"
                    >
                </div>
                <div class="vc-builder-field">
                    <label>URL</label>
                    <input 
                        v-model="selectedBlock.settings.url"
                        type="text"
                        class="vc-input"
                    >
                </div>
                <div class="vc-builder-field">
                    <label>Цель</label>
                    <select 
                        v-model="selectedBlock.settings.target"
                        class="vc-select"
                    >
                        <option value="_self">Текущая вкладка</option>
                        <option value="_blank">Новая вкладка</option>
                    </select>
                </div>
                <div class="vc-builder-field">
                    <label>Стиль</label>
                    <select 
                        v-model="selectedBlock.settings.style"
                        class="vc-select"
                    >
                        <option value="primary">Основной</option>
                        <option value="secondary">Вторичный</option>
                    </select>
                </div>
            </div>

            <!-- FAQ Settings -->
            <div v-else-if="selectedBlock.type === 'faq'" class="vc-builder-form-section">
                <div class="vc-builder-inline-actions">
                    <div class="vc-builder-form-title">FAQ</div>
                    <button 
                        @click="addFaqItem"
                        class="vc-button vc-button-secondary px-3 py-2 text-xs"
                    >
                        + Добавить
                    </button>
                </div>
                <div v-for="(item, i) in selectedBlock.settings.items || []" :key="i" class="vc-builder-settings-card">
                    <input 
                        v-model="item.question"
                        placeholder="Вопрос"
                        class="vc-input"
                    >
                    <textarea 
                        v-model="item.answer"
                        placeholder="Ответ"
                        class="vc-textarea"
                    ></textarea>
                    <button 
                        @click="removeFaqItem(i)"
                        class="text-xs font-semibold text-[var(--vc-danger)]"
                    >
                        Удалить
                    </button>
                </div>
            </div>

            <!-- HTML Settings -->
            <div v-else-if="selectedBlock.type === 'html'" class="vc-builder-form-section">
                <div class="vc-builder-form-title">HTML</div>
                <div class="vc-builder-field">
                    <label>HTML код</label>
                    <textarea 
                        v-model="selectedBlock.settings.html"
                        rows="6"
                        class="vc-textarea font-mono"
                        placeholder="<p>HTML код...</p>"
                    ></textarea>
                </div>
            </div>

            <!-- Image Settings -->
            <div v-else-if="selectedBlock.type === 'image'" class="vc-builder-form-section">
                <div class="vc-builder-form-title">Изображение</div>
                <div class="vc-builder-field">
                    <label>Media ID</label>
                    <input 
                        v-model="selectedBlock.settings.media_id"
                        type="number"
                        class="vc-input"
                        placeholder="ID медиафайла"
                    >
                </div>
                <div class="vc-builder-field">
                    <label>Alt текст</label>
                    <input 
                        v-model="selectedBlock.settings.alt"
                        type="text"
                        class="vc-input"
                    >
                </div>
            </div>

            <!-- Common Block Settings -->
            <div class="vc-builder-form-section">
                <div class="vc-builder-form-title">Действия</div>
                 <div class="vc-builder-field-hint">Тип блока: @{{ selectedBlock.type }}</div>
                <button 
                    @click="duplicateBlock"
                    class="vc-button vc-button-secondary w-full justify-center px-4 py-3"
                >
                    Копировать блок
                </button>
            </div>
        </div>

            <div v-else class="p-6 text-center text-[var(--vc-text-muted)]">
            <p class="text-sm">Выберите блок для редактирования</p>
        </div>
    </aside>
</div>

<!-- Preview Modal -->
<div v-if="showPreview" class="vc-builder-modal fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="vc-builder-modal-card w-full max-w-5xl max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between border-b border-[var(--vc-border)] p-4">
            <h3 class="font-semibold text-[var(--vc-text)]">Превью страницы</h3>
            <button @click="showPreview = false" class="text-[var(--vc-text-soft)] hover:text-[var(--vc-text)]">✕</button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
            <iframe 
                v-if="previewHtml"
                :srcdoc="previewHtml"
                class="w-full min-h-[500px] border-0"
            ></iframe>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ mix('js/app.js') }}"></script>
<script>
    const { createApp, ref, reactive, computed, onMounted } = Vue;
    
    createApp({
        setup() {
            const page = @json($page);
            const content = reactive(@json($page->content_json['sections'][0]['blocks'] ?? []));
            const availableBlocks = ref({});
            const selectedIndex = ref(-1);
            const showPreview = ref(false);
            const previewHtml = ref('');
            const saving = ref(false);

            const selectedBlock = computed(() => {
                if (selectedIndex.value === -1) return null;
                return content[selectedIndex.value] || null;
            });

            onMounted(() => {
                fetchAvailableBlocks();
            });

            async function fetchAvailableBlocks() {
                try {
                    const response = await fetch('/admin/api/builder/blocks');
                    const data = await response.json();
                    availableBlocks.value = data.blocks || {};
                } catch (e) {
                    console.error('Failed to load blocks:', e);
                }
            }

            function addBlock(type) {
                const defaultBlock = availableBlocks.value?.[type]?.default;
                if (!defaultBlock) return;

                const newBlock = {
                    type,
                    settings: JSON.parse(JSON.stringify(defaultBlock.settings || defaultBlock))
                };

                if (selectedIndex.value === -1) {
                    content.push(newBlock);
                    selectedIndex.value = content.length - 1;
                } else {
                    content.splice(selectedIndex.value + 1, 0, newBlock);
                    selectedIndex.value++;
                }
            }

            function selectBlock(index) {
                selectedIndex.value = index;
            }

            function deleteBlock(index) {
                content.splice(index, 1);
                if (selectedIndex.value >= index) {
                    selectedIndex.value = -1;
                }
            }

            function moveBlockUp(index) {
                if (index > 0) {
                    const temp = content[index];
                    content[index] = content[index - 1];
                    content[index - 1] = temp;
                    selectedIndex.value = index - 1;
                }
            }

            function moveBlockDown(index) {
                if (index < content.length - 1) {
                    const temp = content[index];
                    content[index] = content[index + 1];
                    content[index + 1] = temp;
                    selectedIndex.value = index + 1;
                }
            }

            function duplicateBlock() {
                if (selectedIndex.value === -1) return;
                const copy = JSON.parse(JSON.stringify(content[selectedIndex.value]));
                content.splice(selectedIndex.value + 1, 0, copy);
                selectedIndex.value++;
            }

            function addFaqItem() {
                if (!selectedBlock.value?.settings?.items) return;
                selectedBlock.value.settings.items.push({
                    question: 'Новый вопрос',
                    answer: 'Новый ответ'
                });
            }

            function removeFaqItem(index) {
                if (!selectedBlock.value?.settings?.items) return;
                selectedBlock.value.settings.items.splice(index, 1);
            }

            function clearBlocks() {
                if (confirm('Очистить все блоки?')) {
                    content.splice(0, content.length);
                    selectedIndex.value = -1;
                }
            }

            async function previewContent() {
                const blocks = content.map(block => {
                    if (block.type === 'heading' || block.type === 'text' || block.type === 'button' || 
                        block.type === 'divider' || block.type === 'faq' || block.type === 'html' || 
                        block.type === 'image') {
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
                    alert('Ошибка превью: ' + e.message);
                }
            }

            async function saveContent() {
                saving.value = true;
                try {
                    const response = await fetch(`/admin/pages/${page.id}/builder`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({ content_json: JSON.stringify({ version: '1.0', layout: 'default', sections: [{ settings: {}, blocks: content }] }) })
                    });
                    if (response.ok) {
                        alert('Сохранено!');
                    } else {
                        const data = await response.json();
                        alert('Ошибка: ' + (data.message || 'Не удалось сохранить'));
                    }
                } catch (e) {
                    alert('Ошибка сети: ' + e.message);
                } finally {
                    saving.value = false;
                }
            }

            function headingStyle(settings) {
                return {
                    color: settings.color || '#111827',
                    textAlign: settings.align || 'left',
                    fontSize: {
                        h1: '2rem', h2: '1.5rem', h3: '1.25rem',
                        h4: '1.125rem', h5: '1rem', h6: '0.875rem'
                    }[settings.level || 'h2'],
                    fontWeight: settings.font_weight || (settings.level === 'h1' ? 'bold' : 'normal')
                };
            }

            function textStyle(settings) {
                return {
                    color: settings.color || '#374151',
                    textAlign: settings.align || 'left'
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
                textStyle
            };
        }
    }).mount('#page-builder');
</script>
@endpush
