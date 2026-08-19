<template>
  <div class="gallery-block p-4">
    <div v-if="modelValue.images && modelValue.images.length > 0" class="grid" :class="[gapClass, columnClass]">
      <div v-for="(image, index) in modelValue.images" :key="index" class="gallery-item" :class="[radiusClass]">
        <img 
          :src="getImageUrl(image.media_id)" 
          :alt="image.alt || ''" 
          class="w-full h-48 object-cover rounded"
        >
      </div>
    </div>
    <div v-else class="text-center py-8">
      <div class="gallery-placeholder flex flex-col items-center gap-3">
        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 12m-8 4l1.586-1.586a2 2 0 012.828 0L12 12m-2-2l1.586-1.586a2 2 0 012.828 0L16 8m-8 4l1.586-1.586a2 2 0 012.828 0L12 8m8 8a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-600">Галерея изображений</h3>
        <p class="text-sm text-gray-500">Добавьте изображения для отображения в сетке</p>
        <button 
          @click="addImage"
          class="btn btn-primary mt-2"
        >
          Добавить изображение
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      images: [],
      columns: 3,
      gap: 'md',
      radius: 'md',
      lightbox: true
    })
  }
})
const emit = defineEmits(['update:modelValue'])

const gapClass = ref('gap-4')
const columnClass = ref('grid-cols-3')
const radiusClass = ref('rounded')

// Update classes when props change
import { watch } from 'vue'
watch(
  () => props.modelValue,
  (newValue) => {
    gapClass.value = matchGapClass(newValue.gap ?? 'md')
    columnClass.value = matchColumnClass(newValue.columns ?? 3)
    radiusClass.value = matchRadiusClass(newValue.radius ?? 'md')
  },
  { immediate: true, deep: true }
)

function matchGapClass(gap) {
  return {
    'sm': 'gap-2',
    'md': 'gap-4',
    'lg': 'gap-6'
  }[gap] || 'gap-4'
}

function matchColumnClass(columns) {
  return {
    1: 'grid-cols-1',
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
    5: 'grid-cols-5',
    6: 'grid-cols-6'
  }[columns] || 'grid-cols-3'
}

function matchRadiusClass(radius) {
  return {
    'none': 'rounded-none',
    'sm': 'rounded-sm',
    'md': 'rounded',
    'lg': 'rounded-lg',
    'full': 'rounded-full'
  }[radius] || 'rounded'
}

function getImageUrl(mediaId) {
  // In a real implementation, this would get the actual URL from media service
  // For now, we'll return a placeholder or use a route if available
  if (!mediaId) {
    return 'https://via.placeholder.com/400x300'
  }
  // This would normally be: return route('media.download', { id: mediaId })
  return `https://via.placeholder.com/400x300?text=Image+${mediaId}`
}

function addImage() {
  // This would normally open a media picker
  // For now, we'll just add a placeholder
  const newImage = {
    media_id: Date.now(), // Temporary ID
    alt: ''
  }
  
  props.modelValue.images.push(newImage)
  emit('update:modelValue', { ...props.modelValue })
}
</script>

<style scoped>
.gallery-block {
  border: 1px dashed #e2e8f0;
  border-radius: 0.5rem;
  transition: all 0.2s;
}

.gallery-block:hover {
  border-color: #3b82f6;
  background-color: #f0f9ff;
}

.gallery-item {
  overflow: hidden;
}

.gallery-placeholder {
  color: #64748b;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
}

.btn-primary:hover {
  background-color: #2563eb;
}
</style>