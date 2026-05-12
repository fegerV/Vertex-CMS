<template>
  <div class="stack">
    <article v-for="(item, index) in items" :key="index" class="card">
      <label class="field">
        <span>Question</span>
        <input :value="item.question" type="text" @input="updateItem(index, 'question', $event.target.value)" />
      </label>
      <label class="field">
        <span>Answer</span>
        <textarea :value="item.answer" rows="3" @input="updateItem(index, 'answer', $event.target.value)" />
      </label>
      <button type="button" class="danger" @click="removeItem(index)">Remove item</button>
    </article>

    <button type="button" class="secondary" @click="addItem">Add item</button>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      items: [],
    }),
  },
});

const emit = defineEmits(['update:modelValue']);

const items = computed(() => props.modelValue.items ?? []);

function sync(nextItems) {
  emit('update:modelValue', {
    ...props.modelValue,
    items: nextItems,
  });
}

function updateItem(index, key, value) {
  const nextItems = items.value.map((item, itemIndex) => itemIndex === index ? { ...item, [key]: value } : item);
  sync(nextItems);
}

function addItem() {
  sync([...items.value, { question: '', answer: '' }]);
}

function removeItem(index) {
  sync(items.value.filter((_, itemIndex) => itemIndex !== index));
}
</script>

<style scoped>
.stack {
  display: grid;
  gap: 0.75rem;
}

.card {
  display: grid;
  gap: 0.75rem;
  border: 1px solid #dbe3ef;
  border-radius: 0.875rem;
  padding: 0.875rem;
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

.secondary,
.danger {
  justify-self: start;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
}

.danger {
  border: 1px solid #fecaca;
  color: #b91c1c;
  background: #fff5f5;
}
</style>
