<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import type { AppPageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage<AppPageProps>();
const appSettings = computed(() => page.props.appSettings);

let matrixAnimation: number | null = null;

// Matrix rain animation
onMounted(() => {
    const canvas = document.getElementById('matrix-canvas') as HTMLCanvasElement;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Set canvas size
    const resizeCanvas = () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    };
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    // Matrix characters
    const chars = '01ｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜｦﾝABCDEFGHIJKLMNOPQRSTUVWXYZ123456789@#$%^&*()_+-=[]{}|;:,.<>?';
    const charArray = chars.split('');

    const fontSize = 14;
    const columns = canvas.width / fontSize;
    const drops: number[] = [];

    // Initialize drops
    for (let x = 0; x < columns; x++) {
        drops[x] = 1;
    }

    const drawMatrix = () => {
        // Semi-transparent black background for trail effect
        ctx.fillStyle = 'rgba(0, 0, 0, 0.04)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Green text
        ctx.fillStyle = '#0F0';
        ctx.font = fontSize + 'px monospace';

        for (let i = 0; i < drops.length; i++) {
            // Random character
            const text = charArray[Math.floor(Math.random() * charArray.length)];

            // Draw character
            ctx.fillStyle = i % 3 === 0 ? '#00FF41' : i % 5 === 0 ? '#00AA00' : '#008800';
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);

            // Reset drop randomly and based on conditions
            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            drops[i]++;
        }
    };

    const animate = () => {
        drawMatrix();
        matrixAnimation = requestAnimationFrame(animate);
    };

    animate();
});

onUnmounted(() => {
    if (matrixAnimation) {
        cancelAnimationFrame(matrixAnimation);
    }
});
</script>

<template>
    <div class="relative flex min-h-screen flex-col justify-center overflow-hidden bg-black py-12 sm:px-6 lg:px-8">
        <!-- Matrix rain background -->
        <div class="matrix-container absolute inset-0 opacity-20">
            <canvas id="matrix-canvas" class="absolute inset-0 h-full w-full"></canvas>
        </div>

        <!-- Animated grid background -->
        <div class="absolute inset-0 opacity-10">
            <div class="grid-bg h-full w-full"></div>
        </div>

        <!-- Glitch overlay -->
        <div class="glitch-overlay pointer-events-none absolute inset-0 opacity-30"></div>
        <div class="relative z-10 sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo/Icon section with hacker theme -->
            <div class="flex justify-center">
                <Link :href="route('home')" class="hacker-logo flex flex-col items-center gap-3">
                    <div class="relative">
                        <!-- Show login_logo if available, fallback to app_favicon, then default icon -->
                        <div
                            v-if="appSettings.login_logo"
                            class="glow-box flex h-[120px] w-[120px] items-center justify-center rounded-lg border border-green-500/30 bg-black/80 shadow-lg shadow-green-500/10 backdrop-blur-sm"
                        >
                            <img :src="appSettings.login_logo" :alt="appSettings.app_name + ' - Login'" class="h-[110px] w-[110px] object-contain" />
                        </div>
                        <div
                            v-else-if="appSettings.app_favicon && appSettings.app_favicon !== '/favicon.ico'"
                            class="glow-box flex h-20 w-20 items-center justify-center rounded-lg border border-green-500/30 bg-black/80 shadow-lg shadow-green-500/10 backdrop-blur-sm"
                        >
                            <img
                                :src="appSettings.app_favicon"
                                :alt="appSettings.app_name"
                                class="h-12 w-12 object-contain opacity-80 brightness-0 invert filter"
                            />
                        </div>
                        <div
                            v-else
                            class="glow-box flex h-20 w-20 items-center justify-center rounded-lg border border-green-500/30 bg-black/80 shadow-lg shadow-green-500/10 backdrop-blur-sm"
                        >
                            <AppLogoIcon class="h-12 w-12 text-green-400" />
                        </div>
                        <!-- Scanning line effect -->
                        <div class="animate-scan absolute inset-0 rounded-lg bg-gradient-to-b from-transparent via-green-400/20 to-transparent"></div>
                    </div>
                    <div class="text-center">
                        <h1 class="glitch-text font-mono text-3xl font-bold tracking-wider text-green-400">
                            {{ appSettings.app_name || 'CRIME MAP' }}
                        </h1>
                    </div>
                </Link>
            </div>

            <!-- Auth card with cyberpunk design -->
            <div class="mt-8">
                <div
                    class="relative overflow-hidden rounded-lg border border-green-500/20 bg-black/60 px-6 py-8 shadow-xl shadow-green-500/5 backdrop-blur-md"
                >
                    <!-- Card scanning lines -->
                    <div class="animate-slide absolute inset-0 bg-gradient-to-r from-transparent via-green-400/5 to-transparent"></div>

                    <div class="relative z-10 mb-6 text-center">
                        <h2 class="font-mono text-xl font-semibold text-green-400">{{ title || '> ACCESS_TERMINAL' }}</h2>
                        <p class="mt-2 font-mono text-sm text-green-300/70">{{ description || '> enter_credentials_to_continue...' }}</p>
                    </div>

                    <div class="relative z-10">
                        <slot />
                    </div>

                    <!-- Corner brackets -->
                    <div class="absolute top-2 left-2 h-4 w-4 border-t-2 border-l-2 border-green-500/50"></div>
                    <div class="absolute top-2 right-2 h-4 w-4 border-t-2 border-r-2 border-green-500/50"></div>
                    <div class="absolute bottom-2 left-2 h-4 w-4 border-b-2 border-l-2 border-green-500/50"></div>
                    <div class="absolute right-2 bottom-2 h-4 w-4 border-r-2 border-b-2 border-green-500/50"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Grid background */
.grid-bg {
    background-image: linear-gradient(rgba(0, 255, 65, 0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(0, 255, 65, 0.1) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: grid-move 20s linear infinite;
}

@keyframes grid-move {
    0% {
        transform: translate(0, 0);
    }
    100% {
        transform: translate(50px, 50px);
    }
}

/* Glitch overlay */
.glitch-overlay {
    background:
        linear-gradient(90deg, transparent 0%, rgba(0, 255, 65, 0.03) 50%, transparent 100%),
        linear-gradient(0deg, transparent 0%, rgba(0, 255, 65, 0.02) 50%, transparent 100%);
    animation: glitch 3s infinite;
}

@keyframes glitch {
    0%,
    90%,
    100% {
        opacity: 0;
    }
    1%,
    3%,
    5% {
        opacity: 0.3;
        transform: translateX(2px);
    }
    2%,
    4% {
        opacity: 0.3;
        transform: translateX(-2px);
    }
}

/* Glow effects */
.glow-box {
    box-shadow:
        0 0 20px rgba(0, 255, 65, 0.2),
        0 0 40px rgba(0, 255, 65, 0.1),
        inset 0 0 10px rgba(0, 255, 65, 0.1);
    animation: pulse-glow 2s ease-in-out infinite alternate;
}

@keyframes pulse-glow {
    from {
        box-shadow:
            0 0 20px rgba(0, 255, 65, 0.2),
            0 0 40px rgba(0, 255, 65, 0.1),
            inset 0 0 10px rgba(0, 255, 65, 0.1);
    }
    to {
        box-shadow:
            0 0 30px rgba(0, 255, 65, 0.4),
            0 0 60px rgba(0, 255, 65, 0.2),
            inset 0 0 15px rgba(0, 255, 65, 0.2);
    }
}

/* Scanning line animation */
@keyframes scan {
    0% {
        transform: translateY(-100%);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        transform: translateY(100%);
        opacity: 0;
    }
}

.animate-scan {
    animation: scan 3s linear infinite;
}

/* Sliding animation for card */
@keyframes slide {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

.animate-slide {
    animation: slide 8s linear infinite;
}

/* Glitch text effect */
.glitch-text {
    position: relative;
    text-shadow:
        0 0 10px rgba(0, 255, 65, 0.8),
        0 0 20px rgba(0, 255, 65, 0.6),
        0 0 30px rgba(0, 255, 65, 0.4);
    animation: text-flicker 0.15s infinite linear alternate;
}

@keyframes text-flicker {
    0% {
        opacity: 1;
    }
    98% {
        opacity: 1;
    }
    99% {
        opacity: 0.98;
    }
    100% {
        opacity: 1;
    }
}

.glitch-text::before,
.glitch-text::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}

.glitch-text::before {
    animation: glitch-1 0.3s infinite;
    color: #ff00ff;
    z-index: -2;
}

.glitch-text::after {
    animation: glitch-2 0.3s infinite;
    color: #00ffff;
    z-index: -3;
}

@keyframes glitch-1 {
    0%,
    14%,
    15%,
    49%,
    50%,
    99%,
    100% {
        transform: translate(0);
    }
    1%,
    13% {
        transform: translate(-2px, 1px);
    }
    16%,
    48% {
        transform: translate(1px, -1px);
    }
    51%,
    98% {
        transform: translate(-1px, 2px);
    }
}

@keyframes glitch-2 {
    0%,
    20%,
    21%,
    62%,
    63%,
    99%,
    100% {
        transform: translate(0);
    }
    1%,
    19% {
        transform: translate(2px, -1px);
    }
    22%,
    61% {
        transform: translate(-1px, 1px);
    }
    64%,
    98% {
        transform: translate(1px, -2px);
    }
}

/* Hacker logo hover effect */
.hacker-logo:hover .glitch-text {
    animation: intense-flicker 0.1s infinite linear alternate;
}

@keyframes intense-flicker {
    0% {
        opacity: 1;
    }
    25% {
        opacity: 0.8;
    }
    50% {
        opacity: 1;
    }
    75% {
        opacity: 0.9;
    }
    100% {
        opacity: 1;
    }
}

/* Terminal cursor effect */
.terminal-cursor::after {
    content: '_';
    color: #00ff41;
    animation: cursor-blink 1s infinite;
}

@keyframes cursor-blink {
    0%,
    50% {
        opacity: 1;
    }
    51%,
    100% {
        opacity: 0;
    }
}
</style>
