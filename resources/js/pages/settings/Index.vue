<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">Pengaturan Aplikasi</h2>
              <Link
                :href="route('settings.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Setting
              </Link>
            </div>

            <form @submit.prevent="updateBatch">
              <div v-for="(groupSettings, group) in settings" :key="group" class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 capitalize">{{ group }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div 
                    v-for="setting in groupSettings" 
                    :key="setting.id"
                    class="bg-gray-50 p-4 rounded-lg"
                  >
                    <div class="flex justify-between items-start mb-3">
                      <div class="flex-1">
                        <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                          {{ setting.label }}
                        </label>
                        <p v-if="setting.description" class="text-xs text-gray-500 mt-1">
                          {{ setting.description }}
                        </p>
                      </div>
                      <div class="flex space-x-2">
                        <Link
                          :href="route('settings.show', setting.id)"
                          class="text-green-600 hover:text-green-900 text-sm"
                        >
                          Lihat
                        </Link>
                        <Link
                          :href="route('settings.edit', setting.id)"
                          class="text-blue-600 hover:text-blue-900 text-sm"
                        >
                          Edit
                        </Link>
                        <button
                          type="button"
                          @click="confirmDelete(setting)"
                          class="text-red-600 hover:text-red-900 text-sm"
                        >
                          Hapus
                        </button>
                      </div>
                    </div>

                    <div v-if="setting.type === 'text'" class="mt-2">
                      <input
                        :id="setting.key"
                        v-model="form.settings[setting.key]"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        :placeholder="setting.value || ''"
                      />
                    </div>

                    <div v-else-if="setting.type === 'boolean'" class="mt-2">
                      <label class="inline-flex items-center">
                        <input
                          type="checkbox"
                          v-model="form.settings[setting.key]"
                          class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        />
                        <span class="ml-2 text-sm text-gray-600">Aktif</span>
                      </label>
                    </div>

                    <div v-else-if="setting.type === 'number'" class="mt-2">
                      <input
                        :id="setting.key"
                        v-model.number="form.settings[setting.key]"
                        type="number"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        :placeholder="setting.value || ''"
                      />
                    </div>

                    <div v-else-if="setting.type === 'image'" class="mt-2">
                      <div v-if="setting.value" class="mb-2">
                        <img :src="setting.value" alt="Current image" class="h-16 w-16 object-cover rounded">
                      </div>
                      <input
                        :id="setting.key"
                        type="file"
                        accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                      />
                      <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF, ICO, SVG. Max: 2MB</p>
                    </div>

                    <div v-else class="mt-2">
                      <textarea
                        :id="setting.key"
                        v-model="form.settings[setting.key]"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        :placeholder="setting.value || ''"
                      ></textarea>
                    </div>

                    <div class="mt-2 text-xs text-gray-500">
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

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Hapus Setting</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Apakah Anda yakin ingin menghapus setting "{{ settingToDelete?.label }}"? Tindakan ini tidak dapat dibatalkan.
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <button
              @click="deleteConfirmed"
              class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
            >
              Hapus
            </button>
            <button
              @click="cancelDelete"
              class="mt-3 px-4 py-2 bg-gray-100 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
              Batal
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
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

const showDeleteModal = ref(false)
const settingToDelete = ref<Setting | null>(null)

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

const confirmDelete = (setting: Setting) => {
  settingToDelete.value = setting
  showDeleteModal.value = true
}

const deleteConfirmed = () => {
  if (settingToDelete.value) {
    router.delete(route('settings.destroy', settingToDelete.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        showDeleteModal.value = false
        settingToDelete.value = null
      }
    })
  }
}

const cancelDelete = () => {
  showDeleteModal.value = false
  settingToDelete.value = null
}
</script>
