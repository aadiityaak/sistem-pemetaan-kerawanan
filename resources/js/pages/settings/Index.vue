<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
          <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Aplikasi</h2>
            </div>

            <form @submit.prevent="updateBatch">
              <div v-for="(groupSettings, group) in settings" :key="group" class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 capitalize">{{ group }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div 
                    v-for="setting in groupSettings" 
                    :key="setting.id"
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
                      <div class="flex space-x-2">
                        <Link
                          :href="route('settings.show', setting.id)"
                          class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 text-sm"
                        >
                          Edit Value
                        </Link>
                      </div>
                    </div>

                    <div v-if="setting.type === 'text'" class="mt-2">
                      <input
                        :id="setting.key"
                        v-model="form.settings[setting.key]"
                        type="text"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                        :placeholder="setting.value || ''"
                      />
                    </div>

                    <div v-else-if="setting.type === 'boolean'" class="mt-2">
                      <label class="inline-flex items-center">
                        <input
                          type="checkbox"
                          v-model="form.settings[setting.key]"
                          class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 bg-white dark:bg-gray-800"
                        />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Aktif</span>
                      </label>
                    </div>

                    <div v-else-if="setting.type === 'number'" class="mt-2">
                      <input
                        :id="setting.key"
                        v-model.number="form.settings[setting.key]"
                        type="number"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                        :placeholder="setting.value || ''"
                      />
                    </div>

                    <div v-else-if="setting.type === 'image'" class="mt-2">
                      <div v-if="setting.value" class="mb-2">
                        <img :src="setting.value" alt="Current image" class="h-16 w-16 object-cover rounded border border-gray-200 dark:border-gray-600">
                      </div>
                      <input
                        :id="setting.key"
                        type="file"
                        accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-3 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900 file:text-blue-700 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-800"
                      />
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format: JPG, PNG, GIF, ICO, SVG. Max: 2MB</p>
                    </div>

                    <div v-else class="mt-2">
                      <textarea
                        :id="setting.key"
                        v-model="form.settings[setting.key]"
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

              <div class="flex justify-end mt-6">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Semua' }}
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
import { onMounted } from 'vue'
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
}

interface Props {
  settings: Record<string, Setting[]>
}

const props = defineProps<Props>()

const form = useForm({
  settings: {} as Record<string, any>
})

onMounted(() => {
  // Initialize form with current values
  Object.values(props.settings).flat().forEach(setting => {
    form.settings[setting.key] = setting.value
  })
})

const updateBatch = () => {
  form.post(route('settings.update-batch'), {
    preserveScroll: true,
    onSuccess: () => {
      // Update successful
    }
  })
}
</script>
