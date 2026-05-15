import { ref, computed, nextTick } from 'vue';

export function useBuilderCommands({
    sections,
    allBlocks,
    sectionConfig,
    selectedSection,
    selectedBlock,
    selectedBlockIds,
    canUndo,
    canRedo,
    showRevisions,
    showPreview,
    closeQuickAdd,
    closeLibraryManager,
    closeMediaPicker,
    saveContent,
    previewContent,
    undo,
    redo,
    openQuickAdd,
    duplicateSelectedBlocks,
    deleteSelectedBlocks,
    duplicateBlock,
    deleteBlock,
    moveBlockUp,
    moveBlockDown,
    duplicateSection,
    deleteSection,
    openInlineEdit,
}) {
    const showCommandPalette = ref(false);
    const commandQuery = ref('');
    const commandPaletteInput = ref(null);
    const contextMenu = ref({ visible: false, x: 0, y: 0, items: [] });
    const selectedBlockType = computed(() => {
        if (selectedSection.value === null || selectedBlock.value === null) {
            return null;
        }

        return sections.value[selectedSection.value]?.blocks?.[selectedBlock.value]?.type || null;
    });
    const sectionCommands = () => Array.isArray(sectionConfig?.value?.commands) ? sectionConfig.value.commands : [];
    const blockCommands = (type) => allBlocks?.value?.[type]?.editor?.commands || [];
    const selectedSectionCommandItems = computed(() => {
        if (selectedSection.value === null) {
            return [];
        }

        return sectionCommands().map((command) => ({
            ...command,
            description: command.description || 'Section command',
            disabled: false,
        }));
    });
    const selectedBlockCommandItems = computed(() => {
        if (selectedBlockType.value === null || selectedSection.value === null || selectedBlock.value === null) {
            return [];
        }

        return blockCommands(selectedBlockType.value).map((command) => ({
            ...command,
            description: command.description || 'Block command',
            disabled: false,
        }));
    });

    const commandItems = computed(() => {
        const hasSelection = selectedBlockIds.value.length > 0;

        const staticItems = [
            { id: 'save', label: 'Save changes', description: 'Persist current builder JSON', shortcut: 'Ctrl/Cmd+S' },
            { id: 'preview', label: 'Open preview', description: 'Render current page preview', shortcut: 'Ctrl/Cmd+Shift+P' },
            { id: 'undo', label: 'Undo last action', description: 'Step back one builder action', shortcut: 'Ctrl/Cmd+Z', disabled: !canUndo.value },
            { id: 'redo', label: 'Redo action', description: 'Restore next builder action', shortcut: 'Ctrl/Cmd+Shift+Z', disabled: !canRedo.value },
            { id: 'revisions', label: 'Open revisions', description: 'Browse saved revisions', shortcut: 'R' },
            { id: 'duplicate-selection', label: 'Duplicate selected blocks', description: 'Duplicate current multi-selection in this section', shortcut: 'Ctrl/Cmd+D', disabled: !hasSelection },
            { id: 'delete-selection', label: 'Delete selected blocks', description: 'Delete current multi-selection', shortcut: 'Delete', disabled: !hasSelection },
        ];

        return [...staticItems, ...selectedSectionCommandItems.value, ...selectedBlockCommandItems.value]
            .filter((item) => !item.disabled)
            .filter((item, index, items) => items.findIndex((candidate) => candidate.id === item.id) === index);
    });

    const filteredCommandItems = computed(() => {
        const query = commandQuery.value.trim().toLowerCase();
        if (!query) return commandItems.value;
        return commandItems.value.filter((item) =>
            item.label.toLowerCase().includes(query)
            || item.description.toLowerCase().includes(query)
            || (item.shortcut || '').toLowerCase().includes(query)
        );
    });

    const closeCommandPalette = () => {
        showCommandPalette.value = false;
        commandQuery.value = '';
    };

    const openCommandPalette = async () => {
        showCommandPalette.value = true;
        commandQuery.value = '';
        await nextTick();
        commandPaletteInput.value?.focus();
    };

    const closeContextMenu = () => {
        contextMenu.value = { visible: false, x: 0, y: 0, items: [] };
    };

    const openContextMenu = (items, event) => {
        contextMenu.value = {
            visible: true,
            x: event.clientX,
            y: event.clientY,
            items,
        };
    };

    const openSectionContextMenu = (sIndex, event) => {
        const items = sectionCommands().map((command) => ({
            ...command,
            sectionIndex: sIndex,
        }));

        openContextMenu(items, event);
    };

    const openBlockContextMenu = (sIndex, bIndex, event) => {
        const type = sections.value[sIndex]?.blocks?.[bIndex]?.type;
        const items = blockCommands(type).map((command) => ({
            ...command,
            sectionIndex: sIndex,
            blockIndex: bIndex,
        }));

        openContextMenu(items, event);
    };

    const executeCommand = async (commandId, payload = {}) => {
        const targetSection = payload.sectionIndex ?? selectedSection.value;
        const targetBlock = payload.blockIndex ?? selectedBlock.value;

        switch (commandId) {
            case 'save':
                await saveContent();
                break;
            case 'preview':
                await previewContent();
                break;
            case 'undo':
                undo();
                break;
            case 'redo':
                redo();
                break;
            case 'revisions':
                showRevisions.value = true;
                break;
            case 'quick-add':
                if (targetSection !== null) {
                    openQuickAdd(targetSection, sections.value[targetSection].blocks.length);
                }
                break;
            case 'duplicate-selection':
                if (targetSection !== null) {
                    duplicateSelectedBlocks(targetSection);
                }
                break;
            case 'delete-selection':
                if (targetSection !== null) {
                    deleteSelectedBlocks(targetSection);
                }
                break;
            case 'duplicate-block':
                if (targetSection !== null && targetBlock !== null) {
                    duplicateBlock(targetSection, targetBlock);
                }
                break;
            case 'inline-edit':
                if (targetSection !== null && targetBlock !== null) {
                    openInlineEdit(targetSection, targetBlock);
                }
                break;
            case 'delete-block':
                if (targetSection !== null && targetBlock !== null) {
                    deleteBlock(targetSection, targetBlock);
                }
                break;
            case 'move-block-up':
                if (targetSection !== null && targetBlock !== null) {
                    moveBlockUp(targetSection, targetBlock);
                }
                break;
            case 'move-block-down':
                if (targetSection !== null && targetBlock !== null) {
                    moveBlockDown(targetSection, targetBlock);
                }
                break;
            case 'duplicate-section':
                if (targetSection !== null) {
                    duplicateSection(targetSection);
                }
                break;
            case 'delete-section':
                if (targetSection !== null) {
                    deleteSection(targetSection);
                }
                break;
        }

        closeContextMenu();
        closeCommandPalette();
    };

    const executeContextCommand = (item) => executeCommand(item.id, item);

    const handleGlobalPointer = () => {
        if (contextMenu.value.visible) {
            closeContextMenu();
        }
    };

    const handleKeydown = async (event) => {
        const target = event.target;
        const typingTarget = target && (
            ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName)
            || target.isContentEditable
        );

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            await openCommandPalette();
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
            event.preventDefault();
            await saveContent();
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'z') {
            event.preventDefault();
            if (event.shiftKey) {
                redo();
            } else {
                undo();
            }
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'y') {
            event.preventDefault();
            redo();
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.shiftKey && event.key.toLowerCase() === 'p') {
            event.preventDefault();
            await previewContent();
            return;
        }

        if (event.key === 'Escape') {
            closeQuickAdd();
            closeContextMenu();
            closeCommandPalette();
            closeLibraryManager();
            closeMediaPicker();
            showPreview.value = false;
            showRevisions.value = false;
            return;
        }

        if (typingTarget) return;

        if (event.key.toLowerCase() === 'a' && selectedSection.value !== null) {
            event.preventDefault();
            openQuickAdd(selectedSection.value, sections.value[selectedSection.value].blocks.length);
            return;
        }

        if (event.key.toLowerCase() === 'r') {
            event.preventDefault();
            showRevisions.value = true;
            return;
        }

        if (event.key === 'Enter' && selectedSection.value !== null && selectedBlock.value !== null) {
            const blockType = sections.value[selectedSection.value]?.blocks?.[selectedBlock.value]?.type;
            const inlineEditing = allBlocks?.value?.[blockType]?.editor?.inline_editing;
            if (inlineEditing?.enabled) {
                event.preventDefault();
                openInlineEdit(selectedSection.value, selectedBlock.value);
                return;
            }
        }

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'd') {
            event.preventDefault();
            if (selectedBlockIds.value.length && selectedSection.value !== null) {
                duplicateSelectedBlocks(selectedSection.value);
            } else if (selectedSection.value !== null && selectedBlock.value !== null) {
                duplicateBlock(selectedSection.value, selectedBlock.value);
            }
            return;
        }

        if ((event.key === 'Delete' || event.key === 'Backspace') && selectedSection.value !== null) {
            event.preventDefault();
            if (selectedBlockIds.value.length) {
                deleteSelectedBlocks(selectedSection.value);
            } else if (selectedBlock.value !== null) {
                deleteBlock(selectedSection.value, selectedBlock.value);
            }
            return;
        }

        if (event.altKey && event.key === 'ArrowUp' && selectedSection.value !== null && selectedBlock.value !== null) {
            event.preventDefault();
            moveBlockUp(selectedSection.value, selectedBlock.value);
            return;
        }

        if (event.altKey && event.key === 'ArrowDown' && selectedSection.value !== null && selectedBlock.value !== null) {
            event.preventDefault();
            moveBlockDown(selectedSection.value, selectedBlock.value);
        }
    };

    return {
        showCommandPalette,
        commandQuery,
        commandPaletteInput,
        contextMenu,
        filteredCommandItems,
        openCommandPalette,
        closeCommandPalette,
        closeContextMenu,
        openSectionContextMenu,
        openBlockContextMenu,
        executeCommand,
        executeContextCommand,
        handleGlobalPointer,
        handleKeydown,
    };
}
