import { ref, nextTick } from 'vue';

export function useBuilderMedia({
    config,
    sections,
    selectedSection,
    selectedBlock,
    selectedBlockData,
    saveToHistory,
}) {
    const mediaLookup = ref({});
    const showMediaPicker = ref(false);
    const mediaPickerMount = ref(null);
    const mediaPickerSelected = ref(null);
    const mediaPickerTarget = ref(null);

    const collectReferencedMediaIds = () => {
        const ids = new Set();

        const visit = (value, key = null) => {
            if (Array.isArray(value)) {
                value.forEach((item) => visit(item));
                return;
            }

            if (value && typeof value === 'object') {
                Object.entries(value).forEach(([nestedKey, nestedValue]) => {
                    if ((nestedKey === 'media_id' || nestedKey === 'image') && Number.isFinite(Number(nestedValue)) && Number(nestedValue) > 0) {
                        ids.add(Number(nestedValue));
                    }
                    visit(nestedValue, nestedKey);
                });
                return;
            }

            if ((key === 'media_id' || key === 'image') && Number.isFinite(Number(value)) && Number(value) > 0) {
                ids.add(Number(value));
            }
        };

        visit(sections.value);

        return [...ids];
    };

    const hydrateMediaLookup = async (ids = []) => {
        const missingIds = ids
            .map((id) => Number(id))
            .filter((id) => id > 0 && !mediaLookup.value[id]);

        if (!missingIds.length) return;

        try {
            const chunks = [];
            for (let index = 0; index < missingIds.length; index += 50) {
                chunks.push(missingIds.slice(index, index + 50));
            }

            for (const chunk of chunks) {
                const response = await fetch(`/admin/api/media?ids[]=${chunk.join('&ids[]=')}&per_page=${chunk.length}`);
                const data = await response.json();
                const items = data.data || [];
                mediaLookup.value = {
                    ...mediaLookup.value,
                    ...Object.fromEntries(items.map((item) => [Number(item.id), item])),
                };
            }
        } catch (error) {
            console.error('Media hydrate error:', error);
        }
    };

    const closeMediaPicker = () => {
        if (mediaPickerMount.value && window.Vertex?.unmountMediaPicker) {
            window.Vertex.unmountMediaPicker(mediaPickerMount.value);
        }
        showMediaPicker.value = false;
        mediaPickerTarget.value = null;
        mediaPickerSelected.value = null;
    };

    const applyPickedMedia = (pickedItem = mediaPickerSelected.value) => {
        if (!pickedItem || selectedSection.value === null || selectedBlock.value === null) return;

        const block = sections.value[selectedSection.value].blocks[selectedBlock.value];
        const settings = { ...block.settings };
        const targetPath = mediaPickerTarget.value?.path || (mediaPickerTarget.value?.key ? [mediaPickerTarget.value.key] : []);
        const setAtPath = (targetValue, path, value) => {
            const normalized = Array.isArray(path) ? path : [path];
            const last = normalized.at(-1);
            const target = normalized.slice(0, -1).reduce((acc, segment) => acc?.[segment], targetValue);
            if (!target || last === undefined) return;
            target[last] = value;
        };
        const lastKey = targetPath.at(-1);

        if (lastKey === 'media_id') {
            setAtPath(settings, targetPath, pickedItem.id);
            if (targetPath.length === 1) {
                settings.url = pickedItem.url || settings.url || '';
                if (!settings.alt && pickedItem.alt) {
                    settings.alt = pickedItem.alt;
                }
            }
        } else if (lastKey) {
            setAtPath(settings, targetPath, pickedItem.id);
            if (targetPath.length === 1 && (lastKey === 'image' || lastKey.endsWith('_image')) && !settings.url) {
                settings.url = pickedItem.url || '';
            }
        }

        sections.value[selectedSection.value].blocks[selectedBlock.value].settings = settings;
        selectedBlockData.value = {
            type: sections.value[selectedSection.value].blocks[selectedBlock.value].type,
            settings,
        };
        saveToHistory('Attach media');
        closeMediaPicker();
    };

    const mountSharedMediaPicker = () => {
        if (!mediaPickerMount.value || !window.Vertex?.mountMediaPicker) {
            return;
        }

        window.Vertex.mountMediaPicker(mediaPickerMount.value, {
            config: {
                apiBase: config.media?.api_base || '/admin/api/media',
                folderApiBase: config.media?.folder_api_base || '/admin/api/media/folders',
                canManageFolders: Boolean(config.media?.can_manage_folders),
                canUploadMedia: Boolean(config.media?.can_upload_media),
                canEditMedia: Boolean(config.media?.can_edit_media),
                canDeleteMedia: Boolean(config.media?.can_delete_media),
            },
            selectionKind: 'image',
            initialSelectedId: mediaPickerSelected.value?.id ?? null,
            initialSelectedItem: mediaPickerSelected.value,
            onCloseRequest: closeMediaPicker,
            onPick: (item) => {
                mediaPickerSelected.value = item;
                applyPickedMedia(item);
            },
        });
    };

    const openMediaPicker = async (payload) => {
        mediaPickerTarget.value = payload;
        showMediaPicker.value = true;
        mediaPickerSelected.value = null;

        const resolvePathValue = (source, path) => {
            const normalized = Array.isArray(path) ? path : [path];
            return normalized.reduce((acc, segment) => acc?.[segment], source);
        };
        const currentValue = resolvePathValue(selectedBlockData.value?.settings || {}, payload?.path || payload?.key);

        if (currentValue) {
            await hydrateMediaLookup([currentValue]);
            mediaPickerSelected.value = mediaLookup.value[Number(currentValue)] || null;
        }

        await nextTick();
        mountSharedMediaPicker();
    };

    return {
        mediaLookup,
        showMediaPicker,
        mediaPickerMount,
        mediaPickerSelected,
        collectReferencedMediaIds,
        hydrateMediaLookup,
        openMediaPicker,
        closeMediaPicker,
        applyPickedMedia,
    };
}
