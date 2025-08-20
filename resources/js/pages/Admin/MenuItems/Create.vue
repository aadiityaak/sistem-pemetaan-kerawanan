<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Tambah Menu Item</h1>
                <p class="text-muted-foreground">Buat menu item baru untuk navigasi sistem</p>
            </div>

            <!-- Create Form -->
            <Card>
                <CardHeader>
                    <CardTitle>Informasi Menu Item</CardTitle>
                    <CardDescription> Masukkan detail menu item yang ingin ditambahkan </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Title -->
                            <div class="space-y-2">
                                <label for="title" class="text-sm font-medium">Judul Menu</label>
                                <Input id="title" v-model="form.title" placeholder="Masukkan judul menu" required />
                                <div v-if="form.errors.title" class="text-sm text-destructive">
                                    {{ form.errors.title }}
                                </div>
                            </div>

                            <!-- Icon -->
                            <div class="space-y-2">
                                <IconSelector
                                    id="icon"
                                    v-model="form.icon"
                                    label="Icon"
                                    placeholder="Cari atau pilih icon..."
                                    :error="form.errors.icon"
                                    help-text="Pilih icon yang sesuai untuk menu ini"
                                />
                            </div>

                            <!-- Custom Path -->
                            <div class="space-y-2 md:col-span-2">
                                <label for="path" class="text-sm font-medium">Custom Path/URL</label>
                                <Input id="path" v-model="form.path" placeholder="Masukkan path custom (contoh: /dashboard, /indas/data-entry)" />
                                <p class="text-xs text-muted-foreground">
                                    Jika diisi, menu akan redirect ke path ini. Kosongkan jika tidak perlu redirect.
                                </p>
                                <div v-if="form.errors.path" class="text-sm text-destructive">
                                    {{ form.errors.path }}
                                </div>
                            </div>

                            <!-- Parent Menu -->
                            <div class="space-y-2">
                                <label for="parent_id" class="text-sm font-medium">Parent Menu</label>
                                <select
                                    id="parent_id"
                                    v-model="form.parent_id"
                                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option :value="null">Tidak ada parent (Menu utama)</option>
                                    <option v-for="parent in parentMenuItems" :key="parent.id" :value="parent.id">
                                        {{ parent.title }}
                                    </option>
                                </select>
                                <div v-if="form.errors.parent_id" class="text-sm text-destructive">
                                    {{ form.errors.parent_id }}
                                </div>
                            </div>

                            <!-- Sort Order -->
                            <div class="space-y-2">
                                <label for="sort_order" class="text-sm font-medium">Urutan</label>
                                <Input id="sort_order" v-model.number="form.sort_order" type="number" min="0" placeholder="Urutan menu" />
                                <div v-if="form.errors.sort_order" class="text-sm text-destructive">
                                    {{ form.errors.sort_order }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2 md:col-span-2">
                                <label for="description" class="text-sm font-medium">Deskripsi</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Deskripsi menu (opsional)"
                                    rows="3"
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                />
                                <div v-if="form.errors.description" class="text-sm text-destructive">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Checkboxes -->
                            <div class="space-y-4 md:col-span-2">
                                <div class="flex items-center space-x-2">
                                    <Checkbox id="is_active" v-model:checked="form.is_active" />
                                    <label for="is_active" class="text-sm font-medium"> Menu Aktif (ON/OFF) </label>
                                </div>
                                <p class="ml-6 text-xs text-muted-foreground">Jika dinonaktifkan, menu tidak akan muncul di sidebar</p>

                                <div class="flex items-center space-x-2">
                                    <Checkbox id="admin_only" v-model:checked="form.admin_only" />
                                    <label for="admin_only" class="text-sm font-medium"> Hanya untuk Admin </label>
                                </div>
                                <p class="ml-6 text-xs text-muted-foreground">Menu hanya ditampilkan untuk user dengan role admin</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between border-t pt-6">
                            <Button type="button" variant="outline" as-child>
                                <Link :href="route('admin.menu-items.index')"> Batal </Link>
                            </Button>

                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Menu Item' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppSidebarLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import IconSelector from '@/components/ui/IconSelector.vue';
import { Input } from '@/components/ui/input';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
// Removed Textarea import - using native textarea instead
import { Checkbox } from '@/components/ui/checkbox';
// Removed Select components - using native select instead
import { Link, useForm } from '@inertiajs/vue3';

interface MenuItem {
    id: number;
    title: string;
    icon?: string;
    path?: string;
    is_active: boolean;
    sort_order: number;
    parent_id?: number;
    admin_only: boolean;
    description?: string;
}

interface Props {
    parentMenuItems: MenuItem[];
}

defineProps<Props>();

const breadcrumbs = [
    { title: 'Admin', label: 'Admin', href: '#' },
    { title: 'Menu Items', label: 'Menu Items', href: route('admin.menu-items.index') },
    { title: 'Tambah Menu Baru', label: 'Tambah Menu Baru', href: '#' },
];

// Form with default values
const form = useForm({
    title: '',
    icon: '',
    path: '',
    is_active: true,
    sort_order: 0,
    parent_id: null,
    admin_only: false,
    description: '',
});

const submit = () => {
    form.post(route('admin.menu-items.store'), {
        onSuccess: () => {
            // Could add success notification here
        },
    });
};
</script>
