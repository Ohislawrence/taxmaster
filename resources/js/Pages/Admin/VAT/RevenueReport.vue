<template>
    <AdminLayout>
        <Head title="VAT Revenue Report" />

        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">VAT Revenue Report</h1>
                <p class="text-gray-600 mt-2">Monthly VAT collection and revenue tracking across all businesses</p>
            </div>
            <div class="flex space-x-3">
                <Link href="/admin/vat-returns" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Back to VAT Returns
                </Link>
                <button @click="exportReport" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Report</span>
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                <p class="text-blue-100 text-sm font-medium">Total Output VAT</p>
                <p class="text-3xl font-bold mt-2">₦{{ formatCurrency(summary.total_output_vat) }}</p>
                <p class="text-blue-100 text-xs mt-1">Collected from sales</p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
                <p class="text-green-100 text-sm font-medium">Total Input VAT</p>
                <p class="text-3xl font-bold mt-2">₦{{ formatCurrency(summary.total_input_vat) }}</p>
                <p class="text-green-100 text-xs mt-1">Paid on purchases</p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow p-6 text-white">
                <p class="text-orange-100 text-sm font-medium">Net VAT Payable</p>
                <p class="text-3xl font-bold mt-2">₦{{ formatCurrency(summary.net_vat) }}</p>
                <p class="text-orange-100 text-xs mt-1">Government revenue</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow p-6 text-white">
                <p class="text-purple-100 text-sm font-medium">Total Returns</p>
                <p class="text-3xl font-bold mt-2">{{ summary.total_returns }}</p>
                <p class="text-purple-100 text-xs mt-1">{{ summary.businesses_count }} businesses</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                    <select
                        v-model="localFilters.year"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Business</label>
                    <select
                        v-model="localFilters.business_id"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Businesses</option>
                        <option v-for="business in businesses" :key="business.id" :value="business.id">
                            {{ business.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select
                        v-model="localFilters.status"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="submitted">Submitted</option>
                        <option value="draft">Draft</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button
                        @click="resetFilters"
                        class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                    >
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Chart -->
        <div class="bg-white rounded-lg shadow mb-8 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Monthly VAT Revenue Trend ({{ localFilters.year }})</h2>
            <div class="space-y-4">
                <div v-for="month in monthlyData" :key="month.month" class="border-b pb-4 last:border-b-0">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-gray-900">{{ month.month_name }}</span>
                        <span class="text-sm text-gray-600">{{ month.returns_count }} returns</span>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-2">
                        <div>
                            <p class="text-xs text-gray-600">Output VAT</p>
                            <p class="font-semibold text-blue-600">₦{{ formatCurrency(month.output_vat) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Input VAT</p>
                            <p class="font-semibold text-green-600">₦{{ formatCurrency(month.input_vat) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Net Payable</p>
                            <p class="font-semibold text-orange-600">₦{{ formatCurrency(month.net_vat) }}</p>
                        </div>
                    </div>
                    <!-- Visual Bar -->
                    <div class="relative h-8 bg-gray-100 rounded-lg overflow-hidden">
                        <div
                            :style="{ width: getPercentage(month.net_vat) + '%' }"
                            class="absolute h-full bg-gradient-to-r from-orange-400 to-orange-600 transition-all duration-500"
                        >
                            <span v-if="getPercentage(month.net_vat) > 10" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold text-white">
                                {{ getPercentage(month.net_vat).toFixed(1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Contributing Businesses -->
        <div class="bg-white rounded-lg shadow mb-8 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Top VAT Contributing Businesses</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Rank</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Business</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Output VAT</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Input VAT</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Net Payable</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Returns</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Compliance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="(business, index) in topBusinesses" :key="business.id" class="hover:bg-gray-50">
                            <td class="py-4 px-4">
                                <div
                                    :class="getRankClass(index)"
                                    class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm"
                                >
                                    {{ index + 1 }}
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <p class="font-semibold text-gray-900">{{ business.name }}</p>
                                <p class="text-sm text-gray-600">TIN: {{ business.tax_identification_number || 'N/A' }}</p>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <p class="font-semibold text-blue-600">₦{{ formatCurrency(business.total_output_vat) }}</p>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <p class="font-semibold text-green-600">₦{{ formatCurrency(business.total_input_vat) }}</p>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <p class="font-bold text-orange-600">₦{{ formatCurrency(business.total_net_vat) }}</p>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                    {{ business.returns_count }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span
                                    :class="business.compliance_rate >= 80 ? 'bg-green-100 text-green-800' : business.compliance_rate >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'"
                                    class="px-3 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ business.compliance_rate }}%
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Status Breakdown -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Payment Status Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="border border-green-200 rounded-lg p-6 bg-green-50">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-green-600 text-sm font-medium uppercase">Paid</p>
                            <p class="text-3xl font-bold text-green-900 mt-2">{{ paymentBreakdown.paid.count }}</p>
                        </div>
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-green-700">₦{{ formatCurrency(paymentBreakdown.paid.amount) }}</p>
                    <div class="mt-3 h-2 bg-green-200 rounded-full overflow-hidden">
                        <div :style="{ width: paymentBreakdown.paid.percentage + '%' }" class="h-full bg-green-600"></div>
                    </div>
                    <p class="text-xs text-green-600 mt-1">{{ paymentBreakdown.paid.percentage.toFixed(1) }}% of total</p>
                </div>

                <div class="border border-yellow-200 rounded-lg p-6 bg-yellow-50">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-yellow-600 text-sm font-medium uppercase">Pending</p>
                            <p class="text-3xl font-bold text-yellow-900 mt-2">{{ paymentBreakdown.pending.count }}</p>
                        </div>
                        <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-yellow-700">₦{{ formatCurrency(paymentBreakdown.pending.amount) }}</p>
                    <div class="mt-3 h-2 bg-yellow-200 rounded-full overflow-hidden">
                        <div :style="{ width: paymentBreakdown.pending.percentage + '%' }" class="h-full bg-yellow-600"></div>
                    </div>
                    <p class="text-xs text-yellow-600 mt-1">{{ paymentBreakdown.pending.percentage.toFixed(1) }}% of total</p>
                </div>

                <div class="border border-red-200 rounded-lg p-6 bg-red-50">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-red-600 text-sm font-medium uppercase">Overdue</p>
                            <p class="text-3xl font-bold text-red-900 mt-2">{{ paymentBreakdown.overdue.count }}</p>
                        </div>
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-red-700">₦{{ formatCurrency(paymentBreakdown.overdue.amount) }}</p>
                    <div class="mt-3 h-2 bg-red-200 rounded-full overflow-hidden">
                        <div :style="{ width: paymentBreakdown.overdue.percentage + '%' }" class="h-full bg-red-600"></div>
                    </div>
                    <p class="text-xs text-red-600 mt-1">{{ paymentBreakdown.overdue.percentage.toFixed(1) }}% of total</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    summary: Object,
    monthlyData: Array,
    topBusinesses: Array,
    paymentBreakdown: Object,
    businesses: Array,
    years: Array,
    filters: Object,
})

const localFilters = ref({ ...props.filters })

const applyFilters = () => {
    router.get('/admin/vat/revenue-report', localFilters.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const resetFilters = () => {
    localFilters.value = {
        year: new Date().getFullYear(),
        business_id: '',
        status: '',
    }
    applyFilters()
}

const exportReport = () => {
    window.location.href = `/admin/vat/revenue-report/export?${new URLSearchParams(localFilters.value).toString()}`
}

const formatCurrency = (amount) => {
    if (!amount) return '0.00'
    return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

const getPercentage = (amount) => {
    if (!props.summary.net_vat || !amount) return 0
    return (amount / props.summary.net_vat) * 100
}

const getRankClass = (index) => {
    if (index === 0) return 'bg-yellow-400 text-yellow-900'
    if (index === 1) return 'bg-gray-300 text-gray-800'
    if (index === 2) return 'bg-orange-400 text-orange-900'
    return 'bg-blue-100 text-blue-800'
}
</script>
