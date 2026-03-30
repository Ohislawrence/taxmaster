<template>
    <BusinessLayout>
        <Head title="Financial Statements" />

        <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Header Section -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                            Financial Statements
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ business.name }} — Statement of Financial Position, Profit or Loss, and Cash Flows
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Financial Year</label>
                            <input
                                v-model="form.year"
                                type="number"
                                min="2020"
                                max="2030"
                                class="border border-gray-200 rounded-lg px-3 py-2 w-28 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            />
                        </div>
                        <button
                            @click="downloadPdf"
                            class="mt-5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-2 rounded-xl text-sm font-medium transition-all shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="downloading || !isBalanced"
                        >
                            {{ downloading ? 'Preparing...' : 'Download PDF' }}
                        </button>
                    </div>
                </div>

                <!-- Balance Sheet Validation Banner -->
                <div v-if="!isBalanced" class="rounded-2xl border-2 border-red-200 bg-red-50/80 backdrop-blur-sm px-5 py-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-red-600 text-xs font-bold">
                                !
                            </span>
                        </div>
                        <div>
                            <p class="font-semibold text-red-800">Balance Sheet does not balance</p>
                            <p class="text-sm text-red-700 mt-1">
                                Total Assets (₦{{ fmt(totalAssets) }}) must equal Total Liabilities + Equity (₦{{ fmt(totalLiabilitiesAndEquity) }}).
                                Difference: ₦{{ fmt(Math.abs(totalAssets - totalLiabilitiesAndEquity)) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else class="rounded-2xl border border-green-200 bg-green-50/80 backdrop-blur-sm px-5 py-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-green-100 text-green-600 text-xs font-bold">
                                ✓
                            </span>
                        </div>
                        <p class="text-sm font-medium text-green-800">
                            Balance Sheet is balanced — Assets = Liabilities + Equity (₦{{ fmt(totalAssets) }})
                        </p>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="flex gap-8" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            :class="[
                                'py-3 px-1 border-b-2 font-medium text-sm transition-all',
                                activeTab === tab.key
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- TAB: Balance Sheet -->
                <div v-show="activeTab === 'balance_sheet'" class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                        <h2 class="text-base font-semibold text-gray-900">Statement of Financial Position</h2>
                        <p class="text-xs text-gray-500 mt-1">As at 31 December {{ form.year }}</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Assets -->
                            <div>
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide border-b pb-2 mb-3">Assets</h3>

                                <div class="mb-4">
                                    <p class="text-xs font-semibold text-gray-500 mb-2 flex items-center gap-2">
                                        <span class="w-1 h-3 bg-blue-400 rounded-full"></span>
                                        Current Assets
                                    </p>
                                    <div class="space-y-3">
                                        <FieldRow label="Cash & Bank Balances" v-model="form.balance_sheet.cash_and_bank" />
                                        <FieldRow label="Trade Receivables" v-model="form.balance_sheet.trade_receivables" />
                                        <FieldRow label="Inventory" v-model="form.balance_sheet.inventory" />
                                        <FieldRow label="Other Current Assets" v-model="form.balance_sheet.other_current_assets" />
                                    </div>
                                    <div class="flex justify-between text-sm font-semibold text-gray-800 mt-3 pt-2 border-t border-gray-200">
                                        <span>Total Current Assets</span>
                                        <span>₦{{ fmt(currentAssets) }}</span>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-2 flex items-center gap-2">
                                        <span class="w-1 h-3 bg-blue-400 rounded-full"></span>
                                        Non-Current Assets
                                    </p>
                                    <div class="space-y-3">
                                        <FieldRow label="Property, Plant & Equipment" v-model="form.balance_sheet.property_plant_equipment" />
                                        <FieldRow label="Intangible Assets" v-model="form.balance_sheet.intangible_assets" />
                                        <FieldRow label="Other Non-Current Assets" v-model="form.balance_sheet.other_non_current_assets" />
                                    </div>
                                    <div class="flex justify-between text-sm font-semibold text-gray-800 mt-3 pt-2 border-t border-gray-200">
                                        <span>Total Non-Current Assets</span>
                                        <span>₦{{ fmt(nonCurrentAssets) }}</span>
                                    </div>
                                </div>

                                <div class="flex justify-between text-base font-bold text-gray-900 mt-4 pt-3 border-t-2 border-gray-800">
                                    <span>TOTAL ASSETS</span>
                                    <span class="text-blue-600">₦{{ fmt(totalAssets) }}</span>
                                </div>
                            </div>

                            <!-- Liabilities & Equity -->
                            <div>
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide border-b pb-2 mb-3">Liabilities & Equity</h3>

                                <div class="mb-4">
                                    <p class="text-xs font-semibold text-gray-500 mb-2 flex items-center gap-2">
                                        <span class="w-1 h-3 bg-amber-400 rounded-full"></span>
                                        Current Liabilities
                                    </p>
                                    <div class="space-y-3">
                                        <FieldRow label="Trade Payables" v-model="form.balance_sheet.trade_payables" />
                                        <FieldRow label="Tax Payable" v-model="form.balance_sheet.tax_payable" />
                                        <FieldRow label="Other Current Liabilities" v-model="form.balance_sheet.other_current_liabilities" />
                                    </div>
                                    <div class="flex justify-between text-sm font-semibold text-gray-800 mt-3 pt-2 border-t border-gray-200">
                                        <span>Total Current Liabilities</span>
                                        <span>₦{{ fmt(currentLiabilities) }}</span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <p class="text-xs font-semibold text-gray-500 mb-2 flex items-center gap-2">
                                        <span class="w-1 h-3 bg-amber-400 rounded-full"></span>
                                        Non-Current Liabilities
                                    </p>
                                    <div class="space-y-3">
                                        <FieldRow label="Long-Term Borrowings" v-model="form.balance_sheet.long_term_borrowings" />
                                        <FieldRow label="Other Non-Current Liabilities" v-model="form.balance_sheet.other_non_current_liabilities" />
                                    </div>
                                    <div class="flex justify-between text-sm font-semibold text-gray-800 mt-3 pt-2 border-t border-gray-200">
                                        <span>Total Non-Current Liabilities</span>
                                        <span>₦{{ fmt(nonCurrentLiabilities) }}</span>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-2 flex items-center gap-2">
                                        <span class="w-1 h-3 bg-green-400 rounded-full"></span>
                                        Equity
                                    </p>
                                    <div class="space-y-3">
                                        <FieldRow label="Share Capital" v-model="form.balance_sheet.share_capital" />
                                        <FieldRow label="Retained Earnings" v-model="form.balance_sheet.retained_earnings" />
                                        <FieldRow label="Other Reserves" v-model="form.balance_sheet.other_reserves" />
                                    </div>
                                    <div class="flex justify-between text-sm font-semibold text-gray-800 mt-3 pt-2 border-t border-gray-200">
                                        <span>Total Equity</span>
                                        <span>₦{{ fmt(totalEquity) }}</span>
                                    </div>
                                </div>

                                <div class="flex justify-between text-base font-bold text-gray-900 mt-4 pt-3 border-t-2 border-gray-800">
                                    <span>TOTAL LIABILITIES & EQUITY</span>
                                    <span class="text-blue-600">₦{{ fmt(totalLiabilitiesAndEquity) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: Profit & Loss -->
                <div v-show="activeTab === 'profit_loss'" class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                        <h2 class="text-base font-semibold text-gray-900">Statement of Profit or Loss</h2>
                        <p class="text-xs text-gray-500 mt-1">For the year ended 31 December {{ form.year }}</p>
                    </div>
                    <div class="p-6">
                        <div class="max-w-2xl mx-auto space-y-3">
                            <FieldRow label="Revenue" v-model="form.profit_loss.revenue" />
                            <FieldRow label="Cost of Sales" v-model="form.profit_loss.cost_of_sales" />
                            <SummaryRow label="Gross Profit" :value="grossProfit" :highlight="true" />

                            <div class="pt-4">
                                <p class="text-xs font-semibold text-gray-500 mb-2 flex items-center gap-2">
                                    <span class="w-1 h-3 bg-gray-400 rounded-full"></span>
                                    Operating Expenses
                                </p>
                                <div class="space-y-3 pl-4">
                                    <FieldRow label="Salaries & Wages" v-model="form.profit_loss.salaries_wages" />
                                    <FieldRow label="Rent & Facilities" v-model="form.profit_loss.rent_facilities" />
                                    <FieldRow label="Utilities" v-model="form.profit_loss.utilities" />
                                    <FieldRow label="Professional Fees" v-model="form.profit_loss.professional_fees" />
                                    <FieldRow label="Marketing & Advertising" v-model="form.profit_loss.marketing" />
                                    <FieldRow label="Depreciation & Amortisation" v-model="form.profit_loss.depreciation" />
                                    <FieldRow label="Other Operating Expenses" v-model="form.profit_loss.other_operating_expenses" />
                                </div>
                            </div>
                            <SummaryRow label="Total Operating Expenses" :value="totalOpex" />
                            <SummaryRow label="Operating Profit" :value="operatingProfit" :highlight="true" />

                            <FieldRow label="Other Income" v-model="form.profit_loss.other_income" />
                            <FieldRow label="Finance Costs" v-model="form.profit_loss.finance_costs" />
                            <SummaryRow label="Profit Before Tax" :value="profitBeforeTax" :highlight="true" />

                            <FieldRow label="Tax Expense" v-model="form.profit_loss.tax_expense" />
                            <SummaryRow label="Profit After Tax" :value="profitAfterTax" :highlight="true" :bold="true" />
                        </div>
                    </div>
                </div>

                <!-- TAB: Cash Flow -->
                <div v-show="activeTab === 'cash_flow'" class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                        <h2 class="text-base font-semibold text-gray-900">Statement of Cash Flows</h2>
                        <p class="text-xs text-gray-500 mt-1">For the year ended 31 December {{ form.year }} (Indirect Method)</p>
                    </div>
                    <div class="p-6">
                        <div class="max-w-2xl mx-auto space-y-4">
                            <SummaryRow label="Profit Before Tax" :value="profitBeforeTax" />

                            <div class="pt-2">
                                <p class="text-xs font-semibold text-gray-500 mb-2 flex items-center gap-2">
                                    <span class="w-1 h-3 bg-blue-400 rounded-full"></span>
                                    Adjustments for Operating Activities
                                </p>
                                <div class="space-y-3 pl-4">
                                    <FieldRow label="Depreciation Add-back" v-model="form.cash_flow.depreciation_add_back" />
                                    <FieldRow label="(Increase)/Decrease in Receivables" v-model="form.cash_flow.change_in_receivables" />
                                    <FieldRow label="(Increase)/Decrease in Inventory" v-model="form.cash_flow.change_in_inventory" />
                                    <FieldRow label="Increase/(Decrease) in Payables" v-model="form.cash_flow.change_in_payables" />
                                </div>
                            </div>
                            <SummaryRow label="Net Cash from Operations" :value="netCashOperating" :highlight="true" />

                            <div class="pt-2">
                                <p class="text-xs font-semibold text-gray-500 mb-2 flex items-center gap-2">
                                    <span class="w-1 h-3 bg-green-400 rounded-full"></span>
                                    Investing Activities
                                </p>
                                <div class="space-y-3 pl-4">
                                    <FieldRow label="Purchase of Assets (negative)" v-model="form.cash_flow.purchase_of_assets" />
                                    <FieldRow label="Sale of Assets" v-model="form.cash_flow.sale_of_assets" />
                                </div>
                            </div>
                            <SummaryRow label="Net Cash from Investing" :value="netCashInvesting" />

                            <div class="pt-2">
                                <p class="text-xs font-semibold text-gray-500 mb-2 flex items-center gap-2">
                                    <span class="w-1 h-3 bg-amber-400 rounded-full"></span>
                                    Financing Activities
                                </p>
                                <div class="space-y-3 pl-4">
                                    <FieldRow label="Loan Proceeds" v-model="form.cash_flow.loan_proceeds" />
                                    <FieldRow label="Loan Repayments (negative)" v-model="form.cash_flow.loan_repayments" />
                                    <FieldRow label="Equity Contributions" v-model="form.cash_flow.equity_contributions" />
                                    <FieldRow label="Dividends Paid (negative)" v-model="form.cash_flow.dividends_paid" />
                                </div>
                            </div>
                            <SummaryRow label="Net Cash from Financing" :value="netCashFinancing" />

                            <div class="border-t-2 border-gray-800 pt-4 mt-4 space-y-3">
                                <SummaryRow label="Net Change in Cash" :value="netChangeCash" :highlight="true" :bold="true" />
                                <FieldRow label="Opening Cash Balance" v-model="form.cash_flow.opening_cash" />
                                <SummaryRow label="Closing Cash Balance" :value="closingCash" :bold="true" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: Prior Year Comparison -->
                <div v-show="activeTab === 'comparison'" class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                        <h2 class="text-base font-semibold text-gray-900">Year-on-Year Comparison</h2>
                        <p class="text-xs text-gray-500 mt-1">{{ form.year }} vs {{ Number(form.year) - 1 }}</p>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b-2 border-gray-300 bg-gray-50/50">
                                        <th class="text-left py-3 pr-4 font-semibold text-gray-700">Description</th>
                                        <th class="text-right py-3 px-4 font-semibold text-gray-700">{{ form.year }}</th>
                                        <th class="text-right py-3 px-4 font-semibold text-gray-700">{{ Number(form.year) - 1 }}</th>
                                        <th class="text-right py-3 pl-4 font-semibold text-gray-700">Change</th>
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
                                    <tr class="h-4"><td colspan="4"></td></tr>
                                    <CompareRow label="Total Assets" :current="totalAssets" :prior="priorTotalAssets" :bold="true" />
                                    <CompareRow label="Total Liabilities" :current="totalLiabilities" :prior="priorTotalLiabilities" />
                                    <CompareRow label="Total Equity" :current="totalEquity" :prior="priorTotalEquity" :bold="true" />
                                </tbody>
                            </table>
                        </div>
                    </div>
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
    const isNegative = props.modelValue < 0;
    return h('div', { class: 'flex items-center justify-between gap-3' }, [
        h('label', { class: 'text-sm text-gray-600 flex-1' }, props.label),
        h('input', {
            type: 'number',
            step: '0.01',
            value: props.modelValue,
            onInput: (e) => emit('update:modelValue', parseFloat(e.target.value) || 0),
            class: 'border border-gray-200 rounded-lg px-3 py-2 w-44 text-sm text-right focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all',
            style: isNegative ? 'color: #dc2626' : '',
        }),
    ]);
};
FieldRow.props = { label: String, modelValue: Number };
FieldRow.emits = ['update:modelValue'];

const SummaryRow = (props) => {
    const val = props.value || 0;
    const isNegative = val < 0;
    return h('div', {
        class: [
            'flex items-center justify-between gap-3 py-2',
            props.highlight ? 'bg-blue-50/50 px-4 rounded-xl border border-blue-200/50' : '',
            props.bold ? 'font-bold' : 'font-semibold',
        ].filter(Boolean).join(' '),
    }, [
        h('span', { class: 'text-sm text-gray-800' }, props.label),
        h('span', {
            class: ['text-sm', isNegative ? 'text-red-600' : 'text-gray-900'].join(' '),
            style: props.bold && !isNegative ? 'font-weight: 700' : '',
        }, '₦' + fmt(val)),
    ]);
};
SummaryRow.props = { label: String, value: Number, highlight: Boolean, bold: Boolean };

const CompareRow = (props) => {
    const cur = props.current || 0;
    const pri = props.prior || 0;
    const diff = cur - pri;
    const pct = pri !== 0 ? ((diff / Math.abs(pri)) * 100).toFixed(1) : (cur !== 0 ? '∞' : '0.0');
    const diffColor = diff > 0 ? 'text-green-600' : diff < 0 ? 'text-red-600' : 'text-gray-500';
    return h('tr', { class: props.bold ? 'font-semibold bg-gray-50/50' : 'hover:bg-gray-50/30 transition-colors' }, [
        h('td', { class: 'py-2 pr-4 text-gray-700' }, props.label),
        h('td', { class: 'py-2 px-4 text-right' }, '₦' + fmt(cur)),
        h('td', { class: 'py-2 px-4 text-right text-gray-500' }, '₦' + fmt(pri)),
        h('td', { class: ['py-2 pl-4 text-right text-xs font-medium', diffColor].join(' ') },
            (diff >= 0 ? '+' : '') + fmt(diff) + (typeof pct === 'string' ? ` (${pct}%)` : '')
        ),
    ]);
};
CompareRow.props = { label: String, current: Number, prior: Number, bold: Boolean };

// ----- Props -----
const props = defineProps({
    business: {
        type: Object,
        required: true
    },
    year: {
        type: String,
        required: true
    },
    defaults: {
        type: Object,
        required: true
    },
    priorDefaults: {
        type: Object,
        required: true
    },
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
    } catch (error) {
        console.error('Failed to generate PDF:', error);
        alert('Failed to generate PDF. Please try again.');
    } finally {
        downloading.value = false;
    }
};
</script>

<style scoped>
/* Smooth transitions */
button, input {
    transition: all 0.2s ease;
}

/* Form field focus styles */
input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Disabled button styles */
button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
