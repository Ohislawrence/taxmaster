<template>
    <BusinessLayout>
        <Head title="WHT Returns" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">WHT Returns</h1>
                    <p class="text-gray-600 mt-1">Monthly withholding tax return submissions</p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('business.wht.index')"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center"
                    >
                        ← Back to Transactions
                    </Link>
                    <button
                        @click="showGenerateModal = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center"
                    >
                        <span class="mr-2">+</span> Generate Return
                    </button>
                </div>
            </div>

            <!-- Returns List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Monthly Returns</h2>
                </div>

                <div v-if="returns.data.length > 0" class="divide-y divide-gray-200">
                    <div
                        v-for="whtReturn in returns.data"
                        :key="whtReturn.id"
                        class="p-6 hover:bg-gray-50 transition cursor-pointer"
                        @click="viewReturn(whtReturn.id)"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ whtReturn?.period_label || 'Unknown Period' }}
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ whtReturn.transaction_count }} transactions
                                </p>
                            </div>
                            <span
                                :class="getStatusBadgeClass(whtReturn.status)"
                                class="px-3 py-1 rounded-full text-xs font-bold uppercase"
                            >
                                {{ whtReturn.status }}
                            </span>
                        </div>

                        <div class="grid md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded">
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">Period</p>
                                <p class="text-sm font-bold text-gray-900">{{ whtReturn.period || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">Total WHT</p>
                                <p class="text-lg font-bold text-green-600">₦{{ formatCurrency(whtReturn.total_wht_deducted) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">Filed Date</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ whtReturn.filed_date_formatted || 'Not filed' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">FIRS Reference</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ whtReturn.firs_reference || 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="whtReturn.payments && whtReturn.payments.length > 0" class="mt-4">
                            <div
                                v-for="payment in whtReturn.payments"
                                :key="payment.id"
                                class="flex items-center justify-between p-3 bg-blue-50 rounded"
                            >
                                <div>
                                    <p class="text-xs text-blue-600 uppercase tracking-wide">Payment</p>
                                    <p class="text-sm font-bold text-blue-900">
                                        RRR: {{ payment.remita_rrr || 'Pending' }}
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
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No WHT Returns Yet</h3>
                    <p class="text-gray-600 mb-6">Generate your first monthly return from transactions</p>
                    <button
                        @click="showGenerateModal = true"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg"
                    >
                        <span class="mr-2">+</span> Generate Return
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="returns.data.length > 0" class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Showing {{ returns.from }} to {{ returns.to }} of {{ returns.total }} returns
                        </div>
                        <div class="flex gap-2">
                            <component
                                v-for="link in returns.links"
                                :key="link.label"
                                :is="link.url ? Link : 'span'"
                                :href="link.url || undefined"
                                :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                class="px-4 py-2 rounded border text-sm font-medium"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generate Return Modal -->
            <div v-if="showGenerateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Generate WHT Return</h2>
                    <p class="text-gray-600 mb-6">Select the period to generate a return for</p>

                    <form @submit.prevent="generateReturn">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                            <input
                                type="month"
                                v-model="generateForm.period"
                                required
                                class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showGenerateModal = false"
                                class="px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="generating"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium rounded-lg"
                            >
                                {{ generating ? 'Generating...' : 'Generate' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    returns: { type: Object, default: () => ({ data: [], links: [], from: 0, to: 0, total: 0 }) },
});

const showGenerateModal = ref(false);
const generateForm = ref({
    period: new Date().toISOString().slice(0, 7),
});
const generating = ref(false);

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
    router.visit(route('business.wht.return.show', id));
};

const generateReturn = () => {
    generating.value = true;

    router.post(route('business.wht.returns.generate'), generateForm.value, {
        onFinish: () => {
            generating.value = false;
            showGenerateModal.value = false;
        },
    });
};
</script>
