<template>
    <BusinessLayout>
        <Head title="Edit Tax Return" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <div class="mb-8">
                <Link :href="`/business/tax-returns/${taxReturn.id}`" class="text-blue-600 hover:underline">&larr; Back to Tax Return</Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-4">Edit Tax Return</h1>
                <p class="text-gray-600 mt-1">{{ taxReturn.return_type }} - {{ taxReturn.tax_period }}</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6">
                <!-- Current Summary -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-blue-900 text-sm">
                        <strong>Note:</strong> Editing this return will recalculate all tax amounts based on the information you provide.
                    </p>
                </div>

                <!-- Return Type & Period (Read-only) -->
                <div class="grid grid-cols-2 gap-6 mb-6 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Return Type</label>
                        <p class="text-lg font-medium text-gray-900">{{ taxReturn.return_type }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tax Period</label>
                        <p class="text-lg font-medium text-gray-900">{{ taxReturn.tax_period }}</p>
                    </div>
                </div>

                <!-- Editable Fields -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date *</label>
                    <input 
                        v-model="form.due_date" 
                        type="date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="errors.due_date" class="text-red-600 text-sm mt-1">{{ errors.due_date[0] }}</p>
                </div>

                <!-- Income Section -->
                <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                    <h3 class="font-semibold text-gray-900 mb-4">Income Information</h3>
                    
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gross Income *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-2 text-gray-700">₦</span>
                                <input 
                                    v-model="form.gross_income" 
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    @input="recalculateTax"
                                    class="w-full pl-8 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <p v-if="errors.gross_income" class="text-red-600 text-sm mt-1">{{ errors.gross_income[0] }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deductions</label>
                            <div class="relative">
                                <span class="absolute left-4 top-2 text-gray-700">₦</span>
                                <input 
                                    v-model="form.deductions" 
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    @input="recalculateTax"
                                    class="w-full pl-8 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <p class="text-gray-600 text-xs mt-1">Tax-deductible expenses</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-gray-600 text-sm">Taxable Income</p>
                                <p class="text-lg font-bold text-gray-900 mt-1">₦{{ formatCurrency(taxableIncome) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Previous Total Tax</p>
                                <p class="text-lg font-bold text-gray-900 mt-1">₦{{ formatCurrency(taxReturn.total_tax_due) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">New Estimated Tax</p>
                                <p class="text-lg font-bold text-orange-600 mt-1">₦{{ formatCurrency(estimatedTax) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description/Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea 
                        v-model="form.description" 
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Add any notes about changes made..."
                    ></textarea>
                </div>

                <!-- Tax Comparison -->
                <div v-if="taxDiff !== 0" class="mb-6 p-4 rounded-lg" :class="taxDiff > 0 ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'">
                    <p :class="taxDiff > 0 ? 'text-red-900' : 'text-green-900'" class="text-sm font-medium">
                        <v-if condition="taxDiff > 0">
                            Tax will <strong>increase by ₦{{ formatCurrency(Math.abs(taxDiff)) }}</strong>
                        </v-if>
                        <v-else>
                            Tax will <strong>decrease by ₦{{ formatCurrency(Math.abs(taxDiff)) }}</strong>
                        </v-else>
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button 
                        type="submit"
                        :disabled="processing"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        {{ processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <Link :href="`/business/tax-returns/${taxReturn.id}`" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition">
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
    taxReturn: Object,
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const processing = ref(false);
const estimatedTax = ref(props.taxReturn?.total_tax_due || 0);

const form = ref({
    due_date: props.taxReturn?.due_date || '',
    gross_income: props.taxReturn?.gross_income || '',
    deductions: props.taxReturn?.deductions || 0,
    description: '',
});

const taxableIncome = computed(() => {
    const gross = parseFloat(form.value.gross_income) || 0;
    const deductions = parseFloat(form.value.deductions) || 0;
    return gross - deductions;
});

const taxDiff = computed(() => {
    return estimatedTax.value - (props.taxReturn?.total_tax_due || 0);
});

const recalculateTax = () => {
    // Simple tax calculation (10% for demo)
    // In production, this would call the actual TaxCalculationService
    const income = taxableIncome.value;
    estimatedTax.value = income * 0.10; // Placeholder calculation
};

const submitForm = () => {
    processing.value = true;
    router.put(`/business/tax-returns/${props.taxReturn.id}`, form.value, {
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
