<template>
    <div class="space-y-6">
        <!-- AI Analysis Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-600">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">AI Tax Analysis</h3>
                        <p class="text-sm text-gray-600">Get expert insights on your tax return</p>
                    </div>
                </div>
                <button 
                    @click="analyze"
                    :disabled="analyzing"
                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2"
                >
                    <svg v-if="!analyzing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ analyzing ? 'Analyzing...' : 'Analyze with AI' }}
                </button>
            </div>

            <!-- Analysis Result -->
            <div v-if="analysis" class="mt-6 p-4 bg-gray-50 rounded-lg text-gray-700 whitespace-pre-wrap">
                {{ analysis }}
            </div>

            <div v-else-if="!analyzing" class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200 text-blue-700">
                <p class="font-medium mb-2">Get AI-powered insights:</p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li>Detailed tax liability analysis</li>
                    <li>Deduction opportunities</li>
                    <li>Compliance recommendations</li>
                    <li>Tax optimization strategies</li>
                </ul>
            </div>
        </div>

        <!-- Tax Optimization Recommendations -->
        <div v-if="showOptimization" class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg shadow-lg p-6 border-l-4 border-green-600">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Tax Optimization Tips</h3>
                        <p class="text-sm text-gray-600">Strategies to minimize your tax liability</p>
                    </div>
                </div>
                <button 
                    @click="getRecommendations"
                    :disabled="loadingRecommendations"
                    class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2"
                >
                    <svg v-if="!loadingRecommendations" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5h.01"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ loadingRecommendations ? 'Loading...' : 'Get Recommendations' }}
                </button>
            </div>

            <!-- Recommendations Result -->
            <div v-if="recommendations" class="mt-6 p-4 bg-white rounded-lg text-gray-700 whitespace-pre-wrap">
                {{ recommendations }}
            </div>

            <div v-else-if="!loadingRecommendations" class="mt-6 p-4 bg-green-100 rounded-lg border border-green-300 text-green-700">
                <p class="font-medium">AI will provide personalized recommendations to:</p>
                <ul class="list-disc list-inside space-y-1 text-sm mt-2">
                    <li>Minimize tax liability legally</li>
                    <li>Optimize deductions</li>
                    <li>Improve cash flow</li>
                    <li>Plan for future periods</li>
                </ul>
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border-l-4 border-red-600 p-4 rounded-lg">
            <p class="text-red-700 font-medium">{{ error }}</p>
        </div>

        <!-- Info Note -->
        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded-lg text-blue-700 text-sm">
            <p class="font-medium mb-1">💡 AI Disclaimer</p>
            <p>AI analysis is for informational purposes. Always consult with a qualified tax professional for official tax advice and decisions.</p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    taxReturnId: Number,
});

const analyzing = ref(false);
const loadingRecommendations = ref(false);
const analysis = ref(null);
const recommendations = ref(null);
const error = ref(null);
const showOptimization = ref(true);

const analyze = async () => {
    analyzing.value = true;
    error.value = null;

    try {
        const response = await fetch(`/business/ai/tax-returns/${props.taxReturnId}/analyze`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
            },
        });

        const data = await response.json();

        if (data.success) {
            analysis.value = data.analysis;
        } else {
            error.value = data.error || 'Failed to analyze tax return';
        }
    } catch (err) {
        console.error('Error:', err);
        error.value = 'An error occurred while analyzing your tax return';
    } finally {
        analyzing.value = false;
    }
};

const getRecommendations = async () => {
    loadingRecommendations.value = true;
    error.value = null;

    try {
        const response = await fetch(`/business/ai/tax-returns/${props.taxReturnId}/optimize`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
            },
        });

        const data = await response.json();

        if (data.success) {
            recommendations.value = data.recommendations;
        } else {
            error.value = data.error || 'Failed to get recommendations';
        }
    } catch (err) {
        console.error('Error:', err);
        error.value = 'An error occurred while getting recommendations';
    } finally {
        loadingRecommendations.value = false;
    }
};
</script>
