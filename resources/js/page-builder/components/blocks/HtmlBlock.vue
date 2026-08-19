<template>
<div class="block-content">
    <div 
        class="vc-html-block p-4 border border-slate-200 rounded-lg bg-slate-50"
        v-html="renderedHtml"
    ></div>
</div>
</template>

<script>
export default {
    name: 'HtmlBlock',
    props: {
        block: Object,
        settings: {
            type: Object,
            default: () => ({})
        },
        index: Number
    },
    computed: {
        renderedHtml() {
            return this.settings.html || this.settings.content || '<p>HTML контент...</p>';
        }
    }
};
</script>

<style scoped>
.vc-html-block {
    min-height: 100px;
}

.vc-html-block :deep(*) {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
</style>
