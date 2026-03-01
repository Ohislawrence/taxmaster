<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Transactions</h1>
                    <p class="text-gray-600 mt-1">Monitor all transactions across businesses</p>
                </div>
                <button
                    @click="exportCSV"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export CSV</span>
                </button>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Total Transactions</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_transactions }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Uncategorized</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ stats.uncategorized }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">₦{{ formatCurrency(stats.total_revenue) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Total Expenses</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">₦{{ formatCurrency(stats.total_expenses) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">VAT Applicable</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ stats.vat_applicable }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            v-model="filters.search"
                            @input="applyFilters"
                            type="text"
                            placeholder="Description, ref, amount..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select
                            v-model="filters.category"
                            @change="applyFilters"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                        >
                            <option value="">All Categories</option>
                            <option value="uncategorized">Uncategorized</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">
                                {{ cat }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select
                            v-model="filters.type"
                            @change="applyFilters"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                        >
                            <option value="">All Types</option>
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
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

            <!-- Transactions Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="transactions.data && transactions.data.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Business</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Bank Account</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Confidence</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="transaction in transactions.data"
                                :key="transaction.id"
                                class="hover:bg-gray-50 transition"
                            >
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ transaction.business.name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">{{ transaction.transaction_date }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-gray-900 truncate max-w-xs">{{ transaction.description }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <p
                                        :class="transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'"
                                        class="font-bold"
                                    >
                                        {{ transaction.type === 'credit' ? '+' : '-' }}₦{{ formatCurrency(transaction.amount) }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="transaction.type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        class="px-2 py-1 rounded text-xs font-medium"
                                    >
                                        {{ transaction.type === 'credit' ? 'Credit' : 'Debit' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="transaction.category ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                                        class="px-2 py-1 rounded text-xs font-medium"
                                    >
                                        {{ transaction.category_label || 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">{{ transaction.bank_account.bank_name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-0.5">
                                        <span v-for="i in 5" :key="i" :class="i <= Math.round(transaction.confidence / 20) ? 'text-yellow-400' : 'text-gray-300'">
                                            ★
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="`/admin/transactions/${transaction.id}`"
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-4 text-gray-600">No transactions found</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="transactions.links && transactions.links.length > 3" class="flex justify-center space-x-2">
                <Link
                    v-for="link in transactions.links"
                    :key="link.url"
                    :href="link.url"
                    :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-900 border border-gray-300 hover:bg-gray-50'"
                    class="px-4 py-2 rounded-lg transition"
                    v-html="link.label"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    transactions: Object,
    stats: Object,
    businesses: Array,
    categories: Array,
    filters: Object,
})

const filters = ref({
    search: props.filters?.search || '',
    business_id: props.filters?.business_id || '',
    category: props.filters?.category || '',
    type: props.filters?.type || '',
})

const applyFilters = () => {
    window.location.search = new URLSearchParams(filters.value).toString()
}

const resetFilters = () => {
    filters.value = {
        search: '',
        business_id: '',
        category: '',
        type: '',
    }
    applyFilters()
}

const exportCSV = () => {
    window.location.href = `/admin/transactions/export?${new URLSearchParams(filters.value).toString()}`
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0)
}
</script>
