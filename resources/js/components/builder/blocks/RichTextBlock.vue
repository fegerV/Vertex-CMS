<template>
  <div class="stack">
    <Editor :model-value="modelValue.content" @update:model-value="handleUpdate" />
  </div>
</template>

<script setup>
import Editor from '../Editor.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      content: null,
    }),
  },
});

const emit = defineEmits(['update:modelValue']);

function handleUpdate(value) {
  emit('update:modelValue', {
    ...props.modelValue,
    content: value,
  });
}
</script>

<style scoped>
.stack {
  display: grid;
  gap: 0.75rem;
}
</style>
