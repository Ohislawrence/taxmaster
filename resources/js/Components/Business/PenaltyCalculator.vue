<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Penalty & Interest Calculator</h3>
        <p class="text-sm text-gray-600 mb-6">Calculate penalties and interest for late tax payments</p>

        <!-- Input Form -->
        <div class="space-y-4">
            <!-- Original Tax Amount -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Original Tax Amount</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">₦</span>
                    <input 
                        v-model="form.taxAmount" 
                        type="number" 
                        step="0.01"
                        min="0"
                        class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                        @input="calculate"
                    />
                </div>
            </div>

            <!-- Due Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                <input 
                    v-model="form.dueDate" 
                    type="date" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @change="calculate"
                />
            </div>

            <!-- Payment Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date (Optional)</label>
                <input 
                    v-model="form.paymentDate" 
                    type="date" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @change="calculate"
                />
                <p class="text-xs text-gray-500 mt-1">Leave blank to calculate until today</p>
            </div>

            <!-- Penalty Rate -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Penalty Rate (%)</label>
                <input 
                    v-model="form.penaltyRate" 
                    type="number" 
                    step="0.1"
                    min="0"
                    max="100"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @input="calculate"
                />
                <p class="text-xs text-gray-500 mt-1">Default: 10% one-time penalty</p>
            </div>

            <!-- Interest Rate -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (% per annum)</label>
                <input 
                    v-model="form.interestRate" 
                    type="number" 
                    step="0.1"
                    min="0"
                    max="100"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @input="calculate"
                />
                <p class="text-xs text-gray-500 mt-1">Default: 21% per annum (FIRS standard)</p>
            </div>
        </div>

        <!-- Results -->
        <div v-if="result" class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-semibold text-gray-900 mb-3">Calculation Results</h4>
            
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Days Overdue:</span>
                    <span class="font-medium text-gray-900">{{ result.daysOverdue }} days</span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Original Tax Amount:</span>
                    <span class="font-medium text-gray-900">₦{{ formatCurrency(result.originalAmount) }}</span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Late Filing Penalty ({{ form.penaltyRate }}%):</span>
                    <span class="font-medium text-red-600">₦{{ formatCurrency(result.penalty) }}</span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Interest ({{ form.interestRate }}% p.a.):</span>
                    <span class="font-medium text-red-600">₦{{ formatCurrency(result.interest) }}</span>
                </div>

                <div class="pt-3 border-t border-gray-200">
                    <div class="flex justify-between">
                        <span class="font-bold text-gray-900">Total Amount Due:</span>
                        <span class="font-bold text-lg text-red-600">₦{{ formatCurrency(result.totalDue) }}</span>
                    </div>
                </div>
            </div>

            <!-- Warning Message -->
            <div v-if="result.daysOverdue > 0" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-sm text-yellow-800">
                    <span class="font-medium">⚠️ Warning:</span>
                    This payment is {{ result.daysOverdue }} days overdue. Pay immediately to avoid additional charges.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const form = ref({
    taxAmount: '',
    dueDate: '',
    paymentDate: '',
    penaltyRate: 10,
    interestRate: 21,
});

const result = ref(null);

const calculate = () => {
    const taxAmount = parseFloat(form.value.taxAmount) || 0;
    const dueDate = form.value.dueDate ? new Date(form.value.dueDate) : null;
    const paymentDate = form.value.paymentDate ? new Date(form.value.paymentDate) : new Date();
    
    if (!taxAmount || !dueDate) {
        result.value = null;
        return;
    }
    
    // Calculate days overdue
    const daysDiff = Math.max(0, Math.ceil((paymentDate - dueDate) / (1000 * 60 * 60 * 24)));
    
    // Calculate penalty (one-time percentage)
    const penalty = daysDiff > 0 ? (taxAmount * form.value.penaltyRate / 100) : 0;
    
    // Calculate interest (prorated annually)
    const interest = daysDiff > 0 ? (taxAmount * form.value.interestRate / 100 * daysDiff / 365) : 0;
    
    // Total due
    const totalDue = taxAmount + penalty + interest;
    
    result.value = {
        daysOverdue: daysDiff,
        originalAmount: taxAmount,
        penalty: penalty,
        interest: interest,
        totalDue: totalDue,
    };
};

const formatCurrency = (value) => {
    if (!value) return '0.00';
    return parseFloat(value).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

onMounted(() => {
    // Set default payment date to today
    form.value.paymentDate = new Date().toISOString().split('T')[0];
});
</script>
