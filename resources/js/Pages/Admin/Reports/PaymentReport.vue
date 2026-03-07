<template>
    <AdminLayout>
        <Head title="Payment Report" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Payment Report</h1>
                <p class="text-gray-600 mt-1">Overview of payment transactions and status</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="filters.status" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Method</label>
                        <select v-model="filters.method" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2">
                            <option value="">All Methods</option>
                            <option value="paystack">Paystack</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                        </select>
                    </div>

                    <!-- From Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                        <input v-model="filters.from_date" type="date" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2" />
                    </div>

                    <!-- To Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <input v-model="filters.to_date" type="date" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2" />
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Transactions</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ summaryStats.total_count || 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Completed Payments</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">₦{{ formatCurrency(summaryStats.completed_amount || 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Pending Payments</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">₦{{ formatCurrency(summaryStats.pending_amount || 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Failed Payments</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">₦{{ formatCurrency(summaryStats.failed_amount || 0) }}</p>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Reference</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Business</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Method</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Tax Return</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50">
                                <td class="py-4 px-6 font-mono text-xs">{{ payment.payment_reference }}</td>
                                <td class="py-4 px-6 text-gray-900 font-medium">{{ payment.business.name }}</td>
                                <td class="py-4 px-6 font-semibold">₦{{ formatCurrency(payment.amount) }}</td>
                                <td class="py-4 px-6 text-gray-600">{{ payment.payment_method || 'N/A' }}</td>
                                <td class="py-4 px-6">
                                    <span :class="statusBadgeClass(payment.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                                        {{ payment.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-gray-600 text-xs">{{ formatDate(payment.created_at) }}</td>
                                <td class="py-4 px-6 text-gray-600">{{ payment.taxReturn?.period || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="payments.links.length > 3" class="bg-white px-6 py-4 border-t border-gray-200 flex justify-center space-x-2">
                    <template v-for="link in payments.links" :key="link.url || link.label">
                        <Link v-if="link.url" :href="link.url" :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-600'" class="px-3 py-1 rounded text-sm">
                            {{ link.label }}
                        </Link>
                        <span v-else :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-400'" class="px-3 py-1 rounded text-sm cursor-not-allowed">
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
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    payments: Object,
    summary: Array,
});

const filters = ref({
    status: '',
    method: '',
    from_date: '',
    to_date: '',
});

const summaryStats = computed(() => {
    const statsByStatus = {};
    props.summary?.forEach(s => {
        statsByStatus[s.status] = { count: s.count, amount: s.total_amount };
    });

    return {
        total_count: props.summary?.reduce((sum, s) => sum + s.count, 0) || 0,
        completed_amount: statsByStatus.completed?.amount || 0,
        pending_amount: statsByStatus.pending?.amount || 0,
        failed_amount: statsByStatus.failed?.amount || 0,
    };
});

const applyFilters = () => {
    // Implement filter logic
};

const statusBadgeClass = (status) => {
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
