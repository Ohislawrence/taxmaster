<template>
    <AdminLayout>
        <Head title="Overdue Compliance Report" />

        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Overdue Compliance Report</h1>
                <p class="text-gray-600 mt-2">Critical and overdue tax deadline tracking by business</p>
            </div>
            <div class="flex space-x-3">
                <Link href="/admin/compliance" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Back to Compliance
                </Link>
                <button @click="exportReport" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Report</span>
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-red-50 rounded-lg shadow p-6 border border-red-200">
                <p class="text-red-600 text-sm font-medium">Total Overdue</p>
                <p class="text-3xl font-bold text-red-900 mt-2">{{ summary.total_overdue }}</p>
                <p class="text-red-600 text-xs mt-1">{{ summary.businesses_affected }} businesses affected</p>
            </div>
            <div class="bg-orange-50 rounded-lg shadow p-6 border border-orange-200">
                <p class="text-orange-600 text-sm font-medium">Critical (>30 days)</p>
                <p class="text-3xl font-bold text-orange-900 mt-2">{{ summary.critical_overdue }}</p>
                <p class="text-orange-600 text-xs mt-1">Immediate action required</p>
            </div>
            <div class="bg-yellow-50 rounded-lg shadow p-6 border border-yellow-200">
                <p class="text-yellow-600 text-sm font-medium">This Month</p>
                <p class="text-3xl font-bold text-yellow-900 mt-2">{{ summary.overdue_this_month }}</p>
                <p class="text-yellow-600 text-xs mt-1">Recently missed</p>
            </div>
            <div class="bg-purple-50 rounded-lg shadow p-6 border border-purple-200">
                <p class="text-purple-600 text-sm font-medium">Total Penalties</p>
                <p class="text-3xl font-bold text-purple-900 mt-2">₦{{ formatCurrency(summary.estimated_penalties) }}</p>
                <p class="text-purple-600 text-xs mt-1">Estimated amount</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Business</label>
                    <input
                        v-model="localFilters.search"
                        @input="debouncedFilter"
                        type="text"
                        placeholder="Search by name, TIN..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tax Type</label>
                    <select
                        v-model="localFilters.tax_type"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Types</option>
                        <option value="VAT">VAT</option>
                        <option value="WHT">WHT</option>
                        <option value="PAYE">PAYE</option>
                        <option value="CIT">CIT</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Overdue Period</label>
                    <select
                        v-model="localFilters.overdue_period"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Periods</option>
                        <option value="0-7">1-7 days</option>
                        <option value="8-30">8-30 days</option>
                        <option value="31-90">31-90 days</option>
                        <option value="90+">90+ days (Critical)</option>
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

        <!-- Grouped by Business -->
        <div class="space-y-6">
            <div v-for="business in groupedDeadlines" :key="business.id" class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Business Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold">{{ business.business_name }}</h2>
                            <p class="text-red-100 mt-1">
                                TIN: {{ business.tin || 'Not Registered' }} |
                                Owner: {{ business.owner_name }} ({{ business.owner_email }})
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold">{{ business.overdue_count }}</p>
                            <p class="text-red-100 text-sm">Overdue Deadlines</p>
                        </div>
                    </div>
                </div>

                <!-- Overdue Deadlines Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left py-3 px-6 text-xs font-semibold text-gray-600 uppercase">Tax Type</th>
                                <th class="text-left py-3 px-6 text-xs font-semibold text-gray-600 uppercase">Description</th>
                                <th class="text-left py-3 px-6 text-xs font-semibold text-gray-600 uppercase">Deadline</th>
                                <th class="text-left py-3 px-6 text-xs font-semibold text-gray-600 uppercase">Days Overdue</th>
                                <th class="text-left py-3 px-6 text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="text-right py-3 px-6 text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="deadline in business.deadlines" :key="deadline.id" class="hover:bg-gray-50">
                                <td class="py-4 px-6">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        {{ deadline.tax_type }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="font-medium text-gray-900">{{ deadline.description }}</p>
                                    <p class="text-sm text-gray-500">Period: {{ deadline.period }}</p>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="text-gray-900">{{ formatDate(deadline.deadline) }}</p>
                                    <p class="text-sm text-red-600">{{ deadline.days_overdue }} days ago</p>
                                </td>
                                <td class="py-4 px-6">
                                    <span :class="getDaysOverdueClass(deadline.days_overdue)" class="px-3 py-1 rounded-full text-xs font-bold">
                                        {{ deadline.days_overdue }} days
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium flex items-center w-fit">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        {{ deadline.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <Link :href="`/admin/compliance/${deadline.id}`" class="text-blue-600 hover:underline text-sm font-medium">
                                        View Details
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Business Summary Footer -->
                <div class="bg-gray-50 p-4 border-t">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Critical:</span> {{ business.critical_count }} |
                            <span class="font-medium">Total Days:</span> {{ business.total_days_overdue }}
                        </div>
                        <Link :href="`/admin/businesses/${business.id}`" class="text-blue-600 hover:underline text-sm font-medium">
                            View Business Details →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="groupedDeadlines.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">All Clear!</h3>
                <p class="text-gray-600">No overdue compliance deadlines found with the current filters.</p>
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
    groupedDeadlines: Array,
    summary: Object,
    filters: Object,
})

const localFilters = ref({ ...props.filters })

let debounceTimeout = null
const debouncedFilter = () => {
    clearTimeout(debounceTimeout)
    debounceTimeout = setTimeout(() => {
        applyFilters()
    }, 300)
}

const applyFilters = () => {
    router.get('/admin/compliance/overdue-report', localFilters.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const resetFilters = () => {
    localFilters.value = {
        search: '',
        tax_type: '',
        overdue_period: '',
    }
    applyFilters()
}

const exportReport = () => {
    window.location.href = router.visit('/admin/compliance/overdue-report/export', {
        method: 'get',
        data: localFilters.value,
    })
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatCurrency = (amount) => {
    if (!amount) return '0.00'
    return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

const getDaysOverdueClass = (days) => {
    if (days >= 90) return 'bg-red-600 text-white'
    if (days >= 31) return 'bg-orange-500 text-white'
    if (days >= 8) return 'bg-yellow-500 text-white'
    return 'bg-yellow-300 text-yellow-900'
}
</script>
