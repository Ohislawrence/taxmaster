<template>
    <BusinessLayout>
        <Head title="Financial Statements" />

        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Financial Statements</h1>
                    <p class="text-sm text-gray-600">{{ business.name }} &mdash; Statement of Financial Position, Profit or Loss, and Cash Flows</p>
                </div>
                <div class="flex items-center gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Financial Year</label>
                        <input v-model="form.year" type="number" min="2020" max="2030" class="border-gray-300 rounded-lg w-28 text-sm" />
                    </div>
                    <button
                        @click="downloadPdf"
                        class="mt-5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-50"
                        :disabled="downloading || !isBalanced"
                    >
                        {{ downloading ? 'Preparing...' : 'Download PDF' }}
                    </button>
                </div>
            </div>

            <!-- Balance Sheet Validation Banner -->
            <div v-if="!isBalanced" class="rounded-lg border-2 border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="font-semibold">Balance Sheet does not balance</p>
                    <p>Total Assets (₦{{ fmt(totalAssets) }}) must equal Total Liabilities + Equity (₦{{ fmt(totalLiabilitiesAndEquity) }}). Difference: ₦{{ fmt(Math.abs(totalAssets - totalLiabilitiesAndEquity)) }}</p>
                </div>
            </div>
            <div v-else class="rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Balance Sheet is balanced &mdash; Assets = Liabilities + Equity (₦{{ fmt(totalAssets) }})</span>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <nav class="flex gap-6" aria-label="Tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="activeTab === tab.key ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-3 px-1 border-b-2 font-medium text-sm transition"
                    >{{ tab.label }}</button>
                </nav>
            </div>

            <!-- TAB: Balance Sheet -->
            <div v-show="activeTab === 'balance_sheet'" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Statement of Financial Position</h2>
                <p class="text-xs text-gray-500 mb-5">As at 31 December {{ form.year }}</p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Assets -->
                    <div>
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide border-b pb-2 mb-3">Assets</h3>
                        <p class="text-xs font-semibold text-gray-500 mb-2">Current Assets</p>
                        <div class="space-y-2">
                            <FieldRow label="Cash & Bank Balances" v-model="form.balance_sheet.cash_and_bank" />
                            <FieldRow label="Trade Receivables" v-model="form.balance_sheet.trade_receivables" />
                            <FieldRow label="Inventory" v-model="form.balance_sheet.inventory" />
                            <FieldRow label="Other Current Assets" v-model="form.balance_sheet.other_current_assets" />
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-gray-800 mt-2 pt-2 border-t">
                            <span>Total Current Assets</span>
                            <span>₦{{ fmt(currentAssets) }}</span>
                        </div>

                        <p class="text-xs font-semibold text-gray-500 mt-4 mb-2">Non-Current Assets</p>
                        <div class="space-y-2">
                            <FieldRow label="Property, Plant & Equipment" v-model="form.balance_sheet.property_plant_equipment" />
                            <FieldRow label="Intangible Assets" v-model="form.balance_sheet.intangible_assets" />
                            <FieldRow label="Other Non-Current Assets" v-model="form.balance_sheet.other_non_current_assets" />
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-gray-800 mt-2 pt-2 border-t">
                            <span>Total Non-Current Assets</span>
                            <span>₦{{ fmt(nonCurrentAssets) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-gray-900 mt-3 pt-2 border-t-2 border-gray-800">
                            <span>TOTAL ASSETS</span>
                            <span>₦{{ fmt(totalAssets) }}</span>
                        </div>
                    </div>

                    <!-- Liabilities & Equity -->
                    <div>
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide border-b pb-2 mb-3">Liabilities & Equity</h3>
                        <p class="text-xs font-semibold text-gray-500 mb-2">Current Liabilities</p>
                        <div class="space-y-2">
                            <FieldRow label="Trade Payables" v-model="form.balance_sheet.trade_payables" />
                            <FieldRow label="Tax Payable" v-model="form.balance_sheet.tax_payable" />
                            <FieldRow label="Other Current Liabilities" v-model="form.balance_sheet.other_current_liabilities" />
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-gray-800 mt-2 pt-2 border-t">
                            <span>Total Current Liabilities</span>
                            <span>₦{{ fmt(currentLiabilities) }}</span>
                        </div>

                        <p class="text-xs font-semibold text-gray-500 mt-4 mb-2">Non-Current Liabilities</p>
                        <div class="space-y-2">
                            <FieldRow label="Long-Term Borrowings" v-model="form.balance_sheet.long_term_borrowings" />
                            <FieldRow label="Other Non-Current Liabilities" v-model="form.balance_sheet.other_non_current_liabilities" />
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-gray-800 mt-2 pt-2 border-t">
                            <span>Total Non-Current Liabilities</span>
                            <span>₦{{ fmt(nonCurrentLiabilities) }}</span>
                        </div>

                        <p class="text-xs font-semibold text-gray-500 mt-4 mb-2">Equity</p>
                        <div class="space-y-2">
                            <FieldRow label="Share Capital" v-model="form.balance_sheet.share_capital" />
                            <FieldRow label="Retained Earnings" v-model="form.balance_sheet.retained_earnings" />
                            <FieldRow label="Other Reserves" v-model="form.balance_sheet.other_reserves" />
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-gray-800 mt-2 pt-2 border-t">
                            <span>Total Equity</span>
                            <span>₦{{ fmt(totalEquity) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-gray-900 mt-3 pt-2 border-t-2 border-gray-800">
                            <span>TOTAL LIABILITIES &amp; EQUITY</span>
                            <span>₦{{ fmt(totalLiabilitiesAndEquity) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: Profit & Loss -->
            <div v-show="activeTab === 'profit_loss'" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Statement of Profit or Loss</h2>
                <p class="text-xs text-gray-500 mb-5">For the year ended 31 December {{ form.year }}</p>

                <div class="max-w-xl space-y-3">
                    <FieldRow label="Revenue" v-model="form.profit_loss.revenue" />
                    <FieldRow label="Cost of Sales" v-model="form.profit_loss.cost_of_sales" />
                    <SummaryRow label="Gross Profit" :value="grossProfit" :highlight="true" />

                    <p class="text-xs font-semibold text-gray-500 pt-3">Operating Expenses</p>
                    <FieldRow label="Salaries & Wages" v-model="form.profit_loss.salaries_wages" />
                    <FieldRow label="Rent & Facilities" v-model="form.profit_loss.rent_facilities" />
                    <FieldRow label="Utilities" v-model="form.profit_loss.utilities" />
                    <FieldRow label="Professional Fees" v-model="form.profit_loss.professional_fees" />
                    <FieldRow label="Marketing & Advertising" v-model="form.profit_loss.marketing" />
                    <FieldRow label="Depreciation & Amortisation" v-model="form.profit_loss.depreciation" />
                    <FieldRow label="Other Operating Expenses" v-model="form.profit_loss.other_operating_expenses" />
                    <SummaryRow label="Total Operating Expenses" :value="totalOpex" />
                    <SummaryRow label="Operating Profit" :value="operatingProfit" :highlight="true" />

                    <FieldRow label="Other Income" v-model="form.profit_loss.other_income" />
                    <FieldRow label="Finance Costs" v-model="form.profit_loss.finance_costs" />
                    <SummaryRow label="Profit Before Tax" :value="profitBeforeTax" :highlight="true" />

                    <FieldRow label="Tax Expense" v-model="form.profit_loss.tax_expense" />
                    <SummaryRow label="Profit After Tax" :value="profitAfterTax" :highlight="true" :bold="true" />
                </div>
            </div>

            <!-- TAB: Cash Flow -->
            <div v-show="activeTab === 'cash_flow'" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Statement of Cash Flows</h2>
                <p class="text-xs text-gray-500 mb-5">For the year ended 31 December {{ form.year }} (Indirect Method)</p>

                <div class="max-w-xl space-y-3">
                    <SummaryRow label="Profit Before Tax" :value="profitBeforeTax" />

                    <p class="text-xs font-semibold text-gray-500 pt-3">Adjustments for Operating Activities</p>
                    <FieldRow label="Depreciation Add-back" v-model="form.cash_flow.depreciation_add_back" />
                    <FieldRow label="(Increase)/Decrease in Receivables" v-model="form.cash_flow.change_in_receivables" />
                    <FieldRow label="(Increase)/Decrease in Inventory" v-model="form.cash_flow.change_in_inventory" />
                    <FieldRow label="Increase/(Decrease) in Payables" v-model="form.cash_flow.change_in_payables" />
                    <SummaryRow label="Net Cash from Operations" :value="netCashOperating" :highlight="true" />

                    <p class="text-xs font-semibold text-gray-500 pt-3">Investing Activities</p>
                    <FieldRow label="Purchase of Assets (negative)" v-model="form.cash_flow.purchase_of_assets" />
                    <FieldRow label="Sale of Assets" v-model="form.cash_flow.sale_of_assets" />
                    <SummaryRow label="Net Cash from Investing" :value="netCashInvesting" />

                    <p class="text-xs font-semibold text-gray-500 pt-3">Financing Activities</p>
                    <FieldRow label="Loan Proceeds" v-model="form.cash_flow.loan_proceeds" />
                    <FieldRow label="Loan Repayments (negative)" v-model="form.cash_flow.loan_repayments" />
                    <FieldRow label="Equity Contributions" v-model="form.cash_flow.equity_contributions" />
                    <FieldRow label="Dividends Paid (negative)" v-model="form.cash_flow.dividends_paid" />
                    <SummaryRow label="Net Cash from Financing" :value="netCashFinancing" />

                    <div class="border-t-2 border-gray-800 pt-3 mt-3 space-y-2">
                        <SummaryRow label="Net Change in Cash" :value="netChangeCash" :highlight="true" :bold="true" />
                        <FieldRow label="Opening Cash Balance" v-model="form.cash_flow.opening_cash" />
                        <SummaryRow label="Closing Cash Balance" :value="closingCash" :bold="true" />
                    </div>
                </div>
            </div>

            <!-- TAB: Prior Year Comparison -->
            <div v-show="activeTab === 'comparison'" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Year-on-Year Comparison</h2>
                <p class="text-xs text-gray-500 mb-5">{{ form.year }} vs {{ Number(form.year) - 1 }}</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-gray-300">
                                <th class="text-left py-2 pr-4 font-semibold text-gray-700">Description</th>
                                <th class="text-right py-2 px-4 font-semibold text-gray-700">{{ form.year }}</th>
                                <th class="text-right py-2 px-4 font-semibold text-gray-700">{{ Number(form.year) - 1 }}</th>
                                <th class="text-right py-2 pl-4 font-semibold text-gray-700">Change</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <CompareRow label="Revenue" :current="form.profit_loss.revenue" :prior="priorYear.profit_loss.revenue" />
                            <CompareRow label="Cost of Sales" :current="form.profit_loss.cost_of_sales" :prior="priorYear.profit_loss.cost_of_sales" />
                            <CompareRow label="Gross Profit" :current="grossProfit" :prior="priorGrossProfit" :bold="true" />
                            <CompareRow label="Total Operating Expenses" :current="totalOpex" :prior="priorTotalOpex" />
                            <CompareRow label="Operating Profit" :current="operatingProfit" :prior="priorOperatingProfit" :bold="true" />
                            <CompareRow label="Profit Before Tax" :current="profitBeforeTax" :prior="priorProfitBeforeTax" :bold="true" />
                            <CompareRow label="Tax Expense" :current="form.profit_loss.tax_expense" :prior="priorYear.profit_loss.tax_expense" />
                            <CompareRow label="Profit After Tax" :current="profitAfterTax" :prior="priorProfitAfterTax" :bold="true" />
                            <tr class="h-4"></tr>
                            <CompareRow label="Total Assets" :current="totalAssets" :prior="priorTotalAssets" :bold="true" />
                            <CompareRow label="Total Liabilities" :current="totalLiabilities" :prior="priorTotalLiabilities" />
                            <CompareRow label="Total Equity" :current="totalEquity" :prior="priorTotalEquity" :bold="true" />
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, reactive, ref, h } from 'vue';
import axios from 'axios';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

// ----- Inline sub-components -----
const FieldRow = (props, { emit }) => {
    return h('div', { class: 'flex items-center justify-between gap-3' }, [
        h('label', { class: 'text-sm text-gray-600 flex-1' }, props.label),
        h('input', {
            type: 'number',
            step: '0.01',
            value: props.modelValue,
            onInput: (e) => emit('update:modelValue', parseFloat(e.target.value) || 0),
            class: 'border-gray-300 rounded-lg w-44 text-sm text-right',
        }),
    ]);
};
FieldRow.props = { label: String, modelValue: Number };
FieldRow.emits = ['update:modelValue'];

const SummaryRow = (props) => {
    const val = props.value || 0;
    const colorClass = val < 0 ? 'text-red-600' : 'text-gray-900';
    return h('div', {
        class: [
            'flex items-center justify-between gap-3 py-1',
            props.highlight ? 'bg-gray-50 px-3 rounded-lg' : '',
            props.bold ? 'font-bold' : 'font-semibold',
        ].filter(Boolean).join(' '),
    }, [
        h('span', { class: 'text-sm text-gray-800' }, props.label),
        h('span', { class: ['text-sm', colorClass].join(' ') }, '₦' + fmt(val)),
    ]);
};
SummaryRow.props = { label: String, value: Number, highlight: Boolean, bold: Boolean };

const CompareRow = (props) => {
    const cur = props.current || 0;
    const pri = props.prior || 0;
    const diff = cur - pri;
    const pct = pri !== 0 ? ((diff / Math.abs(pri)) * 100).toFixed(1) : (cur !== 0 ? '∞' : '0.0');
    const diffColor = diff > 0 ? 'text-green-600' : diff < 0 ? 'text-red-600' : 'text-gray-500';
    return h('tr', { class: props.bold ? 'font-semibold bg-gray-50' : '' }, [
        h('td', { class: 'py-2 pr-4 text-gray-700' }, props.label),
        h('td', { class: 'py-2 px-4 text-right' }, '₦' + fmt(cur)),
        h('td', { class: 'py-2 px-4 text-right text-gray-500' }, '₦' + fmt(pri)),
        h('td', { class: ['py-2 pl-4 text-right text-xs', diffColor].join(' ') },
            (diff >= 0 ? '+' : '') + fmt(diff) + (typeof pct === 'string' ? ` (${pct}%)` : '')
        ),
    ]);
};
CompareRow.props = { label: String, current: Number, prior: Number, bold: Boolean };

// ----- Props -----
const props = defineProps({
    business: Object,
    year: String,
    defaults: Object,
    priorDefaults: Object,
});

// ----- State -----
const downloading = ref(false);
const activeTab = ref('balance_sheet');
const tabs = [
    { key: 'balance_sheet', label: 'Balance Sheet' },
    { key: 'profit_loss', label: 'Profit & Loss' },
    { key: 'cash_flow', label: 'Cash Flow' },
    { key: 'comparison', label: 'Comparison' },
];

const form = reactive({
    year: props.year,
    balance_sheet: { ...props.defaults.balance_sheet },
    profit_loss: { ...props.defaults.profit_loss },
    cash_flow: { ...props.defaults.cash_flow },
});

const priorYear = reactive({
    balance_sheet: { ...props.priorDefaults.balance_sheet },
    profit_loss: { ...props.priorDefaults.profit_loss },
    cash_flow: { ...props.priorDefaults.cash_flow },
});

// ----- Balance Sheet Computeds -----
const currentAssets = computed(() =>
    (form.balance_sheet.cash_and_bank || 0) + (form.balance_sheet.trade_receivables || 0) +
    (form.balance_sheet.inventory || 0) + (form.balance_sheet.other_current_assets || 0)
);
const nonCurrentAssets = computed(() =>
    (form.balance_sheet.property_plant_equipment || 0) + (form.balance_sheet.intangible_assets || 0) +
    (form.balance_sheet.other_non_current_assets || 0)
);
const totalAssets = computed(() => currentAssets.value + nonCurrentAssets.value);

const currentLiabilities = computed(() =>
    (form.balance_sheet.trade_payables || 0) + (form.balance_sheet.tax_payable || 0) +
    (form.balance_sheet.other_current_liabilities || 0)
);
const nonCurrentLiabilities = computed(() =>
    (form.balance_sheet.long_term_borrowings || 0) + (form.balance_sheet.other_non_current_liabilities || 0)
);
const totalLiabilities = computed(() => currentLiabilities.value + nonCurrentLiabilities.value);
const totalEquity = computed(() =>
    (form.balance_sheet.share_capital || 0) + (form.balance_sheet.retained_earnings || 0) +
    (form.balance_sheet.other_reserves || 0)
);
const totalLiabilitiesAndEquity = computed(() => totalLiabilities.value + totalEquity.value);
const isBalanced = computed(() => Math.abs(totalAssets.value - totalLiabilitiesAndEquity.value) < 0.01);

// ----- P&L Computeds -----
const grossProfit = computed(() => (form.profit_loss.revenue || 0) - (form.profit_loss.cost_of_sales || 0));
const totalOpex = computed(() =>
    (form.profit_loss.salaries_wages || 0) + (form.profit_loss.rent_facilities || 0) +
    (form.profit_loss.utilities || 0) + (form.profit_loss.professional_fees || 0) +
    (form.profit_loss.marketing || 0) + (form.profit_loss.depreciation || 0) +
    (form.profit_loss.other_operating_expenses || 0)
);
const operatingProfit = computed(() => grossProfit.value - totalOpex.value);
const profitBeforeTax = computed(() => operatingProfit.value + (form.profit_loss.other_income || 0) - (form.profit_loss.finance_costs || 0));
const profitAfterTax = computed(() => profitBeforeTax.value - (form.profit_loss.tax_expense || 0));

// ----- Cash Flow Computeds -----
const netCashOperating = computed(() =>
    profitBeforeTax.value + (form.cash_flow.depreciation_add_back || 0) +
    (form.cash_flow.change_in_receivables || 0) + (form.cash_flow.change_in_inventory || 0) +
    (form.cash_flow.change_in_payables || 0)
);
const netCashInvesting = computed(() => (form.cash_flow.purchase_of_assets || 0) + (form.cash_flow.sale_of_assets || 0));
const netCashFinancing = computed(() =>
    (form.cash_flow.loan_proceeds || 0) + (form.cash_flow.loan_repayments || 0) +
    (form.cash_flow.equity_contributions || 0) + (form.cash_flow.dividends_paid || 0)
);
const netChangeCash = computed(() => netCashOperating.value + netCashInvesting.value + netCashFinancing.value);
const closingCash = computed(() => (form.cash_flow.opening_cash || 0) + netChangeCash.value);

// ----- Prior Year Computeds -----
const priorGrossProfit = computed(() => (priorYear.profit_loss.revenue || 0) - (priorYear.profit_loss.cost_of_sales || 0));
const priorTotalOpex = computed(() =>
    (priorYear.profit_loss.salaries_wages || 0) + (priorYear.profit_loss.rent_facilities || 0) +
    (priorYear.profit_loss.utilities || 0) + (priorYear.profit_loss.professional_fees || 0) +
    (priorYear.profit_loss.marketing || 0) + (priorYear.profit_loss.depreciation || 0) +
    (priorYear.profit_loss.other_operating_expenses || 0)
);
const priorOperatingProfit = computed(() => priorGrossProfit.value - priorTotalOpex.value);
const priorProfitBeforeTax = computed(() => priorOperatingProfit.value + (priorYear.profit_loss.other_income || 0) - (priorYear.profit_loss.finance_costs || 0));
const priorProfitAfterTax = computed(() => priorProfitBeforeTax.value - (priorYear.profit_loss.tax_expense || 0));
const priorCurrentAssets = computed(() =>
    (priorYear.balance_sheet.cash_and_bank || 0) + (priorYear.balance_sheet.trade_receivables || 0) +
    (priorYear.balance_sheet.inventory || 0) + (priorYear.balance_sheet.other_current_assets || 0)
);
const priorNonCurrentAssets = computed(() =>
    (priorYear.balance_sheet.property_plant_equipment || 0) + (priorYear.balance_sheet.intangible_assets || 0) +
    (priorYear.balance_sheet.other_non_current_assets || 0)
);
const priorTotalAssets = computed(() => priorCurrentAssets.value + priorNonCurrentAssets.value);
const priorCurrentLiabilities = computed(() =>
    (priorYear.balance_sheet.trade_payables || 0) + (priorYear.balance_sheet.tax_payable || 0) +
    (priorYear.balance_sheet.other_current_liabilities || 0)
);
const priorNonCurrentLiabilities = computed(() =>
    (priorYear.balance_sheet.long_term_borrowings || 0) + (priorYear.balance_sheet.other_non_current_liabilities || 0)
);
const priorTotalLiabilities = computed(() => priorCurrentLiabilities.value + priorNonCurrentLiabilities.value);
const priorTotalEquity = computed(() =>
    (priorYear.balance_sheet.share_capital || 0) + (priorYear.balance_sheet.retained_earnings || 0) +
    (priorYear.balance_sheet.other_reserves || 0)
);

// ----- Helpers -----
const fmt = (v) => new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v || 0);

const downloadPdf = async () => {
    downloading.value = true;
    try {
        const response = await axios.post(route('business.reports.financial-statements.pdf'), {
            ...form,
            prior_year: priorYear,
        }, { responseType: 'blob' });
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
</script>
