<template>
  <div class="schema-repeater">
    <div class="schema-repeater-header">
      <div>
        <div class="schema-repeater-title">{{ field.label || fieldKey }}</div>
        <div v-if="field.help" class="schema-repeater-help">{{ field.help }}</div>
      </div>

      <button type="button" class="schema-button schema-button-primary" @click="addItem">
        Add item
      </button>
    </div>

    <div v-if="items.length === 0" class="schema-empty">
      No items yet.
    </div>

    <div v-else class="schema-repeater-list">
      <article
        v-for="(item, index) in items"
        :key="`${fieldKey}-${index}`"
        class="schema-repeater-item"
      >
        <header class="schema-repeater-item-header">
          <div class="schema-repeater-item-title">
            {{ field.item_label || `Item ${index + 1}` }}
          </div>

          <button
            type="button"
            class="schema-button schema-button-danger"
            @click="removeItem(index)"
          >
            Remove
          </button>
        </header>

        <div class="schema-repeater-item-fields">
          <SchemaField
            v-for="subField in normalizedSubFields"
            :key="`${fieldKey}-${index}-${subField.key}`"
            :field-key="subField.key"
            :field="subField"
            :model-value="item[subField.key]"
            :parent-model="item"
            :depth="depth + 1"
            @update:model-value="(value) => updateItemField(index, subField.key, value)"
          />
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SchemaField from './SchemaField.vue';

const props = defineProps({
  fieldKey: {
    type: String,
    required: true,
  },
  field: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: Array,
    default: () => [],
  },
  depth: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['update:modelValue']);

const items = computed(() => Array.isArray(props.modelValue) ? props.modelValue : []);
const normalizedSubFields = computed(() => (
  Array.isArray(props.field.fields)
    ? props.field.fields.map((subField, index) => ({
        ...subField,
        key: subField.key || `${props.fieldKey}_${index}`,
      }))
    : []
));

function makeDefaultValue(type, fallback = null) {
  if (fallback !== null && fallback !== undefined) {
    return structuredCloneSafe(fallback);
  }

  switch (type) {
    case 'toggle':
      return false;
    case 'number':
      return null;
    case 'repeater':
      return [];
    default:
      return '';
  }
}

function structuredCloneSafe(value) {
  if (typeof structuredClone === 'function') {
    return structuredClone(value);
  }

  return JSON.parse(JSON.stringify(value));
}

function blankItem() {
  return normalizedSubFields.value.reduce((carry, subField) => {
    carry[subField.key] = makeDefaultValue(subField.type, subField.default ?? null);
    return carry;
  }, {});
}

function emitItems(nextItems) {
  emit('update:modelValue', nextItems);
}

function addItem() {
  emitItems([...items.value, blankItem()]);
}

function removeItem(index) {
  emitItems(items.value.filter((_, itemIndex) => itemIndex !== index));
}

function updateItemField(index, key, value) {
  const nextItems = items.value.map((item, itemIndex) => (
    itemIndex === index
      ? { ...item, [key]: value }
      : item
  ));

  emitItems(nextItems);
}
</script>

<style scoped>
.schema-repeater {
  display: grid;
  gap: 0.875rem;
}

.schema-repeater-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
}

.schema-repeater-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #0f172a;
}

.schema-repeater-help {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: #64748b;
}

.schema-empty {
  border: 1px dashed #cbd5e1;
  border-radius: 0.875rem;
  padding: 0.875rem 1rem;
  color: #64748b;
  font-size: 0.875rem;
}

.schema-repeater-list {
  display: grid;
  gap: 0.875rem;
}

.schema-repeater-item {
  border: 1px solid #dbe3ef;
  border-radius: 0.875rem;
  padding: 0.875rem;
  background: #f8fafc;
}

.schema-repeater-item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.schema-repeater-item-title {
  font-size: 0.8125rem;
  font-weight: 700;
  color: #334155;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.schema-repeater-item-fields {
  display: grid;
  gap: 0.875rem;
}

.schema-button {
  border: 1px solid #cbd5e1;
  border-radius: 0.625rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.8125rem;
  font-weight: 600;
  background: #fff;
}

.schema-button-primary {
  border-color: #93c5fd;
  color: #1d4ed8;
}

.schema-button-danger {
  border-color: #fecaca;
  color: #b91c1c;
}
</style>
