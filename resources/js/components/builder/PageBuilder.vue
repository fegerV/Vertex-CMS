<template>
  <div class="page-builder">
    <aside class="builder-sidebar">
      <h3 class="builder-heading">Blocks</h3>
      <p class="builder-copy">
        Backend-synced block library for the next VertexCMS page builder iteration.
      </p>

      <div v-if="registryError" class="builder-error">
        {{ registryError }}
      </div>

      <div v-else-if="!registryReady" class="builder-loading">
        Loading block registry...
      </div>

      <div v-else class="block-library">
        <button
          v-for="definition in palette"
          :key="definition.type"
          type="button"
          class="block-library-item"
          @click="addBlock(definition.type)"
        >
          <span class="block-library-name">{{ definition.label }}</span>
          <span class="block-library-meta">{{ definition.category }}</span>
        </button>
      </div>
    </aside>

    <main class="builder-canvas">
      <div v-if="!registryReady" class="empty-state">
        Builder is preparing the block schema.
      </div>

      <div v-else-if="blocks.length === 0" class="empty-state">
        Add a block from the left to start building the page.
      </div>

      <div v-else class="blocks-list">
        <article
          v-for="(block, index) in blocks"
          :key="block.id"
          class="builder-block-card"
          :class="{ 'builder-block-card-active': selectedBlockId === block.id }"
        >
          <header class="builder-block-header">
            <div>
              <div class="builder-block-title">{{ blockLabel(block.type) }}</div>
              <div class="builder-block-meta">{{ block.type }}</div>
            </div>

            <div class="builder-block-actions">
              <button type="button" class="builder-action" @click="moveBlock(index, -1)" :disabled="index === 0">
                Up
              </button>
              <button type="button" class="builder-action" @click="moveBlock(index, 1)" :disabled="index === blocks.length - 1">
                Down
              </button>
              <button type="button" class="builder-action builder-action-danger" @click="removeBlock(index)">
                Delete
              </button>
            </div>
          </header>

          <div class="builder-block-body" @click="selectedBlockId = block.id">
            <component
              :is="blockComponent(block.type)"
              v-if="blockComponent(block.type)"
              v-model="block.settings"
              :definition="blockDefinition(block.type)"
              :block="block"
            />

            <div v-else class="unknown-block">
              Unknown block type: {{ block.type }}
            </div>
          </div>
        </article>
      </div>

      <footer class="builder-actions">
        <button type="button" class="btn-primary" @click="addFirstAvailableBlock" :disabled="!registryReady || palette.length === 0">
          Add block
        </button>
        <button type="button" class="btn-success" @click="saveContent">
          Save prototype payload
        </button>
      </footer>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import {
  blockDefinition,
  createRegistryBlock,
  loadBuilderRegistry,
  normalizePrototypeBlocks,
  registryEntries,
} from './registry';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['update:modelValue', 'save']);

const blocks = ref(normalizePrototypeBlocks(props.modelValue));
const selectedBlockId = ref(blocks.value[0]?.id ?? null);
const registryReady = ref(false);
const registryError = ref('');

const palette = computed(() => registryEntries());

watch(
  () => props.modelValue,
  (value) => {
    const normalized = normalizePrototypeBlocks(value);

    if (JSON.stringify(normalized) !== JSON.stringify(blocks.value)) {
      blocks.value = normalized;
      selectedBlockId.value = normalized[0]?.id ?? null;
    }
  },
  { deep: true }
);

watch(
  blocks,
  (value) => {
    emit('update:modelValue', normalizePrototypeBlocks(value));
  },
  { deep: true }
);

function blockComponent(type) {
  return blockDefinition(type)?.component ?? null;
}

function blockLabel(type) {
  return blockDefinition(type)?.label ?? type;
}

function addBlock(type) {
  const block = createRegistryBlock(type);
  blocks.value.push(block);
  selectedBlockId.value = block.id;
}

function addFirstAvailableBlock() {
  const firstDefinition = palette.value[0];

  if (!firstDefinition) {
    return;
  }

  addBlock(firstDefinition.type);
}

function removeBlock(index) {
  blocks.value.splice(index, 1);
  selectedBlockId.value = blocks.value[index]?.id ?? blocks.value[index - 1]?.id ?? null;
}

function moveBlock(index, offset) {
  const nextIndex = index + offset;

  if (nextIndex < 0 || nextIndex >= blocks.value.length) {
    return;
  }

  const [block] = blocks.value.splice(index, 1);
  blocks.value.splice(nextIndex, 0, block);
}

function saveContent() {
  const payload = normalizePrototypeBlocks(blocks.value);
  emit('update:modelValue', payload);
  emit('save', payload);
}

onMounted(async () => {
  try {
    await loadBuilderRegistry();
    registryReady.value = true;
    blocks.value = normalizePrototypeBlocks(blocks.value);
  } catch (error) {
    registryError.value = error instanceof Error
      ? error.message
      : 'Failed to load the builder registry.';
  }
});
</script>

<style scoped>
.page-builder {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 1.5rem;
  min-height: 640px;
}

.builder-sidebar {
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  padding: 1rem;
  background: #fff;
}

.builder-heading {
  margin: 0 0 0.5rem;
  font-size: 1.125rem;
  font-weight: 700;
}

.builder-copy {
  margin: 0 0 1rem;
  color: #64748b;
  font-size: 0.875rem;
}

.block-library {
  display: grid;
  gap: 0.75rem;
}

.builder-loading,
.builder-error {
  border: 1px dashed #cbd5e1;
  border-radius: 0.875rem;
  padding: 0.875rem 1rem;
  font-size: 0.875rem;
}

.builder-loading {
  color: #64748b;
}

.builder-error {
  color: #b91c1c;
  border-color: #fecaca;
  background: #fef2f2;
}

.block-library-item {
  text-align: left;
  border: 1px solid #dbe3ef;
  border-radius: 0.875rem;
  padding: 0.875rem;
  background: #f8fafc;
}

.block-library-item:hover {
  border-color: #93c5fd;
  background: #eff6ff;
}

.block-library-name {
  display: block;
  font-weight: 600;
  color: #0f172a;
}

.block-library-meta {
  display: block;
  margin-top: 0.25rem;
  color: #64748b;
  font-size: 0.8125rem;
  text-transform: capitalize;
}

.builder-canvas {
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  padding: 1rem;
  background: #fff;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.empty-state {
  border: 1px dashed #cbd5e1;
  border-radius: 1rem;
  padding: 3rem 1rem;
  text-align: center;
  color: #64748b;
}

.blocks-list {
  display: grid;
  gap: 1rem;
}

.builder-block-card {
  border: 1px solid #dbe3ef;
  border-radius: 1rem;
  overflow: hidden;
}

.builder-block-card-active {
  border-color: #60a5fa;
  box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.15);
}

.builder-block-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.875rem 1rem;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.builder-block-title {
  font-weight: 700;
  color: #0f172a;
}

.builder-block-meta {
  margin-top: 0.125rem;
  color: #64748b;
  font-size: 0.8125rem;
}

.builder-block-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.builder-action {
  border: 1px solid #cbd5e1;
  border-radius: 0.5rem;
  padding: 0.375rem 0.625rem;
  background: #fff;
  font-size: 0.8125rem;
}

.builder-action-danger {
  border-color: #fecaca;
  color: #b91c1c;
}

.builder-block-body {
  padding: 1rem;
}

.builder-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.btn-primary,
.btn-success {
  border: none;
  border-radius: 0.625rem;
  padding: 0.625rem 1rem;
  color: #fff;
  font-weight: 600;
}

.btn-primary {
  background: #2563eb;
}

.btn-success {
  background: #059669;
}

.unknown-block {
  border: 1px dashed #cbd5e1;
  border-radius: 0.75rem;
  padding: 1rem;
  color: #64748b;
}

@media (max-width: 960px) {
  .page-builder {
    grid-template-columns: 1fr;
  }
}
</style>
