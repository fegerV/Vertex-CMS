<template>
  <div class="stack">
    <label class="field">
      <span>Video URL</span>
      <input :value="modelValue.url" type="url" @input="update('url', $event.target.value)" />
    </label>
    <label class="toggle">
      <input :checked="modelValue.autoplay" type="checkbox" @change="update('autoplay', $event.target.checked)" />
      <span>Autoplay</span>
    </label>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      url: '',
      autoplay: false,
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

.field input {
  border: 1px solid #cbd5e1;
  border-radius: 0.625rem;
  padding: 0.625rem 0.75rem;
}

.toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}
</style>
