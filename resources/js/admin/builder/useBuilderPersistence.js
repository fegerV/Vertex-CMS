import { ref, computed } from 'vue';

export function useBuilderPersistence({
    page,
    sections,
    csrfToken,
    saveToHistory,
}) {
    const saving = ref(false);
    const showPreview = ref(false);
    const showRevisions = ref(false);
    const previewHtml = ref('');
    const previewBreakpoint = ref('100%');
    const autoSaveStatus = ref('saved');
    const revisions = ref([]);

    const autoSaveStatusText = computed(() => {
        switch (autoSaveStatus.value) {
            case 'saved':
                return 'All changes saved';
            case 'saving':
                return 'Saving...';
            default:
                return '';
        }
    });

    const saveContent = async () => {
        saving.value = true;
        try {
            const response = await fetch(`/admin/pages/${page.id}/builder/advanced/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    title: page.title,
                    content: sections.value,
                    create_revision: true,
                }),
            });
            const data = await response.json();
            if (data.ok) {
                autoSaveStatus.value = 'saved';
            } else {
                alert('Error: ' + (data.error || 'Save failed'));
            }
        } catch (error) {
            console.error('Save error:', error);
            alert('Network error');
        } finally {
            saving.value = false;
        }
    };

    const exportCurrentSections = async () => {
        try {
            const response = await fetch('/admin/pages/export-sections', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ sections: sections.value }),
            });
            const data = await response.json();
            if (!data.ok) {
                alert(data.error || 'Export failed');
                return;
            }
            await navigator.clipboard.writeText(data.export);
            alert(`Copied ${data.filename} to clipboard`);
        } catch (error) {
            alert('Export error');
        }
    };

    const importSectionsPrompt = async () => {
        const importData = window.prompt('Paste exported sections JSON');
        if (!importData) return;

        try {
            const response = await fetch('/admin/pages/import-sections', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ import_data: importData, page_id: page.id }),
            });
            const data = await response.json();
            if (!data.ok) {
                alert(data.error || 'Import failed');
                return;
            }
            sections.value = data.sections || [];
            saveToHistory('Import sections');
        } catch (error) {
            alert('Import error');
        }
    };

    const autoSave = async () => {
        autoSaveStatus.value = 'saving';
        try {
            await fetch(`/admin/pages/${page.id}/builder/auto-save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ content: sections.value }),
            });
            autoSaveStatus.value = 'saved';
        } catch (error) {
            autoSaveStatus.value = 'error';
        }
    };

    const previewContent = async () => {
        try {
            const response = await fetch(`/admin/pages/${page.id}/builder/preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ content: sections.value }),
            });
            const data = await response.json();
            previewHtml.value = data.html;
            showPreview.value = true;
        } catch (error) {
            alert('Preview error');
        }
    };

    const restoreRevision = async (rev) => {
        if (!confirm('Restore this revision?')) return;

        try {
            const response = await fetch(`/admin/pages/${page.id}/revisions/${rev.id}/restore`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
            });
            const data = await response.json();
            if (data.ok) {
                sections.value = data.page.content_json.sections;
                showRevisions.value = false;
                saveToHistory('Restore revision');
            }
        } catch (error) {
            alert('Restore error');
        }
    };

    const applyTemplate = (tpl) => {
        if (!confirm(`Apply template "${tpl.name}"?`)) return;

        fetch(`/admin/pages/${page.id}/builder/template`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ template_id: tpl.id, merge: true }),
        })
            .then((response) => response.json())
            .then((data) => {
                sections.value = data.page.content_json.sections;
                saveToHistory('Apply template');
            });
    };

    const loadRevisions = async () => {
        try {
            const response = await fetch(`/admin/pages/${page.id}/revisions`);
            const data = await response.json();
            revisions.value = data.data || [];
        } catch (error) {
            console.error('Load revisions error:', error);
        }
    };

    const formatDate = (value) => new Date(value).toLocaleString();
    const countBlocks = (content) => (content?.sections || []).reduce((sum, section) => sum + (section.blocks?.length || 0), 0);

    return {
        saving,
        showPreview,
        showRevisions,
        previewHtml,
        previewBreakpoint,
        autoSaveStatus,
        autoSaveStatusText,
        revisions,
        saveContent,
        exportCurrentSections,
        importSectionsPrompt,
        autoSave,
        previewContent,
        restoreRevision,
        applyTemplate,
        loadRevisions,
        formatDate,
        countBlocks,
    };
}
