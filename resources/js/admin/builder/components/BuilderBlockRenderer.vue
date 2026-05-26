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
        escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },
        inlineStyle(values) {
            return Object.entries(values)
                .filter(([, value]) => value !== undefined && value !== null && value !== '')
                .map(([key, value]) => `${key}: ${this.escapeHtml(value)}`)
                .join('; ');
        },
        size(value) {
            if (value === undefined || value === null || value === '') {
                return null;
            }

            return Number.isFinite(Number(value)) ? `${value}px` : String(value);
        },
        gapValue(value) {
            return {
                none: '0',
                sm: '0.5rem',
                md: '1rem',
                lg: '2rem',
            }[value] || this.size(value) || '1rem';
        },
        ratioValue(value) {
            return {
                auto: 'auto',
                '1:1': '1 / 1',
                '4:3': '4 / 3',
                '3:2': '3 / 2',
                '16:9': '16 / 9',
                '21:9': '21 / 9',
            }[value] || '4 / 3';
        },
        maxWidthValue(value) {
            return {
                sm: '640px',
                md: '768px',
                lg: '1024px',
                xl: '1280px',
                '2xl': '1536px',
                '3xl': '1792px',
                '4xl': '2048px',
                '5xl': '2560px',
                '6xl': '2880px',
                '7xl': '3200px',
            }[value] || value || '1200px';
        },
        nl2br(value) {
            return this.escapeHtml(value).replace(/\r?\n/g, '<br>');
        },
        normalizeBlock(block) {
            if (!block || typeof block !== 'object') {
                return null;
            }

            return {
                type: block.type || 'unknown',
                settings: block.settings && typeof block.settings === 'object' ? block.settings : block,
            };
        },
        renderChildren(blocks) {
            if (!Array.isArray(blocks) || blocks.length === 0) {
                return '<div class="vc-builder-live-empty">Add elements</div>';
            }

            return blocks
                .map((block) => this.normalizeBlock(block))
                .filter(Boolean)
                .map((block) => this.renderBlock(block.type, block.settings))
                .join('');
        },
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
                case 'heading': {
                    const level = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(settings.level) ? settings.level : 'h2';
                    const style = this.inlineStyle({
                        color: settings.color,
                        'text-align': settings.align,
                        'font-size': this.size(settings.font_size),
                        'font-weight': settings.font_weight,
                    });

                    return `<${level} class="vc-heading" style="${style}">${this.escapeHtml(settings.text || '')}</${level}>`;
                }
                case 'text':
                    return `<div class="vc-text" style="${this.inlineStyle({
                        color: settings.color,
                        'text-align': settings.align,
                        'font-size': this.size(settings.font_size),
                    })}">${this.nl2br(settings.text || settings.content || '')}</div>`;
                case 'list': {
                    const tag = settings.type === 'decimal' ? 'ol' : 'ul';
                    const markerClass = settings.type === 'none' ? 'vc-list-none' : (settings.type === 'decimal' ? 'vc-list-decimal' : 'vc-list-disc');
                    const items = Array.isArray(settings.items) ? settings.items : [];

                    return `<${tag} class="vc-list ${markerClass}">${items.map((item) => `<li class="vc-list-item">${this.escapeHtml(typeof item === 'object' ? item.content || '' : item)}</li>`).join('')}</${tag}>`;
                }
                case 'button':
                    return `<p class="vc-button-wrap"><a class="vc-button vc-button-${this.escapeHtml(settings.style || 'primary')}" href="${this.escapeHtml(settings.url || '#')}" target="${this.escapeHtml(settings.target || '_self')}">${this.escapeHtml(settings.text || 'Подробнее')}</a></p>`;
                case 'divider':
                    return '<hr class="vc-divider">';
                case 'faq':
                    return `<div class="vc-faq">${(settings.items || []).map((item) => `<details class="vc-faq-item"><summary>${this.escapeHtml(item.question || '')}</summary><div>${this.nl2br(item.answer || '')}</div></details>`).join('')}</div>`;
                case 'image':
                    return settings.url
                        ? `<img class="vc-image" src="${this.escapeHtml(settings.url)}" alt="${this.escapeHtml(settings.alt || '')}" style="max-width: 100%; height: auto;">`
                        : this.fallbackMarkup(type, 'Image placeholder', 'Bind a media file or image URL in block settings.');
                case 'video':
                    return settings.url
                        ? `<div class="vc-video"><iframe src="${this.escapeHtml(settings.url)}" loading="lazy" allowfullscreen></iframe></div>`
                        : this.fallbackMarkup(type, 'Video placeholder', 'Paste a video URL to render the embedded player preview.');
                case 'gallery': {
                    const layout = ['grid', 'masonry', 'slider', 'carousel'].includes(settings.layout) ? settings.layout : 'grid';
                    const columns = Math.max(1, Math.min(Number(settings.columns || 3), 6));
                    const tabletColumns = Math.max(1, Math.min(Number(settings.tablet_columns || Math.min(columns, 2)), 4));
                    const mobileColumns = Math.max(1, Math.min(Number(settings.mobile_columns || 1), 2));
                    const captionMode = ['none', 'overlay', 'below'].includes(settings.caption_mode) ? settings.caption_mode : 'overlay';
                    const radius = ['none', 'sm', 'md', 'lg'].includes(settings.radius) ? settings.radius : 'md';
                    const objectFit = ['cover', 'contain'].includes(settings.object_fit) ? settings.object_fit : 'cover';
                    const images = Array.isArray(settings.images)
                        ? settings.images.filter((image) => image && typeof image === 'object' && (image.url || image.media_id))
                        : [];
                    const style = this.inlineStyle({
                        '--vc-gallery-columns': columns,
                        '--vc-gallery-tablet-columns': tabletColumns,
                        '--vc-gallery-mobile-columns': mobileColumns,
                        '--vc-gallery-gap': this.gapValue(settings.gap),
                        '--vc-gallery-ratio': this.ratioValue(settings.aspect_ratio || '4:3'),
                    });

                    if (images.length === 0) {
                        return this.fallbackMarkup(type, 'Gallery placeholder', 'Add images in the inspector to see the gallery layout.');
                    }

                    const items = images.map((image) => {
                        const url = image.url || (image.media_id ? `/api/media/${this.escapeHtml(image.media_id)}` : '');
                        const caption = this.escapeHtml(image.caption || '');
                        const captionMarkup = caption && captionMode !== 'none' ? `<figcaption class="vc-gallery-caption">${caption}</figcaption>` : '';
                        return url
                            ? `<figure class="vc-gallery-item vc-gallery-fit-${objectFit}"><span class="vc-gallery-link"><img src="${this.escapeHtml(url)}" alt="${this.escapeHtml(image.alt || image.caption || '')}" loading="lazy">${captionMarkup}</span></figure>`
                            : '';
                    }).join('');

                    const sliderControls = ['slider', 'carousel'].includes(layout) && images.length > 1
                        ? '<button class="vc-gallery-nav vc-gallery-nav-prev" type="button">‹</button><button class="vc-gallery-nav vc-gallery-nav-next" type="button">›</button>'
                        : '';

                    return `<div class="vc-gallery vc-gallery-layout-${layout} vc-gallery-caption-${captionMode} vc-gallery-radius-${radius}" style="${style}"><div class="vc-gallery-track">${items}</div>${sliderControls}</div>`;
                }
                case 'hero': {
                    const style = this.inlineStyle({
                        'background-image': settings.background ? `url('${String(settings.background).replace(/'/g, '%27')}')` : null,
                        'background-size': settings.background ? 'cover' : null,
                        'background-position': settings.background ? 'center' : null,
                        'padding-top': this.size(settings.padding_top || 80),
                        'padding-bottom': this.size(settings.padding_bottom || 80),
                        'text-align': 'center',
                        color: '#fff',
                    });
                    const title = settings.title ? `<h1 class="vc-hero-title" style="color: ${this.escapeHtml(settings.title_color || '#ffffff')}">${this.escapeHtml(settings.title)}</h1>` : '';
                    const subtitle = settings.subtitle ? `<p class="vc-hero-subtitle" style="color: ${this.escapeHtml(settings.subtitle_color || '#ffffff')}">${this.escapeHtml(settings.subtitle)}</p>` : '';
                    const button = settings.button_text ? `<a class="vc-hero-button" href="${this.escapeHtml(settings.button_url || '#')}" target="${this.escapeHtml(settings.button_target || '_self')}" style="${this.inlineStyle({
                        'background-color': settings.button_bg_color || '#3b82f6',
                        color: settings.button_text_color || '#ffffff',
                        'border-color': settings.button_border_color || 'transparent',
                    })}">${this.escapeHtml(settings.button_text)}</a>` : '';

                    return `<section class="vc-hero" style="${style}"><div class="vc-hero-content">${title}${subtitle}${button}</div></section>`;
                }
                case 'columns': {
                    const count = Math.max(1, Math.min(Number(settings.count || settings.columns?.length || 2), 4));
                    const columns = Array.isArray(settings.columns) && settings.columns.length
                        ? settings.columns
                        : Array.from({ length: count }, () => ({ blocks: [] }));
                    const style = this.inlineStyle({
                        display: 'grid',
                        gap: this.gapValue(settings.gap),
                        'grid-template-columns': `repeat(${count}, minmax(0, 1fr))`,
                    });

                    return `<div class="vc-columns" style="${style}">${columns.map((column) => `<div class="vc-column">${this.renderChildren(column.blocks)}</div>`).join('')}</div>`;
                }
                case 'container': {
                    const padding = settings.padding && typeof settings.padding === 'object' ? settings.padding : {};
                    const style = this.inlineStyle({
                        'max-width': this.maxWidthValue(settings.max_width),
                        'padding-top': this.size(settings.padding_top ?? padding.top ?? 16),
                        'padding-bottom': this.size(settings.padding_bottom ?? padding.bottom ?? 16),
                        'padding-left': this.size(settings.padding_left ?? padding.left ?? 4),
                        'padding-right': this.size(settings.padding_right ?? padding.right ?? 4),
                    });

                    return `<div class="vc-container-block" style="${style}">${this.renderChildren(settings.blocks)}</div>`;
                }
                case 'spacer':
                    return `<div class="vc-spacer" style="height: ${this.escapeHtml(this.size(settings.height || 32))};"></div>`;
                case 'html':
                    return settings.html || '<div class="vc-builder-html-preview">HTML block</div>';
                default:
                    return this.fallbackMarkup(type, type, 'No default renderer is defined for this block yet.');
            }
        },
    },
};
</script>
