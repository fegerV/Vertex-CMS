<template>
  <div class="stack">
    <label class="field">
      <span>Column count</span>
      <input :value="modelValue.count" min="1" max="4" type="number" @input="update('count', Number($event.target.value || 1))" />
    </label>
    <label class="field">
      <span>Gap</span>
      <input :value="modelValue.gap" type="text" @input="update('gap', $event.target.value)" />
    </label>
    <label class="field">
      <span>Notes</span>
      <textarea :value="modelValue.notes" rows="3" @input="update('notes', $event.target.value)" />
    </label>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      count: 2,
      gap: '1.5rem',
      notes: '',
    }),
  },
});

const emit = defineEmits(['update:modelValue']);

function update(key, value) {
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: value,
  });
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
</style>
