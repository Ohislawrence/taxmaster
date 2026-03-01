<template>
    <BusinessLayout>
        <Head title="Create Payment" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <div class="mb-8">
                <Link href="/business/payments" class="text-blue-600 hover:underline">&larr; Back to Payments</Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-4">Make a Payment</h1>
                <p class="text-gray-600 mt-1">Complete your tax payment for an approved return</p>
            </div>

            <!-- Payment Form -->
            <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6">
                <!-- Select Tax Return -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Tax Return</label>
                    <select 
                        v-model="form.tax_return_id" 
                        @change="onReturnSelected"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">-- Select a return --</option>
                        <option v-for="ret in availableReturns" :key="ret.id" :value="ret.id">
                            {{ ret.return_type }} - {{ ret.tax_period }} (Balance: ₦{{ formatCurrency(ret.balance) }})
                        </option>
                    </select>
                    <p v-if="errors.tax_return_id" class="text-red-600 text-sm mt-1">{{ errors.tax_return_id[0] }}</p>
                </div>

                <!-- Return Details (if selected) -->
                <div v-if="selectedReturn" class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-gray-600 text-sm">Total Due</p>
                            <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(selectedReturn.total_tax_due) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Already Paid</p>
                            <p class="text-2xl font-bold text-green-600">₦{{ formatCurrency(selectedReturn.amount_paid) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Outstanding</p>
                            <p class="text-2xl font-bold text-red-600">₦{{ formatCurrency(selectedReturn.balance) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Amount -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                    <div class="relative">
                        <span class="absolute left-4 top-2 text-gray-700 font-medium">₦</span>
                        <input 
                            v-model="form.amount" 
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full pl-8 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0.00"
                        />
                    </div>
                    <p class="text-gray-600 text-sm mt-1">Maximum: ₦{{ formatCurrency(selectedReturn?.balance || 0) }}</p>
                    <p v-if="errors.amount" class="text-red-600 text-sm mt-1">{{ errors.amount[0] }}</p>
                </div>

                <!-- Payment Method -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <div class="space-y-3">
                        <label class="flex items-center cursor-pointer">
                            <input 
                                v-model="form.payment_method" 
                                type="radio"
                                value="paystack"
                                class="form-radio"
                            />
                            <span class="ml-3">
                                <span class="font-medium text-gray-900">Paystack (Debit Card / Bank Transfer)</span>
                                <p class="text-sm text-gray-600">Fast and secure online payment</p>
                            </span>
                        </label>
                        <label class="flex items-center cursor-pointer text-gray-400">
                            <input 
                                v-model="form.payment_method" 
                                type="radio"
                                value="bank_transfer"
                                disabled
                                class="form-radio"
                            />
                            <span class="ml-3">
                                <span class="font-medium">Bank Transfer (Coming Soon)</span>
                                <p class="text-sm">Direct bank deposit</p>
                            </span>
                        </label>
                    </div>
                    <p v-if="errors.payment_method" class="text-red-600 text-sm mt-1">{{ errors.payment_method[0] }}</p>
                </div>

                <!-- Email for Receipt -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address (for receipt)</label>
                    <input 
                        v-model="form.email" 
                        type="email"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="errors.email" class="text-red-600 text-sm mt-1">{{ errors.email[0] }}</p>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea 
                        v-model="form.notes" 
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Add any payment references or notes..."
                    ></textarea>
                </div>

                <!-- Terms -->
                <div class="mb-6">
                    <label class="flex items-start cursor-pointer">
                        <input 
                            v-model="form.accept_terms" 
                            type="checkbox"
                            class="mt-1"
                        />
                        <span class="ml-3 text-sm text-gray-700">
                            I agree to the <a href="#" class="text-blue-600 hover:underline">payment terms</a> and 
                            <a href="#" class="text-blue-600 hover:underline">privacy policy</a>
                        </span>
                    </label>
                    <p v-if="errors.accept_terms" class="text-red-600 text-sm mt-1">{{ errors.accept_terms[0] }}</p>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button 
                        type="submit"
                        :disabled="processing || !form.accept_terms"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        {{ processing ? 'Processing...' : 'Continue to Payment' }}
                    </button>
                    <Link href="/business/payments" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

const props = defineProps({
    availableReturns: Array,
    defaultReturnId: Number,
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const processing = ref(false);
const selectedReturn = ref(null);

const form = ref({
    tax_return_id: props.defaultReturnId || '',
    amount: '',
    payment_method: 'paystack',
    email: '',
    notes: '',
    accept_terms: false,
});

const onReturnSelected = () => {
    if (form.value.tax_return_id) {
        selectedReturn.value = props.availableReturns.find(r => r.id == form.value.tax_return_id);
        // Auto-fill with outstanding balance
        form.value.amount = selectedReturn.value.balance;
    }
};

const submitForm = () => {
    processing.value = true;
    router.post('/business/payments', form.value, {
        onFinish: () => {
            processing.value = false;
        },
    });
};

const formatCurrency = (value) => {
    if (!value) return '0.00'
    return parseFloat(value).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
</script>
