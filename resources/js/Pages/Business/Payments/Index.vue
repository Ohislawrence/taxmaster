<template>
    <BusinessLayout>
        <Head title="Payments" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">Payments</h1>
                    <Link href="/business/payments/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                        + New Payment
                    </Link>
                </div>
                <p class="text-gray-600 mt-1">Manage your tax payments</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6 flex gap-4">
                <input 
                    v-model="filters.search"
                    type="text"
                    placeholder="Search by reference or amount..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <select 
                    v-model="filters.status"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>
                <button 
                    @click="applyFilters"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium"
                >
                    Filter
                </button>
            </div>

            <!-- Payments Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="payments.data && payments.data.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-6 font-medium text-gray-700">Reference</th>
                                <th class="text-left py-3 px-6 font-medium text-gray-700">Tax Return</th>
                                <th class="text-right py-3 px-6 font-medium text-gray-700">Amount</th>
                                <th class="text-left py-3 px-6 font-medium text-gray-700">Status</th>
                                <th class="text-left py-3 px-6 font-medium text-gray-700">Date</th>
                                <th class="text-right py-3 px-6 font-medium text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50 transition">
                                <td class="py-3 px-6 font-mono text-sm font-medium">{{ payment.payment_reference }}</td>
                                <td class="py-3 px-6">
                                    <Link :href="`/business/tax-returns/${payment.tax_return_id}`" class="text-blue-600 hover:underline">
                                        {{ payment.tax_return?.return_type }} - {{ payment.tax_return?.tax_period }}
                                    </Link>
                                </td>
                                <td class="py-3 px-6 text-right font-semibold">₦{{ formatCurrency(payment.amount) }}</td>
                                <td class="py-3 px-6">
                                    <span :class="getStatusColor(payment.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                                        {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-sm text-gray-600">{{ formatDate(payment.payment_date) }}</td>
                                <td class="py-3 px-6 text-right">
                                    <Link :href="`/business/payments/${payment.id}`" class="text-blue-600 hover:underline text-sm">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mb-4">No payments found</p>
                    <Link href="/business/payments/create" class="text-blue-600 hover:underline">
                        Create your first payment →
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="payments.links && payments.links.length > 3" class="mt-6 flex justify-center gap-2">
                <Link 
                    v-for="link in payments.links"
                    :key="link.label"
                    :href="link.url"
                    :class="[
                        'px-4 py-2 rounded-lg font-medium transition',
                        link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

defineProps({
    payments: Object,
    filters: Object,
});

const filters = ref({
    search: '',
    status: '',
});

const applyFilters = () => {
    router.get('/business/payments', filters.value);
};

const formatCurrency = (value) => {
    if (!value) return '0.00'
    return parseFloat(value).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status) => {
    const colors = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        failed: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>
