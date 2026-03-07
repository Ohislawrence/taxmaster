<template>
    <BusinessLayout>
        <Head title="Record WHT Transaction" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('business.wht.index')" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
                    ← Back to Transactions
                </Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-4">Record WHT Transaction</h1>
                <p class="text-gray-600 mt-1">Record a withholding tax deduction</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Transaction Details -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Transaction Details</h2>

                    <div class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Transaction Date <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    v-model="form.transaction_date"
                                    required
                                    class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Transaction Type <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.transaction_type"
                                    @change="onTypeChange"
                                    required
                                    class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Select type</option>
                                    <option v-for="type in transactionTypes" :key="type.value" :value="type.value">
                                        {{ type.label }} ({{ type.rate }}%)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Details -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Vendor/Payee Information</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Beneficiary Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.beneficiary_type"
                                required
                                class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="company">Company (WHT remitted to FIRS)</option>
                                <option value="individual">Individual (WHT remitted to State IRS)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                WHT on companies goes to FIRS. WHT on individuals goes to the relevant State IRS.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Vendor Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                v-model="form.vendor_name"
                                required
                                class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter vendor/payee name"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Vendor TIN (Tax Identification Number)
                            </label>
                            <input
                                type="text"
                                v-model="form.vendor_tin"
                                class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter vendor TIN (optional)"
                            >
                        </div>
                    </div>
                </div>

                <!-- Amount Details -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Amount Details</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Gross Amount <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">₦</span>
                                <input
                                    type="number"
                                    v-model="form.gross_amount"
                                    @input="calculateWHT"
                                    step="0.01"
                                    min="0"
                                    required
                                    class="w-full pl-8 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="0.00"
                                >
                            </div>
                        </div>

                        <div v-if="calculation" class="p-4 bg-blue-50 rounded-lg space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">Gross Amount:</span>
                                <span class="text-lg font-bold text-gray-900">₦{{ formatCurrency(calculation.gross_amount) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">WHT Rate:</span>
                                <span class="text-lg font-bold text-blue-600">{{ calculation.wht_rate }}%</span>
                            </div>
                            <div class="flex justify-between items-center border-t border-blue-200 pt-3">
                                <span class="text-sm font-medium text-gray-700">WHT Amount:</span>
                                <span class="text-2xl font-bold text-green-600">₦{{ formatCurrency(calculation.wht_amount) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">Net Amount to Pay:</span>
                                <span class="text-lg font-bold text-gray-900">₦{{ formatCurrency(calculation.net_amount) }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter transaction description (optional)"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Reference
                            </label>
                            <input
                                type="text"
                                v-model="form.payment_reference"
                                class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter payment reference (optional)"
                            >
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <Link
                        :href="route('business.wht.index')"
                        class="px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="!calculation || processing"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium rounded-lg"
                    >
                        {{ processing ? 'Recording...' : 'Record Transaction' }}
                    </button>
                </div>
            </form>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import axios from 'axios';

const props = defineProps({
    transactionTypes: Array,
});

const form = ref({
    transaction_date: new Date().toISOString().split('T')[0],
    transaction_type: '',
    beneficiary_type: 'company',
    vendor_name: '',
    vendor_tin: '',
    gross_amount: '',
    description: '',
    payment_reference: '',
});

const calculation = ref(null);
const processing = ref(false);

const onTypeChange = () => {
    if (form.value.gross_amount && form.value.transaction_type) {
        calculateWHT();
    }
};

const calculateWHT = async () => {
    if (!form.value.gross_amount || !form.value.transaction_type) {
        calculation.value = null;
        return;
    }

    try {
        const response = await axios.post(route('business.wht.calculate-preview'), {
            gross_amount: parseFloat(form.value.gross_amount),
            transaction_type: form.value.transaction_type,
        });

        calculation.value = response.data;
    } catch (error) {
        console.error('Calculation error:', error);
    }
};

const submitForm = () => {
    if (!calculation.value) {
        alert('Please enter valid amount and transaction type');
        return;
    }

    processing.value = true;

    router.post(route('business.wht.store'), form.value, {
        onFinish: () => {
            processing.value = false;
        },
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount || 0);
};

// Watch for changes to recalculate
watch([() => form.value.gross_amount, () => form.value.transaction_type], () => {
    calculateWHT();
});
</script>
