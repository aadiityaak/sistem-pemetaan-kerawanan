<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePWA } from '@/composables/usePWA';

interface Props {
    title?: string;
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

interface MenuItem {
    title: string;
    path?: string;
    children?: MenuItem[];
}

const page = usePage();
const { installPWA } = usePWA();

const pengaturanMenu = computed<MenuItem | null>(() => {
    const items = (page.props as any).menuItems as MenuItem[] | undefined;
    return items?.find((it) => it.title === 'PENGATURAN') ?? null;
});

const tabs = computed(() => {
    const children = pengaturanMenu.value?.children ?? [];
    return children.map((c) => ({
        title: c.title,
        href: c.path || '#',
        isAction: c.title === 'Install App',
    }));
});

const activeHref = computed(() => {
    const currentUrl = page.url.split('?')[0];
    const match = tabs.value.find((t) => t.href !== '#' && currentUrl.startsWith(t.href));
    return match?.href ?? '/settings';
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="tabs.length > 0" class="px-4 pt-4">
            <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-wrap gap-2">
                    <template v-for="tab in tabs" :key="tab.title">
                        <button
                            v-if="tab.isAction"
                            type="button"
                            @click="installPWA"
                            class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                            :class="
                                tab.href === activeHref
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'
                            "
                        >
                            {{ tab.title }}
                        </button>
                        <Link
                            v-else
                            :href="tab.href"
                            class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                            :class="
                                tab.href === activeHref
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'
                            "
                        >
                            {{ tab.title }}
                        </Link>
                    </template>
                </div>
            </div>
        </div>
        <slot />
    </AppLayout>
</template>
