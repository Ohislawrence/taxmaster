<template>
    <BusinessLayout>
        <Head title="Get Started" />

        <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-600">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Get Started with TaxMaster</h1>
                            <p class="text-gray-600 mt-1">Follow these steps to unlock the full potential of your account</p>
                        </div>
                    </div>
                </div>

                <!-- Completion Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <!-- Progress Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-600">Overall Progress</span>
                            <span class="text-2xl font-bold text-blue-600">{{ completionPercentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                                :class="[progressColor, 'h-2 rounded-full transition-all duration-300']"
                                :style="{ width: completionPercentage + '%' }"
                            ></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">{{ completedStepsCount }} of {{ totalStepsCount }} steps</p>
                    </div>

                    <!-- Completed Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Completed</p>
                                <p class="text-2xl font-bold text-gray-900">{{ completedStepsCount }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Next Step Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6" v-if="nextIncompleteStep">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Next Step</p>
                                <p class="text-sm font-semibold text-gray-900 line-clamp-1">{{ nextIncompleteStep.title }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Completion Message -->
                    <div v-else class="bg-gradient-to-br from-green-400 to-green-600 rounded-lg shadow-sm p-6 md:col-span-3">
                        <div class="flex items-center gap-3 text-white">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold">Congratulations! 🎉</p>
                                <p class="text-sm text-white/90">You've completed all onboarding steps. You're ready to make the most of TaxMaster!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Steps by Priority -->
                <div class="space-y-8">
                    <!-- High Priority Steps -->
                    <div v-if="stepsByPriority.high.length > 0">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="h-1 w-8 bg-red-500 rounded"></div>
                            <h2 class="text-lg font-semibold text-gray-900">Essential Setup (Start Here)</h2>
                        </div>
                        <div class="space-y-4">
                            <StepCard 
                                v-for="step in stepsByPriority.high"
                                :key="step.id"
                                :step="step"
                                @toggle="handleStepToggle"
                            />
                        </div>
                    </div>

                    <!-- Medium Priority Steps -->
                    <div v-if="stepsByPriority.medium.length > 0">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="h-1 w-8 bg-yellow-500 rounded"></div>
                            <h2 class="text-lg font-semibold text-gray-900">Optimize Your Setup</h2>
                        </div>
                        <div class="space-y-4">
                            <StepCard 
                                v-for="step in stepsByPriority.medium"
                                :key="step.id"
                                :step="step"
                                @toggle="handleStepToggle"
                            />
                        </div>
                    </div>

                    <!-- Low Priority Steps -->
                    <div v-if="stepsByPriority.low.length > 0">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="h-1 w-8 bg-blue-500 rounded"></div>
                            <h2 class="text-lg font-semibold text-gray-900">Additional Resources</h2>
                        </div>
                        <div class="space-y-4">
                            <StepCard 
                                v-for="step in stepsByPriority.low"
                                :key="step.id"
                                :step="step"
                                @toggle="handleStepToggle"
                            />
                        </div>
                    </div>
                </div>

                <!-- Bottom CTA -->
                <div class="mt-12 bg-white rounded-lg shadow-sm p-8 text-center">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Need Help?</h3>
                    <p class="text-gray-600 mb-6">Check out our documentation or contact our support team for guidance.</p>
                    <div class="flex gap-4 justify-center flex-wrap">
                        <a href="https://docs.taxmaster.app" target="_blank" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:border-gray-400 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Documentation
                        </a>
                        <a href="mailto:support@taxmaster.app" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import StepCard from '@/Components/GetStarted/StepCard.vue';
import { useGetStarted } from '@/composables/useGetStarted';

const {
    completionPercentage,
    isCompleted,
    completedStepsCount,
    totalStepsCount,
    stepsByPriority,
    nextIncompleteStep,
    progressColor,
    completeStep,
} = useGetStarted();

const handleStepToggle = async (stepId, isCompleted) => {
    if (!isCompleted) {
        await completeStep(stepId);
    }
};
</script>
