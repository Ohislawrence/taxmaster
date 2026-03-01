<template>
    <BusinessLayout>
        <Head title="Financial Statements" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto space-y-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Financial Statements</h1>
                <p class="text-gray-600">Generate Statement of Financial Position and Profit or Loss</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Financial Year</label>
                        <input v-model="form.year" type="number" class="border-gray-300 rounded-lg w-40" />
                    </div>
                    <button
                        @click="downloadPdf"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                        :disabled="downloading"
                    >
                        {{ downloading ? 'Preparing...' : 'Download PDF' }}
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Statement of Financial Position</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600">Current Assets</label>
                                <input v-model.number="form.balance_sheet.current_assets" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Non-current Assets</label>
                                <input v-model.number="form.balance_sheet.non_current_assets" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Current Liabilities</label>
                                <input v-model.number="form.balance_sheet.current_liabilities" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Non-current Liabilities</label>
                                <input v-model.number="form.balance_sheet.non_current_liabilities" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Share Capital</label>
                                <input v-model.number="form.balance_sheet.share_capital" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Retained Earnings</label>
                                <input v-model.number="form.balance_sheet.retained_earnings" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Other Reserves</label>
                                <input v-model.number="form.balance_sheet.other_reserves" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Statement of Profit or Loss</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600">Revenue</label>
                                <input v-model.number="form.profit_loss.revenue" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Cost of Sales</label>
                                <input v-model.number="form.profit_loss.cost_of_sales" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Operating Expenses</label>
                                <input v-model.number="form.profit_loss.operating_expenses" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Other Income</label>
                                <input v-model.number="form.profit_loss.other_income" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Finance Costs</label>
                                <input v-model.number="form.profit_loss.finance_costs" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Tax Expense</label>
                                <input v-model.number="form.profit_loss.tax_expense" type="number" class="border-gray-300 rounded-lg w-full" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Preview</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-800">Statement of Financial Position</h3>
                        <div class="text-sm text-gray-700 mt-2 space-y-1">
                            <p>Total Assets: ₦{{ formatCurrency(totalAssets) }}</p>
                            <p>Total Liabilities: ₦{{ formatCurrency(totalLiabilities) }}</p>
                            <p>Total Equity: ₦{{ formatCurrency(totalEquity) }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Statement of Profit or Loss</h3>
                        <div class="text-sm text-gray-700 mt-2 space-y-1">
                            <p>Gross Profit: ₦{{ formatCurrency(grossProfit) }}</p>
                            <p>Profit Before Tax: ₦{{ formatCurrency(profitBeforeTax) }}</p>
                            <p>Profit After Tax: ₦{{ formatCurrency(profitAfterTax) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import axios from 'axios';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    business: Object,
    year: String,
    defaults: Object,
});

const downloading = ref(false);

const form = reactive({
    year: props.year,
    balance_sheet: {
        current_assets: props.defaults.balance_sheet.current_assets,
        non_current_assets: props.defaults.balance_sheet.non_current_assets,
        current_liabilities: props.defaults.balance_sheet.current_liabilities,
        non_current_liabilities: props.defaults.balance_sheet.non_current_liabilities,
        share_capital: props.defaults.balance_sheet.share_capital,
        retained_earnings: props.defaults.balance_sheet.retained_earnings,
        other_reserves: props.defaults.balance_sheet.other_reserves,
    },
    profit_loss: {
        revenue: props.defaults.profit_loss.revenue,
        cost_of_sales: props.defaults.profit_loss.cost_of_sales,
        operating_expenses: props.defaults.profit_loss.operating_expenses,
        other_income: props.defaults.profit_loss.other_income,
        finance_costs: props.defaults.profit_loss.finance_costs,
        tax_expense: props.defaults.profit_loss.tax_expense,
    },
});

const totalAssets = computed(() => form.balance_sheet.current_assets + form.balance_sheet.non_current_assets);
const totalLiabilities = computed(() => form.balance_sheet.current_liabilities + form.balance_sheet.non_current_liabilities);
const totalEquity = computed(() => form.balance_sheet.share_capital + form.balance_sheet.retained_earnings + form.balance_sheet.other_reserves);

const grossProfit = computed(() => form.profit_loss.revenue - form.profit_loss.cost_of_sales);
const operatingProfit = computed(() => grossProfit.value - form.profit_loss.operating_expenses);
const profitBeforeTax = computed(() => operatingProfit.value + form.profit_loss.other_income - form.profit_loss.finance_costs);
const profitAfterTax = computed(() => profitBeforeTax.value - form.profit_loss.tax_expense);

const downloadPdf = async () => {
    downloading.value = true;
    try {
        const response = await axios.post(route('business.reports.financial-statements.pdf'), form, {
            responseType: 'blob',
        });
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `financial-statements-${form.year}.pdf`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } finally {
        downloading.value = false;
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
};
</script>
