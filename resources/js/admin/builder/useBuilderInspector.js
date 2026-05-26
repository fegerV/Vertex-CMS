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
            return `${blockLabel(selectedBlockData.value.type)}: настройки`;
        }

        if (selectedSection.value !== null) {
            return `Секция ${selectedSection.value + 1}: настройки`;
        }

        return inspectorPinned.value
            ? `${inspectorMode.value === 'section' ? 'Инспектор секции' : 'Инспектор блока'}`
            : 'Инспектор';
    });

    const inspectorDescription = computed(() => {
        if (selectedBlockData.value) {
            return 'Настраивайте контент блока, внешний вид и переиспользуемые пресеты.';
        }

        if (selectedSection.value !== null) {
            return 'Управляйте отступами секции, фоном и CSS-параметрами.';
        }

        return inspectorPinned.value
            ? 'Режим инспектора закреплён между переключениями.'
            : 'Выберите секцию или блок на холсте, чтобы начать редактирование.';
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
