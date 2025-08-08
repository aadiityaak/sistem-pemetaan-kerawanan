<template>
  <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Galeri</h3>
    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Upload gambar-gambar yang berkaitan dengan kejadian ini</p>
    
    <!-- Upload Area -->
    <div
      @click="triggerFileInput"
      @dragover.prevent
      @drop.prevent="handleDrop"
      class="relative cursor-pointer rounded-lg border-2 border-dashed border-gray-300 p-6 transition-colors hover:border-gray-400 dark:border-gray-600 dark:hover:border-gray-500"
      :class="{
        'border-blue-400 bg-blue-50 dark:bg-blue-900/20': isDragOver
      }"
      @dragenter="isDragOver = true"
      @dragleave="isDragOver = false"
    >
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
          <path
            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        <div class="mt-4">
          <label class="cursor-pointer">
            <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">
              Klik untuk upload atau drag & drop
            </span>
            <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">
              PNG, JPG, GIF hingga 10MB
            </span>
            <input
              ref="fileInput"
              type="file"
              multiple
              accept="image/*"
              class="sr-only"
              @change="handleFileSelect"
            />
          </label>
        </div>
      </div>
    </div>
    
    <!-- Image Preview Grid -->
    <div v-if="images.length > 0" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      <div
        v-for="(image, index) in images"
        :key="index"
        class="group relative aspect-square overflow-hidden rounded-lg border border-gray-200 dark:border-gray-600"
      >
        <img
          :src="image.preview"
          :alt="`Image ${index + 1}`"
          class="h-full w-full object-cover transition-transform group-hover:scale-105"
        />
        <button
          type="button"
          @click="removeImage(index)"
          class="absolute right-2 top-2 rounded-full bg-red-500 p-1 text-white opacity-0 transition-opacity hover:bg-red-600 group-hover:opacity-100"
          title="Hapus gambar"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Error Messages -->
    <div v-if="errors.length > 0" class="mt-4">
      <div v-for="(error, index) in errors" :key="index" class="text-sm text-red-500">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

interface ImageFile {
  file: File
  preview: string
}

interface Props {
  modelValue?: File[]
  maxFiles?: number
  maxFileSize?: number // in MB
  acceptedTypes?: string[]
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => [],
  maxFiles: 10,
  maxFileSize: 10,
  acceptedTypes: () => ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
})

const emit = defineEmits<{
  'update:modelValue': [files: File[]]
}>()

const fileInput = ref<HTMLInputElement>()
const images = ref<ImageFile[]>([])
const errors = ref<string[]>([])
const isDragOver = ref(false)

// Initialize images from modelValue
watch(
  () => props.modelValue,
  (newFiles) => {
    if (newFiles.length !== images.value.length) {
      loadImages(newFiles)
    }
  },
  { immediate: true }
)

const loadImages = (files: File[]) => {
  images.value = []
  files.forEach((file) => {
    if (file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => {
        images.value.push({
          file,
          preview: e.target?.result as string
        })
      }
      reader.readAsDataURL(file)
    }
  })
}

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files) {
    handleFiles(Array.from(target.files))
  }
}

const handleDrop = (event: DragEvent) => {
  isDragOver.value = false
  if (event.dataTransfer?.files) {
    handleFiles(Array.from(event.dataTransfer.files))
  }
}

const handleFiles = (files: File[]) => {
  errors.value = []
  const validFiles: File[] = []
  
  files.forEach((file) => {
    // Check file type
    if (!props.acceptedTypes.includes(file.type)) {
      errors.value.push(`${file.name}: Tipe file tidak didukung`)
      return
    }
    
    // Check file size
    if (file.size > props.maxFileSize * 1024 * 1024) {
      errors.value.push(`${file.name}: Ukuran file terlalu besar (max ${props.maxFileSize}MB)`)
      return
    }
    
    validFiles.push(file)
  })
  
  // Check total files limit
  const totalFiles = images.value.length + validFiles.length
  if (totalFiles > props.maxFiles) {
    errors.value.push(`Maksimal ${props.maxFiles} file`)
    return
  }
  
  // Add valid files
  validFiles.forEach((file) => {
    const reader = new FileReader()
    reader.onload = (e) => {
      images.value.push({
        file,
        preview: e.target?.result as string
      })
      updateModelValue()
    }
    reader.readAsDataURL(file)
  })
}

const removeImage = (index: number) => {
  images.value.splice(index, 1)
  updateModelValue()
}

const updateModelValue = () => {
  const files = images.value.map(img => img.file)
  emit('update:modelValue', files)
}
</script>