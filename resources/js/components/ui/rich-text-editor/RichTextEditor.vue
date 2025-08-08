<template>
  <div>
    <label v-if="label" :for="id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <div
      class="w-full rounded-md border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
    >
      <!-- Toolbar -->
      <div class="border-b border-gray-200 p-2 dark:border-gray-600">
        <div class="flex gap-1">
          <button
            type="button"
            @click="editor?.chain().focus().toggleBold().run()"
            :class="[
              'rounded p-1 text-sm hover:bg-gray-100 dark:hover:bg-gray-600',
              editor?.isActive('bold') ? 'bg-gray-200 dark:bg-gray-600' : ''
            ]"
            title="Bold"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9m-9-6h9m-9 12h9" />
            </svg>
          </button>
          <button
            type="button"
            @click="editor?.chain().focus().toggleItalic().run()"
            :class="[
              'rounded p-1 text-sm hover:bg-gray-100 dark:hover:bg-gray-600',
              editor?.isActive('italic') ? 'bg-gray-200 dark:bg-gray-600' : ''
            ]"
            title="Italic"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5l-4 14m0 0h4m-4 0l4-14m0 0H7" />
            </svg>
          </button>
          <button
            type="button"
            @click="editor?.chain().focus().toggleBulletList().run()"
            :class="[
              'rounded p-1 text-sm hover:bg-gray-100 dark:hover:bg-gray-600',
              editor?.isActive('bulletList') ? 'bg-gray-200 dark:bg-gray-600' : ''
            ]"
            title="Bullet List"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
          </button>
          <button
            type="button"
            @click="editor?.chain().focus().toggleOrderedList().run()"
            :class="[
              'rounded p-1 text-sm hover:bg-gray-100 dark:hover:bg-gray-600',
              editor?.isActive('orderedList') ? 'bg-gray-200 dark:bg-gray-600' : ''
            ]"
            title="Numbered List"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Editor -->
      <editor-content 
        :editor="editor" 
        :id="id"
        class="prose max-w-none p-3 text-gray-900 dark:text-white focus:outline-none [&_.ProseMirror]:min-h-[600px] [&_.ProseMirror]:outline-none [&_.ProseMirror]:focus:outline-none"
      />
    </div>
    <p v-if="helpText" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ helpText }}</p>
    <div v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
import { EditorContent, useEditor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import { watch } from 'vue'

interface Props {
  modelValue?: string
  placeholder?: string
  label?: string
  required?: boolean
  error?: string
  helpText?: string
  id?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Mulai menulis...',
  id: 'rich-text-editor'
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Placeholder.configure({
      placeholder: props.placeholder,
    }),
  ],
  editorProps: {
    attributes: {
      class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-2xl mx-auto focus:outline-none',
    },
  },
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML())
  },
})

// Watch for external changes to modelValue
watch(
  () => props.modelValue,
  (newValue) => {
    if (editor.value && editor.value.getHTML() !== newValue) {
      editor.value.commands.setContent(newValue)
    }
  }
)
</script>

<style>
.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #6b7280;
  pointer-events: none;
  height: 0;
}

.ProseMirror {
  min-height: 600px;
}

.ProseMirror:focus {
  outline: none;
}
</style>