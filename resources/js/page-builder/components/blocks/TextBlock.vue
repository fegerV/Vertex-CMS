<template>
<div class="block-content">
    <div :style="textStyle" class="vc-text">
        <p v-for="(paragraph, index) in paragraphs" :key="index" v-html="paragraph"></p>
    </div>
</div>
</template>

<script>
export default {
    name: 'TextBlock',
    props: {
        block: Object,
        settings: {
            type: Object,
            default: () => ({})
        },
        index: Number
    },
    computed: {
        paragraphs() {
            const content = this.settings.content || this.settings.text || 'Текстовый блок...';
            return content.split('\n').filter(p => p.trim());
        },
        textStyle() {
            return {
                color: this.settings.color || '#374151',
                textAlign: this.settings.align || 'left',
                lineHeight: this.settings.line_height || '1.7',
                fontSize: this.settings.font_size || '1rem'
            };
        }
    }
};
</script>

<style scoped>
.vc-text {
    line-height: 1.7;
}

.vc-text p {
    margin-bottom: 1em;
}

.vc-text p:last-child {
    margin-bottom: 0;
}
</style>
