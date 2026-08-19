<template>
  <div v-if="isVisible" class="schema-field" :class="{ 'schema-field-nested': depth > 0 }">
    <label v-if="field.type !== 'toggle'" class="schema-label">
      {{ field.label || fieldKey }}
    </label>

    <p v-if="field.help" class="schema-help">
      {{ field.help }}
    </p>

    <textarea
      v-if="field.type === 'textarea'"
      :value="stringValue"
      class="schema-textarea"
      :rows="field.rows || 4"
      @input="emitValue($event.target.value)"
    />

    <select
      v-else-if="field.type === 'select'"
      :value="normalizedValue"
      class="schema-select"
      @change="emitValue($event.target.value)"
    >
      <option
        v-for="option in normalizedOptions"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>

    <input
      v-else-if="field.type === 'number'"
      :value="normalizedValue"
      type="number"
      class="schema-input"
      :min="field.min"
      :max="field.max"
      :step="field.step || 'any'"
      @input="handleNumberInput"
    >

    <input
      v-else-if="field.type === 'color'"
      :value="colorValue"
      type="color"
      class="schema-color"
      @input="emitValue($event.target.value)"
    >

    <label v-else-if="field.type === 'toggle'" class="schema-toggle">
      <input
        :checked="Boolean(modelValue)"
        type="checkbox"
        @change="emitValue($event.target.checked)"
      >
      <span>{{ field.label || fieldKey }}</span>
    </label>

    <input
      v-else-if="field.type === 'media'"
      :value="normalizedValue"
      type="text"
      class="schema-input"
      placeholder="Media ID or URL"
      @input="emitValue($event.target.value)"
    >

    <SchemaRepeaterField
      v-else-if="field.type === 'repeater'"
      :field-key="fieldKey"
      :field="field"
      :model-value="arrayValue"
      :depth="depth"
      @update:model-value="emitValue"
    />

    <input
      v-else
      :value="stringValue"
      type="text"
      class="schema-input"
      @input="handleTextInput"
    >
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SchemaRepeaterField from './SchemaRepeaterField.vue';

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
    type: [String, Number, Boolean, Array, Object, null],
    default: null,
  },
  parentModel: {
    type: Object,
    default: () => ({}),
  },
  depth: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['update:modelValue']);

const normalizedOptions = computed(() => {
  if (Array.isArray(props.field.options)) {
    return props.field.options;
  }

  if (props.field.options && typeof props.field.options === 'object') {
    return Object.entries(props.field.options).map(([value, label]) => ({ value, label }));
  }

  return [];
});

const normalizedValue = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined) {
    return '';
  }

  return props.modelValue;
});

const stringValue = computed(() => {
  if (Array.isArray(props.modelValue)) {
    return props.modelValue.join(', ');
  }

  if (props.modelValue === null || props.modelValue === undefined) {
    return '';
  }

  if (typeof props.modelValue === 'object') {
    return JSON.stringify(props.modelValue);
  }

  return String(props.modelValue);
});

const colorValue = computed(() => {
  return typeof props.modelValue === 'string' && props.modelValue !== ''
    ? props.modelValue
    : '#111827';
});

const arrayValue = computed(() => Array.isArray(props.modelValue) ? props.modelValue : []);

const isVisible = computed(() => {
  const dependencyMap = props.field.depends_on;

  if (!dependencyMap || typeof dependencyMap !== 'object') {
    return true;
  }

  return Object.entries(dependencyMap).every(([key, expected]) => {
    const actual = props.parentModel?.[key];

    if (typeof expected === 'boolean') {
      return Boolean(actual) === expected;
    }

    return String(actual ?? '') === String(expected);
  });
});

function emitValue(value) {
  emit('update:modelValue', value);
}

function handleNumberInput(event) {
  const raw = event.target.value;

  if (raw === '') {
    emitValue(null);
    return;
  }

  const parsed = Number(raw);
  emitValue(Number.isNaN(parsed) ? null : parsed);
}

function handleTextInput(event) {
  const raw = event.target.value;

  if (Array.isArray(props.modelValue)) {
    emitValue(
      raw
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean)
    );

    return;
  }

  emitValue(raw);
}
</script>

<style scoped>
.schema-field {
  display: grid;
  gap: 0.45rem;
}

.schema-field-nested {
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.875rem;
  background: #fff;
}

.schema-label {
  font-size: 0.8125rem;
  font-weight: 700;
  color: #0f172a;
}

.schema-help {
  margin: 0;
  font-size: 0.75rem;
  color: #64748b;
}

.schema-input,
.schema-select,
.schema-textarea {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 0.75rem;
  background: #fff;
  padding: 0.7rem 0.85rem;
  color: #0f172a;
}

.schema-textarea {
  resize: vertical;
  min-height: 7rem;
}

.schema-color {
  width: 100%;
  min-height: 2.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 0.75rem;
  background: #fff;
  padding: 0.25rem;
}

.schema-toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #0f172a;
}
</style>
