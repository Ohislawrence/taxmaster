<template>
    <div class="bg-gradient-to-br from-blue-500 via-blue-600 to-purple-600 rounded-lg shadow-lg p-8 text-white">
        <!-- Header -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold mb-2">Power Your Tax Strategy with AI</h3>
                <p class="text-blue-100">Get intelligent insights and optimization recommendations</p>
            </div>
            <div class="p-3 bg-white/20 rounded-xl backdrop-blur">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Feature 1 -->
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 border border-white/20 hover:bg-white/15 transition">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                    </svg>
                    <h4 class="font-semibold text-sm">AI Analytics</h4>
                </div>
                <p class="text-sm text-blue-100">Analyze returns with AI-powered insights</p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 border border-white/20 hover:bg-white/15 transition">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h.01a1 1 0 110 2H12zm-2.75 9a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0zm12-1a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                    </svg>
                    <h4 class="font-semibold text-sm">Tax Optimization</h4>
                </div>
                <p class="text-sm text-blue-100">Get recommendations to save on taxes</p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 border border-white/20 hover:bg-white/15 transition">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0L10 9.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                    <h4 class="font-semibold text-sm">Compliance Alerts</h4>
                </div>
                <p class="text-sm text-blue-100">Never miss a tax deadline or requirement</p>
            </div>
        </div>

        <!-- Status and Actions -->
        <div v-if="!aiConfigured" class="bg-white/10 backdrop-blur rounded-lg p-4 border border-white/20 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium text-sm">Setup Required</span>
            </div>
            <p class="text-blue-100 text-sm mb-4">Configure your AI settings to unlock these powerful features</p>
            <div class="flex gap-2">
                <Link href="/business/settings" class="flex-1 px-4 py-2 bg-white text-blue-600 rounded-lg font-medium text-sm hover:bg-gray-100 text-center transition">
                    Go to Settings
                </Link>
                <button @click="showSetupGuide = true" class="flex-1 px-4 py-2 bg-white/20 hover:bg-white/30 border border-white/30 rounded-lg font-medium text-sm transition">
                    View Setup Guide
                </button>
            </div>
        </div>

        <div v-else class="bg-white/10 backdrop-blur rounded-lg p-4 border border-white/20 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-medium text-sm">AI Features Active</span>
                </div>
                <span class="text-xs bg-white/20 px-2 py-1 rounded">{{ aiProvider }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <Link href="/business/ai/chat" class="px-4 py-3 bg-white text-blue-600 rounded-lg font-medium hover:bg-gray-100 transition text-center flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Start AI Chat
            </Link>
            <Link href="/business/ai/insights" class="px-4 py-3 bg-white/20 hover:bg-white/30 border border-white rounded-lg font-medium transition text-center flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5h.01M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                View Insights
            </Link>
        </div>

        <!-- Setup Guide Modal -->
        <AiSetupGuideComponent v-if="showSetupGuide && false" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage();
const aiConfigured = ref(false);
const aiProvider = ref('');
const showSetupGuide = ref(false);

onMounted(async () => {
    // Check if AI is configured by attempting to access chat page
    try {
        const response = await fetch('/business/ai/chat', {
            method: 'HEAD',
            headers: {
                'Accept': 'application/json',
            }
        }).catch(() => ({ status: 404 }));

        if (response.status !== 404) {
            aiConfigured.value = true;
            aiProvider.value = 'Deepseek';
        }
    } catch (error) {
        // AI not configured
    }
});
</script>
