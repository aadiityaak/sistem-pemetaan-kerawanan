<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">Tambah Setting Baru</h2>
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

            <form @submit.prevent="submit" enctype="multipart/form-data">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-6">
                  <div>
                    <label for="key" class="block text-sm font-medium text-gray-700">Key *</label>
                    <input
                      id="key"
                      v-model="form.key"
                      type="text"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      placeholder="app_name, app_logo, etc"
                    />
                    <div v-if="form.errors.key" class="mt-1 text-sm text-red-600">{{ form.errors.key }}</div>
                  </div>

                  <div>
                    <label for="label" class="block text-sm font-medium text-gray-700">Label *</label>
                    <input
                      id="label"
                      v-model="form.label"
                      type="text"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      placeholder="Nama Aplikasi, Logo, etc"
                    />
                    <div v-if="form.errors.label" class="mt-1 text-sm text-red-600">{{ form.errors.label }}</div>
                  </div>

                  <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                    <select
                      id="type"
                      v-model="form.type"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="">Pilih Type</option>
                      <option value="text">Text</option>
                      <option value="image">Image</option>
                      <option value="file">File</option>
                      <option value="boolean">Boolean</option>
                      <option value="number">Number</option>
                    </select>
                    <div v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</div>
                  </div>

                  <div>
                    <label for="group" class="block text-sm font-medium text-gray-700">Group *</label>
                    <select
                      id="group"
                      v-model="form.group"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="">Pilih Group</option>
                      <option value="general">General</option>
                      <option value="appearance">Appearance</option>
                      <option value="system">System</option>
                      <option value="advanced">Advanced</option>
                    </select>
                    <div v-if="form.errors.group" class="mt-1 text-sm text-red-600">{{ form.errors.group }}</div>
                  </div>
                </div>

                <div class="space-y-6">
                  <div>
                    <label for="value" class="block text-sm font-medium text-gray-700">Value</label>
                    <div v-if="form.type === 'boolean'">
                      <label class="inline-flex items-center mt-1">
                        <input
                          v-model="form.value"
                          type="checkbox"
                          true-value="1"
                          false-value="0"
                          class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        />
                        <span class="ml-2 text-sm text-gray-600">Aktif</span>
                      </label>
                    </div>
                    <div v-else-if="form.type === 'image' || form.type === 'file'">
                      <input
                        id="file"
                        ref="fileInput"
                        type="file"
                        :accept="form.type === 'image' ? 'image/*' : '*'"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        @change="handleFileChange"
                      />
                      <p class="text-xs text-gray-500 mt-1">
                        <span v-if="form.type === 'image'">Format: JPG, PNG, GIF, ICO, SVG.</span>
                        Max: 2MB
                      </p>
                    </div>
                    <div v-else-if="form.type === 'number'">
                      <input
                        id="value"
                        v-model.number="form.value"
                        type="number"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="0"
                      />
                    </div>
                    <div v-else>
                      <textarea
                        id="value"
                        v-model="form.value"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Nilai setting"
                      ></textarea>
                    </div>
                    <div v-if="form.errors.value" class="mt-1 text-sm text-red-600">{{ form.errors.value }}</div>
                    <div v-if="form.errors.file" class="mt-1 text-sm text-red-600">{{ form.errors.file }}</div>
                  </div>

                  <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea
                      id="description"
                      v-model="form.description"
                      rows="3"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      placeholder="Deskripsi penggunaan setting ini"
                    ></textarea>
                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end mt-6 pt-6 border-t border-gray-200">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Setting' }}
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
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const fileInput = ref<HTMLInputElement>()

const form = useForm({
  key: '',
  value: '',
  type: '',
  group: '',
  label: '',
  description: '',
  file: null as File | null
})

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0] || null
  form.file = file
}

const submit = () => {
  form.post(route('settings.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      // Reset form on success
      form.reset()
      if (fileInput.value) {
        fileInput.value.value = ''
      }
    }
  })
}
</script>
