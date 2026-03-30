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
                            <div class="relative">
                                <input
                                    type="text"
                                    v-model="form.vendor_tin"
                                    @input="onTinChange"
                                    maxlength="16"
                                    class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                    :class="{
                                        'border-green-500': tinValidationStatus === 'valid',
                                        'border-red-500': tinValidationStatus === 'invalid'
                                    }"
                                    placeholder="e.g., 12345678901 (11-14 digits)"
                                >
                                <div v-if="tinValidationStatus === 'valid'" class="absolute right-3 top-2.5 text-green-500">
                                    ✓
                                </div>
                                <div v-else-if="tinValidationStatus === 'invalid'" class="absolute right-3 top-2.5 text-red-500">
                                    ✗
                                </div>
                            </div>
                            <p v-if="!form.vendor_tin" class="text-xs text-orange-600 mt-1 flex items-start gap-1">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Warning:</strong> Per WHT Regulations 2024, double rate will apply if vendor TIN is missing or invalid.</span>
                            </p>
                            <p v-else-if="tinValidationStatus === 'valid'" class="text-xs text-green-600 mt-1">
                                ✓ Valid TIN format - Standard rate will apply
                            </p>
                            <p v-else-if="tinValidationStatus === 'invalid'" class="text-xs text-red-600 mt-1">
                                ✗ Invalid TIN format - Double rate will apply (WHT Regulations 2024)
                            </p>
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
                            <!-- Double Rate Warning -->
                            <div v-if="calculation.is_double_rate" class="mb-3 p-3 bg-orange-100 border border-orange-300 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-orange-900">Double Rate Applied</p>
                                        <p class="text-xs text-orange-800 mt-1">
                                            Per WHT Regulations 2024, suppliers without a valid TIN are subject to double the standard WHT rate.
                                            <span class="font-medium">Standard rate: {{ calculation.original_rate }}% → Applied rate: {{ calculation.wht_rate }}%</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">Gross Amount:</span>
                                <span class="text-lg font-bold text-gray-900">₦{{ formatCurrency(calculation.gross_amount) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">WHT Rate:</span>
                                <span class="text-lg font-bold" :class="calculation.is_double_rate ? 'text-orange-600' : 'text-blue-600'">
                                    {{ calculation.wht_rate }}%
                                    <span v-if="calculation.is_double_rate" class="text-xs">(doubled)</span>
                                </span>
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
const tinValidationStatus = ref(null); // null, 'valid', or 'invalid'

const validateTinFormat = (tin) => {
    if (!tin) {
        return null;
    }

    // Remove spaces and hyphens
    const cleanTin = tin.replace(/[\s\-]/g, '');

    // Must be 11-14 digits
    if (/^\d{11,14}$/.test(cleanTin)) {
        return 'valid';
    }

    return 'invalid';
};

const onTinChange = () => {
    tinValidationStatus.value = validateTinFormat(form.value.vendor_tin);
    calculateWHT();
};

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
