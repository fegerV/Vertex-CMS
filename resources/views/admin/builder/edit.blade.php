<!-- resources/views/admin/builder/edit.blade.php -->
@extends('admin.layouts.app')

@section('title', 'Page Builder - ' . $page->title . ' - VertexCMS')
@section('page_title', 'Page Builder')
@section('page_subtitle', $page->title)

@section('content')
<div id="page-builder" class="flex h-[calc(100vh-80px)]">
    <!-- Sidebar: Blocks Library -->
    <aside class="w-80 border-r border-slate-200 bg-slate-50 flex flex-col">
        <div class="p-4 border-b border-slate-200 bg-white">
            <h3 class="font-semibold text-sm uppercase tracking-wide">Блоки</h3>
        </div>
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            <template v-for="(block, type) in availableBlocks" :key="type">
                <div 
                    @click="addBlock(type)"
                    class="block-item p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-slate-400 hover:shadow transition-all"
                >
                    <div class="font-medium text-sm">{{ block.name }}</div>
                    <div class="text-xs text-slate-500 mt-1">Нажми чтобы добавить</div>
                </div>
            </template>
        </div>
        <div class="p-3 border-t border-slate-200 bg-white">
            <button 
                @click="clearBlocks"
                class="w-full rounded-md border border-red-200 px-3 py-2 text-sm text-red-600 hover:bg-red-50"
            >
                Очистить все
            </button>
        </div>
    </aside>

    <!-- Canvas -->
    <main class="flex-1 overflow-y-auto bg-slate-100 p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Page header info -->
            <div class="mb-6 bg-white rounded-lg border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold">{{ page.title }}</h2>
                        <p class="text-sm text-slate-500">URI: {{ page.uri }} | Статус: {{ page.status }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="previewContent"
                            class="px-4 py-2 text-sm rounded-md border border-slate-300 bg-white hover:bg-slate-50"
                        >
                            Превью
                        </button>
                        <button 
                            @click="saveContent"
                            :disabled="saving"
                            class="px-4 py-2 text-sm rounded-md bg-slate-950 text-white hover:bg-slate-800 disabled:opacity-50"
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
                    class="text-center py-20 bg-white rounded-lg border-2 border-dashed border-slate-300"
                >
                    <p class="text-slate-400">Выберите блок слева, чтобы начать</p>
                </div>

                <div 
                    v-for="(block, index) in content" 
                    :key="block._id"
                    class="relative bg-white rounded-lg border-2 transition-all"
                    :class="{ 'border-slate-200': selectedIndex !== index, 'border-blue-500 shadow-lg': selectedIndex === index }"
                    @click="selectBlock(index)"
                >
                    <!-- Selected overlay -->
                    <div 
                        v-if="selectedIndex === index" 
                        class="absolute -top-2 -right-2 z-10 flex gap-1 bg-slate-950 rounded-md"
                    >
                        <button 
                            @click.stop="moveBlockUp(index)"
                            class="p-1 text-white hover:bg-slate-700 rounded-l"
                            title="Вверх"
                        >
                            ↑
                        </button>
                        <button 
                            @click.stop="moveBlockDown(index)"
                            class="p-1 text-white hover:bg-slate-700"
                            title="Вниз"
                        >
                            ↓
                        </button>
                        <button 
                            @click.stop="deleteBlock(index)"
                            class="p-1 text-red-400 hover:bg-slate-700 hover:text-white rounded-r"
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
                                {{ block.settings.text || 'Заголовок' }}
                            </component>
                            <div class="mt-2 text-xs text-slate-400">
                                Заголовок ({{ block.settings.level || 'h2' }})
                            </div>
                        </template>

                        <!-- Text Block -->
                        <template v-else-if="block.type === 'text'">
                            <div class="vc-text" :style="textStyle(block.settings)">
                                {{ block.settings.text || 'Текстовый блок...' }}
                            </div>
                            <div class="mt-2 text-xs text-slate-400">Текст</div>
                        </template>

                        <!-- Button Block -->
                        <template v-else-if="block.type === 'button'">
                            <div class="inline-block">
                                <a 
                                    class="vc-button vc-button-{{ block.settings.style || 'primary' }}"
                                    :href="block.settings.url || '#'"
                                    :target="block.settings.target || '_self'"
                                >
                                    {{ block.settings.text || 'Кнопка' }}
                                </a>
                            </div>
                            <div class="mt-2 text-xs text-slate-400">Кнопка</div>
                        </template>

                        <!-- Divider Block -->
                        <template v-else-if="block.type === 'divider'">
                            <hr class="vc-divider my-4">
                            <div class="text-xs text-slate-400">Разделитель</div>
                        </template>

                        <!-- FAQ Block -->
                        <template v-else-if="block.type === 'faq'">
                            <div class="vc-faq">
                                <details 
                                    v-for="(item, i) in block.settings.items || []" 
                                    :key="i"
                                    class="vc-faq-item"
                                >
                                    <summary class="cursor-pointer font-medium">{{ item.question || 'Вопрос?' }}</summary>
                                    <div class="mt-2 text-slate-600">{{ item.answer || 'Ответ...' }}</div>
                                </details>
                            </div>
                            <div class="mt-2 text-xs text-slate-400">FAQ</div>
                        </template>

                        <!-- HTML Block -->
                        <template v-else-if="block.type === 'html'">
                            <div class="vc-html">
                                <span class="text-xs text-slate-400">[ HTML код ]</span>
                            </div>
                            <div class="mt-2 text-xs text-slate-400">HTML</div>
                        </template>

                        <!-- Image Block -->
                        <template v-else-if="block.type === 'image'">
                            <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center">
                                <span class="text-slate-400">Изображение</span>
                            </div>
                            <div class="mt-2 text-xs text-slate-400">Изображение</div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Settings Panel -->
    <aside class="w-96 border-l border-slate-200 bg-white overflow-y-auto">
        <div v-if="selectedBlock !== null" class="p-6">
            <h3 class="font-semibold mb-4">Настройки блока</h3>
            
            <!-- Heading Settings -->
            <div v-if="selectedBlock.type === 'heading'" class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Текст</label>
                    <input 
                        v-model="selectedBlock.settings.text"
                        type="text"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Уровень</label>
                    <select 
                        v-model="selectedBlock.settings.level"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="h1">H1</option>
                        <option value="h2">H2</option>
                        <option value="h3">H3</option>
                        <option value="h4">H4</option>
                        <option value="h5">H5</option>
                        <option value="h6">H6</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Цвет</label>
                    <input 
                        v-model="selectedBlock.settings.color"
                        type="color"
                        class="w-10 h-10"
                    >
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Выравнивание</label>
                    <select 
                        v-model="selectedBlock.settings.align"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="left">По левому краю</option>
                        <option value="center">По центру</option>
                        <option value="right">По правому краю</option>
                    </select>
                </div>
            </div>

            <!-- Text Settings -->
            <div v-else-if="selectedBlock.type === 'text'" class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Текст</label>
                    <textarea 
                        v-model="selectedBlock.settings.text"
                        rows="4"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    ></textarea>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Цвет</label>
                    <input 
                        v-model="selectedBlock.settings.color"
                        type="color"
                        class="w-10 h-10"
                    >
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Выравнивание</label>
                    <select 
                        v-model="selectedBlock.settings.align"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="left">По левому краю</option>
                        <option value="center">По центру</option>
                        <option value="right">По правому краю</option>
                    </select>
                </div>
            </div>

            <!-- Button Settings -->
            <div v-else-if="selectedBlock.type === 'button'" class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Текст</label>
                    <input 
                        v-model="selectedBlock.settings.text"
                        type="text"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">URL</label>
                    <input 
                        v-model="selectedBlock.settings.url"
                        type="text"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Цель</label>
                    <select 
                        v-model="selectedBlock.settings.target"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="_self">Текущая вкладка</option>
                        <option value="_blank">Новая вкладка</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Стиль</label>
                    <select 
                        v-model="selectedBlock.settings.style"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="primary">Основной</option>
                        <option value="secondary">Вторичный</option>
                    </select>
                </div>
            </div>

            <!-- FAQ Settings -->
            <div v-else-if="selectedBlock.type === 'faq'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <label class="block text-sm text-slate-600">Вопросы и ответы</label>
                    <button 
                        @click="addFaqItem"
                        class="text-xs bg-slate-950 text-white px-2 py-1 rounded"
                    >
                        + Добавить
                    </button>
                </div>
                <div v-for="(item, i) in selectedBlock.settings.items || []" :key="i" class="space-y-2 p-3 bg-slate-50 rounded">
                    <input 
                        v-model="item.question"
                        placeholder="Вопрос"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                    <textarea 
                        v-model="item.answer"
                        placeholder="Ответ"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    ></textarea>
                    <button 
                        @click="removeFaqItem(i)"
                        class="text-xs text-red-600"
                    >
                        Удалить
                    </button>
                </div>
            </div>

            <!-- HTML Settings -->
            <div v-else-if="selectedBlock.type === 'html'" class="space-y-4">
                <label class="block text-sm text-slate-600 mb-1">HTML код</label>
                <textarea 
                    v-model="selectedBlock.settings.html"
                    rows="6"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-mono"
                    placeholder="<p>HTML код...</p>"
                ></textarea>
            </div>

            <!-- Image Settings -->
            <div v-else-if="selectedBlock.type === 'image'" class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Media ID</label>
                    <input 
                        v-model="selectedBlock.settings.media_id"
                        type="number"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        placeholder="ID медиафайла"
                    >
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Alt текст</label>
                    <input 
                        v-model="selectedBlock.settings.alt"
                        type="text"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                </div>
            </div>

            <!-- Common Block Settings -->
            <div class="pt-4 border-t border-slate-200 mt-4">
                <div class="text-sm font-medium text-slate-600 mb-2">Тип блока: {{ selectedBlock.type }}</div>
                <button 
                    @click="duplicateBlock"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50"
                >
                    Копировать блок
                </button>
            </div>
        </div>

        <div v-else class="p-6 text-center text-slate-400">
            <p class="text-sm">Выберите блок для редактирования</p>
        </div>
    </aside>
</div>

<!-- Preview Modal -->
<div v-if="showPreview" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-5xl max-h-[90vh] overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-semibold">Превью страницы</h3>
            <button @click="showPreview = false" class="text-slate-400 hover:text-slate-600">✕</button>
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
