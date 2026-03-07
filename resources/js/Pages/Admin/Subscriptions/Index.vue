<template>
    <AdminLayout>
        <Head title="Subscriptions" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Subscriptions</h1>
                <p class="text-gray-600 mt-1">Manage business subscriptions and billing</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Plan Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Plan</label>
                        <select v-model="filters.plan" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2">
                            <option value="">All Plans</option>
                            <option value="starter">Starter</option>
                            <option value="professional">Professional</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="filters.status" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="canceled">Canceled</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Business</label>
                        <input
                            v-model="filters.search"
                            @input="applyFilters"
                            type="text"
                            placeholder="Business name..."
                            class="w-full border border-gray-300 rounded px-4 py-2"
                        />
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Active Subscriptions</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ subscriptionStats.active }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Monthly Revenue</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">₦{{ formatCurrency(subscriptionStats.monthly_revenue) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">High Value Plans</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ subscriptionStats.enterprise }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Expiring Soon</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ subscriptionStats.expiring_soon }}</p>
                </div>
            </div>

            <!-- Subscriptions Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Business</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Plan</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Monthly Cost</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Renewal Date</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="subscription in subscriptions.data" :key="subscription.id" class="hover:bg-gray-50">
                                <td class="py-4 px-6 font-medium text-gray-900">
                                    {{ subscription.business.name }}
                                </td>
                                <td class="py-4 px-6">
                                    <span :class="planBadgeClass(subscription.plan_name)" class="px-3 py-1 rounded-full text-xs font-medium">
                                        {{ subscription.plan_name }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-semibold text-gray-900">
                                    ₦{{ formatCurrency(subscription.amount) }}
                                </td>
                                <td class="py-4 px-6">
                                    <span :class="statusBadgeClass(subscription.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                                        {{ subscription.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-gray-600">
                                    <span :class="getRenewalClass(subscription.renews_at)" class="px-3 py-1 rounded text-xs font-medium">
                                        {{ formatDate(subscription.renews_at) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 space-x-2">
                                    <Link :href="`/admin/subscriptions/${subscription.id}`" class="text-blue-600 hover:underline text-xs">View</Link>
                                    <button @click="editSubscription(subscription.id)" class="text-blue-600 hover:underline text-xs">Edit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="subscriptions.links.length > 3" class="bg-white px-6 py-4 border-t border-gray-200 flex justify-center space-x-2">
                    <template v-for="link in subscriptions.links" :key="link.url || link.label">
                        <Link v-if="link.url" :href="link.url" :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-600'" class="px-3 py-1 rounded">
                            {{ link.label }}
                        </Link>
                        <span v-else :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-400'" class="px-3 py-1 rounded cursor-not-allowed">
                            {{ link.label }}
                        </span>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    subscriptions: Object,
    subscriptionStats: Object,
});

const filters = ref({
    plan: '',
    status: '',
    search: '',
});

const applyFilters = () => {
    // Implement filter logic
};

const editSubscription = (id) => {
    // Implement edit logic
};

const planBadgeClass = (plan) => {
    const classes = {
        starter: 'bg-blue-100 text-blue-800',
        professional: 'bg-green-100 text-green-800',
        enterprise: 'bg-purple-100 text-purple-800',
    };
    return classes[plan] || 'bg-gray-100 text-gray-800';
};

const statusBadgeClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-gray-100 text-gray-800',
        canceled: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getRenewalClass = (renewalDate) => {
    const now = new Date();
    const renewal = new Date(renewalDate);
    const daysUntilRenewal = Math.floor((renewal - now) / (1000 * 60 * 60 * 24));

    if (daysUntilRenewal < 0) return 'bg-red-100 text-red-800 font-medium';
    if (daysUntilRenewal <= 7) return 'bg-orange-100 text-orange-800 font-medium';
    return 'bg-green-100 text-green-800';
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
