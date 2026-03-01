<template>
    <div v-if="showWidget && !progress?.dismissed" class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white">Get Started with TaxMaster</h3>
                    <p class="text-sm text-blue-100">{{ completionPercentage }}% Complete</p>
                </div>
            </div>
            <button @click="handleDismiss" class="text-white/60 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Progress bar -->
        <div class="mb-4">
            <div class="w-full bg-white/20 rounded-full h-2">
                <div 
                    class="h-2 rounded-full bg-white transition-all duration-300"
                    :style="{ width: completionPercentage + '%' }"
                ></div>
            </div>
            <p class="text-xs text-blue-100 mt-2">{{ completedStepsCount }} of {{ totalStepsCount }} steps completed</p>
        </div>

        <!-- Next step -->
        <div v-if="nextIncompleteStep" class="mb-4 bg-white/10 rounded-lg p-3">
            <p class="text-xs text-blue-100 mb-1">Next step:</p>
            <p class="text-sm font-medium">{{ nextIncompleteStep.title }}</p>
        </div>

        <!-- CTA -->
        <div class="flex gap-2">
            <Link
                :href="route('business.get-started.index')"
                class="flex-1 inline-block px-4 py-2 bg-white text-blue-600 font-medium rounded-lg hover:bg-blue-50 transition text-center text-sm"
            >
                View Checklist
            </Link>
            <button 
                @click="handleSnooze"
                class="px-4 py-2 bg-white/20 text-white font-medium rounded-lg hover:bg-white/30 transition text-sm"
            >
                Remind Later
            </button>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useGetStarted } from '@/composables/useGetStarted';

const props = defineProps({
    showWidget: {
        type: Boolean,
        default: true,
    },
});

const { 
    progress, 
    completionPercentage, 
    completedStepsCount, 
    totalStepsCount,
    nextIncompleteStep,
    dismissGuide,
} = useGetStarted();

const handleDismiss = async () => {
    await dismissGuide(0); // Permanently dismiss
    window.location.reload();
};

const handleSnooze = async () => {
    await dismissGuide(720); // Remind in 12 hours
    window.location.reload();
};
</script>
