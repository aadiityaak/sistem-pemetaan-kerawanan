<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Menu, Download, ExternalLink, Moon, Sun } from 'lucide-vue-next'
import { useSidebar } from './utils'
import { useAppearance } from '@/composables/useAppearance'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const { toggleSidebar } = useSidebar()
const { appearance, updateAppearance } = useAppearance()

const toggleTheme = () => {
  const nextTheme = appearance.value === 'dark' ? 'light' : 'dark'
  updateAppearance(nextTheme)
}

const deferredPrompt = ref<any>(null)
const showInstallButton = ref(false)
const isStandalone = ref(false)
const isAppInstalled = ref(false)

const handleBeforeInstallPrompt = (e: Event) => {
  e.preventDefault()
  deferredPrompt.value = e
  showInstallButton.value = true
}

const handleAppInstalled = () => {
  showInstallButton.value = false
  isAppInstalled.value = true
  deferredPrompt.value = null
}

const installPWA = async () => {
  if (!deferredPrompt.value) return
  deferredPrompt.value.prompt()
  const { outcome } = await deferredPrompt.value.userChoice
  if (outcome === 'accepted') {
    handleAppInstalled()
  }
}

const openInApp = () => {
  // PWA standalone mode usually handles internal links by opening the app
  // If we're already installed, we just need to trigger the browser's PWA launch
  // There's no standard web API to force launch the PWA from a tab,
  // but most browsers will show an 'Open in App' icon in the address bar
  // if the manifest is correctly detected.
  window.location.reload();
}

onMounted(() => {
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  window.addEventListener('appinstalled', handleAppInstalled)
  
  isStandalone.value = window.matchMedia('(display-mode: standalone)').matches
  
  // Check if it's already installed via localStorage or other hints if possible
  if (isStandalone.value) {
    showInstallButton.value = false
    isAppInstalled.value = true
  }
})

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  window.removeEventListener('appinstalled', handleAppInstalled)
})
</script>

<template>
  <div class="flex items-center gap-2">
    <Button
      v-if="showInstallButton && !isStandalone"
      variant="ghost"
      :class="cn('!flex !items-center !justify-center gap-2 h-9 px-3 min-w-fit sm:h-7 sm:w-7 sm:px-0 sm:gap-0 text-blue-500 hover:text-blue-600', props.class)"
      title="Install App"
      @click="installPWA"
    >
      <Download class="h-6 w-6 sm:h-4 sm:w-4" />
      <span class="text-sm font-medium whitespace-nowrap sm:sr-only">Install App</span>
    </Button>

    <Button
      v-else-if="!isStandalone && isAppInstalled"
      variant="ghost"
      :class="cn('!flex !items-center !justify-center gap-2 h-9 px-3 min-w-fit sm:h-7 sm:w-7 sm:px-0 sm:gap-0 text-emerald-500 hover:text-emerald-600', props.class)"
      title="Buka di App"
      @click="openInApp"
    >
      <ExternalLink class="h-6 w-6 sm:h-4 sm:w-4" />
      <span class="text-sm font-medium whitespace-nowrap sm:sr-only">Buka di App</span>
    </Button>
    <!-- theme toggle -->
    <Button
      variant="ghost"
      :class="cn('!flex !items-center !justify-center gap-2 h-9 px-3 min-w-fit sm:h-7 sm:w-7 sm:px-0 sm:gap-0', props.class)"
      title="Toggle Theme"
      @click="toggleTheme"
    >
      <Sun v-if="appearance === 'dark'" class="h-6 w-6 sm:h-4 sm:w-4" />
      <Moon v-else class="h-6 w-6 sm:h-4 sm:w-4" />
      <span class="text-sm font-medium whitespace-nowrap sm:sr-only">Mode {{ appearance === 'dark' ? 'Terang' : 'Gelap' }}</span>
    </Button>

    <Button
      data-sidebar="trigger"
      data-slot="sidebar-trigger"
      variant="ghost"
      :class="cn('!flex !items-center !justify-center gap-2 h-9 px-3 min-w-fit sm:h-7 sm:w-7 sm:px-0 sm:gap-0', props.class)"
      @click="toggleSidebar"
    >
      <span class="text-sm font-medium whitespace-nowrap sm:sr-only">Menu</span>
      <Menu class="h-6 w-6 sm:h-4 sm:w-4" />
    </Button>
  </div>
</template>
