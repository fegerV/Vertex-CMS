<template>
    <div class="vc-builder-renderer">
        <div v-html="renderedHtml"></div>
    </div>
</template>

<script>
export default {
    name: 'BuilderBlockRenderer',
    props: {
        type: {
            type: String,
            required: true,
        },
        settings: {
            type: Object,
            default: () => ({}),
        },
        registry: {
            type: Object,
            default: () => ({}),
        },
        editable: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        renderedHtml() {
            return this.renderBlock(this.type, this.settings);
        },
    },
    methods: {
        fallbackMarkup(type, title = null, description = null) {
            const block = this.registry?.[type] || window.availableBlocks?.[type];
            const emptyState = block?.editor?.preview?.empty_state || {};

            return `<div class="vc-builder-renderer-fallback"><strong>${title || emptyState.title || 'Block placeholder'}</strong><span>${description || emptyState.description || 'Configure this block in the inspector to see a richer preview.'}</span></div>`;
        },
        renderBlock(type, settings) {
            const block = this.registry?.[type] || window.availableBlocks?.[type];
            if (!block) {
                return '<div class="vc-builder-renderer-fallback"><strong>Unknown block</strong><span>This block type is not registered in the current builder config.</span></div>';
            }

            return block.render ? block.render(settings) : this.defaultRender(type, settings);
        },
        defaultRender(type, settings) {
            switch (type) {
                case 'heading':
                    return `<h2 style="color: ${settings.color || '#111'}; text-align: ${settings.align || 'left'}">${settings.text || 'Heading'}</h2>`;
                case 'text':
                    return `<div style="color: ${settings.color || '#333'}; text-align: ${settings.align || 'left'}">${settings.content || settings.text || ''}</div>`;
                case 'button':
                    return `<a href="${settings.url || '#'}" class="btn" style="background: ${settings.style === 'primary' ? '#3b82f6' : '#6b7280'}; color: white; padding: 0.5rem 1rem; border-radius: 0.25rem; text-decoration: none; display: inline-block;">${settings.text || 'Button'}</a>`;
                case 'divider':
                    return '<hr class="my-4">';
                case 'faq':
                    return `<div class="faq">${(settings.items || []).map((item) => `<details><summary>${item.question || 'Question'}</summary><div>${item.answer || 'Answer'}</div></details>`).join('')}</div>`;
                case 'image':
                    return settings.url
                        ? `<img src="${settings.url}" alt="${settings.alt || ''}" style="max-width: 100%; height: auto; border-radius: 16px;">`
                        : this.fallbackMarkup(type, 'Image placeholder', 'Bind a media file or image URL in block settings.');
                case 'video':
                    return settings.url
                        ? `<div class="vc-builder-html-preview">Video: ${settings.url}</div>`
                        : this.fallbackMarkup(type, 'Video placeholder', 'Paste a video URL to render the embedded player preview.');
                case 'html':
                    return settings.html || '<div class="vc-builder-html-preview">HTML block</div>';
                default:
                    return this.fallbackMarkup(type, type, 'No default renderer is defined for this block yet.');
            }
        },
    },
};
</script>
