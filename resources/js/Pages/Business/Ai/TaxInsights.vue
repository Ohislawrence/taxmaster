<template>
    <BusinessLayout>
        <Head title="Tax Insights & AI Recommendations" />

        <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                            Tax Insights
                        </h1>
                        <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white text-xs font-bold">
                                AI
                            </span>
                            AI-powered recommendations and tax optimization tips
                        </p>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5h.01M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm p-6 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Tax Returns Analyzed</p>
                                <p class="text-3xl lg:text-4xl font-bold text-gray-900 mt-2">{{ analyzedReturns }}</p>
                                <p class="text-xs text-gray-500 mt-2">This year</p>
                            </div>
                            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm p-6 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Recommendations</p>
                                <p class="text-3xl lg:text-4xl font-bold text-gray-900 mt-2">{{ recommendations.length }}</p>
                                <p class="text-xs text-gray-500 mt-2">Pending review</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm p-6 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Potential Savings</p>
                                <p class="text-3xl lg:text-4xl font-bold text-green-600 mt-2">₦{{ formatCurrency(potentialSavings) }}</p>
                                <p class="text-xs text-gray-500 mt-2">Estimated annual</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuration Alert -->
                <div v-if="!props.aiConfigured" class="rounded-2xl border border-blue-200 bg-gradient-to-r from-blue-50/80 to-indigo-50/80 backdrop-blur-sm p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-blue-900">Configure AI to Get Started</h3>
                            <p class="text-blue-700 mt-1">Set up your AI configuration in Settings to unlock AI-powered tax analysis and recommendations.</p>
                        </div>
                        <Link href="/business/settings" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-medium hover:from-blue-700 hover:to-indigo-700 transition-all shadow-sm text-center whitespace-nowrap">
                            Go to Settings
                        </Link>
                    </div>
                </div>

                <!-- Free AI Access Notice -->
                <div v-if="props.aiConfigured" class="rounded-2xl border border-green-200 bg-gradient-to-r from-green-50/80 to-emerald-50/80 backdrop-blur-sm p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-green-900">✨ AI Insights Available for All Plans</h3>
                            <p class="text-green-700 mt-1">
                                AI-powered tax insights and chat are available on all subscription plans including Free.
                                Upgrade to <strong class="font-semibold">Professional</strong> or <strong class="font-semibold">Enterprise</strong> for advanced AI optimization features.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div v-if="props.aiConfigured" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Key Recommendations -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Compliance Alerts -->
                        <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white flex items-center justify-between">
                                <h2 class="text-base font-semibold text-gray-900">Compliance Alerts</h2>
                                <span v-if="complianceAlerts.length > 0" class="px-2.5 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">{{ complianceAlerts.length }}</span>
                            </div>
                            <div class="p-6">
                                <div v-if="complianceAlerts.length > 0" class="space-y-4">
                                    <div v-for="alert in complianceAlerts" :key="alert.id" class="border border-red-200 bg-red-50/50 rounded-xl p-4">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-red-900">{{ alert.title }}</h4>
                                                <p class="text-red-800 text-sm mt-1">{{ alert.message }}</p>
                                                <div class="flex items-center gap-3 mt-2">
                                                    <span class="text-red-700 text-xs font-medium">Deadline: {{ alert.deadline }}</span>
                                                    <button class="text-red-600 text-xs font-medium hover:text-red-700">Take Action →</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-8">
                                    <div class="w-16 h-16 mx-auto bg-green-50 rounded-2xl flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="font-medium text-gray-900">No compliance alerts</p>
                                    <p class="text-sm text-gray-500 mt-1">You're up to date with all requirements</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tax Optimization Tips -->
                        <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white flex items-center justify-between">
                                <h2 class="text-base font-semibold text-gray-900">Optimization Tips</h2>
                                <span class="px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">{{ optimizationTips.length }}</span>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div v-for="(tip, index) in optimizationTips" :key="index" class="border border-green-200 bg-green-50/50 rounded-xl p-4">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-green-900">{{ tip.title }}</h4>
                                                <p class="text-green-800 text-sm mt-1">{{ tip.description }}</p>
                                                <div class="mt-2 flex items-center gap-3">
                                                    <span class="text-green-700 text-xs font-medium">Potential Savings: ₦{{ formatCurrency(tip.savings) }}</span>
                                                    <button class="text-green-600 text-xs font-medium hover:text-green-700">Learn More →</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Analyses -->
                        <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                                <h2 class="text-base font-semibold text-gray-900">Recent AI Analyses</h2>
                            </div>
                            <div class="p-6">
                                <div v-if="recentAnalyses.length > 0" class="space-y-3">
                                    <div v-for="analysis in recentAnalyses" :key="analysis.id" class="border border-gray-200 rounded-xl p-4 hover:shadow-md hover:border-gray-300 transition-all">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-semibold text-gray-900">{{ analysis.tax_return_year }} Tax Return</span>
                                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">{{ analysis.status }}</span>
                                                </div>
                                                <p class="text-gray-500 text-xs">Analyzed on {{ formatDate(analysis.analyzed_at) }}</p>
                                            </div>
                                            <Link
                                                :href="analysis.tax_return_id ? route('business.tax-returns.show', analysis.tax_return_id) : '/business/tax-returns'"
                                                class="text-blue-600 hover:text-blue-700 font-medium text-sm inline-flex items-center gap-1"
                                            >
                                                View Details
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-8">
                                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <p class="font-medium text-gray-900">No analyses yet</p>
                                    <p class="text-sm text-gray-500 mt-1">Submit tax returns to get AI-powered analysis</p>
                                    <Link href="/business/tax-returns" class="inline-block mt-4 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-medium hover:from-blue-700 hover:to-indigo-700 transition-all shadow-sm">
                                        Go to Tax Returns
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Quick Actions -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                                <h3 class="text-base font-semibold text-gray-900">Quick Actions</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <Link href="/business/ai/chat" class="block w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-medium transition-all shadow-sm text-center">
                                        Start AI Chat
                                    </Link>
                                    <Link href="/business/tax-returns" class="block w-full px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all shadow-sm text-center">
                                        Check Tax Return
                                    </Link>
                                    <Link href="/business/settings" class="block w-full px-4 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-xl font-medium transition-all shadow-sm text-center">
                                        Business Settings
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Key Metrics -->
                        <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                                <h3 class="text-base font-semibold text-gray-900">This Year</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Deductions</p>
                                        <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(totalDeductions) }}</p>
                                    </div>
                                    <div class="border-t border-gray-200 pt-4">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tax Liability</p>
                                        <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(taxLiability) }}</p>
                                    </div>
                                    <div class="border-t border-gray-200 pt-4">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Effective Tax Rate</p>
                                        <p class="text-3xl font-bold text-blue-600 mt-1">{{ effectiveTaxRate }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tips Box -->
                        <div class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50/50 to-purple-50/50 p-6">
                            <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="text-xl">💡</span>
                                Pro Tips
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex gap-2">
                                    <span class="flex-shrink-0 text-blue-600 font-bold">•</span>
                                    <span>Keep detailed records of all deductible expenses and capital allowances</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="flex-shrink-0 text-blue-600 font-bold">•</span>
                                    <span>Review AI recommendations quarterly to stay tax-optimized</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="flex-shrink-0 text-blue-600 font-bold">•</span>
                                    <span>Use AI chat for specific Nigerian tax questions (PAYE, VAT, WHT, CIT)</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="flex-shrink-0 text-blue-600 font-bold">•</span>
                                    <span>File VAT returns by the 21st and PAYE by the 10th of each month</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="flex-shrink-0 text-blue-600 font-bold">•</span>
                                    <span>Claim input VAT on qualifying business expenses to reduce liability</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="flex-shrink-0 text-blue-600 font-bold">•</span>
                                    <span>Upgrade to Professional for advanced AI tax optimization features</span>
                                </li>
                            </ul>
                        </div>
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
    business: {
        type: Object,
        required: true
    },
    aiConfigured: {
        type: Boolean,
        default: false
    },
    aiError: {
        type: String,
        default: null
    },
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
        { id: 1, title: 'VAT Input Tax Optimization', description: 'Review opportunities to claim additional input VAT on business expenses' },
    ];

    complianceAlerts.value = [
        { id: 1, title: 'VAT Filing Due', message: 'Your monthly VAT return for April 2026 is approaching.', deadline: 'April 21, 2026' },
        { id: 2, title: 'PAYE Remittance Due', message: 'Staff PAYE for April 2026 must be remitted to FIRS by the deadline.', deadline: 'May 10, 2026' },
        { id: 3, title: 'WHT Documentation', message: 'Recent WHT deductions need supporting documentation and certificates.', deadline: 'May 15, 2026' },
    ];

    optimizationTips.value = [
        {
            title: 'Maximize Pension/RSA Contributions',
            description: 'Increase pension contributions up to 20% of annual emoluments to reduce taxable income under PITA.',
            savings: 180000
        },
        {
            title: 'Capital Allowances on Assets',
            description: 'Claim capital allowances on qualifying equipment and vehicles purchased this year.',
            savings: 340000
        },
        {
            title: 'Research & Development Incentives',
            description: 'Eligible R&D expenses can qualify for enhanced tax deductions under CITA Section 27.',
            savings: 220000
        },
        {
            title: 'VAT Input Tax Recovery',
            description: 'Review business expenses to ensure all eligible input VAT is claimed on your returns.',
            savings: 125000
        },
        {
            title: 'Export Incentives & Exemptions',
            description: 'Zero-rated and exempt transactions may reduce your VAT liability significantly.',
            savings: 95000
        },
    ];

    recentAnalyses.value = [
        {
            id: 1,
            tax_return_id: null,
            tax_return_year: 2025,
            status: 'Analyzed',
            analyzed_at: '2026-03-20 10:30:00'
        },
        {
            id: 2,
            tax_return_id: null,
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

<style scoped>
/* Smooth transitions */
.transition-all {
    transition: all 0.2s ease;
}

/* Hover effects for stat cards */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}
</style>
