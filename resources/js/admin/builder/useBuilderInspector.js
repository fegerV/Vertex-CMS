import { ref, computed } from 'vue';

export function useBuilderInspector({
    storageKey,
    selectedSection,
    selectedBlockData,
    blockLabel,
}) {
    const inspectorPinned = ref(false);
    const inspectorMode = ref('block');

    const inspectorTitle = computed(() => {
        if (selectedBlockData.value) {
            return `${blockLabel(selectedBlockData.value.type)} settings`;
        }

        if (selectedSection.value !== null) {
            return `Section ${selectedSection.value + 1} settings`;
        }

        return inspectorPinned.value
            ? `${inspectorMode.value === 'section' ? 'Section' : 'Block'} inspector`
            : 'Inspector';
    });

    const inspectorDescription = computed(() => {
        if (selectedBlockData.value) {
            return 'Tune block content, appearance and reusable presets.';
        }

        if (selectedSection.value !== null) {
            return 'Adjust section spacing, background and CSS hooks.';
        }

        return inspectorPinned.value
            ? 'Inspector mode is pinned between selections.'
            : 'Pick a section or block on the canvas to start editing.';
    });

    const persistInspectorState = () => {
        localStorage.setItem(storageKey, JSON.stringify({
            pinned: inspectorPinned.value,
            mode: inspectorMode.value,
        }));
    };

    const toggleInspectorPinned = () => {
        inspectorPinned.value = !inspectorPinned.value;
        persistInspectorState();
    };

    const restoreInspectorState = () => {
        try {
            const savedInspectorState = JSON.parse(localStorage.getItem(storageKey) || '{}');
            inspectorPinned.value = Boolean(savedInspectorState.pinned);
            inspectorMode.value = savedInspectorState.mode === 'section' ? 'section' : 'block';
        } catch (error) {
            console.error('Builder inspector restore error:', error);
        }
    };

    return {
        inspectorPinned,
        inspectorMode,
        inspectorTitle,
        inspectorDescription,
        persistInspectorState,
        toggleInspectorPinned,
        restoreInspectorState,
    };
}
