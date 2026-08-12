<template>
<div class="block-content">
    <a 
        :href="settings.url || '#'"
        :target="settings.target || '_self'"
        :style="buttonStyle"
        class="vc-button inline-block transition-all duration-200"
        @click.prevent="handleClick"
    >
        <span v-if="settings.icon" class="mr-2">{{ settings.icon }}</span>
        {{ settings.text || 'Кнопка' }}
    </a>
</div>
</template>

<script>
export default {
    name: 'ButtonBlock',
    props: {
        block: Object,
        settings: {
            type: Object,
            default: () => ({})
        },
        index: Number
    },
    computed: {
        buttonStyle() {
            const styleMap = {
                primary: {
                    backgroundColor: this.settings.background_color || '#3b82f6',
                    color: this.settings.text_color || '#ffffff',
                    border: 'none'
                },
                secondary: {
                    backgroundColor: this.settings.background_color || '#6b7280',
                    color: this.settings.text_color || '#ffffff',
                    border: 'none'
                },
                outline: {
                    backgroundColor: 'transparent',
                    color: this.settings.text_color || '#3b82f6',
                    border: `2px solid ${this.settings.text_color || '#3b82f6'}`
                },
                ghost: {
                    backgroundColor: 'transparent',
                    color: this.settings.text_color || '#3b82f6',
                    border: 'none'
                }
            };

            const sizeMap = {
                sm: { padding: '6px 12px', fontSize: '0.875rem' },
                md: { padding: '10px 20px', fontSize: '1rem' },
                lg: { padding: '14px 28px', fontSize: '1.125rem' }
            };

            const baseStyle = styleMap[this.settings.style || 'primary'];
            const sizeStyle = sizeMap[this.settings.size || 'md'];

            return {
                ...baseStyle,
                ...sizeStyle,
                borderRadius: this.settings.border_radius || '6px',
                fontWeight: this.settings.font_weight || '600',
                textDecoration: 'none',
                display: 'inline-flex',
                alignItems: 'center',
                justifyContent: 'center',
                cursor: 'pointer'
            };
        }
    },
    methods: {
        handleClick(e) {
            if (this.settings.url && this.settings.url !== '#') {
                window.open(this.settings.url, this.settings.target || '_self');
            }
        }
    }
};
</script>

<style scoped>
.vc-button:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.vc-button:active {
    transform: translateY(0);
}
</style>
