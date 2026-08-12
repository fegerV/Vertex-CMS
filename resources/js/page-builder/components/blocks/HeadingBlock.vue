<template>
<div class="block-content">
    <component 
        :is="level" 
        :style="headingStyle"
        class="vc-heading"
    >
        {{ settings.text || 'Заголовок' }}
    </component>
</div>
</template>

<script>
export default {
    name: 'HeadingBlock',
    props: {
        block: {
            type: Object,
            required: true
        },
        settings: {
            type: Object,
            default: () => ({})
        },
        index: {
            type: Number,
            default: 0
        }
    },
    computed: {
        level() {
            return this.settings.level || 'h2';
        },
        headingStyle() {
            const fontSizeMap = {
                h1: '2.5rem',
                h2: '2rem',
                h3: '1.75rem',
                h4: '1.5rem',
                h5: '1.25rem',
                h6: '1rem'
            };
            
            return {
                color: this.settings.color || '#111827',
                textAlign: this.settings.align || 'left',
                fontSize: fontSizeMap[this.settings.level || 'h2'],
                fontWeight: this.settings.font_weight || (this.settings.level === 'h1' ? '700' : '600'),
                lineHeight: '1.3',
                marginBottom: '0.75rem'
            };
        }
    }
};
</script>

<style scoped>
.vc-heading {
    transition: all 0.2s ease;
}
</style>
