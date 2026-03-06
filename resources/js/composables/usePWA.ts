import { ref, onMounted, onUnmounted } from 'vue';

const deferredPrompt = ref<any>(null);
const isInstallable = ref(false);

export function usePWA() {
    const capturePrompt = (e: Event) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt.value = e;
        // Update UI to notify the user they can install the PWA
        isInstallable.value = true;
        console.log('PWA Install prompt captured');
    };

    const installPWA = async () => {
        if (!deferredPrompt.value) {
            console.warn('Install prompt not available');
            return false;
        }

        // Show the prompt
        deferredPrompt.value.prompt();

        // Wait for the user to respond to the prompt
        const { outcome } = await deferredPrompt.value.userChoice;
        console.log(`User response to the install prompt: ${outcome}`);

        // We've used the prompt, and can't use it again, throw it away
        deferredPrompt.value = null;
        isInstallable.value = false;
        
        return outcome === 'accepted';
    };

    const initPWA = () => {
        window.addEventListener('beforeinstallprompt', capturePrompt);
        
        window.addEventListener('appinstalled', () => {
            // Log install to analytics
            console.log('PWA was installed');
            deferredPrompt.value = null;
            isInstallable.value = false;
        });
    };

    return {
        isInstallable,
        installPWA,
        initPWA
    };
}
