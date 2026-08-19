import { createApp, h } from 'vue';
import PageBuilder from './PageBuilder.vue';

function defaultDocument() {
    return {
        version: '1.0',
        layout: 'default',
        settings: {},
        sections: [
            {
                id: 'prototype-section',
                settings: {},
                blocks: [],
            },
        ],
    };
}

function parseInitialDocument(element) {
    const raw = element.dataset.initialValue;

    if (!raw) {
        return defaultDocument();
    }

    try {
        const parsed = JSON.parse(raw);

        if (Array.isArray(parsed)) {
            return {
                ...defaultDocument(),
                sections: [
                    {
                        ...defaultDocument().sections[0],
                        blocks: parsed,
                    },
                ],
            };
        }

        if (parsed && typeof parsed === 'object') {
            const document = {
                ...defaultDocument(),
                ...parsed,
            };

            if (!Array.isArray(document.sections) || document.sections.length === 0) {
                document.sections = defaultDocument().sections;
            }

            document.sections = document.sections.map((section, index) => ({
                id: section?.id || `prototype-section-${index + 1}`,
                settings: section?.settings || {},
                blocks: Array.isArray(section?.blocks) ? section.blocks : [],
            }));

            return document;
        }
    } catch (error) {
        console.warn('VertexCMS builder prototype failed to parse initial content.', error);
    }

    return defaultDocument();
}

function extractEditableBlocks(document) {
    return Array.isArray(document?.sections?.[0]?.blocks)
        ? document.sections[0].blocks
        : [];
}

function syncDocumentBlocks(document, blocks) {
    const nextDocument = {
        ...defaultDocument(),
        ...(document || {}),
    };

    const sections = Array.isArray(nextDocument.sections) && nextDocument.sections.length > 0
        ? [...nextDocument.sections]
        : [...defaultDocument().sections];

    const firstSection = {
        ...sections[0],
        settings: sections[0]?.settings || {},
        blocks,
    };

    sections[0] = firstSection;
    nextDocument.sections = sections;

    return nextDocument;
}

function syncHiddenInput(element, document, blocks) {
    const targetId = element.dataset.inputTarget;

    if (!targetId) {
        return;
    }

    const input = document.getElementById(targetId);

    if (!input) {
        return;
    }

    input.value = JSON.stringify(syncDocumentBlocks(document, blocks), null, 2);
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

export function mountBuilderPrototype() {
    document.querySelectorAll('[data-vc-page-builder-prototype]').forEach((element) => {
        const initialDocument = parseInitialDocument(element);
        const initialBlocks = extractEditableBlocks(initialDocument);

        createApp({
            data() {
                return {
                    blocks: initialBlocks,
                    document: initialDocument,
                };
            },
            methods: {
                handleUpdate(blocks) {
                    this.blocks = blocks;
                    syncHiddenInput(element, this.document, blocks);
                },
                handleSave(blocks) {
                    this.blocks = blocks;
                    syncHiddenInput(element, this.document, blocks);
                    element.dispatchEvent(new CustomEvent('vertex-builder:save', {
                        bubbles: true,
                        detail: { blocks },
                    }));
                },
            },
            render() {
                return h(PageBuilder, {
                    modelValue: this.blocks,
                    'onUpdate:modelValue': this.handleUpdate,
                    onSave: this.handleSave,
                });
            },
        }).mount(element);
    });
}
