<!-- resources/views/admin/builder/edit.blade.php -->
@extends('admin.layouts.app')

@section('title', 'Page Builder - ' . $page->title . ' - VertexCMS')
@section('page_title', 'Page Builder')
@section('page_subtitle', $page->title)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
@endpush

@section('content')
<div id="page-builder" class="flex h-[calc(100vh-80px)]">
    <!-- Sidebar: Blocks Library -->
    <aside class="w-80 border-r border-slate-200 bg-slate-50 flex flex-col">
        <div class="p-4 border-b border-slate-200 bg-white">
            <h3 class="font-semibold text-sm uppercase tracking-wide">Библиотека блоков</h3>
            <input 
                type="text" 
                v-model="searchQuery"
                placeholder="Поиск блоков..."
                class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            >
        </div>
        
        <div class="flex-1 overflow-y-auto">
            <div v-for="(blocks, category) in filteredBlocks" :key="category" class="border-b border-slate-200">
                <div 
                    @click="toggleCategory(category)"
                    class="px-4 py-2 bg-slate-100 cursor-pointer flex items-center justify-between hover:bg-slate-200"
                >
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-600">@{{ getCategoryName(category) }}</span>
                    <span class="text-slate-400">@{{ expandedCategories[category] ? '−' : '+' }}</span>
                </div>
                
                <div v-show="expandedCategories[category]" class="p-3 space-y-2">
                    <div 
                        v-for="block in blocks" 
                        :key="block.type"
                        @dragstart="onDragStart($event, block.type)"
                        draggable="true"
                        @click="addBlock(block.type)"
                        class="p-3 bg-white border border-slate-200 rounded-lg cursor-grab hover:border-blue-400 hover:shadow-md transition-all flex items-center gap-3"
                    >
                        <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-slate-600">
                            <i :class="getBlockIcon(block.type)"></i>
                        </div>
                        <div>
                            <div class="text-sm font-medium">@{{ block.name }}</div>
                            <div class="text-xs text-slate-400">@{{ block.description }}</div>
                        </div>
                    </div>
                </div>
            </div>
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
        <div class="max-w-5xl mx-auto">
            <div class="mb-6 bg-white rounded-lg border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold">{{ $page->title }}</h2>
                        <p class="text-sm text-slate-500">URI: {{ $page->uri }} | Статус: {{ $page->status }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="undo"
                            :disabled="!canUndo"
                            class="px-3 py-2 text-sm rounded-md border border-slate-300 bg-white hover:bg-slate-50 disabled:opacity-50"
                        >↶ Отмена</button>
                        <button 
                            @click="redo"
                            :disabled="!canRedo"
                            class="px-3 py-2 text-sm rounded-md border border-slate-300 bg-white hover:bg-slate-50 disabled:opacity-50"
                        >↷ Повтор</button>
                        <button 
                            @click="previewContent"
                            class="px-4 py-2 text-sm rounded-md border border-slate-300 bg-white hover:bg-slate-50"
                        >👁 Превью</button>
                        <button 
                            @click="saveContent"
                            :disabled="saving"
                            class="px-4 py-2 text-sm rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            <span v-if="!saving">💾 Сохранить</span>
                            <span v-else>Сохранение...</span>
                        </button>
                    </div>
                </div>
            </div>

            <div data-canvas @dragover.prevent @drop="onDrop" class="space-y-4 min-h-[400px]">
                <div v-if="content.length === 0" class="text-center py-20 bg-white rounded-lg border-2 border-dashed border-slate-300">
                    <div class="text-4xl mb-4">📦</div>
                    <p class="text-slate-500 mb-2">Перетащите блоки из библиотеки</p>
                    <p class="text-sm text-slate-400">или кликните на блок для добавления</p>
                </div>

                <div ref="blocksList" class="space-y-4">
                    <div 
                        v-for="(block, index) in content" 
                        :key="block._id"
                        data-block-item
                        class="relative bg-white rounded-lg border-2 transition-all group"
                        :class="{ 
                            'border-slate-200 hover:border-blue-300': selectedIndex !== index, 
                            'border-blue-500 shadow-lg ring-2 ring-blue-200': selectedIndex === index 
                        }"
                        @click="selectBlock(index)"
                    >
                        <div v-if="selectedIndex === index" class="absolute -top-3 -right-3 z-20 flex gap-1 bg-slate-900 rounded-lg shadow-lg p-1">
                            <button @click.stop="moveBlockUp(index)" class="p-1.5 text-white hover:bg-slate-700 rounded-l-lg" title="Вверх">↑</button>
                            <button @click.stop="moveBlockDown(index)" class="p-1.5 text-white hover:bg-slate-700" title="Вниз">↓</button>
                            <button @click.stop="duplicateBlock(index)" class="p-1.5 text-white hover:bg-slate-700" title="Копировать">⧉</button>
                            <button @click.stop="deleteBlock(index)" class="p-1.5 text-red-400 hover:bg-slate-700 hover:text-white rounded-r-lg" title="Удалить">✕</button>
                        </div>

                        <div class="absolute top-2 left-2 z-10">
                            <span class="px-2 py-1 text-xs bg-slate-100 text-slate-600 rounded font-mono">@{{ block.type }}</span>
                        </div>

                        <div class="p-4 pt-10">
                            <component :is="getBlockComponent(block.type)" :settings="block.settings" :index="index"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Settings Panel -->
    <aside class="w-96 border-l border-slate-200 bg-white overflow-y-auto">
        <div v-if="selectedBlock !== null" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg">Настройки блока</h3>
                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded font-mono">@{{ selectedBlock.type }}</span>
            </div>
            
            <div class="space-y-4">
                <template v-for="field in getBlockFields(selectedBlock.type)" :key="field.key">
                    <div v-if="field.type === 'text'">
                        <label class="block text-sm text-slate-600 mb-1">@{{ field.label }}</label>
                        <input v-model="selectedBlock.settings[field.key]" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div v-else-if="field.type === 'textarea'">
                        <label class="block text-sm text-slate-600 mb-1">@{{ field.label }}</label>
                        <textarea v-model="selectedBlock.settings[field.key]" :rows="field.rows || 4" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div v-else-if="field.type === 'select'">
                        <label class="block text-sm text-slate-600 mb-1">@{{ field.label }}</label>
                        <select v-model="selectedBlock.settings[field.key]" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option v-for="(label, value) in field.options" :key="value" :value="value">@{{ label }}</option>
                        </select>
                    </div>
                    
                    <div v-else-if="field.type === 'color'">
                        <label class="block text-sm text-slate-600 mb-1">@{{ field.label }}</label>
                        <div class="flex items-center gap-2">
                            <input v-model="selectedBlock.settings[field.key]" type="color" class="w-12 h-10 rounded-md border border-slate-300">
                            <input v-model="selectedBlock.settings[field.key]" type="text" class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm font-mono">
                        </div>
                    </div>
                    
                    <div v-else-if="field.type === 'toggle'">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-slate-600">@{{ field.label }}</span>
                            <div class="relative">
                                <input v-model="selectedBlock.settings[field.key]" type="checkbox" class="sr-only">
                                <div class="w-11 h-6 bg-slate-200 rounded-full transition-colors" :class="selectedBlock.settings[field.key] ? 'bg-blue-600' : ''"></div>
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform" :class="selectedBlock.settings[field.key] ? 'translate-x-5' : ''"></div>
                            </div>
                        </label>
                    </div>
                    
                    <div v-else-if="field.type === 'number'">
                        <label class="block text-sm text-slate-600 mb-1">@{{ field.label }}</label>
                        <input v-model.number="selectedBlock.settings[field.key]" type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    
                    <div v-else-if="field.type === 'repeater'">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm text-slate-600">@{{ field.label }}</label>
                            <button @click="addRepeaterItem(field.key, field.fields)" class="text-xs bg-blue-600 text-white px-2 py-1 rounded">+ Добавить</button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(item, itemIndex) in getRepeaterItems(field.key)" :key="itemIndex" class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-medium text-slate-500">Элемент #<span>@{{ itemIndex + 1 }}</span></span>
                                    <button @click="removeRepeaterItem(field.key, itemIndex)" class="text-xs text-red-600">Удалить</button>
                                </div>
                                <div class="space-y-2">
                                    <template v-for="subField in field.fields" :key="subField.key || subField[1]?.key">
                                        <input v-if="subField.type === 'text'" v-model="item[subField.key]" type="text" :placeholder="subField.label" class="w-full rounded border px-2 py-1.5 text-xs">
                                        <textarea v-else-if="subField.type === 'textarea'" v-model="item[subField.key]" :placeholder="subField.label" rows="2" class="w-full rounded border px-2 py-1.5 text-xs"></textarea>
                                        <input v-else-if="subField.type === 'number'" v-model.number="item[subField.key]" type="number" class="w-full rounded border px-2 py-1.5 text-xs">
                                        <input v-else-if="subField.type === 'color'" v-model="item[subField.key]" type="color" class="w-full h-8 rounded">
                                        <template v-else-if="Array.isArray(subField)">
                                            <input v-if="subField[1]?.type === 'text'" v-model="item[subField[1].key]" type="text" :placeholder="subField[1].label" class="w-full rounded border px-2 py-1.5 text-xs">
                                        </template>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div v-else class="p-6 text-center text-slate-400">
            <div class="text-4xl mb-4">👆</div>
            <p class="text-sm">Выберите блок для редактирования</p>
        </div>
    </aside>
</div>

<!-- Preview Modal -->
<div v-if="showPreview" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-semibold">Превью страницы</h3>
            <div class="flex items-center gap-2">
                <button @click="setPreviewDevice('desktop')" class="px-3 py-1.5 text-sm rounded" :class="previewDevice === 'desktop' ? 'bg-blue-100 text-blue-700' : 'hover:bg-slate-100'">🖥 Desktop</button>
                <button @click="setPreviewDevice('tablet')" class="px-3 py-1.5 text-sm rounded" :class="previewDevice === 'tablet' ? 'bg-blue-100 text-blue-700' : 'hover:bg-slate-100'">📱 Tablet</button>
                <button @click="setPreviewDevice('mobile')" class="px-3 py-1.5 text-sm rounded" :class="previewDevice === 'mobile' ? 'bg-blue-100 text-blue-700' : 'hover:bg-slate-100'">📱 Mobile</button>
                <button @click="showPreview = false" class="ml-2 text-slate-400 hover:text-slate-600">✕</button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto bg-slate-100 flex-1">
            <div class="bg-white mx-auto transition-all duration-300" :class="{
                'max-w-none w-full': previewDevice === 'desktop',
                'max-w-2xl w-full': previewDevice === 'tablet',
                'max-w-sm w-full': previewDevice === 'mobile'
            }">
                <iframe v-if="previewHtml" :srcdoc="previewHtml" class="w-full min-h-[600px] border-0"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications -->
<div class="fixed bottom-4 right-4 z-50 space-y-2">
    <transition-group name="toast">
        <div v-for="toast in toasts" :key="toast.id" class="px-4 py-3 rounded-lg shadow-lg text-white"
            :class="{
                'bg-green-600': toast.type === 'success',
                'bg-red-600': toast.type === 'error',
                'bg-blue-600': toast.type === 'info'
            }">
            @{{ toast.message }}
        </div>
    </transition-group>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="{{ mix('js/app.js') }}"></script>
<script>
const { createApp, ref, reactive, computed, onMounted, nextTick } = Vue;

const BlockComponents = {
    heading: { props: ['settings'], template: `<component :is="settings.level || 'h2'" :style="{ color: settings.color, textAlign: settings.align, fontSize: settings.font_size, fontWeight: settings.font_weight }">@{{ settings.text || 'Заголовок' }}</component>` },
    text: { props: ['settings'], template: `<div :style="{ color: settings.color, textAlign: settings.align, fontSize: settings.font_size }">@{{ settings.content || 'Текст...' }}</div>` },
    button: { props: ['settings'], template: `<a class="px-6 py-3 rounded-lg font-medium inline-block" :class="settings.style === 'primary' ? 'bg-blue-600 text-white' : 'bg-slate-200'" :href="settings.url || '#'">@{{ settings.text || 'Кнопка' }}</a>` },
    image: { props: ['settings'], template: `<img :src="settings.url || '/images/placeholder.png'" :alt="settings.alt" class="max-w-full h-auto rounded-lg">` },
    spacer: { props: ['settings'], template: `<div :style="{ height: settings.height || '40px' }"></div>` },
    divider: { props: ['settings'], template: `<hr class="my-4 border-t-2">` },
    icon: { props: ['settings'], template: `<i class="fas fa-star text-2xl" :style="{ color: settings.color }"></i>` },
    alert: { props: ['settings'], template: `<div class="p-4 rounded-lg" :class="settings.type === 'success' ? 'bg-green-50 text-green-800' : 'bg-blue-50 text-blue-800'"><strong>@{{ settings.title || 'Уведомление' }}</strong><br>@{{ settings.message || 'Текст' }}</div>` },
    accordion: { props: ['settings'], template: `<div class="space-y-2"><details v-for="(item,i) in (settings.items||[])" :key="i" class="border rounded"><summary class="p-3 cursor-pointer font-medium">@{{ item.title||'Вопрос' }}</summary><div class="p-3 pt-0">@{{ item.content||'Ответ' }}</div></details></div>` },
    tabs: { props: ['settings'], data() { return { activeTab: 0 } }, template: `<div><div class="border-b flex gap-2"><button v-for="(tab,i) in (settings.tabs||[])" :key="i" @click="activeTab=i" class="px-4 py-2" :class="activeTab===i?'text-blue-600 border-b-2 border-blue-600':''">@{{ tab.title||'Вкладка' }}</button></div><div class="p-4">@{{ settings.tabs?.[activeTab]?.content||'Содержимое' }}</div></div>` },
    counter: { props: ['settings'], data() { return { displayValue: 0 } }, mounted() { const t=parseInt(this.settings.value||100); let c=0; const i=setInterval(()=>{c+=t/125;if(c>=t){this.displayValue=t;clearInterval(i);}else{this.displayValue=Math.floor(c);}},16); }, template: `<div class="text-center"><div class="text-4xl font-bold">@{{ displayValue }}@{{ settings.suffix||'' }}</div><div v-if="settings.label">@{{ settings.label }}</div></div>` },
    'pricing-table': { props: ['settings'], template: `<div class="grid grid-cols-3 gap-4"><div v-for="(plan,i) in (settings.plans||[])" :key="i" class="p-6 border rounded text-center"><div class="font-semibold">@{{ plan.name||'Тариф' }}</div><div class="text-2xl font-bold my-2">@{{ plan.price||'0' }}₽</div><button class="w-full py-2 bg-blue-600 text-white rounded">Выбрать</button></div></div>` },
    form: { props: ['settings'], template: `<form class="space-y-3 max-w-md"><input type="text" placeholder="Имя" class="w-full border rounded p-2"><input type="email" placeholder="Email" class="w-full border rounded p-2"><textarea placeholder="Сообщение" rows="3" class="w-full border rounded p-2"></textarea><button class="w-full py-2 bg-blue-600 text-white rounded">@{{ settings.submitText||'Отправить' }}</button></form>` },
    video: { props: ['settings'], template: `<div class="aspect-video bg-slate-200 rounded flex items-center justify-center"><i class="fas fa-play text-4xl text-slate-400"></i></div>` },
    gallery: { props: ['settings'], template: `<div :class="'grid grid-cols-'+(settings.columns||3)+' gap-2'><div v-for="(img,i) in (settings.images||[])" :key="i" class="aspect-square bg-slate-200 rounded"></div></div>` },
    testimonials: { props: ['settings'], template: `<div class="grid grid-cols-2 gap-4"><div v-for="(t,i) in (settings.testimonials||[])" :key="i" class="p-4 bg-slate-50 rounded"><p class="italic mb-2">"@{{ t.text||'Отзыв' }}"</p><div class="font-medium">@{{ t.author||'Автор' }}</div></div></div>` },
    'news-feed': { props: ['settings'], template: `<div class="grid grid-cols-3 gap-4"><div v-for="i in 3" :key="i" class="border rounded overflow-hidden"><div class="h-32 bg-slate-200"></div><div class="p-3"><h4 class="font-semibold">Новость #<span>@{{ i }}</span></h4></div></div></div>` },
    'product-card': { props: ['settings'], template: `<div class="border rounded p-4"><div class="h-32 bg-slate-100 mb-3"></div><h4 class="font-semibold">@{{ settings.productName||'Товар' }}</h4><div class="text-lg font-bold text-blue-600">@{{ settings.price||'0' }} ₽</div></div>` },
    'product-list': { props: ['settings'], template: `<div class="text-center text-slate-400 py-8">Список товаров (@{{ settings.count||0 }})</div>` },
    cart: { props: ['settings'], template: `<div class="text-center text-slate-400 py-8">Корзина</div>` },
    breadcrumbs: { props: ['settings'], template: `<nav class="text-sm"><ol class="flex gap-2"><li><a href="#" class="text-blue-600">Главная</a></li><li>/</li><li>Страница</li></ol></nav>` },
    'progress-bar': { props: ['settings'], template: `<div><div class="flex justify-between mb-1"><span>@{{ settings.label||'Прогресс' }}</span><span>@{{ settings.value||0 }}%</span></div><div class="w-full bg-slate-200 rounded h-3"><div class="h-3 bg-blue-600 rounded" :style="{width:(settings.value||0)+'%'}"></div></div></div>` },
    collapse: { props: ['settings'], template: `<details class="border rounded"><summary class="p-3 cursor-pointer">@{{ settings.title||'Заголовок' }}</summary><div class="p-3">@{{ settings.content||'Содержимое' }}</div></details>` },
    container: { props: ['settings'], template: `<div class="p-6 border-2 border-dashed rounded min-h-[150px] bg-slate-50">Контейнер</div>` },
    columns: { props: ['settings'], template: `<div class="grid gap-4" :class="'grid-cols-'+(settings.columns||2)"><div v-for="i in (settings.columns||2)" :key="i" class="p-4 bg-slate-50 rounded">Колонка <span>@{{ i }}</span></div></div>` },
    'seo-meta': { props: ['settings'], template: `<div class="bg-slate-50 p-4 rounded border"><div class="text-xs text-slate-400 mb-2">SEO</div><div><strong>Title:</strong> @{{ settings.title||'...' }}</div><div><strong>Description:</strong> @{{ settings.description||'...' }}</div></div>` },
    tooltip: { props: ['settings'], template: `<span class="border-b border-dotted border-blue-600 cursor-help">@{{ settings.text||'Текст с подсказкой' }}</span>` },
    modal: { props: ['settings'], template: `<button class="px-6 py-3 bg-blue-600 text-white rounded">@{{ settings.triggerText||'Открыть модальное окно' }}</button>` }
};

createApp({
    setup() {
        const page = @json($page);
        const content = reactive(@json($page->content_json['sections'][0]['blocks'] ?? []));
        const availableBlocks = ref({});
        const blockDefinitions = ref({});
        const selectedIndex = ref(-1);
        const showPreview = ref(false);
        const previewHtml = ref('');
        const saving = ref(false);
        const searchQuery = ref('');
        const expandedCategories = ref({});
        const previewDevice = ref('desktop');
        const toasts = ref([]);
        const history = ref([]);
        const historyIndex = ref(-1);
        const blocksList = ref(null);
        let sortableInstance = null;

        function generateId() { return '_' + Math.random().toString(36).substr(2, 9); }

        const selectedBlock = computed(() => selectedIndex.value === -1 ? null : content[selectedIndex.value]);
        
        const filteredBlocks = computed(() => {
            const q = searchQuery.value.toLowerCase();
            const result = {};
            for (const [cat, blocks] of Object.entries(availableBlocks.value)) {
                const filtered = blocks.filter(b => b.name.toLowerCase().includes(q) || b.description?.toLowerCase().includes(q));
                if (filtered.length > 0) result[cat] = filtered;
            }
            return result;
        });

        const canUndo = computed(() => historyIndex.value > 0);
        const canRedo = computed(() => historyIndex.value < history.value.length - 1);

        function toggleCategory(cat) { expandedCategories.value[cat] = !expandedCategories.value[cat]; }
        
        function getCategoryName(cat) {
            const names = { content:'Контент', media:'Медиа', layout:'Макет', dynamic:'Динамические', interactive:'Интерактивные', ecommerce:'E-commerce', seo:'SEO', utility:'Утилиты' };
            return names[cat] || cat;
        }

        function getBlockIcon(type) {
            const icons = { heading:'fas fa-heading', text:'fas fa-paragraph', button:'fas fa-square', image:'fas fa-image', video:'fas fa-video', gallery:'fas fa-images', icon:'fas fa-star', accordion:'fas fa-chevron-down', tabs:'fas fa-folder', modal:'fas fa-window-maximize', counter:'fas fa-counter', testimonials:'fas fa-quote-left', 'pricing-table':'fas fa-table', form:'fas fa-envelope', spacer:'fas fa-arrows-alt-v', divider:'fas fa-minus', alert:'fas fa-exclamation-triangle', 'progress-bar':'fas fa-chart-bar', breadcrumbs:'fas fa-ellipsis-h', collapse:'fas fa-chevron-right', container:'fas fa-box', columns:'fas fa-columns', 'seo-meta':'fas fa-search', 'news-feed':'fas fa-newspaper', 'product-card':'fas fa-shopping-bag', 'product-list':'fas fa-list', cart:'fas fa-shopping-cart', tooltip:'fas fa-comment-dots' };
            return icons[type] || 'fas fa-cube';
        }

        function getBlockComponent(type) { return BlockComponents[type] || { props:['settings'], template:`<div class="text-slate-400">[@{{ type }}]</div>` }; }
        
        function getBlockFields(type) { return blockDefinitions.value[type]?.fields || []; }

        function getRepeaterItems(key) {
            if (!selectedBlock.value?.settings?.[key]) { if (selectedBlock.value) selectedBlock.value.settings[key] = []; return []; }
            return selectedBlock.value.settings[key];
        }

        function addRepeaterItem(key, fields) {
            if (!selectedBlock.value) return;
            if (!selectedBlock.value.settings[key]) selectedBlock.value.settings[key] = [];
            const newItem = {};
            (fields||[]).forEach(f => { newItem[f?.key || f?.[1]?.key] = ''; });
            selectedBlock.value.settings[key].push(newItem);
        }

        function removeRepeaterItem(key, index) { if (selectedBlock.value?.settings?.[key]) selectedBlock.value.settings[key].splice(index, 1); }

        function onDragStart(e, type) { e.dataTransfer.setData('blockType', type); e.dataTransfer.effectAllowed = 'copy'; }
        
        function onDrop(e) { const type = e.dataTransfer.getData('blockType'); if (type) addBlock(type); }

        function addBlock(type) {
            const cat = Object.keys(availableBlocks.value).find(c => availableBlocks.value[c].some(b => b.type === type));
            const def = availableBlocks.value[cat]?.find(b => b.type === type);
            if (!def) return;
            const newBlock = { type, _id: generateId(), settings: JSON.parse(JSON.stringify(def.default?.settings || def.default || {})) };
            if (selectedIndex.value === -1) { content.push(newBlock); selectedIndex.value = content.length - 1; }
            else { content.splice(selectedIndex.value + 1, 0, newBlock); selectedIndex.value++; }
            saveHistory();
            nextTick(() => initSortable());
        }

        function selectBlock(index) { selectedIndex.value = index; }
        
        function deleteBlock(index) { content.splice(index, 1); if (selectedIndex.value >= index) selectedIndex.value = Math.max(-1, selectedIndex.value - 1); saveHistory(); }
        
        function moveBlockUp(index) { if (index > 0) { [content[index], content[index-1]] = [content[index-1], content[index]]; selectedIndex.value = index - 1; saveHistory(); } }
        
        function moveBlockDown(index) { if (index < content.length - 1) { [content[index], content[index+1]] = [content[index+1], content[index]]; selectedIndex.value = index + 1; saveHistory(); } }
        
        function duplicateBlock(index) { const copy = JSON.parse(JSON.stringify(content[index])); copy._id = generateId(); content.splice(index + 1, 0, copy); selectedIndex.value = index + 1; saveHistory(); }
        
        function clearBlocks() { if (confirm('Очистить все блоки?')) { content.splice(0, content.length); selectedIndex.value = -1; saveHistory(); } }

        function saveHistory() {
            const state = JSON.stringify(content);
            if (historyIndex.value < history.value.length - 1) history.value = history.value.slice(0, historyIndex.value + 1);
            history.value.push(state);
            historyIndex.value = history.value.length - 1;
            if (history.value.length > 50) { history.value.shift(); historyIndex.value--; }
        }

        function undo() { if (!canUndo.value) return; historyIndex.value--; content.splice(0, content.length, ...JSON.parse(history.value[historyIndex.value])); }
        
        function redo() { if (!canRedo.value) return; historyIndex.value++; content.splice(0, content.length, ...JSON.parse(history.value[historyIndex.value])); }

        function handleKeyboard(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); if (e.shiftKey) redo(); else undo(); }
            if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(); }
            if (e.key === 'Delete' && selectedIndex.value !== -1) deleteBlock(selectedIndex.value);
        }

        function showToast(message, type = 'info') {
            const id = Date.now();
            toasts.value.push({ id, message, type });
            setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id); }, 3000);
        }

        function setPreviewDevice(device) { previewDevice.value = device; }

        async function previewContent() {
            try {
                const response = await fetch('/admin/api/builder/render-preview', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify({ content: [{ settings: {}, blocks: content.map(b => ({ type: b.type, settings: b.settings })) }] })
                });
                const data = await response.json();
                previewHtml.value = data.html;
                showPreview.value = true;
                showToast('Превью готово', 'success');
            } catch (e) { showToast('Ошибка превью: ' + e.message, 'error'); }
        }

        async function saveContent() {
            saving.value = true;
            try {
                const response = await fetch(`/admin/pages/${page.id}/builder`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: JSON.stringify({ content_json: JSON.stringify({ version: '1.0', layout: 'default', sections: [{ settings: {}, blocks: content }] }) })
                });
                if (response.ok) { showToast('Страница сохранена!', 'success'); saveHistory(); }
                else { const data = await response.json(); showToast('Ошибка: ' + (data.message || 'Не удалось сохранить'), 'error'); }
            } catch (e) { showToast('Ошибка сети: ' + e.message, 'error'); }
            finally { saving.value = false; }
        }

        function initSortable() {
            if (sortableInstance) sortableInstance.destroy();
            if (blocksList.value) {
                sortableInstance = Sortable.create(blocksList.value, {
                    animation: 150, handle: '[data-block-item]', ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen', dragClass: 'sortable-drag',
                    onEnd: (evt) => { const item = content.splice(evt.oldIndex, 1)[0]; content.splice(evt.newIndex, 0, item); selectedIndex.value = evt.newIndex; saveHistory(); }
                });
            }
        }

        async function fetchAvailableBlocks() {
            try {
                const response = await fetch('/admin/api/builder/blocks');
                const data = await response.json();
                const grouped = {};
                blockDefinitions.value = data.blocks || {};
                for (const [type, def] of Object.entries(data.blocks || {})) {
                    const cat = def.category || 'other';
                    if (!grouped[cat]) grouped[cat] = [];
                    grouped[cat].push({ type, name: def.name, description: def.description, icon: def.icon, default: def.default });
                }
                availableBlocks.value = grouped;
                const first = Object.keys(grouped)[0];
                if (first) expandedCategories.value[first] = true;
            } catch (e) { console.error('Failed to load blocks:', e); showToast('Не удалось загрузить библиотеку блоков', 'error'); }
        }

        onMounted(() => {
            fetchAvailableBlocks();
            document.addEventListener('keydown', handleKeyboard);
            nextTick(() => { initSortable(); saveHistory(); });
        });

        return {
            page, content, availableBlocks, selectedIndex, selectedBlock, showPreview, previewHtml, saving, searchQuery,
            expandedCategories, previewDevice, toasts, canUndo, canRedo, blocksList, filteredBlocks,
            toggleCategory, getCategoryName, getBlockIcon, getBlockComponent, getBlockFields, getRepeaterItems,
            addRepeaterItem, removeRepeaterItem, onDragStart, onDrop, addBlock, selectBlock, deleteBlock,
            moveBlockUp, moveBlockDown, duplicateBlock, clearBlocks, undo, redo, setPreviewDevice, previewContent, saveContent, showToast
        };
    }
}).mount('#page-builder');
</script>

<style>
.sortable-ghost { opacity: 0.4; background: #f1f5f9; border: 2px dashed #94a3b8; }
.sortable-chosen { background: #fff; border-color: #3b82f6; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(30px); }
</style>
@endpush
