@extends('admin.layouts.app')

@section('title', 'Медиа - VertexCMS')
@section('page_title', 'Медиатека')
@section('page_subtitle', 'Загрузка и управление файлами')

@section('content')
<div id="media-manager" class="vc-media-manager flex h-[calc(100vh-180px)] min-h-500px">
    <!-- Sidebar — Folders -->
    <aside class="vc-sidebar w-72 flex-shrink-0 border-r border-[var(--vc-border)] bg-[var(--vc-surface-strong)] p-4 flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-[var(--vc-text)]">Папки</h3>
            <button @click="showCreateFolderModal = true" class="vc-button vc-button-secondary vc-button-sm" title="Новая папка">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto">
            <ul class="space-y-1">
                <li>
                    <button
                        @click="selectedFolderId = null"
                        :class="!selectedFolderId ? 'bg-[var(--vc-primary)] text-white' : 'text-[var(--vc-text)] hover:bg-[var(--vc-surface-muted)]'"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors">
                        <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                        <span class="truncate">Все файлы</span>
                        <span class="ml-auto text-xs opacity-70">{{ mediaCount }}</span>
                    </button>
                </li>
                <li v-for="folder in topFolders" :key="folder.id">
                    <button
                        @click="selectedFolderId = folder.id"
                        :class="selectedFolderId === folder.id ? 'bg-[var(--vc-primary)] text-white' : 'text-[var(--vc-text)] hover:bg-[var(--vc-surface-muted)]'"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors group">
                        <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: folder.color }"></span>
                        <span class="truncate flex-1">{{ folder.name }}</span>
                        <span class="text-xs opacity-70">{{ folder.media_count }}</span>
                        <button @click.stop="editFolder(folder)" class="opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-black/10" title="Редактировать">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                    </button>
                    <!-- Subfolders -->
                    <ul v-if="childrenByParent[folder.id] && childrenByParent[folder.id].length" class="ml-4 mt-1 space-y-1">
                        <li v-for="child in childrenByParent[folder.id]" :key="child.id">
                            <button
                                @click="selectedFolderId = child.id"
                                :class="selectedFolderId === child.id ? 'bg-[var(--vc-primary)] text-white' : 'text-[var(--vc-text)] hover:bg-[var(--vc-surface-muted)]'"
                                class="w-full flex items-center gap-3 px-3 py-1.5 rounded text-sm transition-colors">
                                <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: child.color }"></span>
                                <span class="truncate flex-1">{{ child.name }}</span>
                                <span class="text-xs opacity-70">{{ child.media_count }}</span>
                            </button>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main area -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Upload zone -->
        <div class="p-4 border-b border-[var(--vc-border)]">
            <div
                @drop="handleDrop"
                @dragover.prevent
                @dragenter.prevent
                @dragleave.prevent="dragOver = false"
                :class="dragOver ? 'border-[var(--vc-primary)] bg-[var(--vc-primary)]/10' : 'border-[var(--vc-border)] hover:border-[var(--vc-primary)]/50'"
                class="border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer"
                @click="$refs.fileInput.click()">
                <input
                    ref="fileInput"
                    type="file"
                    multiple
                    accept="image/*,.pdf,.svg"
                    @change="handleFileSelect"
                    class="hidden">
                <svg class="w-8 h-8 mx-auto mb-2 text-[var(--vc-text-soft)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                <p class="text-sm text-[var(--vc-text)]">Перетащите файлы или кликните для загрузки</p>
                <p class="text-xs text-[var(--vc-text-soft)] mt-1">Изображения, PDF, SVG до 10 МБ</p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="px-4 py-3 border-b border-[var(--vc-border)] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm text-[var(--vc-text-soft)]">Файлов: {{ items.length }}</span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="refreshMedia" class="vc-button vc-button-secondary vc-button-sm" title="Обновить">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
            </div>
        </div>

        <!-- Grid -->
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <!-- Loading skeleton -->
                <div v-if="loading" v-for="n in 12" :key="n" class="aspect-video rounded-lg bg-[var(--vc-surface-strong)] animate-pulse"></div>

                <!-- Empty state -->
                <div v-else-if="items.length === 0" class="col-span-full py-12 text-center text-[var(--vc-text-soft)]">
                    Нет файлов. Загрузите первый файл.
                </div>

                <!-- Media cards -->
                <div v-for="item in items" :key="item.id" class="group relative aspect-video rounded-lg border border-[var(--vc-border)] bg-[var(--vc-surface-strong)] overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" @click="previewMedia = item">
                    <!-- Preview -->
                    <div class="w-full h-full flex items-center justify-center bg-[var(--vc-surface-muted)]">
                        <img v-if="isImage(item)" :src="item.url" :alt="item.alt" class="max-w-full max-h-full object-cover" loading="lazy">
                        <div v-else class="text-4xl text-[var(--vc-text-soft)]">
                            <svg v-if="item.extension === 'pdf'" class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <svg v-else class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>

                    <!-- Actions on hover -->
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button @click.stop="editMedia(item)" class="p-2 rounded bg-white/20 hover:bg-white/30 text-white" title="Редактировать">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button @click.stop="moveMedia(item)" class="p-2 rounded bg-white/20 hover:bg-white/30 text-white" title="Переместить">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </button>
                        <button @click.stop="deleteMedia(item)" class="p-2 rounded bg-red-500/80 hover:bg-red-600 text-white" title="Удалить">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>

                    <!-- Footer info -->
                    <div class="absolute bottom-0 left-0 right-0 bg-black/60 px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <p class="text-xs text-white truncate">{{ item.original_filename }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modals -->
    <!-- Create Folder Modal -->
    <div v-if="showCreateFolderModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showCreateFolderModal = false">
        <div class="bg-[var(--vc-surface-strong)] rounded-lg p-6 w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-semibold mb-4 text-[var(--vc-text)]">Создать папку</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-[var(--vc-text)]">Название</label>
                    <input v-model="newFolderName" type="text" class="vc-input w-full" placeholder="Новая папка">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-[var(--vc-text)]">Родительская папка</label>
                    <select v-model="newFolderParentId" class="vc-input w-full">
                        <option value="">— Без родителя —</option>
                        <option v-for="f in folders" :key="f.id" :value="f.id">{{ f.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-[var(--vc-text)]">Цвет</label>
                    <div class="flex gap-2">
                        <input v-model="newFolderColor" type="color" class="w-10 h-10 rounded cursor-pointer border-0">
                        <input v-model="newFolderColor" type="text" class="vc-input flex-1" placeholder="#6366f1">
                    </div>
                    <div class="flex gap-1 mt-2">
                        <button v-for="c in presetColors" :key="c" @click="newFolderColor = c" class="w-6 h-6 rounded-full border border-[var(--vc-border)]" :style="{ backgroundColor: c }"></button>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button @click="showCreateFolderModal = false" class="vc-button vc-button-secondary">Отмена</button>
                <button @click="createFolder" class="vc-button vc-button-primary">Создать</button>
            </div>
        </div>
    </div>

    <!-- Edit Folder Modal -->
    <div v-if="editingFolder" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="editingFolder = null">
        <div class="bg-[var(--vc-surface-strong)] rounded-lg p-6 w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-semibold mb-4 text-[var(--vc-text)]">Редактировать папку</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-[var(--vc-text)]">Название</label>
                    <input v-model="editingFolder.name" type="text" class="vc-input w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-[var(--vc-text)]">Цвет</label>
                    <div class="flex gap-2">
                        <input v-model="editingFolder.color" type="color" class="w-10 h-10 rounded cursor-pointer border-0">
                        <input v-model="editingFolder.color" type="text" class="vc-input flex-1">
                    </div>
                    <div class="flex gap-1 mt-2">
                        <button v-for="c in presetColors" :key="c" @click="editingFolder.color = c" class="w-6 h-6 rounded-full border border-[var(--vc-border)]" :style="{ backgroundColor: c }"></button>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button @click="editingFolder = null" class="vc-button vc-button-secondary">Отмена</button>
                <button @click="updateFolder" class="vc-button vc-button-primary">Сохранить</button>
            </div>
        </div>
    </div>

    <!-- Media Edit Modal -->
    <div v-if="editingMedia" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="editingMedia = null">
        <div class="bg-[var(--vc-surface-strong)] rounded-lg p-6 w-full max-w-lg shadow-2xl">
            <h3 class="text-lg font-semibold mb-4 text-[var(--vc-text)]">Редактировать файл</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-[var(--vc-text)]">Alt</label>
                    <input v-model="editingMedia.alt" type="text" class="vc-input w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-[var(--vc-text)]">Заголовок</label>
                    <input v-model="editingMedia.title" type="text" class="vc-input w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-[var(--vc-text)]">Подпись</label>
                    <textarea v-model="editingMedia.caption" rows="3" class="vc-textarea w-full"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-[var(--vc-text)]">Папка</label>
                    <select v-model="editingMedia.folder_id" class="vc-input w-full">
                        <option value="">— Без папки —</option>
                        <option v-for="folder in flatFolders" :key="folder.id" :value="folder.id">
                            {{ folder.name }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button @click="editingMedia = null" class="vc-button vc-button-secondary">Отмена</button>
                <button @click="updateMedia" class="vc-button vc-button-primary">Сохранить</button>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div v-if="previewMedia" class="fixed inset-0 bg-black/90 flex items-center justify-center z-50" @click.self="previewMedia = null">
        <button @click="previewMedia = null" class="absolute top-4 right-4 text-white/70 hover:text-white p-2">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <div class="max-w-4xl max-h-[90vh] p-4">
            <img v-if="isImage(previewMedia)" :src="previewMedia.url" :alt="previewMedia.alt" class="max-w-full max-h-[85vh] mx-auto rounded shadow-2xl">
            <div v-else class="bg-white p-8 rounded-lg text-center">
                <div class="text-6xl mb-4">
                    <svg v-if="previewMedia.extension === 'pdf'" class="w-24 h-24 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <svg v-else class="w-24 h-24 mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </div>
                <p class="text-lg font-medium">{{ previewMedia.original_filename }}</p>
                <p class="text-sm text-[var(--vc-text-soft)] mt-2">{{ formatSize(previewMedia.size) }}</p>
                <a :href="previewMedia.url" target="_blank" class="vc-button vc-button-primary mt-4 inline-block">Скачать</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const { createApp, ref, reactive, computed, onMounted } = Vue;

    createApp({
        setup() {
            const folders = ref([]);
            const items = ref([]);
            const loading = ref(false);
            const dragOver = ref(false);
            const selectedFolderId = ref(null);
            const showCreateFolderModal = ref(false);
            const editingFolder = ref(null);
            const editingMedia = ref(null);
            const previewMedia = ref(null);

            // New folder form
            const newFolderName = ref('');
            const newFolderParentId = ref('');
            const newFolderColor = ref('#6366f1');

            const presetColors = [...];

            // Computed helpers
            const childrenByParent = computed(() => {
                const map = {};
                folders.value.forEach(f => {
                    if (!map[f.parent_id]) map[f.parent_id] = [];
                    map[f.parent_id].push(f);
                });
                return map;
            });

            const topFolders = computed(() => folders.value.filter(f => !f.parent_id));

            const flatFolders = computed(() => folders.value);

            const mediaCount = computed(() => {
                let count = items.value.length;
                folders.value.forEach(f => { count += f.media_count || 0; });
                return count;
            });

            // Helpers
            const isImage = (item) => item.mime_type?.startsWith('image/');

            const formatSize = (bytes) => { ... };

            // Load folders
            const loadFolders = async () => {
                try {
                    const res = await fetch('/api/media/folders');
                    const data = await res.json();
                    folders.value = data.folders || [];
                } catch (e) {
                    console.error('Folders load error:', e);
                }
            };

            // Load media
            const loadMedia = async () => {
                loading.value = true;
                try {
                    const params = new URLSearchParams();
                    if (selectedFolderId.value) {
                        params.append('folder_id', selectedFolderId.value);
                    }
                    const res = await fetch(`/api/media?${params}`);
                    const data = await res.json();
                    items.value = (data.data || []).map(item => ({
                        ...item,
                        // mappings
                        id: item.id,
                        url: item.url,
                        original_filename: item.original_filename,
                        mime_type: item.mime_type,
                        extension: item.extension,
                        size: item.size,
                        width: item.width,
                        height: item.height,
                        alt: item.alt,
                        title: item.title,
                        caption: item.caption,
                        folder_id: item.folder_id,
                    }));
                } catch (e) {
                    console.error('Media load error:', e);
                } finally {
                    loading.value = false;
                }
            };

            // Upload handlers
            const handleFileSelect = async (e) => {
                const files = e.target.files;
                if (!files.length) return;
                await uploadFiles(files);
                e.target.value = '';
            };

            const handleDrop = async (e) => {
                dragOver.value = false;
                const files = e.dataTransfer.files;
                if (!files.length) return;
                await uploadFiles(files);
            };

            const uploadFiles = async (files) => {
                for (let i = 0; i < files.length; i++) {
                    const form = new FormData();
                    form.append('file', files[i]);
                    try {
                        const res = await fetch('/api/media/upload', {
                            method: 'POST',
                            body: form,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });
                        if (!res.ok) throw new Error('Upload failed');
                    } catch (e) {
                        console.error('Upload error:', e);
                        alert('Ошибка загрузки файла: ' + files[i].name);
                    }
                }
                await loadMedia();
                await loadFolders();
            };

            // Folder actions
            const createFolder = async () => {
                if (!newFolderName.value.trim()) return;
                try {
                    await fetch('/api/media/folders', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({
                            name: newFolderName.value,
                            parent_id: newFolderParentId.value || null,
                            color: newFolderColor.value,
                        }),
                    });
                    showCreateFolderModal.value = false;
                    newFolderName.value = '';
                    newFolderParentId.value = '';
                    newFolderColor.value = '#6366f1';
                    await loadFolders();
                } catch (e) {
                    alert('Ошибка создания папки');
                }
            };

            const editFolder = (folder) => {
                editingFolder.value = { ...folder };
            };

            const updateFolder = async () => {
                if (!editingFolder.value?.id) return;
                try {
                    await fetch(`/api/media/folders/${editingFolder.value.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({
                            name: editingFolder.value.name,
                            color: editingFolder.value.color,
                        }),
                    });
                    editingFolder.value = null;
                    await loadFolders();
                } catch (e) {
                    alert('Ошибка обновления папки');
                }
            };

            // Media actions
            const editMedia = (item) => {
                editingMedia.value = { ...item };
            };

            const updateMedia = async () => {
                if (!editingMedia.value?.id) return;
                try {
                    await fetch(`/api/media/${editingMedia.value.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({
                            alt: editingMedia.value.alt,
                            title: editingMedia.value.title,
                            caption: editingMedia.value.caption,
                            folder_id: editingMedia.value.folder_id,
                        }),
                    });
                    editingMedia.value = null;
                    await loadMedia();
                    await loadFolders();
                } catch (e) {
                    alert('Ошибка обновления файла');
                }
            };

            const moveMedia = (item) => {
                editingMedia.value = { ...item };
            };

            const deleteMedia = async (item) => {
                if (!confirm(`Удалить "${item.original_filename}"?`)) return;
                try {
                    await fetch(`/api/media/${item.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    await loadMedia();
                    await loadFolders();
                } catch (e) {
                    alert('Ошибка удаления');
                }
            };

            const refreshMedia = () => {
                loadMedia();
            };

            onMounted(() => {
                loadFolders();
                loadMedia();
            });

            return {
                folders,
                items,
                loading,
                dragOver,
                selectedFolderId,
                showCreateFolderModal,
                editingFolder,
                editingMedia,
                previewMedia,
                newFolderName,
                newFolderParentId,
                newFolderColor,
                presetColors,
                childrenByParent,
                topFolders,
                flatFolders,
                mediaCount,
                isImage,
                formatSize,
                handleDrop,
                handleFileSelect,
                createFolder,
                editFolder,
                updateFolder,
                editMedia,
                updateMedia,
                moveMedia,
                deleteMedia,
                refreshMedia,
            };
        },
    }).mount('#media-manager');
</script>
@endpush
