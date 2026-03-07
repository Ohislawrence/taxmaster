<template>
    <BusinessLayout>
        <Head :title="`WHT Return - ${whtReturn?.period_label || 'Return'}`" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('business.wht.returns')" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
                    ← Back to WHT Returns
                </Link>
                <div class="flex justify-between items-start mt-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ whtReturn?.period_label || 'WHT Return' }}</h1>
                        <p class="text-gray-600 mt-1">WHT Return Details</p>
                    </div>
                    <span
                        :class="getStatusBadgeClass(whtReturn.status)"
                        class="px-4 py-2 rounded-full text-sm font-bold uppercase"
                    >
                        {{ whtReturn.status }}
                    </span>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                    <p class="text-sm text-blue-600 font-medium">Total Transactions</p>
                    <p class="text-3xl font-bold text-blue-900">{{ whtReturn.transaction_count }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                    <p class="text-sm text-green-600 font-medium">Total WHT Deducted</p>
                    <p class="text-3xl font-bold text-green-900">₦{{ formatCurrency(whtReturn.total_wht_deducted) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                    <p class="text-sm text-purple-600 font-medium">Transaction Types</p>
                    <p class="text-3xl font-bold text-purple-900">{{ scheduleCount }}</p>
                </div>
            </div>

            <!-- Return Information -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Return Information</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600">Period</p>
                        <p class="text-lg font-medium text-gray-900">{{ whtReturn?.period_label || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="text-lg font-medium" :class="getStatusTextClass(whtReturn?.status)">
                            {{ whtReturn?.status_label || 'Unknown' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Filed Date</p>
                        <p class="text-lg font-medium text-gray-900">{{ whtReturn?.filed_date_formatted || 'Not filed' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">FIRS Reference</p>
                        <p class="text-lg font-medium text-gray-900">{{ whtReturn.firs_reference || 'N/A' }}</p>
                    </div>
                </div>

                <div v-if="whtReturn.notes" class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-1">Notes</p>
                    <p class="text-gray-900">{{ whtReturn.notes }}</p>
                </div>
            </div>

            <!-- Schedule Breakdown -->
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">WHT Schedule by Transaction Type</h2>
                </div>

                <div v-if="whtReturn.schedule_data && whtReturn.schedule_data.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction Type</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Count</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">WHT Rate</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Gross</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">WHT Deducted</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in whtReturn.schedule_data" :key="index" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                        {{ item.transaction_type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    {{ item.transaction_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    {{ item.wht_rate }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    ₦{{ formatCurrency(item.total_gross) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                    ₦{{ formatCurrency(item.total_wht) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    ₦{{ formatCurrency(item.total_net) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-900">TOTAL</td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-gray-900">
                                    ₦{{ formatCurrency(totalGross) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-green-600">
                                    ₦{{ formatCurrency(whtReturn.total_wht_deducted) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-gray-900">
                                    ₦{{ formatCurrency(totalNet) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div v-else class="p-8 text-center text-gray-500">
                    No schedule data available
                </div>
            </div>

            <!-- Payment Information -->
            <div v-if="whtReturn.payments && whtReturn.payments.length > 0" class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Information</h2>
                <div v-for="payment in whtReturn.payments" :key="payment.id" class="p-4 border border-gray-200 rounded-lg mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <span
                            :class="getPaymentStatusClass(payment.status)"
                            class="px-3 py-1 rounded-full text-xs font-bold uppercase"
                        >
                            {{ payment.status }}
                        </span>
                        <p class="text-sm text-gray-600">{{ payment.payment_date_formatted || 'Pending' }}</p>
                    </div>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Amount</p>
                            <p class="text-lg font-bold text-gray-900">₦{{ formatCurrency(payment.amount) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Remita RRR</p>
                            <p class="text-lg font-medium text-gray-900">{{ payment.remita_rrr || 'Pending' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Payment Method</p>
                            <p class="text-lg font-medium text-gray-900">{{ payment.payment_method_label || 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center">
                <Link :href="route('business.wht.returns')" class="text-blue-600 hover:text-blue-800">
                    ← Back to Returns
                </Link>

                <div class="flex gap-3">
                    <button
                        v-if="whtReturn.status === 'draft'"
                        @click="markAsFiled"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg"
                    >
                        Mark as Filed
                    </button>
                    <button
                        v-if="whtReturn.status === 'filed' && !hasPayment"
                        @click="generatePaymentRRR"
                        :disabled="generatingRRR"
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-300 text-white font-medium rounded-lg"
                    >
                        {{ generatingRRR ? 'Generating...' : 'Generate Payment RRR' }}
                    </button>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    whtReturn: Object,
});

const generatingRRR = ref(false);

const scheduleCount = computed(() => {
    return props.whtReturn.schedule_data ? props.whtReturn.schedule_data.length : 0;
});

const totalGross = computed(() => {
    if (!props.whtReturn.schedule_data) return 0;
    return props.whtReturn.schedule_data.reduce((sum, item) => sum + parseFloat(item.total_gross || 0), 0);
});

const totalNet = computed(() => {
    if (!props.whtReturn.schedule_data) return 0;
    return props.whtReturn.schedule_data.reduce((sum, item) => sum + parseFloat(item.total_net || 0), 0);
});

const hasPayment = computed(() => {
    return props.whtReturn.payments && props.whtReturn.payments.length > 0;
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount || 0);
};

const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'bg-gray-200 text-gray-800',
        filed: 'bg-blue-200 text-blue-800',
        paid: 'bg-green-200 text-green-800',
        overdue: 'bg-red-200 text-red-800',
    };
    return classes[status] || 'bg-gray-200 text-gray-800';
};

const getStatusTextClass = (status) => {
    const classes = {
        draft: 'text-gray-700',
        filed: 'text-blue-700',
        paid: 'text-green-700',
        overdue: 'text-red-700',
    };
    return classes[status] || 'text-gray-700';
};

const getPaymentStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const markAsFiled = () => {
    if (confirm('Mark this return as filed with FIRS?')) {
        router.put(route('business.wht.return.update-status', props.whtReturn.id), {
            status: 'filed',
            filed_date: new Date().toISOString().split('T')[0],
        });
    }
};

const generatePaymentRRR = () => {
    if (confirm('Generate Remita RRR for payment?')) {
        generatingRRR.value = true;
        router.post(route('business.wht.return.generate-rrr', props.whtReturn.id), {}, {
            onFinish: () => {
                generatingRRR.value = false;
            },
        });
    }
};
</script>
