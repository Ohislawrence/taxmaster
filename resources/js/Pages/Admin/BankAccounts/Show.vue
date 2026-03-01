<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ bankAccount.bank_name }}</h1>
                    <p class="text-gray-600 mt-1">Account Details</p>
                </div>
                <Link
                    href="/admin/bank-accounts"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition"
                >
                    ← Back to Accounts
                </Link>
            </div>

            <!-- Account Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Account Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Account Information</h2>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Account Name</p>
                                    <p class="text-lg font-medium text-gray-900">{{ bankAccount.account_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Account Number</p>
                                    <p class="text-lg font-medium text-gray-900">{{ bankAccount.account_number }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Balance</p>
                                    <p class="text-lg font-bold text-blue-600">₦{{ formatCurrency(bankAccount.balance) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Status</p>
                                    <span
                                        :class="bankAccount.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-1"
                                    >
                                        {{ bankAccount.is_active ? '● Active' : '● Inactive' }}
                                    </span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Auto-Sync</p>
                                    <p class="text-lg font-medium text-gray-900">{{ bankAccount.auto_sync ? 'Enabled' : 'Disabled' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Last Synced</p>
                                    <p class="text-lg font-medium text-gray-900">{{ bankAccount.last_synced_at_full || 'Never' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Business Information</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Business Name</p>
                                <p class="text-lg font-medium text-gray-900">{{ bankAccount.business.name }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Owner</p>
                                    <p class="text-lg font-medium text-gray-900">{{ bankAccount.business.owner.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Email</p>
                                    <p class="text-lg font-medium text-gray-900">{{ bankAccount.business.owner.email }}</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <span
                                    :class="bankAccount.business.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                    class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-1"
                                >
                                    {{ bankAccount.business.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Recent Transactions</h2>
                        <div v-if="recentTransactions.length > 0" class="space-y-2">
                            <div
                                v-for="transaction in recentTransactions"
                                :key="transaction.id"
                                class="p-4 border border-gray-200 rounded hover:bg-gray-50 transition"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ transaction.description }}</p>
                                        <p class="text-sm text-gray-600">{{ transaction.transaction_date }}</p>
                                    </div>
                                    <p
                                        :class="transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'"
                                        class="font-bold"
                                    >
                                        {{ transaction.type === 'credit' ? '+' : '-' }}₦{{ formatCurrency(transaction.amount) }}
                                    </p>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">
                                    Category: <span class="font-medium">{{ transaction.category || 'Uncategorized' }}</span>
                                </p>
                            </div>
                        </div>
                        <div v-else class="p-4 text-center text-gray-600">
                            No recent transactions
                        </div>
                    </div>
                </div>

                <!-- Right Column - Actions -->
                <div class="space-y-4">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Actions</h2>
                        <div class="space-y-3">
                            <button
                                v-if="bankAccount.is_active"
                                @click="deactivateAccount"
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition"
                            >
                                Deactivate Account
                            </button>
                            <button
                                v-else
                                @click="activateAccount"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition"
                            >
                                Activate Account
                            </button>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Summary</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <p class="text-gray-600">Total Transactions</p>
                                <p class="font-bold text-gray-900">{{ bankAccount.transactions_count }}</p>
                            </div>
                            <div class="border-t pt-3 border-blue-200">
                                <p class="text-sm text-gray-600">Connection Date</p>
                                <p class="font-medium text-gray-900">{{ bankAccount.created_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    bankAccount: Object,
    recentTransactions: Array,
})

const form = useForm({})

const deactivateAccount = () => {
    if (confirm('Are you sure you want to deactivate this account?')) {
        form.post(`/admin/bank-accounts/${props.bankAccount.id}/deactivate`, {
            onSuccess: () => window.location.reload(),
        })
    }
}

const activateAccount = () => {
    if (confirm('Are you sure you want to activate this account?')) {
        form.post(`/admin/bank-accounts/${props.bankAccount.id}/activate`, {
            onSuccess: () => window.location.reload(),
        })
    }
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0)
}
</script>
