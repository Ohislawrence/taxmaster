<template>
    <AdminLayout>
        <Head :title="`WHT Transaction - ${transaction.vendor_name}`" />

        <div class="space-y-8">
            <!-- Header -->
            <div class="mb-8">
                <Link href="/admin/wht-returns" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
                    ← Back to WHT Transactions
                </Link>
                <h1 class="text-4xl font-bold text-gray-900 mt-4">WHT Transaction</h1>
                <p class="text-gray-600 mt-2">{{ transaction.business.name }}</p>
            </div>

            <!-- Transaction Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Transaction Details</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600 text-sm">Transaction Date</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ formatDate(transaction.transaction_date) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Transaction Type</p>
                        <p class="text-gray-900 font-semibold mt-1 capitalize">{{ transaction.transaction_type.replace('_', ' ') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Vendor Name</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ transaction.vendor_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Vendor Email</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ transaction.vendor_email || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Invoice Number</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ transaction.invoice_number || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Business Name</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ transaction.business.name }}</p>
                    </div>
                </div>
            </div>

            <!-- Amount Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Gross Amount</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">₦{{ formatCurrency(transaction.gross_amount) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">WHT Rate</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ transaction.wht_rate }}%</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">WHT Amount</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">₦{{ formatCurrency(transaction.wht_amount) }}</p>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600 text-sm">Description</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ transaction.description || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Created At</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ new Date(transaction.created_at).toLocaleDateString() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    transaction: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG');
};
</script>
