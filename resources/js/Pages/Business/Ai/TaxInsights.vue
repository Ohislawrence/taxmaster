<template>
    <BusinessLayout>
        <Head title="Tax Insights & AI Recommendations" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900">Tax Insights</h1>
                        <p class="text-gray-600 mt-2">AI-powered recommendations and tax optimization tips</p>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5h.01M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <p class="text-gray-600 text-sm font-medium">Active AI Config</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ props.aiConfigured ? 'Yes' : 'No' }}</p>
                    <p class="text-xs text-gray-500 mt-2">{{ aiProvider }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <p class="text-gray-600 text-sm font-medium">Tax Returns Analyzed</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ analyzedReturns }}</p>
                    <p class="text-xs text-gray-500 mt-2">This year</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <p class="text-gray-600 text-sm font-medium">Recommendations</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ recommendations.length }}</p>
                    <p class="text-xs text-gray-500 mt-2">Pending review</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                    <p class="text-gray-600 text-sm font-medium">Potential Savings</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(potentialSavings) }}</p>
                    <p class="text-xs text-gray-500 mt-2">Estimated annual</p>
                </div>
            </div>

            <!-- Configuration Alert -->
            <div v-if="!props.aiConfigured" class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-lg font-medium text-blue-900">Configure AI to Get Started</h3>
                        <p class="text-blue-700 mt-2">Set up your AI configuration in Settings to unlock AI-powered tax analysis and recommendations.</p>
                        <Link href="/business/settings" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
                            Go to Settings
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Key Recommendations -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Compliance Alerts -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Compliance Alerts</h2>
                            <span v-if="complianceAlerts.length > 0" class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">{{ complianceAlerts.length }}</span>
                        </div>

                        <div v-if="complianceAlerts.length > 0" class="space-y-4">
                            <div v-for="alert in complianceAlerts" :key="alert.id" class="border border-red-200 bg-red-50 rounded-lg p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="font-semibold text-red-900">{{ alert.title }}</h4>
                                        <p class="text-red-800 text-sm mt-1">{{ alert.message }}</p>
                                        <p class="text-red-700 text-xs mt-2 font-medium">Deadline: {{ alert.deadline }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="font-medium">No compliance alerts</p>
                            <p class="text-sm">You're up to date with all requirements</p>
                        </div>
                    </div>

                    <!-- Tax Optimization Tips -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Optimization Tips</h2>
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">{{ optimizationTips.length }}</span>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(tip, index) in optimizationTips" :key="index" class="border border-green-200 bg-green-50 rounded-lg p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="font-semibold text-green-900">{{ tip.title }}</h4>
                                        <p class="text-green-800 text-sm mt-1">{{ tip.description }}</p>
                                        <p class="text-green-700 text-xs mt-2 font-medium">Potential Savings: ₦{{ formatCurrency(tip.savings) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Analyses -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Recent AI Analyses</h2>
                        <div v-if="recentAnalyses.length > 0" class="space-y-4">
                            <div v-for="analysis in recentAnalyses" :key="analysis.id" class="border rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">{{ analysis.tax_return_year }} Tax Return</p>
                                        <p class="text-gray-600 text-sm mt-1">Analyzed on {{ formatDate(analysis.analyzed_at) }}</p>
                                        <div class="mt-2 flex gap-2">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded font-medium">{{ analysis.status }}</span>
                                        </div>
                                    </div>
                                    <Link :href="route('business.tax-returns.show', analysis.tax_return_id)" class="text-blue-600 hover:text-blue-700 font-medium">
                                        View Details →
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            <p class="font-medium">No analyses yet</p>
                            <p class="text-sm">Submit tax returns to get AI-powered analysis</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Quick Actions -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <Link href="/business/ai/chat" class="block w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition text-center">
                                💬 Start AI Chat
                            </Link>
                            <Link href="/business/tax-returns" class="block w-full px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition text-center">
                                📄 Submit Tax Return
                            </Link>
                            <Link href="/business/settings" class="block w-full px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition text-center">
                                ⚙️ AI Settings
                            </Link>
                        </div>
                    </div>

                    <!-- Key Metrics -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">This Year</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Total Deductions</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(totalDeductions) }}</p>
                            </div>
                            <div class="border-t pt-4">
                                <p class="text-gray-600 text-sm font-medium">Tax Liability</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(taxLiability) }}</p>
                            </div>
                            <div class="border-t pt-4">
                                <p class="text-gray-600 text-sm font-medium">Effective Tax Rate</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ effectiveTaxRate }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tips Box -->
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg border border-blue-200 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">💡 Pro Tips</h3>
                        <ul class="space-y-3 text-sm text-gray-700">
                            <li class="flex gap-2">
                                <span class="flex-shrink-0 text-blue-600 font-bold">•</span>
                                <span>Keep detailed records of all deductible expenses</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="flex-shrink-0 text-blue-600 font-bold">•</span>
                                <span>Review AI recommendations quarterly</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="flex-shrink-0 text-blue-600 font-bold">•</span>
                                <span>Use the chat feature for specific questions</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="flex-shrink-0 text-blue-600 font-bold">•</span>
                                <span>Stay compliant with payment deadlines</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

const props = defineProps({
    business: Object,
    aiConfigured: Boolean,
    aiError: String,
});

const analyzedReturns = ref(0);
const potentialSavings = ref(0);
const totalDeductions = ref(0);
const taxLiability = ref(0);
const effectiveTaxRate = ref(0);

const recommendations = ref([]);
const complianceAlerts = ref([]);
const optimizationTips = ref([]);
const recentAnalyses = ref([]);

const aiProvider = ref('Not configured');

onMounted(async () => {
    // Load insights if AI is configured
    if (props.aiConfigured) {
        aiProvider.value = 'Configured & Active';
        loadInsights();
    } else {
        aiProvider.value = props.aiError || 'Not configured';
    }
});

const loadInsights = async () => {
    // These would be loaded from the backend
    // For now, using sample data to demonstrate the UI

    analyzedReturns.value = 2;
    potentialSavings.value = 450000;
    totalDeductions.value = 2850000;
    taxLiability.value = 3500000;
    effectiveTaxRate.value = 12.5;

    recommendations.value = [
        { id: 1, title: 'Schedule C Income Optimization', description: 'Explore opportunities for income splitting strategies' },
    ];

    complianceAlerts.value = [
        { id: 1, title: 'Q1 Tax Payment Due', message: 'Your quarterly tax payment for Q1 2026 is approaching.', deadline: 'March 31, 2026' },
        { id: 2, title: 'Documentation Review', message: 'Recent deductions need supporting documentation for audit purposes.', deadline: 'April 15, 2026' },
    ];

    optimizationTips.value = [
        {
            title: 'Maximize 401(k) Contributions',
            description: 'You have unused contribution room. Consider maximizing to reduce taxable income.',
            savings: 125000
        },
        {
            title: 'Home Office Deduction',
            description: 'You may qualify for additional home office deductions based on current setup.',
            savings: 85000
        },
        {
            title: 'Equipment Depreciation',
            description: 'Several business assets are eligible for accelerated depreciation.',
            savings: 240000
        },
    ];

    recentAnalyses.value = [
        {
            id: 1,
            tax_return_id: 1,
            tax_return_year: 2025,
            status: 'Analyzed',
            analyzed_at: '2026-02-20 10:30:00'
        },
        {
            id: 2,
            tax_return_id: 2,
            tax_return_year: 2024,
            status: 'Analyzed',
            analyzed_at: '2026-02-18 14:15:00'
        },
    ];
};

const formatCurrency = (value) => {
    if (!value) return '0.00';
    return parseFloat(value).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
