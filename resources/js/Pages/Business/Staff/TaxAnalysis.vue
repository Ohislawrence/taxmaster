<template>
    <BusinessLayout>
        <Head :title="`Tax Analysis - ${staff.full_name}`" />

        <div class="py-4 sm:py-8 px-3 sm:px-4 lg:px-8 max-w-4xl mx-auto">
            <Link :href="`/business/staff/${staff.id}`" class="text-blue-600 hover:underline text-sm">&larr; Back to {{ staff.full_name }}</Link>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-4">Tax Analysis</h1>
            <p class="text-gray-600 mt-1">PAYE tax breakdown for <span class="font-semibold">{{ staff.full_name }}</span></p>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-gray-500 text-sm font-medium">Monthly Gross Salary</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(breakdown.monthly_salary) }}</p>
                </div>
                <div class="bg-orange-50 border border-orange-200 rounded-lg shadow p-5">
                    <p class="text-orange-700 text-sm font-medium">Monthly Tax (PAYE)</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">₦{{ formatCurrency(monthlyTax) }}</p>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-lg shadow p-5">
                    <p class="text-red-700 text-sm font-medium">Annual Tax (PAYE)</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">₦{{ formatCurrency(annualTax) }}</p>
                </div>
            </div>

            <!-- Detailed Breakdown -->
            <div class="bg-white rounded-lg shadow mt-6 p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Monthly Tax Breakdown</h2>

                <div class="space-y-4">
                    <!-- Gross Salary -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <div>
                            <p class="font-medium text-gray-800">Gross Monthly Salary</p>
                            <p class="text-sm text-gray-500">Total compensation before deductions</p>
                        </div>
                        <span class="text-lg font-semibold text-gray-900">₦{{ formatCurrency(breakdown.monthly_salary) }}</span>
                    </div>

                    <!-- Personal Relief -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <div>
                            <p class="font-medium text-gray-800">Less: Personal Relief (Monthly)</p>
                            <p class="text-sm text-gray-500">Annual relief of ₦{{ formatCurrency(breakdown.personal_relief * 12) }} divided by 12</p>
                        </div>
                        <span class="text-lg font-semibold text-green-600">- ₦{{ formatCurrency(breakdown.personal_relief) }}</span>
                    </div>

                    <!-- Taxable Income -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200 bg-gray-50 -mx-4 sm:-mx-6 px-4 sm:px-6">
                        <div>
                            <p class="font-semibold text-gray-900">Taxable Income (Monthly)</p>
                            <p class="text-sm text-gray-500">Gross salary minus personal relief</p>
                        </div>
                        <span class="text-lg font-bold text-gray-900">₦{{ formatCurrency(breakdown.taxable_amount) }}</span>
                    </div>

                    <!-- Tax Rate -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <div>
                            <p class="font-medium text-gray-800">Applicable Tax Rate</p>
                            <p class="text-sm text-gray-500">Based on Nigerian PAYE tax brackets</p>
                        </div>
                        <span class="text-lg font-semibold text-blue-600">{{ effectiveTaxRate }}%</span>
                    </div>

                    <!-- Monthly Tax Due -->
                    <div class="flex justify-between items-center py-3 bg-orange-50 -mx-4 sm:-mx-6 px-4 sm:px-6 rounded-b-lg">
                        <div>
                            <p class="font-semibold text-orange-900">Monthly Tax Due</p>
                            <p class="text-sm text-orange-700">PAYE to be remitted monthly</p>
                        </div>
                        <span class="text-xl font-bold text-orange-600">₦{{ formatCurrency(monthlyTax) }}</span>
                    </div>
                </div>
            </div>

            <!-- Annual Summary -->
            <div class="bg-white rounded-lg shadow mt-6 p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Annual Summary</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 font-medium text-gray-600">Item</th>
                                <th class="text-right py-2 font-medium text-gray-600">Monthly</th>
                                <th class="text-right py-2 font-medium text-gray-600">Annual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100">
                                <td class="py-3 text-gray-800">Gross Salary</td>
                                <td class="py-3 text-right text-gray-900 font-medium">₦{{ formatCurrency(breakdown.monthly_salary) }}</td>
                                <td class="py-3 text-right text-gray-900 font-medium">₦{{ formatCurrency(breakdown.monthly_salary * 12) }}</td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="py-3 text-gray-800">Personal Relief</td>
                                <td class="py-3 text-right text-green-600 font-medium">- ₦{{ formatCurrency(breakdown.personal_relief) }}</td>
                                <td class="py-3 text-right text-green-600 font-medium">- ₦{{ formatCurrency(breakdown.personal_relief * 12) }}</td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="py-3 text-gray-800 font-medium">Taxable Income</td>
                                <td class="py-3 text-right text-gray-900 font-semibold">₦{{ formatCurrency(breakdown.taxable_amount) }}</td>
                                <td class="py-3 text-right text-gray-900 font-semibold">₦{{ formatCurrency(breakdown.taxable_amount * 12) }}</td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="py-3 text-gray-800">PAYE Tax</td>
                                <td class="py-3 text-right text-orange-600 font-medium">₦{{ formatCurrency(monthlyTax) }}</td>
                                <td class="py-3 text-right text-orange-600 font-medium">₦{{ formatCurrency(annualTax) }}</td>
                            </tr>
                            <tr class="bg-blue-50">
                                <td class="py-3 text-blue-900 font-semibold">Net Take-Home</td>
                                <td class="py-3 text-right text-blue-900 font-bold">₦{{ formatCurrency(breakdown.monthly_salary - monthlyTax) }}</td>
                                <td class="py-3 text-right text-blue-900 font-bold">₦{{ formatCurrency((breakdown.monthly_salary - monthlyTax) * 12) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tax Info Panel -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg mt-6 p-4 sm:p-6">
                <h3 class="font-semibold text-blue-900 mb-2">About PAYE in Nigeria</h3>
                <ul class="text-blue-800 text-sm space-y-1 list-disc list-inside">
                    <li>PAYE (Pay-As-You-Earn) is deducted from employee salaries by the employer.</li>
                    <li>Employers must remit PAYE to the relevant State Internal Revenue Service (SIRS) by the 10th of the following month.</li>
                    <li>Personal relief of ₦{{ formatCurrency(breakdown.personal_relief * 12) }}/year is deducted before applying the tax rate.</li>
                    <li>The effective tax rate shown is an estimate. Actual rates depend on applicable tax bands and additional reliefs.</li>
                </ul>
            </div>

            <!-- Staff Info -->
            <div class="mt-6 flex flex-col sm:flex-row gap-3 text-sm text-gray-500">
                <span><strong>Staff:</strong> {{ staff.full_name }}</span>
                <span class="hidden sm:inline">•</span>
                <span><strong>Designation:</strong> {{ staff.designation }}</span>
                <span class="hidden sm:inline">•</span>
                <span><strong>Employment:</strong> {{ staff.employment_type?.replace('_', ' ') }}</span>
                <span class="hidden sm:inline">•</span>
                <span><strong>Status:</strong> {{ staff.status }}</span>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    staff: Object,
    monthlyTax: Number,
    annualTax: Number,
    breakdown: Object,
});

const effectiveTaxRate = computed(() => {
    if (!props.breakdown?.taxable_amount || props.breakdown.taxable_amount <= 0) return '0.0';
    return ((props.monthlyTax / props.breakdown.taxable_amount) * 100).toFixed(1);
});

const formatCurrency = (value) => {
    if (!value && value !== 0) return '0.00';
    return parseFloat(value).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
</script>
