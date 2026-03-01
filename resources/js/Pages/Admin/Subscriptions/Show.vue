<template>
    <AdminLayout>
        <Head :title="`Subscription: ${subscription.business.name}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <Link href="/admin/subscriptions" class="text-blue-600 hover:underline">← Back to Subscriptions</Link>
                    <h1 class="text-3xl font-bold text-gray-900 mt-2">{{ subscription.business.name }} - {{ subscription.plan_name }} Plan</h1>
                </div>
                <span :class="statusBadgeClass(subscription.status)" class="px-4 py-2 rounded-full text-sm font-medium">
                    {{ subscription.status }}
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Plan Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Plan Details</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Plan</p>
                                <p class="text-2xl font-bold text-blue-600">{{ subscription.plan_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Billing Cycle</p>
                                <p class="text-lg font-semibold text-gray-900">{{ subscription.billing_cycle }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Monthly Amount</p>
                                <p class="text-lg font-semibold text-gray-900">₦{{ formatCurrency(subscription.amount) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Paid</p>
                                <p class="text-lg font-semibold text-green-600">₦{{ formatCurrency(subscription.total_paid) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Timeline -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-4">
                                <div class="w-3 h-3 bg-green-600 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <p class="font-medium text-gray-900">Subscription Started</p>
                                    <p class="text-sm text-gray-600">{{ formatDate(subscription.start_date) }}</p>
                                </div>
                            </div>
                            <div v-if="subscription.renews_at" class="flex items-start space-x-4">
                                <div :class="daysUntilRenewal < 0 ? 'bg-red-600' : daysUntilRenewal <= 7 ? 'bg-orange-600' : 'bg-blue-600'" class="w-3 h-3 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <p class="font-medium text-gray-900">Next Renewal</p>
                                    <p class="text-sm text-gray-600">{{ formatDate(subscription.renews_at) }} ({{ daysUntilRenewal > 0 ? `in ${daysUntilRenewal} days` : 'overdue' }})</p>
                                </div>
                            </div>
                            <div v-if="subscription.canceled_at" class="flex items-start space-x-4">
                                <div class="w-3 h-3 bg-red-600 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <p class="font-medium text-gray-900">Subscription Canceled</p>
                                    <p class="text-sm text-gray-600">{{ formatDate(subscription.canceled_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Payment History</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="text-left py-3 px-4 font-medium text-gray-700">Date</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-700">Amount</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-700">Reference</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="payment in paymentHistory" :key="payment.id" class="hover:bg-gray-50">
                                        <td class="py-3 px-4">{{ formatDate(payment.date) }}</td>
                                        <td class="py-3 px-4 font-semibold">₦{{ formatCurrency(payment.amount) }}</td>
                                        <td class="py-3 px-4 font-mono text-xs">{{ payment.reference }}</td>
                                        <td class="py-3 px-4">
                                            <span :class="paymentStatusClass(payment.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                                                {{ payment.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Business Info -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Business</h3>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                {{ subscription.business.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ subscription.business.name }}</p>
                                <p class="text-sm text-gray-600">{{ subscription.business.email }}</p>
                            </div>
                        </div>
                        <Link :href="`/admin/businesses/${subscription.business.id}`" class="text-blue-600 hover:underline text-sm">
                            View Business Profile
                        </Link>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-blue-50 rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Quick Stats</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Active Since</p>
                                <p class="font-medium text-gray-900">{{ daysSinceActive }} days</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Churn Risk</p>
                                <p :class="churnRisk === 'high' ? 'text-red-600' : churnRisk === 'medium' ? 'text-orange-600' : 'text-green-600'" class="font-medium">
                                    {{ churnRisk }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-lg shadow p-6 space-y-3">
                        <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
                        <button @click="upgradePlan" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-medium text-sm">
                            Upgrade Plan
                        </button>
                        <button @click="pauseSubscription" v-if="subscription.status === 'active'" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded font-medium text-sm">
                            Pause
                        </button>
                        <button @click="cancelSubscription" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-medium text-sm">
                            Cancel Subscription
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    subscription: Object,
    paymentHistory: Array,
});

const daysUntilRenewal = computed(() => {
    if (!props.subscription.renews_at) return 0;
    const now = new Date();
    const renewal = new Date(props.subscription.renews_at);
    return Math.floor((renewal - now) / (1000 * 60 * 60 * 24));
});

const daysSinceActive = computed(() => {
    const start = new Date(props.subscription.start_date);
    const now = new Date();
    return Math.floor((now - start) / (1000 * 60 * 60 * 24));
});

const churnRisk = computed(() => {
    if (daysUntilRenewal.value < 0) return 'high';
    if (daysUntilRenewal.value <= 7) return 'medium';
    return 'low';
});

const upgradePlan = () => {
    console.log('Upgrade plan');
};

const pauseSubscription = () => {
    console.log('Pause subscription');
};

const cancelSubscription = () => {
    if (confirm('Are you sure you want to cancel this subscription?')) {
        console.log('Cancel subscription');
    }
};

const statusBadgeClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-800',
        paused: 'bg-yellow-100 text-yellow-800',
        canceled: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const paymentStatusClass = (status) => {
    const classes = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
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
