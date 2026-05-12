<template>
  <div class="stack">
    <label class="field">
      <span>Image URLs (one per line)</span>
      <textarea :value="joinedImages" rows="5" @input="updateImages($event.target.value)" />
    </label>
    <label class="field">
      <span>Columns</span>
      <input :value="modelValue.columns" min="1" max="6" type="number" @input="update('columns', Number($event.target.value || 1))" />
    </label>

    <div class="preview-grid" :style="{ gridTemplateColumns: `repeat(${Math.max(modelValue.columns || 1, 1)}, minmax(0, 1fr))` }">
      <div v-for="(image, index) in modelValue.images || []" :key="`${image}-${index}`" class="preview-card">
        <img :src="image" alt="" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      images: [],
      columns: 3,
    }),
  },
});

const emit = defineEmits(['update:modelValue']);

const joinedImages = computed(() => (props.modelValue.images || []).join('\n'));

function update(key, value) {
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: value,
  });
}

function updateImages(value) {
  const images = value
    .split('\n')
    .map((item) => item.trim())
    .filter(Boolean);

  update('images', images);
}
</script>

<style scoped>
.stack {
  display: grid;
  gap: 0.75rem;
}

.field {
  display: grid;
  gap: 0.375rem;
}

.field input,
.field textarea {
  border: 1px solid #cbd5e1;
  border-radius: 0.625rem;
  padding: 0.625rem 0.75rem;
}

.preview-grid {
  display: grid;
  gap: 0.75rem;
}

.preview-card {
  aspect-ratio: 4 / 3;
  overflow: hidden;
  border-radius: 0.875rem;
  background: #e2e8f0;
}

.preview-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
