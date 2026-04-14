<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0"
        >
            <div
                v-if="showPrompt"
                class="fixed bottom-0 left-0 right-0 z-[9998]"
                style="padding-bottom: max(16px, env(safe-area-inset-bottom));"
            >
                <div class="mx-3 mb-2 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                    <!-- iOS manual instructions -->
                    <div v-if="isIos" class="p-4">
                        <div class="flex items-start gap-3">
                            <img src="/taxmaster-icon.png" alt="TaxMaster" class="w-11 h-11 rounded-xl flex-shrink-0">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 text-sm">Install TaxMaster</p>
                                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                    Tap
                                    <svg class="inline w-4 h-4 text-blue-500 align-middle mx-0.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l-4 4h3v8h2V6h3l-4-4zm-7 14v4h14v-4h-2v2H7v-2H5z"/>
                                    </svg>
                                    then <strong>"Add to Home Screen"</strong>
                                </p>
                            </div>
                            <button @click="dismiss" class="text-gray-400 hover:text-gray-600 p-1 -mr-1 -mt-1 rounded-full">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Arrow pointing to Share button -->
                        <div class="flex justify-center mt-3">
                            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                                Tap the share button at the bottom of Safari
                            </div>
                        </div>
                    </div>

                    <!-- Android / Chrome native prompt -->
                    <div v-else class="p-4 flex items-center gap-3">
                        <img src="/taxmaster-icon.png" alt="TaxMaster" class="w-11 h-11 rounded-xl flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm">Install TaxMaster</p>
                            <p class="text-xs text-gray-500 mt-0.5">Add to home screen for instant access</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button
                                @click="dismiss"
                                class="text-gray-500 text-xs font-medium px-3 py-2 rounded-xl hover:bg-gray-100 transition-colors"
                            >
                                Later
                            </button>
                            <button
                                @click="install"
                                class="bg-blue-600 text-white text-xs font-semibold px-4 py-2 rounded-xl hover:bg-blue-700 active:scale-95 transition-all"
                            >
                                Install
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const showPrompt = ref(false);
const isIos = ref(false);
let deferredPrompt = null;

const DISMISS_KEY = 'pwa-install-dismissed';
const DISMISS_DURATION_MS = 7 * 24 * 60 * 60 * 1000; // 7 days

onMounted(() => {
    // Already installed — do nothing
    if (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true
    ) {
        return;
    }

    // Respect user's previous dismissal
    const dismissed = localStorage.getItem(DISMISS_KEY);
    if (dismissed && Date.now() - parseInt(dismissed) < DISMISS_DURATION_MS) {
        return;
    }

    const ua = window.navigator.userAgent.toLowerCase();
    const isIosDevice = /iphone|ipad|ipod/.test(ua) && !window.MSStream;
    const isSafari = /safari/.test(ua) && !/chrome|crios|fxios/.test(ua);

    if (isIosDevice && isSafari) {
        // iOS Safari: show manual instructions banner
        isIos.value = true;
        showPrompt.value = true;
        return;
    }

    // Android/Chrome: wait for native beforeinstallprompt
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        showPrompt.value = true;
    });
});

function install() {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(() => {
        deferredPrompt = null;
        showPrompt.value = false;
    });
}

function dismiss() {
    showPrompt.value = false;
    localStorage.setItem(DISMISS_KEY, Date.now().toString());
}
</script>
