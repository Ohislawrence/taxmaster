<template>
    <BusinessLayout>
        <Head :title="`Payment - ${payment.payment_reference}`" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <Link href="/business/payments" class="text-blue-600 hover:underline">&larr; Back to Payments</Link>

            <div class="mt-6 bg-white rounded-lg shadow p-8">
                <!-- Receipt Header -->
                <div class="text-center py-6 border-b border-gray-200 mb-6">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-full" :class="getStatusBg(payment.status)">
                        <svg v-if="payment.status === 'completed'" class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else-if="payment.status === 'pending'" class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ getStatusText(payment.status) }}</h1>
                    <p class="text-gray-600 mt-2">Reference: {{ payment.payment_reference }}</p>
                </div>

                <!-- Payment Amount -->
                <div class="text-center py-6 border-b border-gray-200 mb-6">
                    <p class="text-gray-600 text-sm">Payment Amount</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(payment.amount) }}</p>
                </div>

                <!-- Payment Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-6 border-b border-gray-200 mb-6">
                    <div>
                        <p class="text-gray-600 text-sm">Tax Return</p>
                        <p class="font-medium text-gray-900 mt-1">{{ payment.taxReturn.return_type }} - {{ payment.taxReturn.tax_period }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Payment Date</p>
                        <p class="font-medium text-gray-900 mt-1">{{ formatDate(payment.payment_date) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Payment Method</p>
                        <p class="font-medium text-gray-900 mt-1 capitalize">{{ payment.payment_method }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Status</p>
                        <p class="font-medium text-gray-900 mt-1 capitalize" :class="getStatusColor(payment.status)">
                            {{ payment.status }}
                        </p>
                    </div>
                </div>

                <!-- Paystack Reference (if completed) -->
                <div v-if="payment.status === 'completed' && payment.paystack_reference" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-900 text-sm"><strong>Paystack Reference:</strong> {{ payment.paystack_reference }}</p>
                </div>

                <!-- Failure Reason (if failed) -->
                <div v-if="payment.status === 'failed' && payment.failure_reason" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-900 text-sm"><strong>Reason:</strong> {{ payment.failure_reason }}</p>
                </div>

                <!-- Payment Timeline -->
                <div class="py-6 border-b border-gray-200 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Timeline</h3>
                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="w-3 h-3 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
                            <div>
                                <p class="font-medium text-gray-900">Payment Initiated</p>
                                <p class="text-sm text-gray-600">{{ formatDate(payment.created_at) }}</p>
                            </div>
                        </div>
                        <div v-if="payment.payment_date" class="flex gap-4">
                            <div class="w-3 h-3 bg-green-600 rounded-full mt-2 flex-shrink-0"></div>
                            <div>
                                <p class="font-medium text-gray-900">Payment {{ payment.status === 'completed' ? 'Completed' : 'Processed' }}</p>
                                <p class="text-sm text-gray-600">{{ formatDate(payment.payment_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button 
                        @click="downloadReceipt"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        Download Receipt
                    </button>
                    <button 
                        @click="printReceipt"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        Print Receipt
                    </button>
                    <Link href="/business/payments" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition">
                        Back to Payments
                    </Link>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

defineProps({
    payment: Object,
});

const getStatusText = (status) => {
    const texts = {
        completed: 'Payment Successful',
        pending: 'Payment Pending',
        failed: 'Payment Failed',
        processing: 'Processing Payment',
    };
    return texts[status] || 'Unknown Status';
};

const getStatusBg = (status) => {
    const bg = {
        completed: 'bg-green-100',
        pending: 'bg-yellow-100',
        failed: 'bg-red-100',
        processing: 'bg-blue-100',
    };
    return bg[status] || 'bg-gray-100';
};

const getStatusColor = (status) => {
    const colors = {
        completed: 'text-green-600',
        pending: 'text-yellow-600',
        failed: 'text-red-600',
        processing: 'text-blue-600',
    };
    return colors[status] || 'text-gray-600';
};

const formatCurrency = (value) => {
    if (!value) return '0.00'
    return parseFloat(value).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const downloadReceipt = () => {
    alert('Download receipt functionality coming soon');
};

const printReceipt = () => {
    window.print();
};
</script>
