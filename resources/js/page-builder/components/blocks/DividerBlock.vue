<template>
<div class="block-content">
    <hr :style="dividerStyle" class="vc-divider" />
</div>
</template>

<script>
export default {
    name: 'DividerBlock',
    props: {
        block: Object,
        settings: {
            type: Object,
            default: () => ({})
        },
        index: Number
    },
    computed: {
        dividerStyle() {
            return {
                borderTopWidth: this.settings.thickness || '2px',
                borderTopColor: this.settings.color || '#e2e8f0',
                borderTopStyle: this.settings.style || 'solid',
                width: this.settings.width || '100%',
                margin: `${this.settings.spacing_top || 16}px auto ${this.settings.spacing_bottom || 16}px`
            };
        }
    }
};
</script>

<style scoped>
.vc-divider {
    border: none;
}
</style>
