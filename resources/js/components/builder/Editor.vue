<script setup>
import { onBeforeUnmount, watch } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Link from '@tiptap/extension-link'

const props = defineProps({
  modelValue: {
    type: [Object, String, null],
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

function normalizeContent(value) {
  if (value && typeof value === 'object' && value.type === 'doc') {
    return value
  }

  if (typeof value === 'string' && value.trim() !== '') {
    return {
      type: 'doc',
      content: [
        {
          type: 'paragraph',
          content: [
            {
              type: 'text',
              text: value
            }
          ]
        }
      ]
    }
  }

  return {
    type: 'doc',
    content: [
      {
        type: 'paragraph',
        content: []
      }
    ]
  }
}

const editor = useEditor({
  content: normalizeContent(props.modelValue),
  extensions: [
    StarterKit,
    Image.configure({ inline: true }),
    Link.configure({ openOnClick: false })
  ],
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getJSON())
  }
})

watch(
  () => props.modelValue,
  (value) => {
    if (!editor.value) {
      return
    }

    const nextContent = normalizeContent(value)

    if (JSON.stringify(editor.value.getJSON()) !== JSON.stringify(nextContent)) {
      editor.value.commands.setContent(nextContent, false)
    }
  },
  { deep: true }
)

onBeforeUnmount(() => {
  editor.value?.destroy()
})
</script>

<template>
  <div class="tiptap-wrapper">
    <EditorContent :editor="editor" />
  </div>
</template>

<style scoped>
.tiptap-wrapper {
  @apply border rounded p-4 min-h-[300px];
}
.tiptap p {
  @apply mb-2;
}
.tiptap img {
  @apply max-w-full rounded;
}
</style>
