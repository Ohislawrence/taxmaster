<template>
    <BusinessLayout>
        <Head title="WHT Transaction Details" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('business.wht.index')" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Transactions
                </Link>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mt-4 gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">WHT Transaction</h1>
                        <p class="text-gray-600 mt-1">{{ transaction.vendor_name }}</p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-800 self-start">
                        {{ transaction.transaction_type_label || formatType(transaction.transaction_type) }}
                    </span>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white shadow rounded-lg p-5">
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Gross Amount</p>
                    <p class="text-xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(transaction.gross_amount) }}</p>
                </div>
                <div :class="transaction.is_double_rate ? 'bg-orange-50 border-orange-200' : 'bg-green-50 border-green-200'"
                     class="shadow rounded-lg p-5 border">
                    <p :class="transaction.is_double_rate ? 'text-orange-600' : 'text-green-600'"
                       class="text-xs font-medium uppercase tracking-wide flex items-center gap-1">
                        WHT Deducted ({{ transaction.wht_rate }}%)
                        <span v-if="transaction.is_double_rate"
                              class="px-1.5 py-0.5 bg-orange-200 text-orange-900 rounded text-xs font-bold"
                              title="Double rate applied per WHT Regulations 2024"
                        >
                            2x
                        </span>
                    </p>
                    <p :class="transaction.is_double_rate ? 'text-orange-700' : 'text-green-700'"
                       class="text-xl font-bold mt-2">₦{{ formatCurrency(transaction.wht_amount) }}</p>
                </div>
                <div class="bg-white shadow rounded-lg p-5">
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Net Amount</p>
                    <p class="text-xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(transaction.net_amount) }}</p>
                </div>
            </div>

            <!-- Double Rate Warning Banner -->
            <div v-if="transaction.is_double_rate" class="mb-6 bg-orange-100 border-l-4 border-orange-500 p-4 rounded">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-orange-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="text-orange-900 font-semibold text-sm">Double Rate Applied</h3>
                        <p class="text-orange-800 text-sm mt-1">
                            Per <strong>WHT Regulations 2024</strong>, this transaction was subject to double the standard WHT rate
                            because the supplier did not provide a valid Tax Identification Number (TIN).
                        </p>
                        <p class="text-orange-800 text-xs mt-2">
                            Standard rate: <strong>{{ transaction.original_rate }}%</strong> → Applied rate: <strong>{{ transaction.wht_rate }}%</strong> (doubled)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Transaction Details -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>Transaction Details
                    </h2>
                </div>
                <div class="px-6 py-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Transaction Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ formatDate(transaction.transaction_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Transaction Type</dt>
                            <dd class="mt-1">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                    {{ transaction.transaction_type_label || formatType(transaction.transaction_type) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Vendor Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">{{ transaction.vendor_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Vendor TIN</dt>
                            <dd class="mt-1 text-sm">
                                <span class="text-gray-900">{{ transaction.vendor_tin || 'Not provided' }}</span>
                                <span v-if="!transaction.vendor_tin || !transaction.tin_validated"
                                      class="ml-2 px-2 py-0.5 bg-orange-100 text-orange-800 text-xs rounded font-medium"
                                >
                                    Missing/Invalid
                                </span>
                                <span v-else-if="transaction.tin_validated"
                                      class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded font-medium"
                                >
                                    ✓ Valid
                                </span>
                            </dd>
                        </div>
                        <div v-if="transaction.payment_reference">
                            <dt class="text-sm font-medium text-gray-500">Payment Reference</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ transaction.payment_reference }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Recorded On</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ formatDate(transaction.created_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Calculation Breakdown -->
            <div class="bg-white shadow rounded-lg mt-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-calculator mr-2 text-green-600"></i>WHT Calculation
                    </h2>
                </div>
                <div class="px-6 py-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Gross Amount</span>
                        <span class="text-gray-900 font-medium">₦{{ formatCurrency(transaction.gross_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            WHT Rate
                            <span v-if="transaction.is_double_rate" class="text-orange-600 font-semibold">
                                (Double Rate Applied)
                            </span>
                        </span>
                        <div class="text-right">
                            <span :class="transaction.is_double_rate ? 'text-orange-600' : 'text-gray-900'"
                                  class="font-medium">{{ transaction.wht_rate }}%</span>
                            <div v-if="transaction.is_double_rate && transaction.original_rate" class="text-xs text-gray-500 mt-1">
                                Standard: {{ transaction.original_rate }}% × 2 = {{ transaction.wht_rate }}%
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between text-sm border-t pt-3">
                        <span class="text-gray-600">WHT Amount ({{ transaction.wht_rate }}% × ₦{{ formatCurrency(transaction.gross_amount) }})</span>
                        <span :class="transaction.is_double_rate ? 'text-orange-600' : 'text-green-600'"
                              class="font-bold">₦{{ formatCurrency(transaction.wht_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-sm border-t pt-3">
                        <span class="font-medium text-gray-900">Net Amount Payable to Vendor</span>
                        <span class="text-lg font-bold text-gray-900">₦{{ formatCurrency(transaction.net_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div v-if="transaction.description" class="bg-white shadow rounded-lg mt-6 px-6 py-4">
                <h3 class="text-sm font-medium text-gray-900">
                    <i class="fas fa-sticky-note mr-1 text-gray-500"></i> Description
                </h3>
                <p class="mt-2 text-sm text-gray-600 whitespace-pre-line">{{ transaction.description }}</p>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-between">
                <Link :href="route('business.wht.index')" class="text-blue-600 hover:text-blue-900 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Transactions
                </Link>
                <button
                    @click="deleteTransaction"
                    class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50"
                >
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    transaction: { type: Object, required: true },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount || 0);
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatType = (type) => {
    if (!type) return 'Unknown';
    return type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

const deleteTransaction = () => {
    if (confirm('Are you sure you want to delete this WHT transaction? This cannot be undone.')) {
        router.delete(route('business.wht.destroy', props.transaction.id));
    }
};
</script>
