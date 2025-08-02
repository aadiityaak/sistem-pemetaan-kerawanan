<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
          <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Aplikasi</h2>
            </div>

            <form @submit.prevent="saveAllSettings" v-if="formsReady">
              <div v-for="(groupSettings, group) in settings" :key="group" class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 capitalize">{{ group }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div 
                    v-for="setting in groupSettings" 
                    :key="setting.key"
                    class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600"
                  >
                    <div class="flex justify-between items-start mb-3">
                      <div class="flex-1">
                        <label :for="setting.key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                          {{ setting.label }}
                        </label>
                        <p v-if="setting.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                          {{ setting.description }}
                        </p>
                      </div>
                    </div>

                    <div v-if="setting.type === 'text'" class="mt-2">
                      <input
                        :id="setting.key"
                        v-model="forms[setting.key].value"
                        type="text"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                        :placeholder="setting.value || ''"
                      />
                    </div>

                    <div v-else-if="setting.type === 'image'" class="mt-2">
                      <div v-if="imagePreviews[setting.key] || setting.value" class="mb-2">
                        <img 
                          :src="imagePreviews[setting.key] || setting.value || ''" 
                          alt="Current image" 
                          class="h-16 w-16 object-cover rounded border border-gray-200 dark:border-gray-600"
                        >
                      </div>
                      <input
                        :id="setting.key"
                        @change="handleFileChange(setting.key, $event)"
                        type="file"
                        accept="image/*,image/x-icon"
                        class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-3 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900 file:text-blue-700 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-800"
                      />
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format: JPG, PNG, GIF, ICO, SVG. Max: 2MB</p>
                    </div>

                    <div v-else class="mt-2">
                      <textarea
                        :id="setting.key"
                        v-model="forms[setting.key].value"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                        :placeholder="setting.value || ''"
                      ></textarea>
                    </div>

                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                      <span class="font-medium">Key:</span> {{ setting.key }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Single Save Button -->
              <div class="mt-8 flex justify-center">
                <button
                  type="submit"
                  :disabled="isProcessing"
                  class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                  <svg v-if="isProcessing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isProcessing ? 'Menyimpan...' : 'Simpan Semua Pengaturan' }}
                </button>
              </div>
            </form>
            
            <!-- Loading state while forms are initializing -->
            <div v-else class="flex items-center justify-center p-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/layouts/AppLayout.vue'

interface Setting {
  key: string
  value: string | null
  type: string
  group: string
  label: string
  description: string | null
}

interface Props {
  settings: Record<string, Setting[]>
}

const props = defineProps<Props>()

// Create ref for forms and state
const forms = ref<Record<string, any>>({})
const formsReady = ref(false)
const isProcessing = ref(false)
const imagePreviews = ref<Record<string, string>>({})

// Initialize forms when component mounts
onMounted(async () => {
  await nextTick()
  
  const formMap: Record<string, any> = {}
  Object.values(props.settings).flat().forEach(setting => {
    formMap[setting.key] = useForm({
      value: setting.value || '',
      file: null as File | null
    })
  })
  
  forms.value = formMap
  formsReady.value = true
})

const handleFileChange = (key: string, event: Event) => {
  const input = event.target as HTMLInputElement
  if (input.files && input.files[0]) {
    const file = input.files[0]
    forms.value[key].file = file
    
    // Create preview URL for the selected image
    const reader = new FileReader()
    reader.onload = (e) => {
      if (e.target?.result) {
        imagePreviews.value[key] = e.target.result as string
      }
    }
    reader.readAsDataURL(file)
  }
}

const saveAllSettings = async () => {
  isProcessing.value = true
  
  try {
    // Get all settings that need to be updated
    const allSettings = Object.values(props.settings).flat()
    
    for (const setting of allSettings) {
      const form = forms.value[setting.key]
      if (!form) continue
      
      // Check if the setting has changes
      const hasTextChange = form.value !== (setting.value || '')
      const hasFileChange = form.file !== null
      
      // Debug: Log form data before submission
      if (hasFileChange) {
        console.log('Submitting file for:', setting.key, {
          file: form.file,
          fileName: form.file?.name,
          fileSize: form.file?.size,
          fileType: form.file?.type
        })
      }
      
      if (hasTextChange || hasFileChange) {
        // Submit this setting update
        await new Promise<void>((resolve, reject) => {
          form.post(route('settings.update', setting.key), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
              console.log('Success updating:', setting.key)
              // Reset file input if it was a file upload
              if (form.file) {
                form.file = null
                const fileInput = document.getElementById(setting.key) as HTMLInputElement
                if (fileInput && fileInput.type === 'file') {
                  fileInput.value = ''
                }
                // Clear preview
                delete imagePreviews.value[setting.key]
              }
              resolve()
            },
            onError: (errors) => {
              console.error('Error updating:', setting.key, errors)
              reject(new Error(`Failed to update ${setting.key}`))
            }
          })
        })
      }
    }
    
    // Refresh the page to show updated values
    router.reload()
    
  } catch (error) {
    console.error('Error updating settings:', error)
  } finally {
    isProcessing.value = false
  }
}
</script>
