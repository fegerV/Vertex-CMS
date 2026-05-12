<template>
  <div class="stack">
    <div class="grid">
      <label class="field">
        <span>Title</span>
        <input :value="modelValue.title" type="text" @input="update('title', $event.target.value)" />
      </label>

      <label class="field">
        <span>Subtitle</span>
        <textarea :value="modelValue.subtitle" rows="3" @input="update('subtitle', $event.target.value)" />
      </label>

      <label class="field">
        <span>Background URL</span>
        <input :value="modelValue.background" type="url" @input="update('background', $event.target.value)" />
      </label>

      <label class="field">
        <span>Button text</span>
        <input :value="modelValue.buttonText" type="text" @input="update('buttonText', $event.target.value)" />
      </label>

      <label class="field">
        <span>Button URL</span>
        <input :value="modelValue.buttonUrl" type="url" @input="update('buttonUrl', $event.target.value)" />
      </label>
    </div>

    <div class="preview" :style="previewStyle">
      <div class="preview-overlay">
        <h2>{{ modelValue.title || 'Hero title' }}</h2>
        <p>{{ modelValue.subtitle || 'Hero subtitle' }}</p>
        <a v-if="modelValue.buttonText" :href="modelValue.buttonUrl || '#'" class="preview-button">
          {{ modelValue.buttonText }}
        </a>
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
      title: '',
      subtitle: '',
      background: '',
      buttonText: '',
      buttonUrl: '',
    }),
  },
});

const emit = defineEmits(['update:modelValue']);

const previewStyle = computed(() => ({
  backgroundColor: '#0f172a',
  backgroundSize: 'cover',
  backgroundPosition: 'center',
  backgroundImage: props.modelValue.background ? `url(${props.modelValue.background})` : 'none',
}));

function update(key, value) {
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: value,
  });
}
</script>

<style scoped>
.stack,
.grid {
  display: grid;
  gap: 0.75rem;
}

.field {
  display: grid;
  gap: 0.375rem;
}

.field span {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #334155;
}

.field input,
.field textarea {
  border: 1px solid #cbd5e1;
  border-radius: 0.625rem;
  padding: 0.625rem 0.75rem;
}

.preview {
  position: relative;
  overflow: hidden;
  border-radius: 1rem;
  min-height: 220px;
}

.preview-overlay {
  padding: 2rem;
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.88), rgba(30, 41, 59, 0.55));
  color: #fff;
  min-height: 220px;
}

.preview h2 {
  margin: 0 0 0.75rem;
  font-size: 2rem;
  font-weight: 700;
}

.preview p {
  margin: 0 0 1rem;
  max-width: 42rem;
}

.preview-button {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 0.75rem 1rem;
  background: #2563eb;
  color: #fff;
  text-decoration: none;
}
</style>
