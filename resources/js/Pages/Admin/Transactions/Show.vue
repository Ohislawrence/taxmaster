<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Transaction Details</h1>
                    <p class="text-gray-600 mt-1">{{ transaction.description }}</p>
                </div>
                <Link
                    href="/admin/transactions"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition"
                >
                    ← Back to Transactions
                </Link>
            </div>

            <!-- Transaction Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Info -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Transaction Summary -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 mb-2">{{ transaction.description }}</h2>
                                <p class="text-sm text-gray-600">{{ transaction.transaction_date }}</p>
                            </div>
                            <p :class="transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'" class="text-3xl font-bold">
                                {{ transaction.type === 'credit' ? '+' : '-' }}₦{{ formatCurrency(transaction.amount) }}
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-4 pt-6 border-t">
                            <div>
                                <p class="text-sm text-gray-600">Type</p>
                                <span
                                    :class="transaction.type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-2"
                                >
                                    {{ transaction.type === 'credit' ? 'Credit' : 'Debit' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Category</p>
                                <p class="font-medium text-gray-900 mt-2">{{ transaction.category_label || 'Uncategorized' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Confidence</p>
                                <div class="flex space-x-0.5 mt-2">
                                    <span v-for="i in 5" :key="i" :class="i <= Math.round(transaction.confidence / 20) ? 'text-yellow-400' : 'text-gray-300'">
                                        ★
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Detailed Information</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Reference</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.reference }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Counterparty</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.counterparty || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">VAT Applicable</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.vat_applicable ? 'Yes' : 'No' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Business Expense</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.is_business_expense ? 'Yes' : 'No' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">User Verified</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.user_verified ? 'Yes' : 'No' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Sub Category</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.sub_category || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Business Information</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Business Name</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.business.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.business.email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Owner</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.business.owner.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Owner Email</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.business.owner.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Account Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Bank Account</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Bank Name</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.bank_account.bank_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Account Number</p>
                                <p class="text-gray-900 font-medium mt-1">{{ transaction.bank_account.account_number }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Metadata -->
                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Metadata</h3>
                        <div class="space-y-3 text-sm">
                            <div class="border-b border-blue-200 pb-3">
                                <p class="text-gray-600">Created At</p>
                                <p class="font-medium text-gray-900">{{ transaction.created_at }}</p>
                            </div>
                            <div class="border-b border-blue-200 pb-3">
                                <p class="text-gray-600">Currency</p>
                                <p class="font-medium text-gray-900">{{ transaction.currency }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Confidence Label</p>
                                <p class="font-medium text-gray-900">{{ transaction.confidence_label }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Status</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Classification</span>
                                <span
                                    :class="transaction.category ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                    class="px-2 py-1 rounded text-xs font-medium"
                                >
                                    {{ transaction.category ? 'Categorized' : 'Uncategorized' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Verification</span>
                                <span
                                    :class="transaction.user_verified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                    class="px-2 py-1 rounded text-xs font-medium"
                                >
                                    {{ transaction.user_verified ? 'Verified' : 'Not Verified' }}
                                </span>
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
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    transaction: Object,
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0)
}
</script>
