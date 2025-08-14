<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="> ACCESS_REQUIRED" description="> authenticate_user_credentials...">
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 rounded border border-green-500/30 bg-green-500/10 p-3 text-center font-mono text-sm font-medium text-green-400"
        >
            > {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6 font-mono">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="font-mono text-sm text-green-400">> user_id:</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="user@system.local"
                        class="border-green-500/30 bg-black/50 font-mono text-green-300 placeholder-green-500/50 focus:border-green-400 focus:ring-green-400/50"
                    />
                    <InputError :message="form.errors.email" class="font-mono text-xs text-red-400" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="font-mono text-sm text-green-400">> password:</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="font-mono text-xs text-green-300/70 hover:text-green-300"
                            :tabindex="5"
                        >
                            > recovery_mode?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="••••••••"
                        class="border-green-500/30 bg-black/50 font-mono text-green-300 placeholder-green-500/50 focus:border-green-400 focus:ring-green-400/50"
                    />
                    <InputError :message="form.errors.password" class="font-mono text-xs text-red-400" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3 font-mono text-sm text-green-300/70">
                        <Checkbox
                            id="remember"
                            v-model="form.remember"
                            :tabindex="3"
                            class="border-green-500/30 text-green-400 focus:ring-green-400/50"
                        />
                        <span>> maintain_session</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="glow-button mt-4 w-full border border-green-500/50 bg-green-600/20 font-mono tracking-wider text-green-400 transition-all duration-300 hover:border-green-400 hover:bg-green-500/30"
                    :tabindex="4"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ form.processing ? '> AUTHENTICATING...' : '> EXECUTE LOGIN' }}
                </Button>
            </div>

            <div class="text-center font-mono text-sm text-green-300/60">
                > need_access_token?
                <TextLink :href="route('register')" :tabindex="5" class="ml-1 text-green-400 hover:text-green-300">register_user</TextLink>
            </div>
        </form>
    </AuthBase>
</template>

<style scoped>
.glow-button {
    box-shadow:
        0 0 10px rgba(0, 255, 65, 0.2),
        0 0 20px rgba(0, 255, 65, 0.1);
    transition: all 0.3s ease;
}

.glow-button:hover {
    box-shadow:
        0 0 20px rgba(0, 255, 65, 0.4),
        0 0 40px rgba(0, 255, 65, 0.2),
        0 0 60px rgba(0, 255, 65, 0.1);
    transform: translateY(-1px);
}

.glow-button:active {
    transform: translateY(0);
}
</style>
