<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

// Inputs
const turnover = ref(5000000);
const expenses = ref(2000000);
const vatRate = ref(7.5);
const whtRate = ref(5);
const citRate = ref(30);
const grossSalary = ref(150000);

// Calculations
const vatDue = computed(() => (turnover.value * (vatRate.value / 100)).toFixed(2));
const taxableProfit = computed(() => Math.max(0, turnover.value - expenses.value));
const citDue = computed(() => (taxableProfit.value * (citRate.value / 100)).toFixed(2));
const whtDue = computed(() => (turnover.value * (whtRate.value / 100)).toFixed(2));

// PAYE: simplified estimate using a single-rate slider (for marketing/estimation only)
const payeRate = ref(10);
const payeDue = computed(() => (grossSalary.value * (payeRate.value / 100)).toFixed(2));

const formatN = (v) => new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2 }).format(v);

// Total tax estimate
const totalTaxEstimate = computed(() => {
    return Number(vatDue.value) + Number(citDue.value) + Number(whtDue.value) + Number(payeDue.value);
});
</script>

<template>
    <div class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-28 sm:pb-16 lg:pt-36 lg:pb-24">
        <!-- Subtle grid background - Mono.co style -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:3rem_3rem] sm:bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header Section - Mono.co style -->
            <div class="text-center sm:text-left mb-8 sm:mb-12">
                <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6">
                    <div class="max-w-2xl">
                        <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight mb-3">
                            Tax Calculator
                        </h1>
                        <p class="text-base sm:text-lg text-slate-500 leading-relaxed">
                            Quickly estimate VAT, WHT, CIT and PAYE obligations. These are estimates for planning purposes — TaxMaster automates filing and payment preparation for you.
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <Link 
                            :href="route('register')" 
                            class="group inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95"
                        >
                            Sign up to file
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Total Estimate Pill -->
                <div class="mt-6 inline-flex items-center gap-3 rounded-full bg-slate-50 px-4 py-2 sm:px-6 sm:py-3 border border-slate-200">
                    <span class="text-sm text-slate-600">Estimated total tax:</span>
                    <span class="text-xl sm:text-2xl font-bold text-slate-900">₦{{ formatN(totalTaxEstimate) }}</span>
                    <span class="text-xs text-slate-400">/month</span>
                </div>
            </div>

            <!-- Main Calculator Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                <!-- Left Column - VAT, WHT, CIT -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- VAT Card -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 transition-all duration-300 hover:border-slate-200 hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)]">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg sm:text-xl font-semibold text-slate-900">VAT</h3>
                                    <div class="relative group">
                                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                                        </svg>
                                        <div class="absolute z-10 hidden group-hover:block w-64 right-0 mt-2 rounded-lg bg-white text-xs text-slate-700 shadow-lg border border-slate-100 p-3">
                                            VAT applies to most goods and services. Enter your sales (turnover) and the VAT rate to estimate monthly VAT due. This calculator uses a simple percentage estimate for planning only.
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1">Value Added Tax at <span class="font-medium text-blue-600">{{ vatRate }}%</span></p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                Standard rate
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Turnover (₦)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">₦</span>
                                    <input 
                                        type="number" 
                                        v-model.number="turnover" 
                                        class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                        placeholder="Enter amount"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">VAT Rate (%)</label>
                                <input 
                                    type="number" 
                                    v-model.number="vatRate" 
                                    step="0.1"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <span class="text-sm text-slate-500">Estimated VAT Due</span>
                            <div class="text-right">
                                <span class="text-xs text-slate-400">Monthly</span>
                                <div class="text-2xl font-bold text-slate-900">₦{{ formatN(vatDue) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- WHT Card -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 transition-all duration-300 hover:border-slate-200 hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)]">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg sm:text-xl font-semibold text-slate-900">WHT</h3>
                                <div class="relative group">
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                                    </svg>
                                    <div class="absolute z-10 hidden group-hover:block w-64 right-0 mt-2 rounded-lg bg-white text-xs text-slate-700 shadow-lg border border-slate-100 p-3">
                                        Withholding Tax is deducted at source on certain payments. Use the amount subject to WHT and the applicable rate for an estimate. This is a planning tool only.
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1">Withholding Tax at <span class="font-medium text-blue-600">{{ whtRate }}%</span></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Amount subject to WHT (₦)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">₦</span>
                                    <input 
                                        type="number" 
                                        v-model.number="turnover" 
                                        class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">WHT Rate (%)</label>
                                <input 
                                    type="number" 
                                    v-model.number="whtRate" 
                                    step="0.1"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <span class="text-sm text-slate-500">Estimated WHT</span>
                            <div class="text-right">
                                <span class="text-xs text-slate-400">Monthly</span>
                                <div class="text-2xl font-bold text-slate-900">₦{{ formatN(whtDue) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- CIT Card -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 transition-all duration-300 hover:border-slate-200 hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)]">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg sm:text-xl font-semibold text-slate-900">CIT</h3>
                                <div class="relative group">
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                                    </svg>
                                    <div class="absolute z-10 hidden group-hover:block w-64 right-0 mt-2 rounded-lg bg-white text-xs text-slate-700 shadow-lg border border-slate-100 p-3">
                                        Corporate Income Tax is charged on taxable profit (turnover minus allowable expenses). Enter expenses to estimate taxable profit and CIT due. For accuracy, use detailed ledgers.
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1">Company Income Tax at <span class="font-medium text-blue-600">{{ citRate }}%</span></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Turnover (₦)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">₦</span>
                                    <input 
                                        type="number" 
                                        v-model.number="turnover" 
                                        class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Deductible expenses (₦)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">₦</span>
                                    <input 
                                        type="number" 
                                        v-model.number="expenses" 
                                        class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">CIT Rate (%)</label>
                                <input 
                                    type="number" 
                                    v-model.number="citRate" 
                                    step="0.1"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                />
                            </div>
                            <div class="bg-slate-50 rounded-xl p-3">
                                <span class="text-xs text-slate-500">Taxable Profit</span>
                                <div class="text-lg font-semibold text-slate-900">₦{{ formatN(taxableProfit) }}</div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <span class="text-sm text-slate-500">Estimated CIT</span>
                            <div class="text-right">
                                <span class="text-xs text-slate-400">Monthly</span>
                                <div class="text-2xl font-bold text-slate-900">₦{{ formatN(citDue) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - PAYE & Marketing -->
                <div class="space-y-6">
                    <!-- PAYE Card -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 transition-all duration-300 hover:border-slate-200 hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)]">
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg sm:text-xl font-semibold text-slate-900 mb-2">PAYE</h3>
                            <div class="relative group">
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                                </svg>
                                <div class="absolute z-10 hidden group-hover:block w-64 right-0 mt-2 rounded-lg bg-white text-xs text-slate-700 shadow-lg border border-slate-100 p-3">
                                    PAYE is calculated on employee wages using progressive bands and allowances. This slider provides a simple estimate — TaxMaster calculates exact PAYE using statutory bands when you import payroll data.
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mb-6">Employee Income Tax — simplified estimate</p>

                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Gross Monthly Salary (₦)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">₦</span>
                                    <input 
                                        type="number" 
                                        v-model.number="grossSalary" 
                                        class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                    />
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-xs font-medium text-slate-500">Assumed PAYE Rate</label>
                                    <span class="text-sm font-semibold text-blue-600">{{ payeRate }}%</span>
                                </div>
                                <input 
                                    type="range" 
                                    v-model.number="payeRate" 
                                    min="0" 
                                    max="30" 
                                    step="0.5"
                                    class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-slate-900"
                                />
                                <div class="flex justify-between text-xs text-slate-400 mt-1">
                                    <span>0%</span>
                                    <span>15%</span>
                                    <span>30%</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <span class="text-sm text-slate-500">Estimated PAYE (monthly)</span>
                            <div class="text-2xl font-bold text-slate-900">₦{{ formatN(payeDue) }}</div>
                        </div>

                        <p class="text-xs text-slate-400 mt-4">
                            ⓘ For accurate PAYE, TaxMaster uses progressive bands and allowances.
                        </p>
                    </div>

                    <!-- Marketing Card -->
                    <div class="bg-slate-50 rounded-2xl sm:rounded-3xl border border-slate-200 p-6 sm:p-8">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">How TaxMaster helps</h3>
                        
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Auto-calculate PAYE with statutory bands
                            </li>
                            <li class="flex items-start gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Generate VAT returns ready for filing
                            </li>
                            <li class="flex items-start gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Record WHT credits and prepare remittances
                            </li>
                            <li class="flex items-start gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Compute CIT from your ledgers
                            </li>
                            <li class="flex items-start gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Bank integration for auto-categorization
                            </li>
                        </ul>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <Link 
                                :href="route('pricing')" 
                                class="flex-1 inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-600 transition-all hover:border-slate-300 hover:bg-slate-50"
                            >
                                See pricing
                            </Link>
                            <Link 
                                :href="route('register')" 
                                class="flex-1 inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95"
                            >
                                Create account
                            </Link>
                        </div>
                    </div>

                    <!-- Disclaimer -->
                    <p class="text-xs text-slate-400 text-center">
                        Estimates are indicative and for planning only. For exact liabilities use TaxMaster's full filing workflow or consult a tax professional.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom range input styling */
input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    background: transparent;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    background: #0f172a;
    border-radius: 50%;
    cursor: pointer;
    margin-top: -8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: all 0.2s;
}

input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.1);
    background: #1e293b;
}

input[type="range"]::-webkit-slider-runnable-track {
    width: 100%;
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
}

input[type="range"]:focus {
    outline: none;
}

/* Remove spinner from number inputs */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>