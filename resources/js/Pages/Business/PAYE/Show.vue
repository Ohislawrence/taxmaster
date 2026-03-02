<template>
    <BusinessLayout>
        <Head :title="`PAYE Return - ${payeReturn.period_label}`" />

        <div class="py-4 sm:py-8 px-3 sm:px-4 lg:px-8">
            <!-- Header -->
            <div class="mb-6 sm:mb-8">
                <Link :href="route('business.paye.index')" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center text-sm">
                    ← Back to PAYE Returns
                </Link>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mt-4">
                    <div class="flex-1">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ payeReturn.period_label }}</h1>
                        <p class="text-sm sm:text-base text-gray-600 mt-1">PAYE Return Details</p>
                    </div>
                    <span
                        :class="getStatusBadgeClass(payeReturn.status)"
                        class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold uppercase w-full sm:w-auto text-center"
                    >
                        {{ payeReturn.status }}
                    </span>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <div class="bg-blue-50 rounded-lg p-4 sm:p-6 border border-blue-200">
                    <p class="text-xs sm:text-sm text-blue-600 font-medium">Total Gross Pay</p>
                    <p class="text-2xl sm:text-3xl font-bold text-blue-900 mt-2">₦{{ formatCurrency(payeReturn.total_gross_pay) }}</p>
                    <p class="text-xs text-blue-600 mt-2">{{ payeReturn.staff_count }} staff members</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 sm:p-6 border border-green-200">
                    <p class="text-xs sm:text-sm text-green-600 font-medium">PAYE Deducted</p>
                    <p class="text-2xl sm:text-3xl font-bold text-green-900 mt-2">₦{{ formatCurrency(payeReturn.total_tax_deducted) }}</p>
                    <p class="text-xs text-green-600 mt-2">Total tax liability</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 sm:p-6 border border-purple-200">
                    <p class="text-xs sm:text-sm text-purple-600 font-medium">Average PAYE Rate</p>
                    <p class="text-2xl sm:text-3xl font-bold text-purple-900 mt-2">{{ averageRate }}%</p>
                    <p class="text-xs text-purple-600 mt-2">Effective tax rate</p>
                </div>
            </div>

            <!-- Return Information -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Return Information</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600">Period</p>
                        <p class="text-lg font-medium text-gray-900">{{ payeReturn.period_label }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="text-lg font-medium" :class="getStatusTextClass(payeReturn.status)">
                            {{ payeReturn.status_label }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Filed Date</p>
                        <p class="text-lg font-medium text-gray-900">{{ payeReturn.filed_date || 'Not filed' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">FIRS Reference</p>
                        <p class="text-lg font-medium text-gray-900">{{ payeReturn.firs_reference || 'N/A' }}</p>
                    </div>
                </div>

                <div v-if="payeReturn.notes" class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-1">Notes</p>
                    <p class="text-gray-900">{{ payeReturn.notes }}</p>
                </div>
            </div>

            <!-- Staff Schedules -->
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Staff PAYE Schedules</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross Pay</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Allowances</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reliefs</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Taxable</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">PAYE Due</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="schedule in payeReturn.schedules" :key="schedule.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ schedule.staff.first_name }} {{ schedule.staff.last_name }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ schedule.staff.job_title }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    ₦{{ formatCurrency(schedule.gross_pay) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                    ₦{{ formatCurrency(schedule.total_allowances) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                    ₦{{ formatCurrency(schedule.total_reliefs) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    ₦{{ formatCurrency(schedule.taxable_income) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                    ₦{{ formatCurrency(schedule.paye_due) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Information -->
            <div v-if="payeReturn.payments && payeReturn.payments.length > 0" class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Information</h2>
                <div v-for="payment in payeReturn.payments" :key="payment.id" class="p-4 border border-gray-200 rounded-lg mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <span
                            :class="getPaymentStatusClass(payment.status)"
                            class="px-3 py-1 rounded-full text-xs font-bold uppercase"
                        >
                            {{ payment.status }}
                        </span>
                        <p class="text-sm text-gray-600">{{ payment.payment_date }}</p>
                    </div>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Amount</p>
                            <p class="text-lg font-bold text-gray-900">₦{{ formatCurrency(payment.amount) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Remita RRR</p>
                            <p class="text-lg font-medium text-gray-900">{{ payment.remita_rrr }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Payment Method</p>
                            <p class="text-lg font-medium text-gray-900">{{ payment.payment_method_label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center">
                <Link :href="route('business.paye.index')" class="text-blue-600 hover:text-blue-800">
                    ← Back to Returns
                </Link>

                <div class="flex gap-3">
                    <button
                        v-if="payeReturn.status === 'draft'"
                        @click="markAsFiled"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg"
                    >
                        Mark as Filed
                    </button>
                    <button
                        v-if="payeReturn.status === 'filed' && !hasPayment"
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
    payeReturn: Object,
});

const generatingRRR = ref(false);

const averageRate = computed(() => {
    if (props.payeReturn.total_gross_pay === 0) return '0.00';
    return ((props.payeReturn.total_tax_deducted / props.payeReturn.total_gross_pay) * 100).toFixed(2);
});

const hasPayment = computed(() => {
    return props.payeReturn.payments && props.payeReturn.payments.length > 0;
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
        router.put(route('business.paye.update-status', props.payeReturn.id), {
            status: 'filed',
            filed_date: new Date().toISOString().split('T')[0],
        });
    }
};

const generatePaymentRRR = () => {
    if (confirm('Generate Remita RRR for payment?')) {
        generatingRRR.value = true;
        router.post(route('business.paye.generate-rrr', props.payeReturn.id), {}, {
            onFinish: () => {
                generatingRRR.value = false;
            },
        });
    }
};
</script>
