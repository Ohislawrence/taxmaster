<template>
    <AdminLayout>
        <Head title="Revenue Report" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Revenue Report</h1>
                <p class="text-gray-600 mt-1">Platform revenue from subscriptions and payments</p>
            </div>

            <!-- Summary Period Selector -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex gap-4">
                    <button
                        v-for="period in ['7d', '30d', '90d', '1y', 'custom']"
                        :key="period"
                        @click="selectedPeriod = period"
                        :class="selectedPeriod === period ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'"
                        class="px-6 py-2 rounded font-medium text-sm transition"
                    >
                        {{ periodLabel(period) }}
                    </button>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">{{ periodLabel(selectedPeriod) }} Revenue</p>
                            <p class="text-4xl font-bold text-blue-600 mt-2">₦{{ formatCurrency(metrics.total_revenue) }}</p>
                            <p class="text-green-600 text-sm mt-2">+{{ metrics.growth_rate }}% from previous period</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Subscription Revenue</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">₦{{ formatCurrency(metrics.subscription_revenue) }}</p>
                        <p class="text-gray-600 text-sm mt-2">{{ metrics.active_subscriptions }} active subscriptions</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Payment Processing Revenue</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">₦{{ formatCurrency(metrics.payment_revenue) }}</p>
                        <p class="text-gray-600 text-sm mt-2">{{ metrics.total_transactions }} transactions</p>
                    </div>
                </div>
            </div>

            <!-- Revenue by Plan Type -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Revenue by Subscription Plan</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Plan</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Active Count</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Monthly Revenue</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">% of Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="plan in revenueByPlan" :key="plan.plan_name" class="hover:bg-gray-50">
                                <td class="py-4 px-6 font-medium text-gray-900">
                                    <span class="capitalize">{{ plan.plan_name }}</span>
                                </td>
                                <td class="py-4 px-6 text-gray-600">{{ plan.count }}</td>
                                <td class="py-4 px-6 font-semibold">₦{{ formatCurrency(plan.revenue) }}</td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 h-2 bg-gray-200 rounded overflow-hidden">
                                            <div :style="{ width: plan.percentage + '%' }" class="h-full bg-blue-600"></div>
                                        </div>
                                        <span class="text-gray-600">{{ plan.percentage }}%</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Top Revenue Sources (Last 30 days)</h2>
                </div>
                <div class="space-y-4">
                    <div v-for="item in topRevenueSources" :key="item.id" class="flex items-center justify-between border-b pb-4 last:border-b-0">
                        <div>
                            <p class="font-medium text-gray-900">{{ item.business_name }}</p>
                            <p class="text-sm text-gray-600">{{ item.transaction_type }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">₦{{ formatCurrency(item.amount) }}</p>
                            <p class="text-xs text-gray-600">{{ formatDate(item.date) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    metrics: Object,
    revenueByPlan: Array,
    topRevenueSources: Array,
});

const selectedPeriod = ref('30d');

const metrics = computed(() => {
    return props.metrics || {
        total_revenue: 0,
        growth_rate: 0,
        subscription_revenue: 0,
        active_subscriptions: 0,
        payment_revenue: 0,
        total_transactions: 0,
    };
});

const periodLabel = (period) => {
    const labels = {
        '7d': 'Last 7 Days',
        '30d': 'Last 30 Days',
        '90d': 'Last 90 Days',
        '1y': 'Last Year',
        'custom': 'Custom Period',
    };
    return labels[period];
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatCurrency = (amount) => {
    if (!amount) return '0.00';
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};
</script>
