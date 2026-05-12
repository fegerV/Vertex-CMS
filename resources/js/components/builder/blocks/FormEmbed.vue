<template>
  <div class="stack">
    <label class="field">
      <span>Form ID</span>
      <input :value="modelValue.formId" type="text" @input="update('formId', $event.target.value)" />
    </label>
    <label class="field">
      <span>Title</span>
      <input :value="modelValue.title" type="text" @input="update('title', $event.target.value)" />
    </label>
    <label class="field">
      <span>Description</span>
      <textarea :value="modelValue.description" rows="3" @input="update('description', $event.target.value)" />
    </label>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      formId: '',
      title: '',
      description: '',
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
