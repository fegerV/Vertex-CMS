<template>
<div class="block-content">
    <div class="vc-gallery grid gap-4" :style="gridStyle">
        <div 
            v-for="(image, index) in images" 
            :key="index"
            class="gallery-item overflow-hidden rounded-lg cursor-pointer"
            @click="openLightbox(index)"
        >
            <img 
                :src="image.src || image"
                :alt="image.alt || ''"
                class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                :style="imageStyle"
            />
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div 
        v-if="lightboxOpen"
        class="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center p-4"
        @click="closeLightbox"
    >
        <button 
            class="absolute top-4 right-4 text-white hover:text-slate-300"
            @click="closeLightbox"
        >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <button 
            class="absolute left-4 text-white hover:text-slate-300 p-2"
            @click.stop="previousImage"
        >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        
        <img 
            :src="currentImage"
            class="max-w-full max-h-[90vh] object-contain"
            @click.stop
        />
        
        <button 
            class="absolute right-4 text-white hover:text-slate-300 p-2"
            @click.stop="nextImage"
        >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</div>
</template>

<script>
export default {
    name: 'GalleryBlock',
    props: {
        block: Object,
        settings: {
            type: Object,
            default: () => ({})
        },
        index: Number
    },
    data() {
        return {
            lightboxOpen: false,
            currentIndex: 0
        };
    },
    computed: {
        images() {
            return this.settings.images || this.settings.items || [];
        },
        gridStyle() {
            const columns = this.settings.columns || 3;
            return {
                gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`
            };
        },
        imageStyle() {
            return {
                aspectRatio: this.settings.aspect_ratio || '1',
                borderRadius: this.settings.border_radius || '8px'
            };
        },
        currentImage() {
            const img = this.images[this.currentIndex];
            return img.src || img || '';
        }
    },
    methods: {
        openLightbox(index) {
            this.currentIndex = index;
            this.lightboxOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeLightbox() {
            this.lightboxOpen = false;
            document.body.style.overflow = '';
        },
        previousImage() {
            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        },
        nextImage() {
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
        }
    },
    beforeUnmount() {
        this.closeLightbox();
    }
};
</script>

<style scoped>
.gallery-item {
    aspect-ratio: 1;
}
</style>
