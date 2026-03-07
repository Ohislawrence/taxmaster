<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-if="showBanner"
            class="fixed bottom-0 left-0 right-0 z-[60] bg-white border-t border-gray-200 shadow-2xl"
        >
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] opacity-40 pointer-events-none"></div>
            <div class="relative max-w-3xl mx-auto px-4 py-5 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-sm text-gray-700">
                            <span class="font-semibold text-blue-600">Cookie Notice:</span>
                            We use cookies and similar technologies to enhance your experience, analyze usage, and assist with tax calculations.
                            By continuing to use TaxMaster, you consent to our use of cookies in accordance with our
                            <a href="/privacy" class="text-blue-600 hover:text-blue-800 underline">Privacy Policy</a>,
                            <a href="/cookie-policy" class="text-blue-600 hover:text-blue-800 underline">Cookie Policy</a>,
                            and the Nigeria Data Protection Act (NDPA) 2023.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <button
                            @click="acceptEssential"
                            class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg border border-gray-200 shadow-sm transition"
                        >
                            Essential Only
                        </button>
                        <button
                            @click="acceptAll"
                            class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-lg transition"
                        >
                            Accept All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const showBanner = ref(false);

const COOKIE_CONSENT_KEY = 'taxmaster_cookie_consent';

onMounted(() => {
    const consent = localStorage.getItem(COOKIE_CONSENT_KEY);
    if (!consent) {
        // Show banner after a short delay for better UX
        setTimeout(() => {
            showBanner.value = true;
        }, 1000);
    }
});

const acceptAll = () => {
    localStorage.setItem(COOKIE_CONSENT_KEY, JSON.stringify({
        essential: true,
        analytics: true,
        accepted_at: new Date().toISOString(),
    }));
    showBanner.value = false;
};

const acceptEssential = () => {
    localStorage.setItem(COOKIE_CONSENT_KEY, JSON.stringify({
        essential: true,
        analytics: false,
        accepted_at: new Date().toISOString(),
    }));
    showBanner.value = false;
};
</script>
