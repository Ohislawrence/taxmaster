<template>
    <!-- Subscription Status Banner -->
    <div v-if="!currentSubscription" class="mb-6 bg-gradient-to-r from-yellow-50 to-red-50 border-l-4 border-red-500 rounded-lg p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div class="flex gap-4">
                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M7.08 6.24a9 9 0 1111.84 0M9.9 5.1A5 5 0 0114.1 3"></path>
                </svg>
                <div>
                    <h3 class="font-bold text-red-900 mb-1">No Active Subscription</h3>
                    <p class="text-sm text-red-800 mb-3">Your business doesn't have an active subscription plan. Select a plan to unlock full features and access premium tools.</p>
                    <Link href="/business/plans" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Browse Plans
                    </Link>
                </div>
            </div>
            <button @click="dismissed = true" class="text-red-600 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Active Subscription Banner -->
    <div v-else-if="currentSubscription && !dismissed" class="mb-6 bg-gradient-to-r from-green-50 to-blue-50 border-l-4 border-green-500 rounded-lg p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div class="flex gap-4">
                <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-bold text-green-900">{{ currentSubscription.plan?.name || currentSubscription.plan_type }}</h3>
                        <span v-if="billingCycle" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">{{ billingCycle }}</span>
                    </div>
                    <p class="text-sm text-green-800 mb-2">
                        Renews on {{ formatDate(currentSubscription.renews_at) }}
                    </p>
                    <div class="flex gap-4 text-sm text-green-700">
                        <span>Staff: {{ usageStats?.staff_count || 0 }}/{{ usageStats?.staff_limit || 0 }}</span>
                        <span>Returns: {{ usageStats?.returns_this_year || 0 }}/{{ usageStats?.returns_limit || 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <Link href="/business/subscription" class="px-4 py-2 bg-white border border-green-300 text-green-700 rounded-lg font-medium hover:bg-green-50 transition">
                    Manage
                </Link>
                <button @click="dismissed = true" class="text-green-600 hover:text-green-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Usage Warning -->
    <div v-if="currentSubscription && usageStats && getUsageWarnings().length > 0" class="mb-6 space-y-3">
        <div v-for="warning in getUsageWarnings()" :key="warning.type" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v2m0 0h.01M12 2a10 10 0 110 20 10 10 0 010-20z"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-sm text-amber-800">{{ warning.message }}</p>
                    <div class="mt-1 w-full bg-amber-200 rounded-full h-2">
                        <div class="bg-amber-600 h-2 rounded-full" :style="{ width: warning.percentage + '%' }"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    currentSubscription: Object,
    usageStats: Object,
})

const dismissed = ref(false)

const billingCycle = computed(() => {
    if (!props.currentSubscription) return null
    return props.currentSubscription.billing_cycle === 'annual' ? 'Annual' : 'Monthly'
})

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getUsageWarnings = () => {
    const warnings = []
    
    if (!props.usageStats) return warnings

    if (props.usageStats.staff_percentage > 80) {
        warnings.push({
            type: 'staff',
            message: `You've used ${props.usageStats.staff_percentage}% of your staff member limit (${props.usageStats.staff_count}/${props.usageStats.staff_limit})`,
            percentage: props.usageStats.staff_percentage
        })
    }

    if (props.usageStats.returns_percentage > 80) {
        warnings.push({
            type: 'returns',
            message: `You've used ${props.usageStats.returns_percentage}% of your tax returns limit (${props.usageStats.returns_this_year}/${props.usageStats.returns_limit}) this year`,
            percentage: props.usageStats.returns_percentage
        })
    }

    return warnings
}
</script>
