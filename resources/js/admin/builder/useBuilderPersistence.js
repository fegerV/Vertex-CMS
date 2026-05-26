import { ref, computed } from 'vue';

export function useBuilderPersistence({
    page,
    sections,
    csrfToken,
    saveToHistory,
}) {
    let livePreviewController = null;
    let livePreviewSequence = 0;
    let lastPersistedPayload = JSON.stringify({ content: sections.value });
    let lastAutoSavePayload = '';
    let lastLivePreviewPayload = '';
    const saving = ref(false);
    const showPreview = ref(false);
    const showRevisions = ref(false);
    const previewHtml = ref('');
    const livePreviewDocument = ref('');
    const livePreviewLoading = ref(false);
    const livePreviewError = ref('');
    const previewBreakpoint = ref('100%');
    const autoSaveStatus = ref('saved');
    const revisions = ref([]);

    const parseJsonResponse = async (response) => {
        const payload = await response.text();

        if (!payload) {
            return {};
        }

        try {
            return JSON.parse(payload);
        } catch (error) {
            throw new Error(`Invalid JSON response (${response.status})`);
        }
    };

    const responseErrorMessage = (data, fallback) => {
        const errors = Array.isArray(data?.errors)
            ? data.errors
            : Object.values(data?.errors || {}).flat();

        if (errors.length) {
            return errors.join('\n');
        }

        return data?.message || data?.error || fallback;
    };

    const autoSaveStatusText = computed(() => {
        switch (autoSaveStatus.value) {
            case 'saved':
                return 'Р’СЃРµ РёР·РјРµРЅРµРЅРёСЏ СЃРѕС…СЂР°РЅРµРЅС‹';
            case 'saving':
                return 'РЎРѕС…СЂР°РЅРµРЅРёРµ...';
            case 'error':
                return 'РћС€РёР±РєР° СЃРѕС…СЂР°РЅРµРЅРёСЏ';
            default:
                return '';
        }
    });

    const hasPendingChanges = computed(() => JSON.stringify({ content: sections.value }) !== lastPersistedPayload);

    const syncPersistedPayload = () => {
        lastPersistedPayload = JSON.stringify({ content: sections.value });
        lastAutoSavePayload = lastPersistedPayload;
    };

    const saveContent = async () => {
        if (saving.value) {
            return;
        }

        const payload = JSON.stringify({
            title: page.title,
            content: sections.value,
            create_revision: true,
        });

        saving.value = true;
        autoSaveStatus.value = 'saving';
        try {
            const response = await fetch(`/admin/pages/${page.id}/builder/advanced/save`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: payload,
            });
            const data = await parseJsonResponse(response);
            if (response.ok && data.ok) {
                autoSaveStatus.value = 'saved';
                syncPersistedPayload();
            } else {
                autoSaveStatus.value = 'error';
                const message = data.error || data.message || data.errors?.[0] || 'РќРµ СѓРґР°Р»РѕСЃСЊ СЃРѕС…СЂР°РЅРёС‚СЊ СЃС‚СЂР°РЅРёС†Сѓ';
                alert(`РћС€РёР±РєР°: ${message}`);
            }
        } catch (error) {
            console.error('Save error:', error);
            autoSaveStatus.value = 'error';
            alert('РЎРµС‚РµРІР°СЏ РѕС€РёР±РєР° РїСЂРё СЃРѕС…СЂР°РЅРµРЅРёРё');
        } finally {
            saving.value = false;
        }
    };

    const exportCurrentSections = async () => {
        try {
            const response = await fetch('/admin/pages/export-sections', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ sections: sections.value }),
            });
            const data = await parseJsonResponse(response);
            if (!data.ok) {
                alert(data.error || 'РќРµ СѓРґР°Р»РѕСЃСЊ РІС‹РїРѕР»РЅРёС‚СЊ СЌРєСЃРїРѕСЂС‚');
                return;
            }
            await navigator.clipboard.writeText(data.export);
            alert(`Р¤Р°Р№Р» ${data.filename} СЃРєРѕРїРёСЂРѕРІР°РЅ РІ Р±СѓС„РµСЂ РѕР±РјРµРЅР°`);
        } catch (error) {
            alert('РћС€РёР±РєР° СЌРєСЃРїРѕСЂС‚Р°');
        }
    };

    const importSectionsPrompt = async () => {
        const importData = window.prompt('Р’СЃС‚Р°РІСЊС‚Рµ JSON СЃ СЌРєСЃРїРѕСЂС‚РёСЂРѕРІР°РЅРЅС‹РјРё СЃРµРєС†РёСЏРјРё');
        if (!importData) return;

        try {
            const response = await fetch('/admin/pages/import-sections', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ import_data: importData, page_id: page.id }),
            });
            const data = await parseJsonResponse(response);
            if (!response.ok || !data.ok) {
                alert(data.error || 'РќРµ СѓРґР°Р»РѕСЃСЊ РёРјРїРѕСЂС‚РёСЂРѕРІР°С‚СЊ СЃРµРєС†РёРё');
                return;
            }
            sections.value = data.sections || [];
            saveToHistory('РРјРїРѕСЂС‚ СЃРµРєС†РёР№');
        } catch (error) {
            alert('РћС€РёР±РєР° РёРјРїРѕСЂС‚Р°');
        }
    };

    const autoSave = async () => {
        const payload = JSON.stringify({ content: sections.value });

        if (saving.value || payload === lastAutoSavePayload || payload === lastPersistedPayload) {
            autoSaveStatus.value = 'saved';
            return;
        }

        autoSaveStatus.value = 'saving';
        try {
            const response = await fetch(`/admin/pages/${page.id}/builder/auto-save`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: payload,
            });
            if (!response.ok) {
                autoSaveStatus.value = 'error';
                return;
            }
            const data = await parseJsonResponse(response);
            autoSaveStatus.value = data.ok === false ? 'error' : 'saved';
            if (data.ok !== false) {
                lastAutoSavePayload = payload;
            }
        } catch (error) {
            autoSaveStatus.value = 'error';
        }
    };

    const previewContent = async () => {
        try {
            const response = await fetch(`/admin/pages/${page.id}/builder/preview`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ content: sections.value }),
            });
            const data = await parseJsonResponse(response);
            if (!response.ok || data.ok === false) {
                const message = responseErrorMessage(data, `HTTP ${response.status}`);
                throw new Error(message);
            }
            if (typeof data.html !== 'string') {
                throw new Error('Preview payload is missing html.');
            }
            previewHtml.value = data.html;
            showPreview.value = true;
        } catch (error) {
            console.error('Preview error:', error);
            alert(`РћС€РёР±РєР° РїСЂРµРґРїСЂРѕСЃРјРѕС‚СЂР°: ${error.message || 'РЅРµРёР·РІРµСЃС‚РЅР°СЏ РѕС€РёР±РєР°'}`);
        }
    };

    const refreshLivePreview = async () => {
        const payload = JSON.stringify({ content: sections.value, document: true });

        if (payload === lastLivePreviewPayload && livePreviewDocument.value) {
            livePreviewError.value = '';
            livePreviewLoading.value = false;
            return;
        }

        const sequence = ++livePreviewSequence;
        if (livePreviewController) {
            livePreviewController.abort();
        }
        livePreviewController = new AbortController();
        livePreviewLoading.value = true;
        livePreviewError.value = '';

        try {
            const response = await fetch(`/admin/pages/${page.id}/builder/preview`, {
                method: 'POST',
                signal: livePreviewController.signal,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: payload,
            });
            const data = await parseJsonResponse(response);
            if (!response.ok || data.ok === false || typeof data.document !== 'string') {
                const message = responseErrorMessage(data, `HTTP ${response.status}`);
                throw new Error(message);
            }

            if (sequence !== livePreviewSequence) {
                return;
            }
            livePreviewDocument.value = data.document;
            lastLivePreviewPayload = payload;
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            console.error('Live preview error:', error);
            livePreviewError.value = error.message || 'Live preview failed';
        } finally {
            if (sequence === livePreviewSequence) {
                livePreviewLoading.value = false;
            }
        }
    };

    const restoreRevision = async (rev) => {
        if (!confirm('Р’РѕСЃСЃС‚Р°РЅРѕРІРёС‚СЊ СЌС‚Сѓ СЂРµРІРёР·РёСЋ?')) return;

        try {
            const response = await fetch(`/admin/pages/${page.id}/revisions/${rev.id}/restore`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const data = await parseJsonResponse(response);
            if (data.ok) {
                sections.value = data.page.content_json.sections;
                syncPersistedPayload();
                showRevisions.value = false;
                saveToHistory('Р’РѕСЃСЃС‚Р°РЅРѕРІР»РµРЅРёРµ СЂРµРІРёР·РёРё');
            }
        } catch (error) {
            alert('РћС€РёР±РєР° РІРѕСЃСЃС‚Р°РЅРѕРІР»РµРЅРёСЏ');
        }
    };

    const applyTemplate = (tpl) => {
        if (!confirm(`РџСЂРёРјРµРЅРёС‚СЊ С€Р°Р±Р»РѕРЅ "${tpl.name}"?`)) return;

        fetch(`/admin/pages/${page.id}/builder/template`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ template_id: tpl.id, merge: true }),
        })
            .then(async (response) => {
                const data = await parseJsonResponse(response);
                if (!response.ok || !data.ok) {
                    throw new Error(data.error || 'РќРµ СѓРґР°Р»РѕСЃСЊ РїСЂРёРјРµРЅРёС‚СЊ С€Р°Р±Р»РѕРЅ');
                }
                return data;
            })
            .then((data) => {
                sections.value = data.page.content_json.sections;
                syncPersistedPayload();
                saveToHistory('РџСЂРёРјРµРЅРµРЅРёРµ С€Р°Р±Р»РѕРЅР°');
            })
            .catch(() => {
                alert('РћС€РёР±РєР° РїСЂРёРјРµРЅРµРЅРёСЏ С€Р°Р±Р»РѕРЅР°');
            });
    };

    const loadRevisions = async () => {
        try {
            const response = await fetch(`/admin/pages/${page.id}/revisions`, {
                headers: {
                    Accept: 'application/json',
                },
            });
            const data = await parseJsonResponse(response);
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
        livePreviewDocument,
        livePreviewLoading,
        livePreviewError,
        previewBreakpoint,
        autoSaveStatus,
        autoSaveStatusText,
        hasPendingChanges,
        revisions,
        saveContent,
        exportCurrentSections,
        importSectionsPrompt,
        autoSave,
        previewContent,
        refreshLivePreview,
        restoreRevision,
        applyTemplate,
        loadRevisions,
        formatDate,
        countBlocks,
    };
}
