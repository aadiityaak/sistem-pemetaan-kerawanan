<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
          <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Setting: {{ setting.label }}</h2>
              <div class="flex space-x-3">
                <Link
                  :href="route('settings.index')"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                  </svg>
                  Kembali
                </Link>
              </div>
            </div>

            <form @submit.prevent="submit" enctype="multipart/form-data" class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Info Setting (Read Only) -->
                <div class="space-y-4">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Setting</h3>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-600">{{ setting.key }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Label</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-600">{{ setting.label }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                          :class="{
                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': setting.type === 'text',
                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': setting.type === 'image',
                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': setting.type === 'file',
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': setting.type === 'boolean',
                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': setting.type === 'number'
                          }">
                      {{ setting.type }}
                    </span>
                  </div>

                  <div v-if="setting.description">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-600">{{ setting.description }}</p>
                  </div>
                </div>

                <!-- Form Edit Value -->
                <div class="space-y-4">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Edit Nilai</h3>
                  
                  <!-- Show current image if exists -->
                  <div v-if="setting.type === 'image' && setting.value" class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Current Image:</label>
                    <img :src="setting.value" :alt="setting.label" class="h-20 w-auto object-contain border rounded">
                  </div>

                  <div>
                    <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Value</label>
                    
                    <div v-if="setting.type === 'boolean'">
                      <label class="inline-flex items-center mt-1">
                        <input
                          v-model="form.value"
                          type="checkbox"
                          true-value="1"
                          false-value="0"
                          class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 bg-white dark:bg-gray-800"
                        />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Aktif</span>
                      </label>
                    </div>
                    
                    <div v-else-if="setting.type === 'image' || setting.type === 'file'">
                      <input
                        id="file"
                        ref="fileInput"
                        type="file"
                        :accept="setting.type === 'image' ? 'image/*' : '*'"
                        class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-3 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900 file:text-blue-700 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-800"
                        @change="handleFileChange"
                      />
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <span v-if="setting.type === 'image'">Format: JPG, PNG, GIF, ICO, SVG.</span>
                        Max: 2MB. Kosongkan jika tidak ingin mengubah file.
                      </p>
                    </div>
                    
                    <div v-else-if="setting.type === 'number'">
                      <input
                        id="value"
                        v-model.number="form.value"
                        type="number"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                        placeholder="0"
                      />
                    </div>
                    
                    <div v-else>
                      <textarea
                        id="value"
                        v-model="form.value"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                        placeholder="Nilai setting"
                      ></textarea>
                    </div>
                    
                    <div v-if="form.errors.value" class="mt-1 text-sm text-red-600">{{ form.errors.value }}</div>
                    <div v-if="form.errors.file" class="mt-1 text-sm text-red-600">{{ form.errors.file }}</div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Setting {
  id: number
  key: string
  value: string | null
  type: string
  group: string
  label: string
  description: string | null
  created_at: string
  updated_at: string
}

interface Props {
  setting: Setting
}

const props = defineProps<Props>()

const fileInput = ref<HTMLInputElement>()

const form = useForm({
  value: '',
  file: null as File | null
})

onMounted(() => {
  // Initialize form with setting value
  form.value = props.setting.value || ''
})

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0] || null
  form.file = file
}

const submit = () => {
  form.put(route('settings.update', props.setting.id), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      // Clear file input on success
      if (fileInput.value) {
        fileInput.value.value = ''
      }
      form.file = null
    }
  })
}
</script>
