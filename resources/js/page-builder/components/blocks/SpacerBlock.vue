<template>
<div class="block-content">
    <div :style="spacerStyle" class="vc-spacer">
        <span class="text-xs text-slate-400">Распорка: {{ height }}</span>
    </div>
</div>
</template>

<script>
export default {
    name: 'SpacerBlock',
    props: {
        block: Object,
        settings: {
            type: Object,
            default: () => ({})
        },
        index: Number
    },
    computed: {
        height() {
            return this.settings.height || '40px';
        },
        spacerStyle() {
            return {
                height: this.height,
                minHeight: '20px',
                background: '#f1f5f9',
                border: '1px dashed #cbd5e1',
                borderRadius: '4px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center'
            };
        }
    }
};
</script>

<style scoped>
.vc-spacer {
    transition: all 0.2s ease;
}
</style>
