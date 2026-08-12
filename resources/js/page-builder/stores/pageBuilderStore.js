/**
 * Page Builder Store - State Management
 * Manages blocks, selection, history, and global styles
 */

import { reactive, readonly, toRefs } from 'vue';

export function createPageBuilderStore(initialState = {}) {
    const state = reactive({
        // Content
        content: initialState.content || [],
        
        // Selection
        selectedIndex: -1,
        
        // History for undo/redo
        history: [],
        historyIndex: -1,
        maxHistory: 50,
        
        // UI State
        viewMode: 'desktop', // desktop, tablet, mobile
        isPreviewOpen: false,
        isSaving: false,
        hasChanges: false,
        hoveredBlockType: null,
        
        // Drag & Drop
        draggingIndex: null,
        dragOverIndex: null,
        
        // Available blocks configuration
        availableBlocks: {},
        
        // Global Styles
        globalStyles: initialState.globalStyles || {
            colors: {},
            typography: {},
            spacing: {},
            buttons: {}
        },
        
        // Color Palette
        colorPalette: initialState.colorPalette || [],
        
        // Typography Presets
        typographyPresets: initialState.typographyPresets || [],
        
        // Button Presets
        buttonPresets: initialState.buttonPresets || [],
        
        // Design Library / Templates
        designLibrary: initialState.designLibrary || [],
        
        // Toast notifications
        toast: {
            visible: false,
            message: '',
            type: 'success'
        }
    });

    // Computed properties
    const selectedBlock = () => {
        if (state.selectedIndex === -1) return null;
        return state.content[state.selectedIndex] || null;
    };

    const canUndo = () => state.historyIndex > 0;
    
    const canRedo = () => state.historyIndex < state.history.length - 1;

    // Actions
    function saveToHistory() {
        // Remove any future history if we're in the middle
        if (state.historyIndex < state.history.length - 1) {
            state.history = state.history.slice(0, state.historyIndex + 1);
        }
        
        // Add current state
        state.history.push(JSON.stringify(state.content));
        if (state.history.length > state.maxHistory) {
            state.history.shift();
        } else {
            state.historyIndex++;
        }
    }

    function undo() {
        if (!canUndo()) return false;
        state.historyIndex--;
        const previousState = JSON.parse(state.history[state.historyIndex]);
        state.content.splice(0, state.content.length, ...previousState);
        state.hasChanges = true;
        return true;
    }

    function redo() {
        if (!canRedo()) return false;
        state.historyIndex++;
        const nextState = JSON.parse(state.history[state.historyIndex]);
        state.content.splice(0, state.content.length, ...nextState);
        state.hasChanges = true;
        return true;
    }

    function addBlock(type, blockConfig) {
        if (!blockConfig) return null;

        const newBlock = {
            type,
            settings: JSON.parse(JSON.stringify(blockConfig.default?.settings || blockConfig.default || {})),
            _id: `block_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            _createdAt: Date.now()
        };

        saveToHistory();

        if (state.selectedIndex === -1) {
            state.content.push(newBlock);
            state.selectedIndex = state.content.length - 1;
        } else {
            state.content.splice(state.selectedIndex + 1, 0, newBlock);
            state.selectedIndex++;
        }
        
        state.hasChanges = true;
        return newBlock;
    }

    function deleteBlock(index) {
        if (index < 0 || index >= state.content.length) return false;
        
        saveToHistory();
        state.content.splice(index, 1);
        
        if (state.selectedIndex >= index) {
            state.selectedIndex = Math.max(-1, state.selectedIndex - 1);
        }
        
        state.hasChanges = true;
        return true;
    }

    function selectBlock(index) {
        if (index < -1 || index >= state.content.length) return;
        state.selectedIndex = index;
    }

    function duplicateBlock(index) {
        if (index === -1 || index >= state.content.length) return null;
        
        saveToHistory();
        const original = state.content[index];
        const copy = JSON.parse(JSON.stringify(original));
        copy._id = `block_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        copy._duplicatedFrom = original._id;
        
        state.content.splice(index + 1, 0, copy);
        state.selectedIndex = index + 1;
        state.hasChanges = true;
        
        return copy;
    }

    function moveBlock(fromIndex, toIndex) {
        if (fromIndex < 0 || fromIndex >= state.content.length ||
            toIndex < 0 || toIndex >= state.content.length) return false;

        saveToHistory();
        const [block] = state.content.splice(fromIndex, 1);
        state.content.splice(toIndex, 0, block);
        state.selectedIndex = toIndex;
        state.hasChanges = true;
        
        return true;
    }

    function updateBlockSetting(key, value) {
        if (state.selectedIndex === -1) return false;
        
        const block = state.content[state.selectedIndex];
        if (!block) return false;
        
        if (!block.settings) {
            block.settings = {};
        }
        
        // Support nested settings (e.g., "items.0.question")
        const keys = key.split('.');
        let obj = block.settings;
        
        for (let i = 0; i < keys.length - 1; i++) {
            const k = keys[i];
            if (!(k in obj)) {
                obj[k] = isNaN(keys[i + 1]) ? {} : [];
            }
            obj = obj[k];
        }
        
        obj[keys[keys.length - 1]] = value;
        state.hasChanges = true;
        
        return true;
    }

    function setViewMode(mode) {
        state.viewMode = mode;
    }

    function showToast(message, type = 'success') {
        state.toast.message = message;
        state.toast.type = type;
        state.toast.visible = true;
        setTimeout(() => {
            state.toast.visible = false;
        }, 3000);
    }

    function clearContent() {
        saveToHistory();
        state.content.splice(0, state.content.length);
        state.selectedIndex = -1;
        state.hasChanges = true;
    }

    function setContent(content) {
        saveToHistory();
        state.content = content || [];
        state.selectedIndex = -1;
        state.hasChanges = true;
    }

    function getContent() {
        return state.content;
    }

    function exportJSON() {
        return JSON.stringify(state.content, null, 2);
    }

    function importJSON(jsonString) {
        try {
            const content = JSON.parse(jsonString);
            setContent(content);
            return true;
        } catch (e) {
            console.error('Invalid JSON:', e);
            return false;
        }
    }

    // Apply global color to all instances
    function applyGlobalColor(colorName, colorValue) {
        const existingIndex = state.colorPalette.findIndex(c => c.name === colorName);
        if (existingIndex >= 0) {
            state.colorPalette[existingIndex].value = colorValue;
        } else {
            state.colorPalette.push({ name: colorName, value: colorValue });
        }
        state.hasChanges = true;
    }

    // Save preset for reuse
    function savePreset(name, type, settings) {
        const preset = {
            id: `preset_${Date.now()}`,
            name,
            type,
            settings,
            createdAt: Date.now()
        };
        
        // Store in appropriate preset collection based on type
        switch(type) {
            case 'button':
                state.buttonPresets.push(preset);
                break;
            case 'typography':
                state.typographyPresets.push(preset);
                break;
            default:
                // Generic preset
                if (!state.globalStyles.presets) {
                    state.globalStyles.presets = [];
                }
                state.globalStyles.presets.push(preset);
        }
        
        return preset;
    }

    return {
        state: readonly(state),
        selectedBlock,
        canUndo,
        canRedo,
        saveToHistory,
        undo,
        redo,
        addBlock,
        deleteBlock,
        selectBlock,
        duplicateBlock,
        moveBlock,
        updateBlockSetting,
        setViewMode,
        showToast,
        clearContent,
        setContent,
        getContent,
        exportJSON,
        importJSON,
        applyGlobalColor,
        savePreset
    };
}

export default createPageBuilderStore;
