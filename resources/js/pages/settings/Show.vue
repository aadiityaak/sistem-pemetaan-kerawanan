<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">Detail Setting</h2>
              <div class="flex space-x-3">
                <Link
                  :href="route('settings.edit', setting.id)"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  Edit
                </Link>
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

            <div class="bg-gray-50 p-6 rounded-lg">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Setting</h3>
                  
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Key</label>
                      <p class="mt-1 text-sm text-gray-900 bg-white p-2 rounded border">{{ setting.key }}</p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">Label</label>
                      <p class="mt-1 text-sm text-gray-900 bg-white p-2 rounded border">{{ setting.label }}</p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">Type</label>
                      <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                            :class="{
                              'bg-blue-100 text-blue-800': setting.type === 'text',
                              'bg-green-100 text-green-800': setting.type === 'image',
                              'bg-purple-100 text-purple-800': setting.type === 'file',
                              'bg-yellow-100 text-yellow-800': setting.type === 'boolean',
                              'bg-red-100 text-red-800': setting.type === 'number'
                            }">
                        {{ setting.type }}
                      </span>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">Group</label>
                      <p class="mt-1 text-sm text-gray-900 bg-white p-2 rounded border capitalize">{{ setting.group }}</p>
                    </div>
                  </div>
                </div>

                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Nilai Setting</h3>
                  
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Value</label>
                      <div v-if="setting.type === 'image' && setting.value" class="mt-1">
                        <img :src="setting.value" :alt="setting.label" class="h-32 w-auto object-contain border rounded">
                        <p class="text-xs text-gray-500 mt-1">{{ setting.value }}</p>
                      </div>
                      <div v-else-if="setting.type === 'boolean'" class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                              :class="setting.value === 'true' || setting.value === '1' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                          {{ setting.value === 'true' || setting.value === '1' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                      </div>
                      <p v-else class="mt-1 text-sm text-gray-900 bg-white p-3 rounded border whitespace-pre-wrap">{{ setting.value || 'Tidak ada nilai' }}</p>
                    </div>

                    <div v-if="setting.description">
                      <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                      <p class="mt-1 text-sm text-gray-600 bg-white p-3 rounded border">{{ setting.description }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs text-gray-500">
                  <div>
                    <span class="font-medium">Dibuat:</span> {{ new Date(setting.created_at).toLocaleString('id-ID') }}
                  </div>
                  <div>
                    <span class="font-medium">Diperbarui:</span> {{ new Date(setting.updated_at).toLocaleString('id-ID') }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
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

defineProps<Props>()
</script>
