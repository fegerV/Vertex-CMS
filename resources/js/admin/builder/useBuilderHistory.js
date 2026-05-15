import { ref, computed } from 'vue';

const cloneSnapshot = (value) => JSON.parse(JSON.stringify(value));

export function useBuilderHistory({
    sections,
    selectedSection,
    selectedBlock,
    selectedBlockData,
    selectedBlockIds,
    closeQuickAdd,
    closeContextMenu,
}) {
    const history = ref([]);
    const historyIndex = ref(-1);

    const canUndo = computed(() => historyIndex.value > 0);
    const canRedo = computed(() => historyIndex.value < history.value.length - 1);
    const currentHistoryEntry = computed(() => history.value[historyIndex.value] || null);
    const currentHistoryLabel = computed(() => currentHistoryEntry.value?.label || '');

    const applyHistoryEntry = (entry) => {
        sections.value = cloneSnapshot(entry.snapshot);
        selectedSection.value = null;
        selectedBlock.value = null;
        selectedBlockData.value = null;
        selectedBlockIds.value = [];
        closeQuickAdd();
        closeContextMenu();
    };

    const saveToHistory = (label = 'Edit builder', options = {}) => {
        const { mergeKey = null } = options;
        const snapshot = cloneSnapshot(sections.value);

        history.value = history.value.slice(0, historyIndex.value + 1);

        const lastEntry = history.value[history.value.length - 1];
        if (mergeKey && lastEntry?.mergeKey === mergeKey) {
            history.value[history.value.length - 1] = { snapshot, label, mergeKey };
            historyIndex.value = history.value.length - 1;
            return;
        }

        history.value.push({ snapshot, label, mergeKey });
        if (history.value.length > 100) {
            history.value.shift();
            historyIndex.value = history.value.length - 1;
            return;
        }

        historyIndex.value++;
    };

    const undo = () => {
        if (!canUndo.value) return;
        historyIndex.value--;
        applyHistoryEntry(history.value[historyIndex.value]);
    };

    const redo = () => {
        if (!canRedo.value) return;
        historyIndex.value++;
        applyHistoryEntry(history.value[historyIndex.value]);
    };

    return {
        history,
        historyIndex,
        canUndo,
        canRedo,
        currentHistoryLabel,
        saveToHistory,
        undo,
        redo,
    };
}
