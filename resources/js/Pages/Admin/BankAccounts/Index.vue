<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Bank Accounts</h1>
                    <p class="text-gray-600 mt-1">Monitor all connected bank accounts across businesses</p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Total Accounts</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_accounts }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Active Accounts</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ stats.active_accounts }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Inactive Accounts</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ stats.inactive_accounts }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Total Balance</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(stats.total_balance) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Auto-Sync Enabled</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ stats.auto_sync_enabled }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            v-model="filters.search"
                            @input="applyFilters"
                            type="text"
                            placeholder="Bank name, account..."
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                        >
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
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

            <!-- Bank Accounts Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="bankAccounts.data && bankAccounts.data.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Business</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Bank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Account</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Last Synced</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Transactions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="account in bankAccounts.data"
                                :key="account.id"
                                class="hover:bg-gray-50 transition"
                            >
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ account.business.name }}</p>
                                        <p class="text-xs text-gray-600">{{ account.business.owner_name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ account.bank_name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-gray-900">{{ account.account_name }}</p>
                                    <p class="text-xs text-gray-600">{{ account.masked_account_number }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900">₦{{ formatCurrency(account.balance) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        class="px-3 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ account.is_active ? '● Active' : '● Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">{{ account.last_synced_at || 'Never' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ account.transactions_count }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="`/admin/bank-accounts/${account.id}`"
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-4 text-gray-600">No bank accounts found</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="bankAccounts.links && bankAccounts.links.length > 3" class="flex justify-center space-x-2">
                <Link
                    v-for="link in bankAccounts.links"
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
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    bankAccounts: Object,
    stats: Object,
    businesses: Array,
    filters: Object,
})

const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    business_id: props.filters?.business_id || '',
})

const applyFilters = () => {
    // Inertia will handle this through form submission
    window.location.search = new URLSearchParams(filters.value).toString()
}

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        business_id: '',
    }
    applyFilters()
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0)
}
</script>
