<template>
    <div 
        v-if="show" 
        class="rounded-lg border-2 border-dashed p-6 text-center"
        :class="[
            variant === 'warning' ? 'border-yellow-300 bg-yellow-50' : 'border-blue-300 bg-blue-50'
        ]"
    >
        <!-- Icon -->
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full"
            :class="[
                variant === 'warning' ? 'bg-yellow-100' : 'bg-blue-100'
            ]"
        >
            <svg v-if="variant === 'warning'" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <svg v-else class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        
        <!-- Message -->
        <h3 class="mb-2 text-lg font-semibold"
            :class="[
                variant === 'warning' ? 'text-yellow-900' : 'text-blue-900'
            ]"
        >
            {{ title }}
        </h3>
        <p class="mb-4 text-sm"
            :class="[
                variant === 'warning' ? 'text-yellow-700' : 'text-blue-700'
            ]"
        >
            {{ message }}
        </p>
        
        <!-- Required Plan Badge -->
        <div v-if="requiredPlan" class="mb-4 inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
            :class="getPlanBadgeColor(requiredPlan)"
        >
            <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
            </svg>
            Requires {{ requiredPlan }} Plan
        </div>
        
        <!-- CTA Button -->
        <div class="flex justify-center gap-3">
            <Link
                :href="route('business.plans.index')"
                class="inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors"
                :class="[
                    variant === 'warning' 
                        ? 'bg-yellow-600 hover:bg-yellow-700' 
                        : 'bg-blue-600 hover:bg-blue-700'
                ]"
            >
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                {{ ctaText }}
            </Link>
            
            <button
                v-if="dismissible"
                @click="dismiss"
                class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium transition-colors"
                :class="[
                    variant === 'warning' 
                        ? 'text-yellow-700 hover:bg-yellow-100' 
                        : 'text-blue-700 hover:bg-blue-100'
                ]"
            >
                Maybe Later
            </button>
        </div>
        
        <!-- Features Teaser (Optional) -->
        <div v-if="features && features.length > 0" class="mt-6 border-t pt-4"
            :class="[
                variant === 'warning' ? 'border-yellow-200' : 'border-blue-200'
            ]"
        >
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide"
                :class="[
                    variant === 'warning' ? 'text-yellow-700' : 'text-blue-700'
                ]"
            >
                {{ requiredPlan }} Plan Includes:
            </p>
            <ul class="space-y-2 text-left text-sm"
                :class="[
                    variant === 'warning' ? 'text-yellow-800' : 'text-blue-800'
                ]"
            >
                <li v-for="(feature, index) in features.slice(0, 4)" :key="index" class="flex items-start">
                    <svg class="mr-2 h-5 w-5 flex-shrink-0"
                        :class="[
                            variant === 'warning' ? 'text-yellow-600' : 'text-blue-600'
                        ]"
                        fill="currentColor" viewBox="0 0 20 20"
                    >
                        <path fill-rule="evenodd" 
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" 
                            clip-rule="evenodd" 
                        />
                    </svg>
                    <span>{{ feature }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: true,
    },
    feature: {
        type: String,
        required: true,
    },
    requiredPlan: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: 'Upgrade Required',
    },
    message: {
        type: String,
        default: null,
    },
    variant: {
        type: String,
        default: 'info', // 'info' or 'warning'
        validator: (value) => ['info', 'warning'].includes(value),
    },
    ctaText: {
        type: String,
        default: 'Upgrade Now',
    },
    dismissible: {
        type: Boolean,
        default: false,
    },
    features: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['dismiss']);

const dismiss = () => {
    emit('dismiss');
};

const getPlanBadgeColor = (plan) => {
    const colors = {
        'free': 'bg-gray-100 text-gray-800',
        'basic': 'bg-green-100 text-green-800',
        'professional': 'bg-blue-100 text-blue-800',
        'enterprise': 'bg-purple-100 text-purple-800',
    };
    return colors[plan.toLowerCase()] || colors.professional;
};

// Feature-specific benefits map
const featureBenefits = {
    file_cit: [
        'File unlimited CIT returns',
        'Automatic tax calculations',
        'RRR generation for payments',
        'Return status tracking',
    ],
    file_vat: [
        'File VAT Form 002 and 001',
        'Real-time VAT calculations',
        'Sales and purchase tracking',
        'Settlement management',
    ],
    file_cgt: [
        'Capital Gains Tax returns',
        'Asset disposal tracking',
        'Gain/loss calculations',
        'Professional tax reports',
    ],
    use_ai_chat: [
        'AI Tax Advisor chat',
        'Instant tax guidance',
        'Context-aware responses',
        'Chat history & bookmarks',
    ],
    use_ai_optimization: [
        'AI tax optimization',
        'Deduction recommendations',
        'Tax-saving strategies',
        'Compliance improvement tips',
    ],
    link_bank_account: [
        'Connect multiple bank accounts',
        'Auto-sync transactions',
        'Transaction categorization',
        'Real-time balance tracking',
    ],
    generate_financial_statements: [
        'Generate P&L statements',
        'Balance sheet reports',
        'Cash flow analysis',
        'Professional PDF exports',
    ],
};

// Use provided features or fallback to predefined benefits
const displayFeatures = computed(() => {
    return props.features.length > 0 
        ? props.features 
        : (featureBenefits[props.feature] || []);
});
</script>
