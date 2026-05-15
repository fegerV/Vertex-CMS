import { ref, computed } from 'vue';

const cloneSnapshot = (value) => JSON.parse(JSON.stringify(value));

export function useBuilderCanvas({
    sections,
    allBlocks,
    sectionConfig,
    activeBreakpoint,
    selectedSection,
    selectedBlock,
    selectedBlockData,
    selectedBlockIds,
    inspectorMode,
    persistInspectorState,
    saveToHistory,
}) {
    const draggedSectionIndex = ref(null);
    const dropSectionIndex = ref(null);
    const draggedBlock = ref(null);
    const dropBlockTarget = ref(null);
    const quickAddSectionIndex = ref(null);
    const quickAddInsertIndex = ref(0);
    const quickAddQuery = ref('');
    const quickAddMode = ref('blocks');
    const sectionSelectionConfig = computed(() => ({
        mode: sectionConfig.value?.presentation?.selection?.mode || 'single',
        clearBlockSelection: sectionConfig.value?.presentation?.selection?.clear_block_selection !== false,
    }));

    const generateId = () => 'blk_' + Math.random().toString(36).substr(2, 9);
    const blockLabel = (type) => allBlocks.value?.[type]?.name || type;
    const blockSelectionMode = (type) => allBlocks.value?.[type]?.editor?.presentation?.selection?.mode || 'single';

    const templateBlocksFromItem = (item) => {
        if (Array.isArray(item.blocks) && item.blocks.length) {
            return item.blocks.map((block) => ({
                type: block.type,
                settings: cloneSnapshot(block.settings || {}),
            }));
        }

        return (item.sections || [])
            .flatMap((section) => section.blocks || [])
            .map((block) => ({
                type: block.type,
                settings: cloneSnapshot(block.settings || {}),
            }));
    };

    const renderPreviewBlocks = (blocks) => {
        if (!Array.isArray(blocks) || blocks.length === 0) {
            return '<div class="vc-builder-renderer-fallback"><strong>Empty</strong><span>No preview available.</span></div>';
        }

        return blocks.slice(0, 2).map((block) => {
            const definition = allBlocks.value?.[block.type];
            if (!definition) {
                return `<div class="vc-builder-html-preview">${block.type}</div>`;
            }

            try {
                if (typeof definition.render === 'function') {
                    return definition.render(block.settings || {});
                }
            } catch (error) {
                console.error('Quick preview render error:', error);
            }

            if (block.type === 'image' && block.settings?.url) {
                return `<img src="${block.settings.url}" alt="${block.settings.alt || ''}" style="max-width:100%;height:88px;object-fit:cover;border-radius:12px;">`;
            }

            const emptyStateTitle = definition.editor?.preview?.empty_state?.title;

            return `<div class="vc-builder-html-preview">${emptyStateTitle || definition.name || block.type}</div>`;
        }).join('');
    };

    const quickAddBlocks = computed(() => {
        const entries = Object.entries(allBlocks.value);
        const query = quickAddQuery.value.trim().toLowerCase();
        const filtered = !query
            ? entries
            : entries.filter(([_, block]) =>
                block.name.toLowerCase().includes(query)
                || (block.description || '').toLowerCase().includes(query)
                || (block.category || '').toLowerCase().includes(query)
                || (block.editor?.quick_add?.hint || '').toLowerCase().includes(query)
                || (block.editor?.quick_add?.keywords || []).some((keyword) => String(keyword).toLowerCase().includes(query))
            );

        return Object.fromEntries(filtered.slice(0, 8));
    });

    const sectionCanvasStyle = (section) => ({
        backgroundColor: section.settings?.background_color ?? sectionDefaults.value.background_color ?? '',
        paddingTop: `${section.settings?.padding_top ?? sectionDefaults.value.padding_top ?? 16}px`,
        paddingBottom: `${section.settings?.padding_bottom ?? sectionDefaults.value.padding_bottom ?? 16}px`,
    });

    const isDraggedBlock = (sIndex, bIndex) => {
        return draggedBlock.value?.sectionIndex === sIndex && draggedBlock.value?.blockIndex === bIndex;
    };

    const isBlockDropTarget = (sIndex, bIndex) => {
        return dropBlockTarget.value?.sectionIndex === sIndex && dropBlockTarget.value?.blockIndex === bIndex;
    };

    const isInsertTarget = (sIndex, insertIndex) => {
        return dropBlockTarget.value?.sectionIndex === sIndex && dropBlockTarget.value?.blockIndex === insertIndex;
    };

    const isBlockSelected = (blockId) => selectedBlockIds.value.includes(blockId);

    const selectedCountForSection = (sIndex) => {
        return sections.value[sIndex]?.blocks.filter((block) => selectedBlockIds.value.includes(block.id)).length || 0;
    };

    const buildBlock = (type) => {
        const block = allBlocks.value[type];
        if (!block) return null;

        return {
            id: generateId(),
            type,
            settings: cloneSnapshot(block.default?.settings || block.default || {}),
        };
    };

    const renderQuickAddPreview = (item) => {
        if (item.kind === 'preset') {
            return renderPreviewBlocks([{ type: item.preset.type, settings: item.preset.settings || {} }]);
        }

        if (item.kind === 'template') {
            return renderPreviewBlocks(templateBlocksFromItem(item));
        }

        const block = buildBlock(item.type);
        return block ? renderPreviewBlocks([block]) : '<div class="vc-builder-html-preview">Preview</div>';
    };

    const openQuickAdd = (sIndex, insertIndex) => {
        selectedSection.value = sIndex;
        quickAddSectionIndex.value = sIndex;
        quickAddInsertIndex.value = insertIndex;
        quickAddQuery.value = '';
        quickAddMode.value = 'blocks';
    };

    const closeQuickAdd = () => {
        quickAddSectionIndex.value = null;
        quickAddInsertIndex.value = 0;
        quickAddQuery.value = '';
        quickAddMode.value = 'blocks';
    };

    const selectSection = (sIndex) => {
        selectedSection.value = sIndex;
        selectedBlock.value = null;
        selectedBlockData.value = null;
        if (sectionSelectionConfig.value.clearBlockSelection) {
            selectedBlockIds.value = [];
        }
        inspectorMode.value = 'section';
        persistInspectorState();
        closeQuickAdd();
    };

    const toggleBlockSelection = (blockId) => {
        if (selectedBlockIds.value.includes(blockId)) {
            selectedBlockIds.value = selectedBlockIds.value.filter((id) => id !== blockId);
            return;
        }
        selectedBlockIds.value = [...selectedBlockIds.value, blockId];
    };

    const selectBlock = (sIndex, bIndex, event = null) => {
        const block = sections.value[sIndex].blocks[bIndex];
        if (!block) return;
        const selectionMode = blockSelectionMode(block.type);

        if ((event?.ctrlKey || event?.metaKey) && selectionMode === 'multi') {
            toggleBlockSelection(block.id);
        } else {
            selectedBlockIds.value = [block.id];
        }

        selectedSection.value = sIndex;
        selectedBlock.value = bIndex;
        inspectorMode.value = 'block';
        persistInspectorState();
        selectedBlockData.value = {
            type: block.type,
            settings: block.settings,
        };
    };

    const addBlock = (type) => {
        const newBlock = buildBlock(type);
        if (!newBlock) return;

        if (selectedSection.value !== null) {
            sections.value[selectedSection.value].blocks.push(newBlock);
        } else {
            sections.value.push({
                id: generateId(),
                settings: {},
                blocks: [newBlock],
            });
        }

        saveToHistory('Add block');
    };

    const insertBlockAt = (sIndex, insertIndex, type) => {
        const newBlock = buildBlock(type);
        if (!newBlock) return;
        sections.value[sIndex].blocks.splice(insertIndex, 0, newBlock);
        selectBlock(sIndex, insertIndex);
        closeQuickAdd();
        saveToHistory('Insert block');
    };

    const buildPresetBlock = (preset) => ({
        id: generateId(),
        type: preset.type,
        settings: cloneSnapshot(preset.settings || {}),
    });

    const insertPresetAt = (sIndex, insertIndex, preset) => {
        sections.value[sIndex].blocks.splice(insertIndex, 0, buildPresetBlock(preset));
        selectBlock(sIndex, insertIndex);
        closeQuickAdd();
        saveToHistory('Insert preset block');
    };

    const insertTemplateBlocksAt = (sIndex, insertIndex, template) => {
        const blocks = templateBlocksFromItem(template).map((block) => ({
            id: generateId(),
            type: block.type,
            settings: cloneSnapshot(block.settings || {}),
        }));

        sections.value[sIndex].blocks.splice(insertIndex, 0, ...blocks);
        selectBlock(sIndex, insertIndex);
        closeQuickAdd();
        saveToHistory('Insert quick template');
    };

    const runQuickAddItem = (sIndex, insertIndex, item) => {
        if (item.kind === 'preset') {
            insertPresetAt(sIndex, insertIndex, item.preset);
            return;
        }

        if (item.kind === 'template') {
            insertTemplateBlocksAt(sIndex, insertIndex, item);
            return;
        }

        insertBlockAt(sIndex, insertIndex, item.type);
    };

    const addBlockToSection = (sIndex) => {
        openQuickAdd(sIndex, sections.value[sIndex].blocks.length);
    };

    const deleteSection = (sIndex) => {
        const removedIds = new Set((sections.value[sIndex]?.blocks || []).map((block) => block.id));
        sections.value.splice(sIndex, 1);
        selectedBlockIds.value = selectedBlockIds.value.filter((id) => !removedIds.has(id));
        selectedSection.value = null;
        selectedBlock.value = null;
        selectedBlockData.value = null;
        saveToHistory('Delete section');
    };

    const duplicateSection = (sIndex) => {
        const section = cloneSnapshot(sections.value[sIndex]);
        section.id = generateId();
        section.blocks = section.blocks.map((block) => ({ ...block, id: generateId() }));
        sections.value.splice(sIndex + 1, 0, section);
        saveToHistory('Duplicate section');
    };

    const moveSectionUp = (sIndex) => {
        if (sIndex <= 0) return;
        const temp = sections.value[sIndex];
        sections.value[sIndex] = sections.value[sIndex - 1];
        sections.value[sIndex - 1] = temp;
        saveToHistory('Move section up');
    };

    const moveSectionDown = (sIndex) => {
        if (sIndex >= sections.value.length - 1) return;
        const temp = sections.value[sIndex];
        sections.value[sIndex] = sections.value[sIndex + 1];
        sections.value[sIndex + 1] = temp;
        saveToHistory('Move section down');
    };

    const moveBlockUp = (sIndex, bIndex) => {
        if (bIndex <= 0) return;
        const blocks = sections.value[sIndex].blocks;
        const temp = blocks[bIndex];
        blocks[bIndex] = blocks[bIndex - 1];
        blocks[bIndex - 1] = temp;
        selectBlock(sIndex, bIndex - 1);
        saveToHistory('Move block up');
    };

    const moveBlockDown = (sIndex, bIndex) => {
        const blocks = sections.value[sIndex].blocks;
        if (bIndex >= blocks.length - 1) return;
        const temp = blocks[bIndex];
        blocks[bIndex] = blocks[bIndex + 1];
        blocks[bIndex + 1] = temp;
        selectBlock(sIndex, bIndex + 1);
        saveToHistory('Move block down');
    };

    const duplicateBlock = (sIndex, bIndex) => {
        const source = sections.value[sIndex].blocks[bIndex];
        if (!source) return;
        const copy = cloneSnapshot(source);
        copy.id = generateId();
        sections.value[sIndex].blocks.splice(bIndex + 1, 0, copy);
        selectBlock(sIndex, bIndex + 1);
        saveToHistory('Duplicate block');
    };

    const duplicateSelectedBlocks = (sIndex) => {
        const blocks = sections.value[sIndex].blocks;
        const selected = blocks
            .map((block, index) => ({ block, index }))
            .filter(({ block }) => selectedBlockIds.value.includes(block.id));

        if (!selected.length) return;

        let offset = 0;
        for (const { block, index } of selected) {
            const copy = cloneSnapshot(block);
            copy.id = generateId();
            blocks.splice(index + 1 + offset, 0, copy);
            offset++;
        }

        saveToHistory('Duplicate selected blocks');
    };

    const deleteSelectedBlocks = (sIndex) => {
        const idsToDelete = new Set(
            sections.value[sIndex].blocks
                .filter((block) => selectedBlockIds.value.includes(block.id))
                .map((block) => block.id)
        );
        if (!idsToDelete.size) return;

        sections.value[sIndex].blocks = sections.value[sIndex].blocks.filter((block) => !idsToDelete.has(block.id));
        selectedBlockIds.value = selectedBlockIds.value.filter((id) => !idsToDelete.has(id));
        if (selectedSection.value === sIndex) {
            selectedBlock.value = null;
            selectedBlockData.value = null;
        }
        saveToHistory('Delete selected blocks');
    };

    const deleteBlock = (sIndex, bIndex) => {
        const removedId = sections.value[sIndex].blocks[bIndex]?.id;
        sections.value[sIndex].blocks.splice(bIndex, 1);
        if (removedId) {
            selectedBlockIds.value = selectedBlockIds.value.filter((id) => id !== removedId);
        }
        if (selectedSection.value === sIndex && selectedBlock.value === bIndex) {
            selectedBlock.value = null;
            selectedBlockData.value = null;
        } else if (selectedSection.value === sIndex && selectedBlock.value > bIndex) {
            selectedBlock.value--;
            const nextBlock = sections.value[sIndex].blocks[selectedBlock.value];
            selectedBlockData.value = nextBlock
                ? { type: nextBlock.type, settings: nextBlock.settings }
                : null;
        }
        saveToHistory('Delete block');
    };

    const updateBlockSettings = (newSettings) => {
        if (selectedSection.value === null || selectedBlock.value === null) return;
        sections.value[selectedSection.value].blocks[selectedBlock.value].settings = newSettings;
        selectedBlockData.value = {
            type: sections.value[selectedSection.value].blocks[selectedBlock.value].type,
            settings: newSettings,
        };
        saveToHistory('Edit block settings', { mergeKey: `block-settings:${selectedSection.value}:${selectedBlock.value}` });
    };

    const updateSectionSettings = (newSettings) => {
        if (selectedSection.value === null) return;
        sections.value[selectedSection.value].settings = newSettings;
        saveToHistory('Edit section settings', { mergeKey: `section-settings:${selectedSection.value}` });
    };

    const onSectionDragStart = (sIndex, event) => {
        draggedSectionIndex.value = sIndex;
        dropSectionIndex.value = sIndex;
        if (event?.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', `section:${sIndex}`);
        }
    };

    const onSectionDragOver = (sIndex) => {
        if (draggedSectionIndex.value === null) return;
        dropSectionIndex.value = sIndex;
    };

    const onSectionDrop = (sIndex) => {
        const from = draggedSectionIndex.value;
        if (from === null || from === sIndex) {
            onSectionDragEnd();
            return;
        }
        const [section] = sections.value.splice(from, 1);
        sections.value.splice(sIndex, 0, section);
        selectSection(sIndex);
        saveToHistory('Reorder sections');
        onSectionDragEnd();
    };

    const onSectionDragEnd = () => {
        draggedSectionIndex.value = null;
        dropSectionIndex.value = null;
    };

    const onBlockDragStart = (sIndex, bIndex, event) => {
        draggedBlock.value = { sectionIndex: sIndex, blockIndex: bIndex };
        dropBlockTarget.value = { sectionIndex: sIndex, blockIndex: bIndex };
        if (event?.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', `block:${sIndex}:${bIndex}`);
        }
    };

    const onBlockDragOver = (sIndex, bIndex) => {
        if (!draggedBlock.value) return;
        dropBlockTarget.value = { sectionIndex: sIndex, blockIndex: bIndex };
    };

    const onInsertDragOver = (sIndex, insertIndex) => {
        if (!draggedBlock.value) return;
        dropBlockTarget.value = { sectionIndex: sIndex, blockIndex: insertIndex };
    };

    const moveDraggedBlock = (targetSectionIndex, targetBlockIndex = null) => {
        if (!draggedBlock.value) return;
        const { sectionIndex: fromSectionIndex, blockIndex: fromBlockIndex } = draggedBlock.value;
        const sourceBlocks = sections.value[fromSectionIndex]?.blocks;
        if (!sourceBlocks?.[fromBlockIndex]) {
            onBlockDragEnd();
            return;
        }

        const [block] = sourceBlocks.splice(fromBlockIndex, 1);
        let insertIndex = targetBlockIndex;

        if (insertIndex === null || insertIndex === undefined) {
            insertIndex = sections.value[targetSectionIndex].blocks.length;
        }

        if (fromSectionIndex === targetSectionIndex && fromBlockIndex < insertIndex) {
            insertIndex--;
        }

        sections.value[targetSectionIndex].blocks.splice(insertIndex, 0, block);
        selectBlock(targetSectionIndex, insertIndex);
        saveToHistory('Reorder blocks');
        onBlockDragEnd();
    };

    const onBlockDrop = (sIndex, bIndex) => {
        if (!draggedBlock.value) return;
        moveDraggedBlock(sIndex, bIndex);
    };

    const onInsertDrop = (sIndex, insertIndex) => {
        if (!draggedBlock.value) return;
        moveDraggedBlock(sIndex, insertIndex);
    };

    const onSectionBodyDragOver = (sIndex) => {
        if (!draggedBlock.value) return;
        dropBlockTarget.value = { sectionIndex: sIndex, blockIndex: sections.value[sIndex].blocks.length };
    };

    const onSectionBodyDrop = (sIndex) => {
        if (!draggedBlock.value) return;
        moveDraggedBlock(sIndex, sections.value[sIndex].blocks.length);
    };

    const onBlockDragEnd = () => {
        draggedBlock.value = null;
        dropBlockTarget.value = null;
    };

    const canvasClass = computed(() => {
        if (activeBreakpoint.value === 'desktop') return '';
        return `simulate-${activeBreakpoint.value}`;
    });

    return {
        draggedSectionIndex,
        dropSectionIndex,
        quickAddSectionIndex,
        quickAddInsertIndex,
        quickAddQuery,
        quickAddMode,
        quickAddBlocks,
        sectionCanvasStyle,
        isDraggedBlock,
        isBlockDropTarget,
        isInsertTarget,
        isBlockSelected,
        selectedCountForSection,
        buildBlock,
        renderQuickAddPreview,
        openQuickAdd,
        closeQuickAdd,
        selectSection,
        selectBlock,
        addBlock,
        insertBlockAt,
        insertPresetAt,
        insertTemplateBlocksAt,
        runQuickAddItem,
        addBlockToSection,
        deleteSection,
        duplicateSection,
        moveSectionUp,
        moveSectionDown,
        moveBlockUp,
        moveBlockDown,
        duplicateBlock,
        toggleBlockSelection,
        duplicateSelectedBlocks,
        deleteSelectedBlocks,
        deleteBlock,
        updateBlockSettings,
        updateSectionSettings,
        onSectionDragStart,
        onSectionDragOver,
        onSectionDrop,
        onSectionDragEnd,
        onBlockDragStart,
        onBlockDragOver,
        onInsertDragOver,
        onBlockDrop,
        onInsertDrop,
        onSectionBodyDragOver,
        onSectionBodyDrop,
        onBlockDragEnd,
        canvasClass,
        generateId,
        blockLabel,
        blockSelectionMode,
        templateBlocksFromItem,
        renderPreviewBlocks,
    };
}
    const sectionDefaults = computed(() => ({
        padding_top: 16,
        padding_bottom: 16,
        background_color: null,
        ...(sectionConfig.value?.default_settings || {}),
    }));
