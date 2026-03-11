<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">VAT Returns</h1>
                    <p class="text-gray-600 mt-1">Monitor VAT filings and payments across all businesses</p>
                </div>
                <Link
                    href="/admin/vat-returns/reports/revenue"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Revenue Report</span>
                </Link>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Total Returns</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_returns }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">VAT Collected</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">₦{{ formatCurrency(stats.total_vat_collected) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">VAT Paid</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">₦{{ formatCurrency(stats.total_vat_paid) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">VAT Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">₦{{ formatCurrency(stats.total_vat_pending) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Submitted Returns</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ stats.submitted }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business</label>
                        <select
                            v-model="filters.business_id"
                            @change="applyFilters"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                        >
                            <option value="">All Businesses</option>
                            <option v-for="business in businesses" :key="business.id" :value="business.id">
                                {{ business.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                        <input
                            v-model="filters.year"
                            @input="applyFilters"
                            type="number"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="resetFilters"
                            class="w-full px-4 py-2 border border-gray-300 hover:bg-gray-50 rounded-lg font-medium transition"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- VAT Returns Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="returns.data && returns.data.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Business</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Output VAT</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Input VAT</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Net VAT</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="record in returns.data"
                                :key="record.id"
                                class="hover:bg-gray-50 transition"
                            >
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ record.business.name }}</p>
                                        <p class="text-xs text-gray-600">{{ record.business.owner_name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">{{ record.period_label }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <p class="font-bold text-gray-900">₦{{ formatCurrency(record.output_vat) }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <p class="font-bold text-gray-900">₦{{ formatCurrency(record.input_vat) }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <p class="font-bold text-blue-600">₦{{ formatCurrency(record.net_vat) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="getStatusClass(record.status)"
                                        class="px-3 py-1 rounded-full text-xs font-medium capitalize"
                                    >
                                        {{ record.status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs">
                                        <p v-if="record.submitted_at" class="text-gray-600">Submitted: {{ record.submitted_at }}</p>
                                        <p v-if="record.paid_at" class="text-green-600">Paid: {{ record.paid_at }}</p>
                                        <p v-if="!record.submitted_at && !record.paid_at" class="text-gray-400">Not yet filed</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="`/admin/vat-returns/${record.id}`"
                                        class="text-blue-600 hover:text-blue-700 font-medium text-sm"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-4 text-gray-600">No VAT returns found</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="returns.links && returns.links.length > 3" class="flex justify-center space-x-2">
                <template v-for="link in returns.links">
                    <Link
                        v-if="link.url"
                        :key="link.url"
                        :href="link.url"
                        :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-900 border border-gray-300 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-lg transition"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        :key="link.label"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-400 cursor-not-allowed"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    returns: Object,
    stats: Object,
    businesses: Array,
    filters: Object,
})

const filters = ref({
    status: props.filters?.status || '',
    business_id: props.filters?.business_id || '',
    year: props.filters?.year || new Date().getFullYear().toString(),
})

const applyFilters = () => {
    window.location.search = new URLSearchParams(filters.value).toString()
}

const resetFilters = () => {
    filters.value = {
        status: '',
        business_id: '',
        year: new Date().getFullYear().toString(),
    }
    applyFilters()
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0)
}

const getStatusClass = (status) => {
    const classes = {
        'draft': 'bg-gray-100 text-gray-800',
        'submitted': 'bg-blue-100 text-blue-800',
        'paid': 'bg-green-100 text-green-800',
        'overdue': 'bg-red-100 text-red-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
