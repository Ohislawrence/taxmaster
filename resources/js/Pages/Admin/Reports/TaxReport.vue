<template>
    <AdminLayout>
        <Head title="Tax Report" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Tax Report</h1>
                <p class="text-gray-600 mt-1">Overview of all tax returns and filing status</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="filters.status" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <!-- Date Range From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                        <input v-model="filters.from_date" type="date" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2" />
                    </div>

                    <!-- Date Range To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <input v-model="filters.to_date" type="date" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2" />
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Returns</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ summaryStats.total_returns || 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Submitted</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ summaryStats.submitted || 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Tax Due</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">₦{{ formatCurrency(summaryStats.total_tax_due || 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Tax Paid</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">₦{{ formatCurrency(summaryStats.total_tax_paid || 0) }}</p>
                </div>
            </div>

            <!-- Returns Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Business</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Period</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Type</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Tax Due</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Tax Paid</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Due Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="taxReturn in taxReturns.data" :key="taxReturn.id" class="hover:bg-gray-50">
                                <td class="py-4 px-6 font-medium text-gray-900">{{ taxReturn.business.name }}</td>
                                <td class="py-4 px-6 text-gray-600">{{ taxReturn.period }}</td>
                                <td class="py-4 px-6 text-gray-600">{{ taxReturn.return_type }}</td>
                                <td class="py-4 px-6 font-semibold">₦{{ formatCurrency(taxReturn.total_tax_due) }}</td>
                                <td class="py-4 px-6 font-semibold text-green-600">₦{{ formatCurrency(taxReturn.total_tax_paid) }}</td>
                                <td class="py-4 px-6">
                                    <span :class="statusBadgeClass(taxReturn.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                                        {{ taxReturn.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-gray-600 text-xs">{{ formatDate(taxReturn.due_date) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="taxReturns.links.length > 3" class="bg-white px-6 py-4 border-t border-gray-200 flex justify-center space-x-2">
                    <Link v-for="link in taxReturns.links" :key="link.url || link.label" :href="link.url || '#'" :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-600'" class="px-3 py-1 rounded text-sm">
                        {{ link.label }}
                    </Link>
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
    taxReturns: Object,
    summary: Array,
});

const filters = ref({
    status: '',
    from_date: '',
    to_date: '',
});

const summaryStats = ref({
    total_returns: props.summary?.reduce((sum, s) => sum + s.count, 0) || 0,
    submitted: props.summary?.find(s => s.status === 'submitted')?.count || 0,
    total_tax_due: props.summary?.reduce((sum, s) => sum + (s.total_due || 0), 0) || 0,
    total_tax_paid: props.summary?.reduce((sum, s) => sum + (s.total_paid || 0), 0) || 0,
});

const applyFilters = () => {
    // Implement filter logic
};

const statusBadgeClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        submitted: 'bg-blue-100 text-blue-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
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
