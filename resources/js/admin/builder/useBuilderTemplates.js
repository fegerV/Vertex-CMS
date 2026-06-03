import { ref, computed } from 'vue';

const cloneSnapshot = (value) => {
    if (typeof structuredClone === 'function') {
        try {
            return structuredClone(value);
        } catch (error) {
            // Vue proxies cannot be cloned natively; JSON fallback keeps template payloads plain.
        }
    }

    return JSON.parse(JSON.stringify(value));
};

export function useBuilderTemplates({
    page,
    storageKey,
    selectedSection,
    selectedBlock,
    selectedBlockData,
    sections,
    saveToHistory,
    generateId,
    blockLabel,
    csrfToken,
    selectBlock,
    markContentChanged = () => {},
}) {
    const presetDraftName = ref('');
    const presetVisibility = ref('shared');
    const templateDraftName = ref('');
    const templateDraftCategory = ref('custom');
    const templateVisibility = ref('shared');
    const libraryManagerTab = ref('presets');
    const librarySearchQuery = ref('');
    const presetLibraryScope = ref('all');
    const templateLibraryScope = ref('all');
    const showLibraryManager = ref(false);
    const sharedPresets = ref([]);
    const templates = ref([]);

    const matchesLibraryScope = (item, scope) => {
        if (scope === 'all') return true;
        if (scope === 'builtin') return item.source === 'builtin';
        if (scope === 'shared') return item.visibility === 'shared' && item.source !== 'builtin';
        if (scope === 'mine') return Boolean(item.is_owner);
        return true;
    };

    const currentBlockPresets = computed(() => {
        if (!selectedBlockData.value) return [];
        return sharedPresets.value.filter((preset) => preset.type === selectedBlockData.value.type);
    });

    const filteredCurrentBlockPresets = computed(() => {
        return currentBlockPresets.value.filter((preset) => matchesLibraryScope(preset, presetLibraryScope.value));
    });

    const filteredTemplates = computed(() => {
        return templates.value.filter((template) => matchesLibraryScope(template, templateLibraryScope.value));
    });

    const managedPresetLibraryItems = computed(() => {
        const query = librarySearchQuery.value.trim().toLowerCase();

        return sharedPresets.value.filter((preset) => {
            if (!matchesLibraryScope(preset, presetLibraryScope.value)) return false;
            if (!query) return true;

            return preset.name.toLowerCase().includes(query)
                || preset.type.toLowerCase().includes(query)
                || (preset.owner || '').toLowerCase().includes(query);
        });
    });

    const managedTemplateLibraryItems = computed(() => {
        const query = librarySearchQuery.value.trim().toLowerCase();

        return templates.value.filter((template) => {
            if (!matchesLibraryScope(template, templateLibraryScope.value)) return false;
            if (!query) return true;

            return template.name.toLowerCase().includes(query)
                || (template.category || '').toLowerCase().includes(query)
                || (template.owner || '').toLowerCase().includes(query)
                || (template.source || '').toLowerCase().includes(query);
        });
    });

    const persistBlockPresets = () => {
        localStorage.setItem(storageKey, JSON.stringify(sharedPresets.value));
    };

    const restorePresetCache = () => {
        try {
            sharedPresets.value = JSON.parse(localStorage.getItem(storageKey) || '[]');
        } catch (error) {
            console.error('Builder preset cache restore error:', error);
        }
    };

    const openLibraryManager = (tab = 'presets') => {
        libraryManagerTab.value = tab;
        showLibraryManager.value = true;
        librarySearchQuery.value = '';
    };

    const closeLibraryManager = () => {
        showLibraryManager.value = false;
        librarySearchQuery.value = '';
    };

    const loadSharedPresets = async () => {
        try {
            const response = await fetch('/admin/pages/builder/presets');
            const data = await response.json();
            sharedPresets.value = data.data || [];
            persistBlockPresets();
        } catch (error) {
            console.error('Shared presets load error:', error);
        }
    };

    const loadSharedTemplates = async () => {
        try {
            const response = await fetch('/admin/pages/builder/shared-templates');
            const data = await response.json();
            templates.value = data.data || [];
        } catch (error) {
            console.error('Shared templates load error:', error);
        }
    };

    const saveCurrentBlockPreset = async () => {
        if (!selectedBlockData.value) return;

        const name = presetDraftName.value.trim() || `${blockLabel(selectedBlockData.value.type)} preset`;
        const existingPreset = sharedPresets.value.find((preset) =>
            preset.type === selectedBlockData.value.type && preset.name.toLowerCase() === name.toLowerCase()
        );
        const payload = {
            type: selectedBlockData.value.type,
            name,
            settings: cloneSnapshot(selectedBlockData.value.settings || {}),
            visibility: presetVisibility.value,
        };
        const url = existingPreset
            ? `/admin/pages/builder/presets/${existingPreset.id}`
            : '/admin/pages/builder/presets';
        const method = existingPreset ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            sharedPresets.value = data.presets || sharedPresets.value;
            persistBlockPresets();
        } catch (error) {
            console.error('Shared preset save error:', error);
        }

        presetDraftName.value = '';
    };

    const applyBlockPreset = (preset) => {
        if (!selectedBlockData.value || selectedSection.value === null || selectedBlock.value === null) return;

        const mergedSettings = {
            ...sections.value[selectedSection.value].blocks[selectedBlock.value].settings,
            ...cloneSnapshot(preset.settings || {}),
        };

        sections.value[selectedSection.value].blocks[selectedBlock.value].settings = mergedSettings;
        selectedBlockData.value = {
            type: sections.value[selectedSection.value].blocks[selectedBlock.value].type,
            settings: mergedSettings,
        };
        markContentChanged();
        saveToHistory('Apply block preset');
    };

    const insertPresetAfterSelection = (preset) => {
        if (selectedSection.value === null) return;

        const insertIndex = selectedBlock.value !== null
            ? selectedBlock.value + 1
            : sections.value[selectedSection.value].blocks.length;

        const block = {
            id: generateId(),
            type: preset.type,
            settings: cloneSnapshot(preset.settings || {}),
        };

        sections.value[selectedSection.value].blocks.splice(insertIndex, 0, block);
        selectBlock(selectedSection.value, insertIndex);
        markContentChanged();
        saveToHistory('Insert block preset');
    };

    const updateSharedPresetItem = async (preset) => {
        if (!preset?.can_edit) return;

        try {
            const response = await fetch(`/admin/pages/builder/presets/${preset.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    name: preset.name,
                    visibility: preset.visibility,
                }),
            });
            const data = await response.json();
            sharedPresets.value = data.presets || sharedPresets.value;
            persistBlockPresets();
        } catch (error) {
            console.error('Shared preset metadata update error:', error);
        }
    };

    const deleteBlockPreset = async (presetId) => {
        try {
            const response = await fetch(`/admin/pages/builder/presets/${presetId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const data = await response.json();
            sharedPresets.value = data.presets || sharedPresets.value.filter((preset) => preset.id !== presetId);
            persistBlockPresets();
        } catch (error) {
            console.error('Shared preset delete error:', error);
        }
    };

    const saveSelectedSectionAsTemplate = async () => {
        if (selectedSection.value === null || !sections.value[selectedSection.value]) {
            alert('Select a section first.');
            return;
        }

        const name = templateDraftName.value.trim() || `Section ${selectedSection.value + 1} template`;
        const payload = {
            name,
            category: templateDraftCategory.value.trim() || 'custom',
            sections: [cloneSnapshot(sections.value[selectedSection.value])],
            visibility: templateVisibility.value,
        };

        try {
            const response = await fetch('/admin/pages/builder/shared-templates', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            templates.value = data.templates || templates.value;
        } catch (error) {
            console.error('Shared template save error:', error);
        }

        templateDraftName.value = '';
        templateDraftCategory.value = 'custom';
    };

    const syncSectionToTemplate = async (template) => {
        if (selectedSection.value === null || !sections.value[selectedSection.value]) {
            alert('Select a section first.');
            return;
        }

        if (!template?.id || !template?.can_edit) return;

        try {
            const response = await fetch(`/admin/pages/builder/shared-templates/${template.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    name: template.name,
                    category: template.category,
                    visibility: template.visibility,
                    sections: [cloneSnapshot(sections.value[selectedSection.value])],
                }),
            });
            const data = await response.json();
            templates.value = data.templates || templates.value;
        } catch (error) {
            console.error('Shared template sync error:', error);
        }
    };

    const updateSharedTemplateItem = async (template) => {
        if (!template?.can_edit) return;

        try {
            const response = await fetch(`/admin/pages/builder/shared-templates/${template.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    name: template.name,
                    category: template.category,
                    visibility: template.visibility,
                }),
            });
            const data = await response.json();
            templates.value = data.templates || templates.value;
        } catch (error) {
            console.error('Shared template metadata update error:', error);
        }
    };

    const deleteSharedTemplate = async (templateId) => {
        try {
            const response = await fetch(`/admin/pages/builder/shared-templates/${templateId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const data = await response.json();
            templates.value = data.templates || templates.value.filter((template) => template.id !== templateId);
        } catch (error) {
            console.error('Shared template delete error:', error);
        }
    };

    const formatPresetDate = (value) => {
        if (!value) return 'Recently updated';
        return new Date(value).toLocaleString();
    };

    return {
        presetDraftName,
        presetVisibility,
        templateDraftName,
        templateDraftCategory,
        templateVisibility,
        libraryManagerTab,
        librarySearchQuery,
        presetLibraryScope,
        templateLibraryScope,
        showLibraryManager,
        sharedPresets,
        templates,
        currentBlockPresets,
        filteredCurrentBlockPresets,
        filteredTemplates,
        managedPresetLibraryItems,
        managedTemplateLibraryItems,
        restorePresetCache,
        openLibraryManager,
        closeLibraryManager,
        loadSharedPresets,
        loadSharedTemplates,
        saveCurrentBlockPreset,
        applyBlockPreset,
        insertPresetAfterSelection,
        updateSharedPresetItem,
        deleteBlockPreset,
        saveSelectedSectionAsTemplate,
        syncSectionToTemplate,
        updateSharedTemplateItem,
        deleteSharedTemplate,
        formatPresetDate,
    };
}
