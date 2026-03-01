<template>
    <BusinessLayout>
        <Head title="PAYE Management" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">PAYE Management</h1>
                    <p class="text-gray-600 mt-1">Manage payroll tax (Pay As You Earn) returns and payments</p>
                </div>
                <Link
                    :href="route('business.paye.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center"
                >
                    <span class="mr-2">+</span> Create PAYE Return
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                    <p class="text-sm text-blue-600 font-medium">Total Returns</p>
                    <p class="text-3xl font-bold text-blue-900">{{ stats.total_returns }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                    <p class="text-sm text-green-600 font-medium">Total Tax Collected</p>
                    <p class="text-2xl font-bold text-green-900">₦{{ formatCurrency(stats.total_tax_collected) }}</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-6 border border-orange-200">
                    <p class="text-sm text-orange-600 font-medium">Pending Returns</p>
                    <p class="text-3xl font-bold text-orange-900">{{ stats.pending_returns }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                    <p class="text-sm text-purple-600 font-medium">This Month</p>
                    <p class="text-2xl font-bold text-purple-900">₦{{ formatCurrency(stats.this_month_tax) }}</p>
                </div>
            </div>

            <!-- Returns List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">PAYE Returns</h2>
                </div>

                <div v-if="returns.data.length > 0" class="divide-y divide-gray-200">
                    <div
                        v-for="payeReturn in returns.data"
                        :key="payeReturn.id"
                        class="p-6 hover:bg-gray-50 transition cursor-pointer"
                        @click="viewReturn(payeReturn.id)"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ payeReturn.period_label }}
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ payeReturn.staff_count }} staff members
                                </p>
                            </div>
                            <span
                                :class="getStatusBadgeClass(payeReturn.status)"
                                class="px-3 py-1 rounded-full text-xs font-bold uppercase"
                            >
                                {{ payeReturn.status }}
                            </span>
                        </div>

                        <div class="grid md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded">
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">Total Gross Pay</p>
                                <p class="text-lg font-bold text-gray-900">₦{{ formatCurrency(payeReturn.total_gross_pay) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">PAYE Deducted</p>
                                <p class="text-lg font-bold text-green-600">₦{{ formatCurrency(payeReturn.total_tax_deducted) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">Filed Date</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ payeReturn.filed_date || 'Not filed' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">FIRS Reference</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ payeReturn.firs_reference || 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="payeReturn.payments && payeReturn.payments.length > 0" class="mt-4">
                            <div
                                v-for="payment in payeReturn.payments"
                                :key="payment.id"
                                class="flex items-center justify-between p-3 bg-blue-50 rounded"
                            >
                                <div>
                                    <p class="text-xs text-blue-600 uppercase tracking-wide">Payment</p>
                                    <p class="text-sm font-bold text-blue-900">
                                        RRR: {{ payment.remita_rrr }}
                                    </p>
                                </div>
                                <span
                                    :class="getPaymentStatusClass(payment.status)"
                                    class="px-3 py-1 rounded-full text-xs font-bold"
                                >
                                    {{ payment.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="p-12 text-center">
                    <div class="text-gray-400 text-6xl mb-4">📊</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No PAYE Returns Yet</h3>
                    <p class="text-gray-600 mb-6">Create your first PAYE return to track payroll taxes</p>
                    <Link
                        :href="route('business.paye.create')"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg"
                    >
                        <span class="mr-2">+</span> Create PAYE Return
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="returns.data.length > 0" class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Showing {{ returns.from }} to {{ returns.to }} of {{ returns.total }} returns
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in returns.links"
                                :key="link.label"
                                :href="link.url"
                                :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                class="px-4 py-2 rounded border text-sm font-medium"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    returns: Object,
    stats: Object,
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

const getPaymentStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const viewReturn = (id) => {
    router.visit(route('business.paye.show', id));
};
</script>
