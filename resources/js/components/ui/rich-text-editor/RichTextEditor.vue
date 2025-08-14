<template>
  <div>
    <label v-if="label" :for="id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <!-- Mode Toggle -->
    <div class="mb-3 flex rounded-lg border border-gray-300 bg-gray-50 p-1 dark:border-gray-600 dark:bg-gray-700">
      <button
        type="button"
        @click="mode = 'edit'"
        :class="[
          'flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors',
          mode === 'edit'
            ? 'bg-white text-blue-600 shadow-sm dark:bg-gray-800 dark:text-blue-400'
            : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100'
        ]"
      >
        <svg class="mr-2 h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
        </svg>
        Editor
      </button>
      <button
        type="button"
        @click="mode = 'preview'"
        :class="[
          'flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors',
          mode === 'preview'
            ? 'bg-white text-blue-600 shadow-sm dark:bg-gray-800 dark:text-blue-400'
            : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100'
        ]"
      >
        <svg class="mr-2 h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        Preview
      </button>
    </div>

    <div
      v-show="mode === 'edit'"
      class="w-full rounded-md border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
    >
      <!-- Enhanced Toolbar -->
      <div class="border-b border-gray-200 p-2 dark:border-gray-600">
        <div class="flex flex-wrap gap-1">
          <!-- Text Formatting -->
          <div class="flex gap-1 border-r border-gray-200 pr-2 dark:border-gray-600">
            <button
              type="button"
              @click="editor?.chain().focus().toggleBold().run()"
              :class="toolbarButtonClass(editor?.isActive('bold'))"
              title="Bold (Ctrl+B)"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z" />
              </svg>
            </button>
            <button
              type="button"
              @click="editor?.chain().focus().toggleItalic().run()"
              :class="toolbarButtonClass(editor?.isActive('italic'))"
              title="Italic (Ctrl+I)"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4l4 16" />
              </svg>
            </button>
            <button
              type="button"
              @click="editor?.chain().focus().toggleUnderline().run()"
              :class="toolbarButtonClass(editor?.isActive('underline'))"
              title="Underline (Ctrl+U)"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h12M8 20h8m-8-16v8a4 4 0 008 0V4" />
              </svg>
            </button>
            <button
              type="button"
              @click="editor?.chain().focus().toggleStrike().run()"
              :class="toolbarButtonClass(editor?.isActive('strike'))"
              title="Strikethrough"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h12M8 4h8M8 20h8" />
              </svg>
            </button>
          </div>

          <!-- Headings -->
          <div class="flex gap-1 border-r border-gray-200 pr-2 dark:border-gray-600">
            <select
              @change="setHeading($event)"
              class="rounded border border-gray-300 bg-white px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :value="getHeadingLevel()"
            >
              <option value="paragraph">Paragraf</option>
              <option value="1">Heading 1</option>
              <option value="2">Heading 2</option>
              <option value="3">Heading 3</option>
              <option value="4">Heading 4</option>
              <option value="5">Heading 5</option>
              <option value="6">Heading 6</option>
            </select>
          </div>

          <!-- Text Alignment -->
          <div class="flex gap-1 border-r border-gray-200 pr-2 dark:border-gray-600">
            <button
              type="button"
              @click="editor?.chain().focus().setTextAlign('left').run()"
              :class="toolbarButtonClass(editor?.isActive({textAlign: 'left'}))"
              title="Align Left"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h12M4 14h16M4 18h12" />
              </svg>
            </button>
            <button
              type="button"
              @click="editor?.chain().focus().setTextAlign('center').run()"
              :class="toolbarButtonClass(editor?.isActive({textAlign: 'center'}))"
              title="Align Center"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M6 10h12M4 14h16M6 18h12" />
              </svg>
            </button>
            <button
              type="button"
              @click="editor?.chain().focus().setTextAlign('right').run()"
              :class="toolbarButtonClass(editor?.isActive({textAlign: 'right'}))"
              title="Align Right"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M8 10h12M4 14h16M8 18h12" />
              </svg>
            </button>
            <button
              type="button"
              @click="editor?.chain().focus().setTextAlign('justify').run()"
              :class="toolbarButtonClass(editor?.isActive({textAlign: 'justify'}))"
              title="Justify"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
              </svg>
            </button>
          </div>

          <!-- Lists -->
          <div class="flex gap-1 border-r border-gray-200 pr-2 dark:border-gray-600">
            <button
              type="button"
              @click="editor?.chain().focus().toggleBulletList().run()"
              :class="toolbarButtonClass(editor?.isActive('bulletList'))"
              title="Bullet List"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            <button
              type="button"
              @click="editor?.chain().focus().toggleOrderedList().run()"
              :class="toolbarButtonClass(editor?.isActive('orderedList'))"
              title="Numbered List"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
              </svg>
            </button>
          </div>

          <!-- Link -->
          <div class="flex gap-1 border-r border-gray-200 pr-2 dark:border-gray-600">
            <button
              type="button"
              @click="toggleLink"
              :class="toolbarButtonClass(editor?.isActive('link'))"
              title="Add Link"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
              </svg>
            </button>
          </div>

          <!-- Text Color -->
          <div class="flex gap-1">
            <div class="relative">
              <input
                type="color"
                @input="setTextColor($event)"
                class="h-8 w-8 rounded border border-gray-300 cursor-pointer dark:border-gray-600"
                title="Text Color"
              />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Editor -->
      <editor-content 
        :editor="editor" 
        :id="id"
        class="prose max-w-none p-3 text-gray-900 dark:text-white focus:outline-none [&_.ProseMirror]:min-h-[400px] [&_.ProseMirror]:outline-none [&_.ProseMirror]:focus:outline-none"
      />
    </div>
    
    <!-- Preview Mode -->
    <div
      v-show="mode === 'preview'"
      class="w-full rounded-md border border-gray-300 bg-gray-50 dark:border-gray-600 dark:bg-gray-800"
    >
      <div class="p-3">
        <div class="text-sm text-gray-600 dark:text-gray-400 mb-3 pb-2 border-b border-gray-200 dark:border-gray-600">
          <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          Preview Konten
        </div>
        <div 
          v-if="modelValue && modelValue.trim()"
          class="prose prose-sm max-w-none dark:prose-invert min-h-[400px] text-gray-900 dark:text-white"
          v-html="modelValue"
        ></div>
        <div 
          v-else 
          class="flex items-center justify-center min-h-[400px] text-gray-500 dark:text-gray-400"
        >
          <div class="text-center">
            <svg class="mx-auto h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-sm">Tidak ada konten untuk ditampilkan</p>
          </div>
        </div>
      </div>
    </div>
    
    <p v-if="helpText" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ helpText }}</p>
    <div v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
import { EditorContent, useEditor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Underline from '@tiptap/extension-underline'
import { TextStyle, Color } from '@tiptap/extension-text-style'
import TextAlign from '@tiptap/extension-text-align'
import Link from '@tiptap/extension-link'
import { ref, watch } from 'vue'

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

// Mode state for editor/preview toggle
const mode = ref<'edit' | 'preview'>('edit')

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit.configure({
      // Configure StarterKit to exclude duplicate extensions
      link: false, // We'll add our own Link extension
    }),
    Underline,
    TextStyle,
    Color,
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
    Link.configure({
      openOnClick: false,
    }),
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

// Helper functions
const toolbarButtonClass = (isActive: boolean | undefined) => {
  return [
    'rounded p-1 text-sm hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors',
    isActive ? 'bg-gray-200 text-blue-600 dark:bg-gray-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300'
  ]
}

const getHeadingLevel = () => {
  if (editor.value?.isActive('heading', { level: 1 })) return '1'
  if (editor.value?.isActive('heading', { level: 2 })) return '2'
  if (editor.value?.isActive('heading', { level: 3 })) return '3'
  if (editor.value?.isActive('heading', { level: 4 })) return '4'
  if (editor.value?.isActive('heading', { level: 5 })) return '5'
  if (editor.value?.isActive('heading', { level: 6 })) return '6'
  return 'paragraph'
}

const setHeading = (event: Event) => {
  const value = (event.target as HTMLSelectElement).value
  if (value === 'paragraph') {
    editor.value?.chain().focus().setParagraph().run()
  } else {
    const level = parseInt(value) as 1 | 2 | 3 | 4 | 5 | 6
    editor.value?.chain().focus().toggleHeading({ level }).run()
  }
}

const setTextColor = (event: Event) => {
  const color = (event.target as HTMLInputElement).value
  editor.value?.chain().focus().setColor(color).run()
}

const toggleLink = () => {
  const previousUrl = editor.value?.getAttributes('link').href
  const url = window.prompt('URL', previousUrl)
  
  if (url === null) {
    return
  }
  
  if (url === '') {
    editor.value?.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }
  
  editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

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
  min-height: 400px;
}

.ProseMirror:focus {
  outline: none;
}

/* Enhanced styling for editor content */
.ProseMirror h1,
.ProseMirror h2,
.ProseMirror h3,
.ProseMirror h4,
.ProseMirror h5,
.ProseMirror h6 {
  margin-top: 1.5em;
  margin-bottom: 0.5em;
  font-weight: bold;
  line-height: 1.25;
}

.ProseMirror h1 { font-size: 2em; }
.ProseMirror h2 { font-size: 1.5em; }
.ProseMirror h3 { font-size: 1.25em; }
.ProseMirror h4 { font-size: 1.1em; }
.ProseMirror h5 { font-size: 1em; }
.ProseMirror h6 { font-size: 0.9em; }

.ProseMirror a {
  color: #3b82f6;
  text-decoration: underline;
}

.ProseMirror a:hover {
  color: #1d4ed8;
}

.dark .ProseMirror a {
  color: #60a5fa;
}

.dark .ProseMirror a:hover {
  color: #93c5fd;
}

.ProseMirror ul,
.ProseMirror ol {
  margin-left: 1.5em;
  margin-bottom: 1em;
}

.ProseMirror li {
  margin-bottom: 0.25em;
}
</style>