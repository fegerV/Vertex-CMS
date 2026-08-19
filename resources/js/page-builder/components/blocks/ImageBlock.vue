<template>
<div class="block-content">
    <div class="image-wrapper" :style="imageWrapperStyle">
        <img 
            v-if="settings.src"
            :src="settings.src"
            :alt="settings.alt || 'Изображение'"
            :style="imageStyle"
            class="vc-image"
        />
        <div v-else class="image-placeholder">
            <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-sm text-slate-400 mt-2">{{ settings.alt || 'Изображение' }}</span>
        </div>
    </div>
</div>
</template>

<script>
export default {
    name: 'ImageBlock',
    props: {
        block: Object,
        settings: {
            type: Object,
            default: () => ({})
        },
        index: Number
    },
    computed: {
        imageWrapperStyle() {
            return {
                textAlign: this.settings.align || 'center'
            };
        },
        imageStyle() {
            return {
                maxWidth: this.settings.max_width || '100%',
                height: this.settings.height || 'auto',
                borderRadius: this.settings.border_radius || '8px',
                boxShadow: this.settings.shadow ? '0 4px 6px -1px rgba(0, 0, 0, 0.1)' : 'none'
            };
        }
    }
};
</script>

<style scoped>
.image-wrapper {
    padding: 1rem;
}

.vc-image {
    transition: all 0.3s ease;
    display: inline-block;
}

.vc-image:hover {
    transform: scale(1.02);
}

.image-placeholder {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: #f1f5f9;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    min-width: 200px;
    min-height: 150px;
}
</style>
