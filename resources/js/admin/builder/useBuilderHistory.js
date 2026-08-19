import { ref, computed } from 'vue';

const cloneSnapshot = (value) => {
    if (typeof structuredClone === 'function') {
        try {
            return structuredClone(value);
        } catch (error) {
            // Vue proxies cannot be cloned natively; JSON fallback keeps snapshots serializable.
        }
    }

    return JSON.parse(JSON.stringify(value));
};

const snapshotSignature = (value) => JSON.stringify(value);

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
        const signature = snapshotSignature(sections.value);
        const lastEntry = history.value[historyIndex.value];

        if (lastEntry?.signature === signature) {
            return;
        }

        const snapshot = cloneSnapshot(sections.value);

        history.value = history.value.slice(0, historyIndex.value + 1);

        const lastAvailableEntry = history.value[history.value.length - 1];
        if (mergeKey && lastAvailableEntry?.mergeKey === mergeKey) {
            history.value[history.value.length - 1] = { snapshot, label, mergeKey, signature };
            historyIndex.value = history.value.length - 1;
            return;
        }

        history.value.push({ snapshot, label, mergeKey, signature });
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
