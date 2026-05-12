<template>
  <div class="schema-block">
    <div v-if="definition?.description" class="schema-block-description">
      {{ definition.description }}
    </div>

    <div v-if="fieldEntries.length === 0" class="schema-block-empty">
      This block has no editable schema fields yet.
    </div>

    <div v-else class="schema-block-fields">
      <SchemaField
        v-for="([fieldKey, field]) in fieldEntries"
        :key="fieldKey"
        :field-key="fieldKey"
        :field="field"
        :model-value="resolvedModel[fieldKey]"
        :parent-model="resolvedModel"
        @update:model-value="(value) => updateField(fieldKey, value)"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SchemaField from '../fields/SchemaField.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({}),
  },
  definition: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['update:modelValue']);

const fieldEntries = computed(() => Object.entries(props.definition?.fields || {}));
const resolvedModel = computed(() => props.modelValue || {});

function updateField(fieldKey, value) {
  emit('update:modelValue', {
    ...resolvedModel.value,
    [fieldKey]: value,
  });
}
</script>

<style scoped>
.schema-block {
  display: grid;
  gap: 1rem;
}

.schema-block-description {
  font-size: 0.875rem;
  color: #64748b;
}

.schema-block-fields {
  display: grid;
  gap: 1rem;
}

.schema-block-empty {
  border: 1px dashed #cbd5e1;
  border-radius: 0.875rem;
  padding: 1rem;
  color: #64748b;
}
</style>
