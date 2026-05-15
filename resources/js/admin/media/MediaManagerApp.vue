<template>
  <div class="media-layout" :class="{ 'media-layout-picker': isPickerMode }">
    <aside class="media-sidebar">
      <div class="media-sidebar-card media-sidebar-head">
        <div class="media-sidebar-copy">
          <h2 class="media-title">Папки</h2>
          <p class="media-copy">Дерево папок всегда слева, а галерея занимает основное рабочее пространство.</p>
        </div>
        <div class="media-sidebar-stats">
          <span class="media-sidebar-stat">{{ totalItems }} файлов</span>
          <span class="media-sidebar-stat">{{ folders.length }} папок</span>
        </div>
      </div>

      <div class="media-sidebar-card media-sidebar-tree-card">
        <div class="media-sidebar-card-head">
          <strong>Дерево папок</strong>
          <button
            v-if="config.canManageFolders"
            type="button"
            class="vc-button vc-button-secondary"
            @click="openCreateFolderModal(selectedFolderId)"
          >
            Новая папка
          </button>
        </div>
        <div class="folder-tree">
          <div class="folder-all-card" :class="{ 'folder-all-card-active': selectedFolderId === null }" @click="selectFolder(null)">
            <div class="folder-all-dot"></div>
            <div class="folder-all-copy">
              <strong>Все файлы</strong>
              <span>Корень медиатеки</span>
            </div>
            <span class="folder-total">{{ totalItems }}</span>
          </div>

          <div v-if="folderEntries.length === 0" class="folder-empty">
            Папок пока нет. Создайте первую цветную папку, чтобы слева появилось дерево разделов.
          </div>

          <template v-else>
            <div
              v-for="entry in folderEntries"
              :key="entry.id"
              class="folder-row"
              role="button"
              tabindex="0"
              :class="{ 'folder-row-active': selectedFolderId === entry.id }"
              :style="folderRowStyle(entry.color, selectedFolderId === entry.id)"
              @click="selectFolder(entry.id)"
              @keydown.enter.prevent="selectFolder(entry.id)"
              @keydown.space.prevent="selectFolder(entry.id)"
            >
              <span class="folder-indent" :style="{ width: `${entry.depth * 16}px` }"></span>
              <span class="folder-icon" :style="folderIconStyle(entry.color)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h5.379a1.5 1.5 0 0 1 1.06.44l1.122 1.12a1.5 1.5 0 0 0 1.06.44h7.879a1.5 1.5 0 0 1 1.5 1.5v6.75a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 17.75V8.25A.75.75 0 0 1 3.75 7.5Z" />
                </svg>
              </span>
              <span class="folder-copy">
                <span class="folder-name">{{ entry.name }}</span>
                <span class="folder-meta">{{ entry.media_count }} файлов</span>
              </span>
              <span class="folder-actions" v-if="config.canManageFolders">
                <button type="button" class="folder-action" title="Подпапка" @click.stop="openCreateFolderModal(entry.id)">+</button>
                <button type="button" class="folder-action" title="Редактировать" @click.stop="openEditFolderModal(entry)">✎</button>
                <button type="button" class="folder-action folder-action-danger" title="Удалить" @click.stop="confirmDeleteFolder(entry)">×</button>
              </span>
            </div>
          </template>
        </div>
      </div>

      <div class="media-sidebar-card palette-card">
        <div class="palette-head">
          <h3>Цвета папок</h3>
          <span>{{ folderPalette.length }} вариантов</span>
        </div>
        <div class="palette-grid">
          <button
            v-for="color in folderPalette"
            :key="color"
            type="button"
            class="palette-swatch"
            :style="{ backgroundColor: color }"
            @click="openCreateFolderWithColor(color)"
          ></button>
        </div>
        <p class="palette-copy">Нажмите на цвет, чтобы создать папку уже с выбранным оттенком.</p>
      </div>
    </aside>

    <section class="media-main">
      <div class="media-toolbar">
        <div v-if="isPickerMode" class="media-picker-bar">
          <div class="media-picker-copy">
            <strong>Выбор файла для конструктора</strong>
            <span v-if="pickerSelectedItem">Выбрано: {{ pickerSelectedItem.title || pickerSelectedItem.original_filename }}</span>
            <span v-else>Выберите файл из галереи или загрузите новый.</span>
          </div>
          <div class="media-picker-actions">
            <button type="button" class="vc-button vc-button-secondary" @click="requestClose">Закрыть</button>
            <button
              type="button"
              class="vc-button vc-button-primary"
              :disabled="!pickerSelectedItem"
              @click="submitPickedMedia"
            >
              Использовать файл
            </button>
          </div>
        </div>

        <div class="media-context">
          <div class="media-context-copy">
            <p class="media-context-kicker">Текущий раздел</p>
            <div class="media-context-title-row">
              <h2 class="media-context-title">{{ selectedFolder ? selectedFolder.name : 'Все файлы' }}</h2>
              <span v-if="selectedFolder" class="media-folder-pill media-folder-pill-current" :style="folderPillStyle(selectedFolder.id)">
                Цвет папки
              </span>
            </div>
            <p class="media-context-copy-text">
              <template v-if="selectedFolderPath.length">
                {{ selectedFolderPath.join(' / ') }}
              </template>
              <template v-else>
                Корневая медиатека без фильтра по папкам.
              </template>
            </p>
          </div>

          <div class="media-context-stats">
            <div class="media-stat-chip">
              <strong>{{ items.length }}</strong>
              <span>на экране</span>
            </div>
            <div class="media-stat-chip">
              <strong>{{ imageCount }}</strong>
              <span>изображений</span>
            </div>
            <div class="media-stat-chip">
              <strong>{{ documentCount }}</strong>
              <span>документов</span>
            </div>
          </div>
        </div>

        <div class="media-toolbar-row">
          <div class="media-toolbar-search">
            <input
              v-model.trim="searchQuery"
              type="search"
              class="vc-input"
              placeholder="Поиск по имени, alt, title или подписи"
            >
          </div>
          <div class="media-toolbar-actions">
            <button type="button" class="vc-button vc-button-secondary" @click="refreshAll">Обновить</button>
            <button
              v-if="config.canUploadMedia"
              type="button"
              class="vc-button vc-button-primary"
              @click="triggerFilePicker"
            >
              Загрузить файлы
            </button>
          </div>
        </div>

        <div
          v-if="config.canUploadMedia"
          class="upload-dropzone"
          :class="{ 'upload-dropzone-active': dragOver }"
          @drop.prevent="handleDrop"
          @dragover.prevent="dragOver = true"
          @dragleave.prevent="dragOver = false"
          @click="triggerFilePicker"
        >
          <input
            ref="fileInput"
            type="file"
            multiple
            accept="image/*,.pdf,.svg"
            class="hidden"
            @change="handleFileSelect"
          >
          <div class="upload-dropzone-copy">
            <strong>{{ uploading ? 'Идёт загрузка…' : 'Перетащите сюда один или несколько файлов' }}</strong>
            <span>
              <template v-if="selectedFolder">
                Загрузка пойдёт в папку «{{ selectedFolder.name }}».
              </template>
              <template v-else>
                Загрузка пойдёт в корень медиатеки.
              </template>
              Поддерживается множественная загрузка.
            </span>
          </div>
        </div>
      </div>

      <div class="media-gallery-shell">
        <div class="media-gallery-head">
          <div>
            <h3>Галерея</h3>
            <p>
              <template v-if="selectedFolderPath.length">
                {{ selectedFolderPath.join(' / ') }}
              </template>
              <template v-else>
                Все файлы медиатеки
              </template>
            </p>
          </div>
          <span class="media-gallery-total">{{ totalItems }} всего</span>
        </div>

        <div v-if="errorMessage" class="media-error">
          {{ errorMessage }}
        </div>

        <div v-if="loading" class="media-grid media-grid-loading">
          <div v-for="n in 10" :key="n" class="media-skeleton"></div>
        </div>

        <div v-else-if="items.length === 0" class="media-empty">
          Ничего не найдено. Попробуйте сменить папку, очистить поиск или загрузить новые файлы.
        </div>

        <div v-else class="media-grid">
          <article v-for="item in items" :key="item.id" class="media-card">
            <button
              type="button"
              class="media-preview-button"
              :class="{ 'media-preview-button-active': isPickerMode && pickerSelectedId === Number(item.id) }"
              @click="handleCardClick(item)"
            >
              <img
                v-if="isImage(item)"
                :src="item.url"
                :alt="item.alt || item.original_filename"
                class="media-thumb"
                loading="lazy"
              >
              <iframe
                v-else-if="isPdf(item)"
                :src="item.url"
                class="media-pdf-thumb"
                title="PDF preview"
              ></iframe>
              <div v-else class="media-file-fallback">
                {{ fileBadge(item) }}
              </div>
            </button>

            <div class="media-card-body">
              <div class="media-card-head">
                <div>
                  <p class="media-card-title">{{ item.title || item.original_filename }}</p>
                  <p class="media-card-subtitle">{{ item.original_filename }}</p>
                </div>
                <span v-if="folderNameById(item.folder_id)" class="media-folder-pill" :style="folderPillStyle(item.folder_id)">
                  {{ folderNameById(item.folder_id) }}
                </span>
              </div>

              <div class="media-card-meta">
                <span>{{ formatSize(item.size) }}</span>
                <span v-if="item.width && item.height">{{ item.width }}×{{ item.height }}</span>
                <span v-else>{{ fileBadge(item) }}</span>
              </div>

              <div class="media-card-actions">
                <button
                  v-if="isPickerMode"
                  type="button"
                  class="vc-button vc-button-primary"
                  @click="selectForPicker(item)"
                >
                  Выбрать
                </button>
                <button type="button" class="vc-button vc-button-secondary" @click="openPreview(item)">Просмотр</button>
                <button v-if="config.canEditMedia" type="button" class="vc-button vc-button-secondary" @click="openEditMediaModal(item)">Редактировать</button>
                <button v-if="!isPickerMode && config.canDeleteMedia" type="button" class="vc-button vc-button-danger" @click="deleteMedia(item)">Удалить</button>
              </div>
            </div>
          </article>
        </div>

        <div v-if="lastPage > 1" class="media-pagination">
          <button type="button" class="vc-button vc-button-secondary" :disabled="currentPage <= 1 || loading" @click="changePage(-1)">Назад</button>
          <span>Страница {{ currentPage }} из {{ lastPage }}</span>
          <button type="button" class="vc-button vc-button-secondary" :disabled="currentPage >= lastPage || loading" @click="changePage(1)">Вперёд</button>
        </div>
      </div>
    </section>

    <div v-if="folderModal.open" class="media-modal-backdrop" @click.self="closeFolderModal">
      <div class="media-modal">
        <div class="media-modal-head">
          <div>
            <h3>{{ folderModal.mode === 'create' ? 'Новая папка' : 'Редактирование папки' }}</h3>
            <p>Выберите название, цвет и положение папки в дереве.</p>
          </div>
          <button type="button" class="media-modal-close" @click="closeFolderModal">×</button>
        </div>

        <div class="media-modal-body">
          <label class="vc-field">
            <span class="vc-field-label">Название</span>
            <input v-model.trim="folderForm.name" type="text" class="vc-input">
          </label>

          <label class="vc-field">
            <span class="vc-field-label">Родительская папка</span>
            <select v-model="folderForm.parent_id" class="vc-select">
              <option :value="null">Без родителя</option>
              <option v-for="folder in folderOptions" :key="folder.id" :value="folder.id">
                {{ folder.label }}
              </option>
            </select>
          </label>

          <label class="vc-field">
            <span class="vc-field-label">Цвет</span>
            <div class="folder-color-picker">
              <input v-model="folderForm.color" type="color" class="folder-color-input">
              <input v-model="folderForm.color" type="text" class="vc-input">
            </div>
          </label>

          <div class="folder-presets">
            <button
              v-for="color in folderPalette"
              :key="color"
              type="button"
              class="folder-preset"
              :class="{ 'folder-preset-active': normalizedFolderColor === color }"
              :style="{ backgroundColor: color }"
              @click="folderForm.color = color"
            ></button>
          </div>
        </div>

        <div class="media-modal-actions">
          <button type="button" class="vc-button vc-button-secondary" @click="closeFolderModal">Отмена</button>
          <button type="button" class="vc-button vc-button-primary" @click="submitFolderModal">Сохранить</button>
        </div>
      </div>
    </div>

    <div v-if="editingMedia" class="media-modal-backdrop" @click.self="closeMediaModal">
      <div class="media-modal media-modal-wide">
        <div class="media-modal-head">
          <div>
            <h3>Редактирование файла</h3>
            <p>{{ editingMedia.original_filename }}</p>
          </div>
          <button type="button" class="media-modal-close" @click="closeMediaModal">×</button>
        </div>

        <div class="media-modal-body media-edit-grid">
          <div class="media-edit-preview">
            <img
              v-if="isImage(editingMedia)"
              :src="editingMedia.url"
              :alt="editingMedia.alt || editingMedia.original_filename"
            >
            <iframe v-else-if="isPdf(editingMedia)" :src="editingMedia.url" title="PDF preview"></iframe>
            <div v-else class="media-file-fallback media-file-fallback-large">
              {{ fileBadge(editingMedia) }}
            </div>
          </div>

          <div class="media-edit-form">
            <label class="vc-field">
              <span class="vc-field-label">Название</span>
              <input v-model="editingMedia.title" type="text" class="vc-input">
            </label>

            <label class="vc-field">
              <span class="vc-field-label">Alt</span>
              <input v-model="editingMedia.alt" type="text" class="vc-input">
            </label>

            <label class="vc-field">
              <span class="vc-field-label">Подпись</span>
              <textarea v-model="editingMedia.caption" rows="4" class="vc-textarea"></textarea>
            </label>

            <label class="vc-field">
              <span class="vc-field-label">Папка</span>
              <select v-model="editingMedia.folder_id" class="vc-select">
                <option :value="null">Без папки</option>
                <option v-for="folder in folderOptions" :key="folder.id" :value="folder.id">
                  {{ folder.label }}
                </option>
              </select>
            </label>
          </div>
        </div>

        <div class="media-modal-actions">
          <button type="button" class="vc-button vc-button-secondary" @click="closeMediaModal">Отмена</button>
          <button type="button" class="vc-button vc-button-primary" @click="updateMedia">Сохранить</button>
        </div>
      </div>
    </div>

    <div v-if="previewMedia" class="media-modal-backdrop media-preview-backdrop" @click.self="closePreview">
      <div class="media-modal media-preview-modal">
        <div class="media-modal-head">
          <div>
            <h3>{{ previewMedia.title || previewMedia.original_filename }}</h3>
            <p>{{ previewMedia.original_filename }}</p>
          </div>
          <button type="button" class="media-modal-close" @click="closePreview">×</button>
        </div>

        <div class="media-preview-layout">
          <div class="media-preview-stage">
            <img
              v-if="isImage(previewMedia)"
              :src="previewMedia.url"
              :alt="previewMedia.alt || previewMedia.original_filename"
              class="media-preview-image"
            >
            <iframe
              v-else-if="isPdf(previewMedia)"
              :src="previewMedia.url"
              class="media-preview-frame"
              title="Document preview"
            ></iframe>
            <div v-else class="media-file-fallback media-file-fallback-large">
              {{ fileBadge(previewMedia) }}
            </div>
          </div>

          <aside class="media-preview-meta">
            <div class="media-preview-panel">
              <span class="media-preview-label">Тип</span>
              <strong>{{ previewMedia.mime_type }}</strong>
            </div>
            <div class="media-preview-panel">
              <span class="media-preview-label">Размер</span>
              <strong>{{ formatSize(previewMedia.size) }}</strong>
            </div>
            <div class="media-preview-panel">
              <span class="media-preview-label">Размеры</span>
              <strong>{{ previewMedia.width && previewMedia.height ? `${previewMedia.width}×${previewMedia.height}` : 'Не указаны' }}</strong>
            </div>
            <div class="media-preview-panel">
              <span class="media-preview-label">Папка</span>
              <strong>{{ folderNameById(previewMedia.folder_id) || 'Без папки' }}</strong>
            </div>
            <div class="media-preview-actions">
              <a :href="previewMedia.url" target="_blank" rel="noopener" class="vc-button vc-button-secondary">Открыть файл</a>
              <button v-if="isPickerMode" type="button" class="vc-button vc-button-primary" @click="pickPreviewMedia">Использовать файл</button>
              <button v-if="config.canEditMedia" type="button" class="vc-button vc-button-primary" @click="openEditFromPreview">Редактировать</button>
            </div>
          </aside>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    config: {
        type: Object,
        required: true,
    },
    mode: {
        type: String,
        default: 'manager',
    },
    selectionKind: {
        type: String,
        default: null,
    },
    initialSelectedId: {
        type: [Number, String],
        default: null,
    },
    initialSelectedItem: {
        type: Object,
        default: null,
    },
    onPick: {
        type: Function,
        default: null,
    },
    onCloseRequest: {
        type: Function,
        default: null,
    },
});

const folders = ref(Array.isArray(props.config.initialFolders) ? props.config.initialFolders : []);
const items = ref(Array.isArray(props.config.initialItems) ? props.config.initialItems : []);
const totalItems = ref(Number(props.config.initialTotalItems || items.value.length || 0));
const currentPage = ref(1);
const lastPage = ref(1);
const loading = ref(false);
const uploading = ref(false);
const dragOver = ref(false);
const selectedFolderId = ref(null);
const searchQuery = ref('');
const errorMessage = ref('');
const previewMedia = ref(null);
const editingMedia = ref(null);
const fileInput = ref(null);
const folderModal = ref({ open: false, mode: 'create', id: null });
const folderForm = ref({ name: '', parent_id: null, color: '#6366F1' });
const pickerSelectedId = ref(props.initialSelectedId !== null ? Number(props.initialSelectedId) : (props.initialSelectedItem?.id ? Number(props.initialSelectedItem.id) : null));
const pickerSelectedSnapshot = ref(props.initialSelectedItem || null);

const folderPalette = [
    '#6366F1',
    '#0EA5E9',
    '#14B8A6',
    '#22C55E',
    '#F59E0B',
    '#F97316',
    '#EF4444',
    '#EC4899',
    '#8B5CF6',
    '#64748B',
];

const requestHeaders = (json = false) => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    return {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        ...(json ? { 'Content-Type': 'application/json' } : {}),
    };
};

const normalizedFolders = computed(() => [...folders.value].sort((left, right) => left.name.localeCompare(right.name, 'ru')));

const childrenMap = computed(() => normalizedFolders.value.reduce((acc, folder) => {
    const key = folder.parent_id ?? 0;
    acc[key] = acc[key] || [];
    acc[key].push(folder);
    return acc;
}, {}));

function walkFolders(parentId = null, depth = 0, trail = []) {
    const key = parentId ?? 0;
    const branch = childrenMap.value[key] || [];

    return branch.flatMap((folder) => {
        const currentTrail = [...trail, folder.name];
        return [
            {
                ...folder,
                depth,
                trail: currentTrail,
            },
            ...walkFolders(folder.id, depth + 1, currentTrail),
        ];
    });
}

const folderEntries = computed(() => walkFolders());

const folderOptions = computed(() => folderEntries.value.map((folder) => ({
    id: folder.id,
    label: `${'  '.repeat(folder.depth)}${folder.depth ? '> ' : ''}${folder.name}`,
})));

const selectedFolder = computed(() => folders.value.find((folder) => folder.id === selectedFolderId.value) || null);

const selectedFolderPath = computed(() => {
    if (!selectedFolder.value) {
        return [];
    }

    return folderEntries.value.find((folder) => folder.id === selectedFolder.value.id)?.trail || [selectedFolder.value.name];
});

const normalizedFolderColor = computed(() => normalizeColor(folderForm.value.color));
const isPickerMode = computed(() => props.mode === 'picker');

const imageCount = computed(() => items.value.filter((item) => isImage(item)).length);
const documentCount = computed(() => items.value.length - imageCount.value);
const pickerSelectedItem = computed(() => {
    if (pickerSelectedId.value === null) {
        return null;
    }

    return items.value.find((item) => Number(item.id) === pickerSelectedId.value)
        || (pickerSelectedSnapshot.value && Number(pickerSelectedSnapshot.value.id) === pickerSelectedId.value ? pickerSelectedSnapshot.value : null);
});

function normalizeColor(color) {
    const value = String(color || '').trim().toUpperCase();
    return /^#[0-9A-F]{6}$/.test(value) ? value : '#6366F1';
}

function hexToRgba(hex, alpha) {
    const normalized = normalizeColor(hex).replace('#', '');
    const red = Number.parseInt(normalized.slice(0, 2), 16);
    const green = Number.parseInt(normalized.slice(2, 4), 16);
    const blue = Number.parseInt(normalized.slice(4, 6), 16);

    return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

function folderRowStyle(color, active) {
    const accent = normalizeColor(color);

    return active
        ? {
            background: `linear-gradient(135deg, ${hexToRgba(accent, 0.2)} 0%, ${hexToRgba(accent, 0.1)} 100%)`,
            borderColor: hexToRgba(accent, 0.38),
        }
        : {
            borderColor: 'transparent',
        };
}

function folderIconStyle(color) {
    const accent = normalizeColor(color);
    return {
        color: accent,
        backgroundColor: hexToRgba(accent, 0.14),
    };
}

function folderPillStyle(folderId) {
    const folder = folders.value.find((entry) => entry.id === folderId);
    const color = folder?.color || '#6366F1';
    return {
        color,
        backgroundColor: hexToRgba(color, 0.12),
        borderColor: hexToRgba(color, 0.22),
    };
}

function folderNameById(folderId) {
    return folders.value.find((folder) => folder.id === folderId)?.name || '';
}

function isImage(item) {
    return String(item?.mime_type || '').startsWith('image/');
}

function isPdf(item) {
    return String(item?.mime_type || '') === 'application/pdf' || String(item?.extension || '').toLowerCase() === 'pdf';
}

function fileBadge(item) {
    return String(item?.extension || 'file').toUpperCase();
}

function formatSize(bytes) {
    const value = Number(bytes || 0);
    if (value < 1024) return `${value} B`;
    if (value < 1024 * 1024) return `${(value / 1024).toFixed(1)} KB`;
    return `${(value / (1024 * 1024)).toFixed(1)} MB`;
}

async function loadFolders() {
    const response = await fetch(props.config.folderApiBase, { headers: requestHeaders() });
    if (!response.ok) {
        throw new Error('Не удалось загрузить список папок.');
    }

    const payload = await response.json();
    folders.value = Array.isArray(payload.folders) ? payload.folders : [];
}

async function loadMedia() {
    loading.value = true;
    errorMessage.value = '';

    try {
        const params = new URLSearchParams();
        params.set('per_page', '60');
        params.set('page', String(currentPage.value));

        if (selectedFolderId.value !== null) {
            params.set('folder_id', String(selectedFolderId.value));
        }

        if (searchQuery.value) {
            params.set('q', searchQuery.value);
        }

        if (props.selectionKind) {
            params.set('kind', props.selectionKind);
        }

        const response = await fetch(`${props.config.apiBase}?${params.toString()}`, { headers: requestHeaders() });
        if (!response.ok) {
            throw new Error('Не удалось загрузить медиафайлы.');
        }

        const payload = await response.json();
        items.value = Array.isArray(payload.data) ? payload.data : [];
        totalItems.value = Number(payload.total || items.value.length || 0);
        currentPage.value = Number(payload.current_page || currentPage.value || 1);
        lastPage.value = Math.max(1, Number(payload.last_page || 1));
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Не удалось загрузить медиатеку.';
    } finally {
        loading.value = false;
    }
}

async function refreshAll() {
    errorMessage.value = '';

    try {
        await Promise.all([loadFolders(), loadMedia()]);
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Не удалось обновить медиатеку.';
    }
}

function selectFolder(folderId) {
    selectedFolderId.value = folderId;
}

function changePage(step) {
    const nextPage = currentPage.value + step;
    if (nextPage < 1 || nextPage > lastPage.value || nextPage === currentPage.value) {
        return;
    }

    currentPage.value = nextPage;
    loadMedia();
}

function resetFolderForm(parentId = null, color = '#6366F1') {
    folderForm.value = {
        name: '',
        parent_id: parentId,
        color: normalizeColor(color),
    };
}

function openCreateFolderModal(parentId = null) {
    folderModal.value = { open: true, mode: 'create', id: null };
    resetFolderForm(parentId, selectedFolder.value?.color || '#6366F1');
}

function openCreateFolderWithColor(color) {
    folderModal.value = { open: true, mode: 'create', id: null };
    resetFolderForm(selectedFolderId.value, color);
}

function openEditFolderModal(folder) {
    folderModal.value = { open: true, mode: 'edit', id: folder.id };
    folderForm.value = {
        name: folder.name,
        parent_id: folder.parent_id ?? null,
        color: normalizeColor(folder.color),
    };
}

function closeFolderModal() {
    folderModal.value = { open: false, mode: 'create', id: null };
    resetFolderForm();
}

async function submitFolderModal() {
    if (!folderForm.value.name.trim()) {
        errorMessage.value = 'Введите название папки.';
        return;
    }

    const isCreate = folderModal.value.mode === 'create';
    const endpoint = isCreate
        ? props.config.folderApiBase
        : `${props.config.folderApiBase}/${folderModal.value.id}`;

    const response = await fetch(endpoint, {
        method: isCreate ? 'POST' : 'PUT',
        headers: requestHeaders(true),
        body: JSON.stringify({
            name: folderForm.value.name.trim(),
            parent_id: folderForm.value.parent_id,
            color: normalizeColor(folderForm.value.color),
        }),
    });

    if (!response.ok) {
        const payload = await safeJson(response);
        errorMessage.value = payload?.message || 'Не удалось сохранить папку.';
        return;
    }

    closeFolderModal();
    await loadFolders();
}

async function confirmDeleteFolder(folder) {
    if (!confirm(`Удалить папку «${folder.name}»? Файлы из неё останутся в медиатеке.`)) {
        return;
    }

    const response = await fetch(`${props.config.folderApiBase}/${folder.id}`, {
        method: 'DELETE',
        headers: requestHeaders(),
    });

    if (!response.ok) {
        errorMessage.value = 'Не удалось удалить папку.';
        return;
    }

    if (selectedFolderId.value === folder.id) {
        selectedFolderId.value = null;
    }

    await refreshAll();
}

function openEditMediaModal(item) {
    editingMedia.value = {
        ...item,
        folder_id: item.folder_id ?? null,
    };
}

function closeMediaModal() {
    editingMedia.value = null;
}

async function updateMedia() {
    if (!editingMedia.value) {
        return;
    }

    const response = await fetch(`${props.config.apiBase}/${editingMedia.value.id}`, {
        method: 'PUT',
        headers: requestHeaders(true),
        body: JSON.stringify({
            title: editingMedia.value.title || '',
            alt: editingMedia.value.alt || '',
            caption: editingMedia.value.caption || '',
            folder_id: editingMedia.value.folder_id,
        }),
    });

    if (!response.ok) {
        const payload = await safeJson(response);
        errorMessage.value = payload?.message || 'Не удалось сохранить изменения файла.';
        return;
    }

    closeMediaModal();
    await refreshAll();
}

async function deleteMedia(item) {
    if (!confirm(`Удалить файл «${item.original_filename}»?`)) {
        return;
    }

    const response = await fetch(`${props.config.apiBase}/${item.id}`, {
        method: 'DELETE',
        headers: requestHeaders(),
    });

    if (!response.ok) {
        errorMessage.value = 'Не удалось удалить файл.';
        return;
    }

    if (previewMedia.value?.id === item.id) {
        previewMedia.value = null;
    }

    await refreshAll();
}

async function uploadFiles(fileList) {
    const files = Array.from(fileList || []);
    if (!files.length) {
        return;
    }

    uploading.value = true;
    errorMessage.value = '';

    try {
        for (const file of files) {
            const formData = new FormData();
            formData.append('file', file);

            if (selectedFolderId.value !== null) {
                formData.append('folder_id', String(selectedFolderId.value));
            }

            const response = await fetch(`${props.config.apiBase}/upload`, {
                method: 'POST',
                headers: requestHeaders(),
                body: formData,
            });

            if (!response.ok) {
                const payload = await safeJson(response);
                throw new Error(payload?.message || `Не удалось загрузить файл ${file.name}.`);
            }
        }

        await refreshAll();
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Ошибка загрузки файлов.';
    } finally {
        uploading.value = false;
    }
}

async function handleFileSelect(event) {
    await uploadFiles(event.target.files);
    event.target.value = '';
}

async function handleDrop(event) {
    dragOver.value = false;
    await uploadFiles(event.dataTransfer?.files);
}

function openPreview(item) {
    previewMedia.value = item;
}

function closePreview() {
    previewMedia.value = null;
}

function selectForPicker(item) {
    pickerSelectedId.value = Number(item.id);
    pickerSelectedSnapshot.value = item;
}

function handleCardClick(item) {
    if (isPickerMode.value) {
        selectForPicker(item);
        return;
    }

    openPreview(item);
}

function requestClose() {
    if (typeof props.onCloseRequest === 'function') {
        props.onCloseRequest();
    }
}

function submitPickedMedia() {
    if (!pickerSelectedItem.value || typeof props.onPick !== 'function') {
        return;
    }

    props.onPick(pickerSelectedItem.value);
}

function pickPreviewMedia() {
    if (!previewMedia.value) {
        return;
    }

    selectForPicker(previewMedia.value);
    submitPickedMedia();
}

function triggerFilePicker() {
    fileInput.value?.click();
}

function openEditFromPreview() {
    if (!previewMedia.value) {
        return;
    }

    openEditMediaModal(previewMedia.value);
    closePreview();
}

async function safeJson(response) {
    try {
        return await response.json();
    } catch {
        return null;
    }
}

let searchTimer = null;

watch(selectedFolderId, () => {
    currentPage.value = 1;
    loadMedia();
});

watch(searchQuery, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentPage.value = 1;
        loadMedia();
    }, 250);
});

watch(() => props.initialSelectedId, (value) => {
    pickerSelectedId.value = value !== null && value !== undefined ? Number(value) : null;
});

watch(() => props.initialSelectedItem, (value) => {
    pickerSelectedSnapshot.value = value || null;
    if (value?.id) {
        pickerSelectedId.value = Number(value.id);
    }
});

watch(items, (nextItems) => {
    if (pickerSelectedId.value === null) {
        return;
    }

    const match = nextItems.find((item) => Number(item.id) === pickerSelectedId.value);
    if (match) {
        pickerSelectedSnapshot.value = match;
    }
});

onMounted(() => {
    if (isPickerMode.value || !items.value.length || !folders.value.length) {
        refreshAll();
    }
});
</script>

<style scoped>
.media-layout {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 1.25rem;
}

.media-layout-picker {
  min-height: min(78vh, 920px);
}

.media-sidebar,
.media-main {
  min-width: 0;
}

.media-sidebar {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  position: sticky;
  top: 1rem;
  align-self: start;
}

.media-sidebar-card,
.media-toolbar,
.media-gallery-shell {
  border: 1px solid var(--vc-border);
  border-radius: 1.25rem;
  background: var(--vc-surface);
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.04);
}

.media-sidebar-card,
.media-toolbar,
.media-gallery-shell {
  padding: 1rem;
}

.media-sidebar-head {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.media-sidebar-copy {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.media-sidebar-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.media-sidebar-stat,
.media-gallery-total {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 2rem;
  border: 1px solid var(--vc-border);
  border-radius: 999px;
  padding: 0.25rem 0.7rem;
  color: var(--vc-text-soft);
  font-size: 0.78rem;
  font-weight: 700;
}

.media-sidebar-tree-card {
  padding: 0.9rem;
}

.media-sidebar-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.9rem;
}

.media-sidebar-card-head strong {
  color: var(--vc-text);
  font-size: 0.95rem;
  font-weight: 800;
}

.media-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--vc-text);
}

.media-copy,
.palette-copy {
  margin: 0;
  color: var(--vc-text-soft);
  font-size: 0.84rem;
  line-height: 1.45;
}

.folder-all-card,
.folder-row {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  border: 1px solid transparent;
  border-radius: 0.95rem;
  background: transparent;
  padding: 0.75rem 0.85rem;
  text-align: left;
  transition: 0.2s ease;
}

.folder-all-card:hover,
.folder-row:hover {
  border-color: var(--vc-border);
  background: var(--vc-surface-strong);
}

.folder-all-card-active {
  border-color: rgba(99, 102, 241, 0.28);
  background: rgba(99, 102, 241, 0.08);
}

.folder-all-dot {
  width: 0.9rem;
  height: 0.9rem;
  border-radius: 999px;
  background: linear-gradient(135deg, #94a3b8, #64748b);
  flex: 0 0 auto;
}

.folder-all-copy,
.folder-copy {
  display: flex;
  flex-direction: column;
  min-width: 0;
  flex: 1 1 auto;
}

.folder-all-copy strong,
.folder-name {
  color: var(--vc-text);
  font-size: 0.9rem;
  font-weight: 700;
  line-height: 1.3;
}

.folder-all-copy span,
.folder-meta {
  color: var(--vc-text-soft);
  font-size: 0.75rem;
}

.folder-total {
  color: var(--vc-text);
  font-size: 0.82rem;
  font-weight: 800;
}

.folder-empty,
.media-empty,
.media-error {
  border: 1px dashed var(--vc-border);
  border-radius: 1rem;
  background: var(--vc-surface-strong);
  padding: 1rem;
  color: var(--vc-text-soft);
}

.media-error {
  border-style: solid;
  border-color: rgba(239, 68, 68, 0.18);
  background: rgba(239, 68, 68, 0.06);
  color: #b91c1c;
}

.folder-tree {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  max-height: min(62vh, 760px);
  overflow: auto;
}

.folder-indent {
  flex: 0 0 auto;
}

.folder-icon {
  width: 1.9rem;
  height: 1.9rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.7rem;
  flex: 0 0 auto;
}

.folder-actions {
  display: inline-flex;
  gap: 0.35rem;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.folder-row:hover .folder-actions,
.folder-row-active .folder-actions {
  opacity: 1;
}

.folder-action {
  width: 1.7rem;
  height: 1.7rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.65rem;
  border: 1px solid rgba(148, 163, 184, 0.24);
  background: var(--vc-surface);
  color: var(--vc-text);
  font-size: 0.9rem;
}

.folder-action-danger {
  color: #b91c1c;
}

.palette-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.palette-head h3 {
  margin: 0;
  color: var(--vc-text);
  font-size: 0.95rem;
  font-weight: 700;
}

.palette-head span {
  color: var(--vc-text-soft);
  font-size: 0.75rem;
}

.palette-grid,
.folder-presets {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 0.55rem;
  margin-top: 1rem;
}

.palette-swatch,
.folder-preset {
  width: 100%;
  aspect-ratio: 1;
  border: 2px solid rgba(255, 255, 255, 0.72);
  border-radius: 0.85rem;
  box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.06);
}

.folder-preset-active {
  outline: 2px solid var(--vc-text);
  outline-offset: 2px;
}

.media-main {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.media-toolbar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.media-picker-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  border: 1px solid var(--vc-border);
  border-radius: 1rem;
  background: var(--vc-surface-strong);
  padding: 0.85rem 0.95rem;
}

.media-picker-copy {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  min-width: 0;
}

.media-picker-copy strong {
  color: var(--vc-text);
  font-size: 0.92rem;
  font-weight: 800;
}

.media-picker-copy span {
  color: var(--vc-text-soft);
  font-size: 0.8rem;
  line-height: 1.4;
}

.media-picker-actions {
  display: flex;
  gap: 0.6rem;
}

.media-context {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.media-context-copy {
  min-width: 0;
}

.media-context-kicker {
  margin: 0;
  color: var(--vc-text-soft);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.media-context-title-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.6rem;
  margin-top: 0.35rem;
}

.media-context-title {
  margin: 0;
  color: var(--vc-text);
  font-size: 1.45rem;
  font-weight: 800;
}

.media-context-copy-text {
  margin: 0.35rem 0 0;
  color: var(--vc-text-soft);
  font-size: 0.88rem;
  line-height: 1.45;
}

.media-context-stats {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 0.55rem;
}

.media-stat-chip {
  min-width: 92px;
  border: 1px solid var(--vc-border);
  border-radius: 1rem;
  background: var(--vc-surface-strong);
  padding: 0.7rem 0.8rem;
  text-align: center;
}

.media-stat-chip strong {
  display: block;
  color: var(--vc-text);
  font-size: 1rem;
  font-weight: 800;
}

.media-stat-chip span {
  display: block;
  margin-top: 0.15rem;
  color: var(--vc-text-soft);
  font-size: 0.72rem;
}

.media-toolbar-row {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.media-toolbar-search {
  flex: 1 1 auto;
}

.media-toolbar-actions {
  display: flex;
  gap: 0.75rem;
}

.upload-dropzone {
  border: 2px dashed var(--vc-border);
  border-radius: 1rem;
  background: var(--vc-surface-strong);
  padding: 1rem 1.1rem;
  cursor: pointer;
  transition: 0.2s ease;
}

.upload-dropzone-copy {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.upload-dropzone strong {
  color: var(--vc-text);
  font-size: 0.95rem;
}

.upload-dropzone span {
  color: var(--vc-text-soft);
  font-size: 0.82rem;
  line-height: 1.45;
}

.upload-dropzone-active {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.08);
}

.media-gallery-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.media-gallery-head h3 {
  margin: 0;
  color: var(--vc-text);
  font-size: 1rem;
  font-weight: 800;
}

.media-gallery-head p {
  margin: 0.3rem 0 0;
  color: var(--vc-text-soft);
  font-size: 0.84rem;
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}

.media-grid-loading {
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
}

.media-skeleton {
  aspect-ratio: 1 / 1;
  border-radius: 1.25rem;
  background: linear-gradient(90deg, rgba(148, 163, 184, 0.12), rgba(148, 163, 184, 0.24), rgba(148, 163, 184, 0.12));
  animation: pulse 1.2s infinite linear;
}

@keyframes pulse {
  from { opacity: 0.75; }
  50% { opacity: 1; }
  to { opacity: 0.75; }
}

.media-card {
  border: 1px solid var(--vc-border);
  border-radius: 1.15rem;
  overflow: hidden;
  background: var(--vc-surface);
  box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
}

.media-preview-button {
  width: 100%;
  aspect-ratio: 1 / 1;
  background: var(--vc-surface-strong);
  display: flex;
  align-items: center;
  justify-content: center;
}

.media-preview-button-active {
  box-shadow: inset 0 0 0 2px rgba(99, 102, 241, 0.75);
}

.media-thumb,
.media-edit-preview img,
.media-preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.media-pdf-thumb,
.media-edit-preview iframe,
.media-preview-frame {
  width: 100%;
  height: 100%;
  border: 0;
  background: white;
}

.media-file-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  font-size: 1.2rem;
  font-weight: 800;
  color: var(--vc-text-soft);
}

.media-file-fallback-large {
  min-height: 240px;
  border-radius: 1.5rem;
  background: var(--vc-surface-strong);
}

.media-card-body {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  padding: 1rem;
}

.media-card-head {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
}

.media-card-title {
  margin: 0;
  color: var(--vc-text);
  font-weight: 700;
}

.media-card-subtitle {
  margin: 0.25rem 0 0;
  color: var(--vc-text-soft);
  font-size: 0.8rem;
  word-break: break-word;
}

.media-card-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  color: var(--vc-text-soft);
  font-size: 0.8rem;
}

.media-card-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.media-folder-pill {
  display: inline-flex;
  align-items: center;
  border: 1px solid transparent;
  border-radius: 999px;
  padding: 0.35rem 0.65rem;
  font-size: 0.72rem;
  font-weight: 700;
}

.media-folder-pill-current {
  white-space: nowrap;
}

.media-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 1rem;
  border-top: 1px solid var(--vc-border);
  padding-top: 1rem;
  color: var(--vc-text-soft);
  font-size: 0.84rem;
}

.media-modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 60;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(2, 6, 23, 0.72);
  padding: 1.25rem;
}

.media-modal {
  width: min(720px, 100%);
  border-radius: 1.75rem;
  background: var(--vc-surface);
  box-shadow: 0 30px 80px rgba(2, 6, 23, 0.35);
}

.media-modal-wide {
  width: min(960px, 100%);
}

.media-preview-modal {
  width: min(1180px, 100%);
}

.media-modal-head,
.media-modal-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.25rem 1.25rem 0;
}

.media-modal-head h3 {
  margin: 0;
  color: var(--vc-text);
  font-size: 1.15rem;
  font-weight: 800;
}

.media-modal-head p {
  margin: 0.3rem 0 0;
  color: var(--vc-text-soft);
}

.media-modal-close {
  width: 2.25rem;
  height: 2.25rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: var(--vc-surface-strong);
  color: var(--vc-text);
  font-size: 1.4rem;
}

.media-modal-body {
  padding: 1.25rem;
}

.media-modal-actions {
  justify-content: flex-end;
  padding: 0 1.25rem 1.25rem;
}

.folder-color-picker {
  display: grid;
  grid-template-columns: 64px minmax(0, 1fr);
  gap: 0.75rem;
}

.folder-color-input {
  width: 64px;
  height: 44px;
  border: 1px solid var(--vc-border);
  border-radius: 1rem;
  background: transparent;
}

.media-edit-grid {
  display: grid;
  grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
  gap: 1rem;
}

.media-edit-preview {
  overflow: hidden;
  min-height: 280px;
  border-radius: 1.5rem;
  background: var(--vc-surface-strong);
}

.media-edit-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.media-preview-backdrop {
  background: rgba(2, 6, 23, 0.82);
}

.media-preview-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 300px;
  gap: 1rem;
  padding: 1.25rem;
}

.media-preview-stage {
  overflow: hidden;
  min-height: 520px;
  border-radius: 1.5rem;
  background: rgba(15, 23, 42, 0.04);
}

.media-preview-meta {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.media-preview-panel {
  border: 1px solid var(--vc-border);
  border-radius: 1.2rem;
  background: var(--vc-surface-strong);
  padding: 0.95rem 1rem;
}

.media-preview-label {
  display: block;
  color: var(--vc-text-soft);
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.media-preview-panel strong {
  display: block;
  margin-top: 0.35rem;
  color: var(--vc-text);
  line-height: 1.45;
}

.media-preview-actions {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  margin-top: auto;
}

@media (max-width: 1200px) {
  .media-layout {
    grid-template-columns: 1fr;
  }

  .media-sidebar {
    position: static;
  }
}

@media (max-width: 960px) {
  .media-grid,
  .media-grid-loading {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .media-context,
  .media-toolbar-row,
  .media-picker-bar,
  .media-edit-grid,
  .media-preview-layout {
    flex-direction: column;
    grid-template-columns: 1fr;
  }

  .media-context-stats {
    width: 100%;
    justify-content: flex-start;
  }

  .media-sidebar-card-head,
  .media-gallery-head {
    align-items: flex-start;
    flex-direction: column;
  }
}

@media (max-width: 640px) {
  .media-grid,
  .media-grid-loading {
    grid-template-columns: 1fr;
  }

  .media-toolbar-actions,
  .media-picker-actions,
  .media-sidebar-stats,
  .media-context-stats {
    width: 100%;
  }

  .media-toolbar-actions > *,
  .media-picker-actions > * {
    flex: 1 1 0;
  }

  .folder-color-picker {
    grid-template-columns: 1fr;
  }
}
</style>
