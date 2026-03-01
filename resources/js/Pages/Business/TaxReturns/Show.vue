<template>
    <BusinessLayout>
        <Head :title="`${taxReturn.return_type} - ${taxReturn.tax_period}`" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <div class="mb-8">
                <Link href="/business/tax-returns" class="text-blue-600 hover:underline">&larr; Back to Tax Returns</Link>
                <div class="flex justify-between items-start mt-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ taxReturn.return_type }} - {{ taxReturn.tax_period }}</h1>
                        <p class="text-gray-600 mt-1">Due by {{ formatDate(taxReturn.due_date) }}</p>
                    </div>
                    <span :class="getStatusColor(taxReturn.status)" class="px-4 py-2 rounded-full font-medium">
                        {{ taxReturn.status.charAt(0).toUpperCase() + taxReturn.status.slice(1) }}
                    </span>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Summary Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Tax Summary</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="border-b border-gray-200 pb-4">
                                <p class="text-gray-600 text-sm">Total Tax Due</p>
                                <p class="text-2xl font-bold text-red-600 mt-1">₦{{ formatCurrency(taxReturn.total_tax_due) }}</p>
                            </div>
                            <div class="border-b border-gray-200 pb-4">
                                <p class="text-gray-600 text-sm">Amount Paid</p>
                                <p class="text-2xl font-bold text-green-600 mt-1">₦{{ formatCurrency(taxReturn.amount_paid) }}</p>
                            </div>
                            <div class="pt-4">
                                <p class="text-gray-600 text-sm">Outstanding Balance</p>
                                <p class="text-2xl font-bold" :class="taxReturn.balance > 0 ? 'text-orange-600' : 'text-green-600'" >
                                    ₦{{ formatCurrency(taxReturn.balance) }}
                                </p>
                            </div>
                            <div class="pt-4">
                                <p class="text-gray-600 text-sm">Status</p>
                                <p class="text-lg font-semibold text-gray-900 mt-1 capitalize">{{ taxReturn.status }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Breakdown -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Staff Tax Breakdown</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-4 font-medium text-gray-700">Staff Member</th>
                                        <th class="text-right py-3 px-4 font-medium text-gray-700">Gross Salary</th>
                                        <th class="text-right py-3 px-4 font-medium text-gray-700">Tax</th>
                                        <th class="text-right py-3 px-4 font-medium text-gray-700">Net Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in staffBreakdown" :key="item.staff_id" class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3 px-4 font-medium text-gray-900">{{ item.staff_name }}</td>
                                        <td class="py-3 px-4 text-right font-mono">₦{{ formatCurrency(item.gross_salary) }}</td>
                                        <td class="py-3 px-4 text-right font-mono text-red-600">₦{{ formatCurrency(item.tax) }}</td>
                                        <td class="py-3 px-4 text-right font-mono text-green-600 font-medium">₦{{ formatCurrency(item.net_pay) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Deductions -->
                    <div v-if="taxReturn.deductions && taxReturn.deductions.length > 0" class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Deductions</h2>
                        <div class="space-y-3">
                            <div v-for="deduction in taxReturn.deductions" :key="deduction.id" class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-700">{{ deduction.name }}</span>
                                <span class="font-mono">₦{{ formatCurrency(deduction.amount) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-4">
                        <button 
                            v-if="taxReturn.status === 'draft'"
                            @click="editReturn"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
                        >
                            Edit Return
                        </button>
                        <button 
                            v-if="taxReturn.status === 'draft'"
                            @click="submitReturn"
                            :disabled="submitting"
                            class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition"
                        >
                            {{ submitting ? 'Submitting...' : 'Submit Return' }}
                        </button>
                        <button 
                            v-if="taxReturn.status === 'approved' && taxReturn.balance > 0"
                            @click="makePayment"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition"
                        >
                            Make Payment
                        </button>
                        <button 
                            @click="requestAnalysis"
                            :disabled="analyzing"
                            class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition"
                        >
                            {{ analyzing ? 'Analyzing...' : 'AI Analysis' }}
                        </button>
                    </div>

                    <!-- AI Analysis Component -->
                    <TaxAnalysis :taxReturnId="taxReturn.id" />
                </div>

                <!-- Right Sidebar -->
                <div class="space-y-6">
                    <!-- Return Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Return Details</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-gray-600 text-sm">Return Type</p>
                                <p class="font-medium text-gray-900">{{ taxReturn.return_type }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Period</p>
                                <p class="font-medium text-gray-900">{{ formatDate(taxReturn.tax_period_start) }} - {{ formatDate(taxReturn.tax_period_end) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Due Date</p>
                                <p class="font-medium text-gray-900">{{ formatDate(taxReturn.due_date) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Status</p>
                                <p class="font-medium text-gray-900 capitalize">{{ taxReturn.status }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Created</p>
                                <p class="font-medium text-gray-900">{{ formatDate(taxReturn.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- AI Analysis Result -->
                    <div v-if="aiAnalysis" class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="font-semibold text-blue-900 mb-2">AI Analysis</h3>
                        <p class="text-sm text-blue-800">{{ aiAnalysis }}</p>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'
import TaxAnalysis from '@/Pages/Business/Ai/TaxAnalysis.vue'

defineProps({
    taxReturn: Object,
    staffBreakdown: Array,
    aiAnalysis: String,
});

const submitting = ref(false);
const analyzing = ref(false);

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
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status) => {
    const colors = {
        draft: 'bg-gray-100 text-gray-800',
        submitted: 'bg-blue-100 text-blue-800',
        approved: 'bg-green-100 text-green-800',
        paid: 'bg-purple-100 text-purple-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const editReturn = () => {
    router.get(`/business/tax-returns/${taxReturn.id}/edit`);
};

const submitReturn = () => {
    submitting.value = true;
    router.post(`/business/tax-returns/${taxReturn.id}/submit`, {}, {
        onFinish: () => {
            submitting.value = false;
        },
    });
};

const makePayment = () => {
    router.get(`/business/payments/create?tax_return_id=${taxReturn.id}`);
};

const requestAnalysis = () => {
    analyzing.value = true;
    router.post(`/business/tax-returns/${taxReturn.id}/analyze`, {}, {
        onFinish: () => {
            analyzing.value = false;
        },
    });
};
</script>
