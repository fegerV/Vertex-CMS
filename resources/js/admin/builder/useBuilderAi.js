import { computed, nextTick, ref, watch } from 'vue';

const clone = (value) => JSON.parse(JSON.stringify(value ?? []));

const parseJson = async (response) => {
    const payload = await response.text();

    if (!payload) {
        return {};
    }

    try {
        return JSON.parse(payload);
    } catch (error) {
        throw new Error(`Некорректный ответ сервера (${response.status})`);
    }
};

export function useBuilderAi({
    page,
    config,
    sections,
    selectedSection,
    selectedBlock,
    csrfToken,
    saveToHistory,
    markContentChanged,
}) {
    const aiConfig = computed(() => config?.ai || {});
    const aiAvailable = computed(() => Boolean(aiConfig.value.enabled));
    const aiConfigured = computed(() => Boolean(aiConfig.value.configured));
    const aiOpen = ref(false);
    const aiPrompt = ref('');
    const aiLoading = ref(false);
    const aiError = ref('');
    const aiMessages = ref([]);
    const aiProposal = ref(null);
    const aiApplyStatus = ref('');
    const aiScope = ref('page');

    const aiQuickPrompts = [
        { label: 'Лендинг', prompt: 'Собери лендинг под этот запрос: ' },
        { label: 'Улучшить тексты', prompt: 'Улучши тексты страницы, сохрани текущую структуру блоков' },
        { label: 'Hero-секция', prompt: 'Сделай hero-секцию с заголовком, подзаголовком и кнопкой' },
        { label: 'Секция FAQ', prompt: 'Добавь секцию FAQ из 5 вопросов' },
        { label: 'Упростить', prompt: 'Упрости страницу: оставь только ключевые блоки' },
    ];

    const aiHasSectionTarget = computed(() => selectedSection.value !== null);
    const aiHasBlockTarget = computed(() => selectedBlock.value !== null && aiHasSectionTarget.value);

    const aiScopeOptions = computed(() => [
        { id: 'page', label: 'Вся страница', available: true },
        { id: 'section', label: aiHasSectionTarget.value ? `Секция ${selectedSection.value + 1}` : 'Секция', available: aiHasSectionTarget.value },
        { id: 'block', label: aiHasBlockTarget.value ? 'Выбранный блок' : 'Блок', available: aiHasBlockTarget.value },
    ]);

    watch([selectedSection, selectedBlock], () => {
        if (aiHasBlockTarget.value) {
            aiScope.value = 'block';
        } else if (aiHasSectionTarget.value) {
            aiScope.value = 'section';
        } else {
            aiScope.value = 'page';
        }
    }, { immediate: true });

    watch(aiScopeOptions, (options) => {
        const current = options.find((option) => option.id === aiScope.value);

        if (!current?.available) {
            aiScope.value = 'page';
        }
    });

    const aiSelectedContext = computed(() => {
        if (selectedSection.value === null) {
            return {};
        }

        const section = sections.value[selectedSection.value] || {};

        if (selectedBlock.value !== null) {
            return {
                section_index: selectedSection.value,
                block_index: selectedBlock.value,
                block_type: section.blocks?.[selectedBlock.value]?.type || null,
                block_settings: section.blocks?.[selectedBlock.value]?.settings || {},
            };
        }

        return {
            section_index: selectedSection.value,
            block_count: section.blocks?.length || 0,
        };
    });

    const aiSummary = computed(() => {
        if (!aiProposal.value) return '';
        const count = (aiProposal.value.sections || []).reduce(
            (sum, section) => sum + (section.blocks?.length || 0),
            0,
        );

        const target = aiProposal.value.target?.label
            ? ` · ${aiProposal.value.target.label}`
            : '';

        return `${(aiProposal.value.sections || []).length} секций, ${count} блоков${target}`;
    });

    const toggleAiPanel = () => {
        aiOpen.value = !aiOpen.value;
        aiError.value = '';
    };

    const closeAiPanel = () => {
        aiOpen.value = false;
    };

    const useQuickPrompt = (item) => {
        aiPrompt.value = item.prompt;
    };

    const applyAiProposal = () => {
        if (!aiProposal.value) {
            return;
        }

        const nextSections = clone(aiProposal.value.sections);
        sections.value = nextSections;

        saveToHistory(`AI: ${aiProposal.value.message || 'изменение страницы'}`.slice(0, 120));
        markContentChanged();

        aiApplyStatus.value = `Применено: ${aiProposal.value.message || 'структура обновлена'}`;
        aiMessages.value = [
            ...aiMessages.value,
            {
                role: 'assistant',
                content: `Готово. Применено на холст: ${aiProposal.value.message || 'структура обновлена'}. Не забудьте сохранить страницу.`,
                applied: true,
            },
        ];
        aiProposal.value = null;
    };

    const discardAiProposal = () => {
        aiProposal.value = null;
        aiApplyStatus.value = '';
    };

    const askAi = async (promptOverride = null) => {
        const instruction = String(promptOverride ?? aiPrompt.value).trim();

        if (!instruction || aiLoading.value) {
            return;
        }

        if (!aiConfigured.value) {
            aiError.value = 'AI-провайдер не настроен: добавьте API-ключ в настройках AI.';
            return;
        }

        aiError.value = '';
        aiApplyStatus.value = '';
        aiMessages.value = [...aiMessages.value, { role: 'user', content: instruction }];
        aiPrompt.value = '';
        aiLoading.value = true;

        try {
            const response = await fetch(`/admin/pages/${page.id}/builder/ai`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    instruction,
                    scope: aiScope.value,
                    sections: sections.value,
                    selection: aiSelectedContext.value,
                    provider: aiConfig.value.provider || null,
                }),
            });

            const data = await parseJson(response);

            if (!response.ok || data.ok !== true) {
                const message = data.message || data.error || `HTTP ${response.status}`;
                aiError.value = message;
                aiMessages.value = [...aiMessages.value, { role: 'assistant', content: message, error: true }];
                return;
            }

            aiMessages.value = [
                ...aiMessages.value,
                {
                    role: 'assistant',
                    content: data.message || 'Готово, проверьте предложение ниже.',
                    model: data.provider?.name || null,
                },
            ];
            aiProposal.value = {
                sections: data.sections || [],
                message: data.message || 'Структура страницы обновлена',
                scope: data.scope || aiScope.value,
                target: data.target || null,
            };

            await nextTick();
        } catch (error) {
            aiError.value = error.message || 'Ошибка запроса к AI';
            aiMessages.value = [...aiMessages.value, { role: 'assistant', content: aiError.value, error: true }];
        } finally {
            aiLoading.value = false;
        }
    };

    return {
        aiConfig,
        aiAvailable,
        aiConfigured,
        aiOpen,
        aiPrompt,
        aiLoading,
        aiError,
        aiMessages,
        aiProposal,
        aiApplyStatus,
        aiScope,
        aiScopeOptions,
        aiQuickPrompts,
        aiSummary,
        toggleAiPanel,
        closeAiPanel,
        useQuickPrompt,
        askAi,
        applyAiProposal,
        discardAiProposal,
    };
}
