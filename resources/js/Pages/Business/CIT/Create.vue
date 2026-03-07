<template>
  <BusinessLayout>
    <Head title="New CIT Return" />

    <div class="space-y-6 max-w-4xl mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">New CIT Return</h1>
          <p class="mt-2 text-gray-600">File a corporate income tax return for your business</p>
        </div>
      </div>

      <!-- Info Card -->
      <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
          </div>
          <div class="ml-3">
            <p class="text-sm text-blue-700">
              <strong>Nigerian CIT Rates (Finance Act 2019):</strong>
              Small companies (turnover &lt; ₦25M) = 0% · Medium (₦25M–₦100M) = 20% · Large (&gt; ₦100M) = 30%.
              Minimum tax of 0.5% of turnover applies when CIT is less.
            </p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">

        <!-- Section 1: Period & Company Info -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <h2 class="text-lg font-semibold text-gray-900">
              <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Period &amp; Company Info
            </h2>
          </div>
          <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="period" class="block text-sm font-medium text-gray-700">Financial Year Period <span class="text-red-500">*</span></label>
              <input v-model="form.period" type="month" id="period"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': form.errors?.period }" />
              <p v-if="form.errors?.period" class="mt-1 text-sm text-red-600">{{ form.errors.period }}</p>
            </div>
            <div>
              <label for="turnover" class="block text-sm font-medium text-gray-700">Annual Turnover <span class="text-red-500">*</span></label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₦</span>
                <input v-model="form.turnover" type="number" id="turnover" step="0.01" placeholder="0.00"
                  class="pl-8 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-500': form.errors?.turnover }" />
              </div>
              <p v-if="form.errors?.turnover" class="mt-1 text-sm text-red-600">{{ form.errors.turnover }}</p>
              <p class="mt-1 text-xs text-gray-500">Total revenue/sales for the year. Determines your CIT rate tier.</p>
            </div>
            <div>
              <label for="gross_assets" class="block text-sm font-medium text-gray-700">Gross Assets</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₦</span>
                <input v-model="form.gross_assets" type="number" id="gross_assets" step="0.01" placeholder="0.00"
                  class="pl-8 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" />
              </div>
              <p class="mt-1 text-xs text-gray-500">Total value of company assets (used for minimum tax calculation)</p>
            </div>
            <div>
              <label for="paid_up_capital" class="block text-sm font-medium text-gray-700">Paid-up Capital</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₦</span>
                <input v-model="form.paid_up_capital" type="number" id="paid_up_capital" step="0.01" placeholder="0.00"
                  class="pl-8 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" />
              </div>
              <p class="mt-1 text-xs text-gray-500">Issued share capital (optional, for minimum tax calc)</p>
            </div>
          </div>
        </div>

        <!-- Section 2: Income -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <h2 class="text-lg font-semibold text-gray-900">
              <i class="fas fa-chart-line mr-2 text-green-600"></i>Income
            </h2>
          </div>
          <div class="px-6 py-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="revenue" class="block text-sm font-medium text-gray-700">Revenue / Sales <span class="text-red-500">*</span></label>
                <div class="mt-1 relative rounded-md shadow-sm">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₦</span>
                  <input v-model="form.revenue" type="number" id="revenue" step="0.01" placeholder="0.00"
                    class="pl-8 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': form.errors?.revenue }" />
                </div>
                <p v-if="form.errors?.revenue" class="mt-1 text-sm text-red-600">{{ form.errors.revenue }}</p>
              </div>
              <div>
                <label for="cost_of_goods_sold" class="block text-sm font-medium text-gray-700">Cost of Sales / COGS</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₦</span>
                  <input v-model="form.cost_of_goods_sold" type="number" id="cost_of_goods_sold" step="0.01" placeholder="0.00"
                    class="pl-8 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" />
                </div>
              </div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-green-800">Gross Profit (Revenue - COGS)</span>
                <span class="text-lg font-bold text-green-700">₦{{ formatCurrency(calc.grossProfit) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Section 3: Tax Adjustments -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <h2 class="text-lg font-semibold text-gray-900">
              <i class="fas fa-sliders-h mr-2 text-orange-600"></i>Tax Adjustments
            </h2>
            <p class="text-sm text-gray-500 mt-1">Add back disallowable expenses and claim allowable deductions per FIRS rules</p>
          </div>
          <div class="px-6 py-6 space-y-6">
            <div>
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">
                <i class="fas fa-plus-circle text-red-500 mr-1"></i> Add-backs (Disallowable Expenses)
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label for="depreciation" class="block text-xs font-medium text-gray-600">Depreciation</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm">₦</span>
                    <input v-model="form.depreciation" type="number" id="depreciation" step="0.01" placeholder="0.00"
                      class="pl-7 block w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500" />
                  </div>
                  <p class="mt-0.5 text-xs text-gray-400">Replaced by capital allowances for tax</p>
                </div>
                <div>
                  <label for="amortization" class="block text-xs font-medium text-gray-600">Amortization</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm">₦</span>
                    <input v-model="form.amortization" type="number" id="amortization" step="0.01" placeholder="0.00"
                      class="pl-7 block w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500" />
                  </div>
                </div>
                <div>
                  <label for="other_add_backs" class="block text-xs font-medium text-gray-600">Other Disallowable</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm">₦</span>
                    <input v-model="form.other_add_backs" type="number" id="other_add_backs" step="0.01" placeholder="0.00"
                      class="pl-7 block w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500" />
                  </div>
                  <p class="mt-0.5 text-xs text-gray-400">Donations, entertainment, penalties, etc.</p>
                </div>
              </div>
            </div>
            <hr />
            <div>
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">
                <i class="fas fa-minus-circle text-green-500 mr-1"></i> Deductions (Allowable)
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label for="capital_allowances" class="block text-xs font-medium text-gray-600">Capital Allowances</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm">₦</span>
                    <input v-model="form.capital_allowances" type="number" id="capital_allowances" step="0.01" placeholder="0.00"
                      class="pl-7 block w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500" />
                  </div>
                  <p class="mt-0.5 text-xs text-gray-400">FIRS-approved wear &amp; tear allowance</p>
                </div>
                <div>
                  <label for="allowable_expenses" class="block text-xs font-medium text-gray-600">Allowable Expenses</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm">₦</span>
                    <input v-model="form.allowable_expenses" type="number" id="allowable_expenses" step="0.01" placeholder="0.00"
                      class="pl-7 block w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500" />
                  </div>
                </div>
                <div>
                  <label for="other_deductions" class="block text-xs font-medium text-gray-600">Other Deductions</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm">₦</span>
                    <input v-model="form.other_deductions" type="number" id="other_deductions" step="0.01" placeholder="0.00"
                      class="pl-7 block w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500" />
                  </div>
                  <p class="mt-0.5 text-xs text-gray-400">Loss relief, investment allowances, etc.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section 4: Tax Credits -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <h2 class="text-lg font-semibold text-gray-900">
              <i class="fas fa-receipt mr-2 text-purple-600"></i>Tax Credits &amp; Advance Payments
            </h2>
            <p class="text-sm text-gray-500 mt-1">Deduct WHT credits and advance payments already made</p>
          </div>
          <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="withholding_tax" class="block text-sm font-medium text-gray-700">WHT Credits</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₦</span>
                <input v-model="form.withholding_tax" type="number" id="withholding_tax" step="0.01" placeholder="0.00"
                  class="pl-8 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" />
              </div>
              <p class="mt-1 text-xs text-gray-500">Withholding tax deducted at source on your income</p>
            </div>
            <div>
              <label for="advance_tax" class="block text-sm font-medium text-gray-700">Advance Tax / Instalments</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₦</span>
                <input v-model="form.advance_tax" type="number" id="advance_tax" step="0.01" placeholder="0.00"
                  class="pl-8 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" />
              </div>
              <p class="mt-1 text-xs text-gray-500">Tax instalments already paid for this period</p>
            </div>
          </div>
        </div>

        <!-- Section 5: Additional Info -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <h2 class="text-lg font-semibold text-gray-900">
              <i class="fas fa-sticky-note mr-2 text-gray-500"></i>Additional Info
            </h2>
          </div>
          <div class="px-6 py-6 space-y-6">
            <div>
              <label for="reviewer_id" class="block text-sm font-medium text-gray-700">Reviewer (Accountant)</label>
              <select v-model="form.reviewer_id" id="reviewer_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">-- Select Accountant (Optional) --</option>
                <option v-for="accountant in accountants" :key="accountant.id" :value="accountant.id">
                  {{ accountant.name }}
                </option>
              </select>
            </div>
            <div>
              <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
              <textarea v-model="form.notes" id="notes" rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Add any additional notes or context..."></textarea>
            </div>
          </div>
        </div>

        <!-- Tax Calculation Summary -->
        <div class="bg-white shadow rounded-lg border-2 border-blue-200">
          <div class="px-6 py-4 border-b border-blue-200 bg-blue-50 rounded-t-lg">
            <h2 class="text-lg font-semibold text-blue-900">
              <i class="fas fa-calculator mr-2"></i>Tax Calculation Summary
            </h2>
          </div>
          <div class="px-6 py-6 space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Revenue</span>
              <span class="text-gray-900">₦{{ formatCurrency(calc.revenue) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Less: Cost of Sales</span>
              <span class="text-red-600">(₦{{ formatCurrency(calc.cogs) }})</span>
            </div>
            <div class="flex justify-between text-sm font-medium border-t pt-2">
              <span class="text-gray-900">Gross Profit</span>
              <span class="text-gray-900">₦{{ formatCurrency(calc.grossProfit) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Add: Disallowable Expenses</span>
              <span class="text-gray-900">₦{{ formatCurrency(calc.totalAddBacks) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Less: Allowable Deductions</span>
              <span class="text-red-600">(₦{{ formatCurrency(calc.totalDeductions) }})</span>
            </div>
            <div class="flex justify-between text-sm font-medium border-t pt-2">
              <span class="text-gray-900">Taxable Profit (Assessable Profit)</span>
              <span class="text-gray-900">₦{{ formatCurrency(calc.taxableIncome) }}</span>
            </div>

            <div class="mt-4 bg-gray-50 p-4 rounded-lg space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Company Size</span>
                <span class="font-medium" :class="calc.sizeClass">{{ calc.companySize }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">CIT Rate</span>
                <span class="font-medium text-gray-900">{{ calc.citRatePercent }}%</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">CIT on Taxable Profit</span>
                <span class="text-gray-900">₦{{ formatCurrency(calc.citPayable) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Minimum Tax (0.5% of Turnover)</span>
                <span class="text-gray-900">₦{{ formatCurrency(calc.minimumTax) }}</span>
              </div>
              <div class="flex justify-between text-sm font-bold border-t pt-2">
                <span class="text-gray-900">Tax Due (Higher of CIT or Minimum Tax)</span>
                <span class="text-blue-600">₦{{ formatCurrency(calc.taxDue) }}</span>
              </div>
            </div>

            <div v-if="calc.totalCredits > 0" class="space-y-2 mt-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Less: WHT Credits</span>
                <span class="text-green-600">(₦{{ formatCurrency(parseFloat(form.withholding_tax) || 0) }})</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Less: Advance Tax</span>
                <span class="text-green-600">(₦{{ formatCurrency(parseFloat(form.advance_tax) || 0) }})</span>
              </div>
            </div>

            <div class="border-t-2 border-blue-300 pt-3 mt-3">
              <div v-if="calc.balanceDue > 0" class="flex justify-between">
                <span class="text-lg font-bold text-gray-900">Balance Due to FIRS</span>
                <span class="text-xl font-bold text-red-600">₦{{ formatCurrency(calc.balanceDue) }}</span>
              </div>
              <div v-else-if="calc.balanceRefund > 0" class="flex justify-between">
                <span class="text-lg font-bold text-gray-900">Refund / Credit Due</span>
                <span class="text-xl font-bold text-green-600">₦{{ formatCurrency(calc.balanceRefund) }}</span>
              </div>
              <div v-else class="flex justify-between">
                <span class="text-lg font-bold text-gray-900">Balance</span>
                <span class="text-xl font-bold text-gray-600">₦0.00</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <Link href="/business/cit" class="text-gray-700 hover:text-gray-900">
              <i class="fas fa-arrow-left mr-1"></i> Cancel
            </Link>
            <div class="flex flex-col sm:flex-row gap-3">
              <button type="button" @click="saveDraft"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-save mr-2"></i> Save as Draft
              </button>
              <button type="submit" :disabled="form.processing"
                class="inline-flex items-center justify-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50">
                <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                <i v-else class="fas fa-paper-plane mr-2"></i>
                {{ form.processing ? 'Submitting...' : 'Submit CIT Return' }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

defineProps({
  accountants: { type: Array, default: () => [] },
});

const form = useForm({
  period: new Date().toISOString().slice(0, 7),
  turnover: '',
  gross_assets: '',
  paid_up_capital: '',
  revenue: '',
  cost_of_goods_sold: '',
  depreciation: '',
  amortization: '',
  other_add_backs: '',
  capital_allowances: '',
  allowable_expenses: '',
  other_deductions: '',
  withholding_tax: '',
  advance_tax: '',
  reviewer_id: '',
  notes: '',
});

const calc = computed(() => {
  const revenue = parseFloat(form.revenue) || 0;
  const cogs = parseFloat(form.cost_of_goods_sold) || 0;
  const grossProfit = revenue - cogs;

  const totalAddBacks = (parseFloat(form.depreciation) || 0) +
    (parseFloat(form.amortization) || 0) +
    (parseFloat(form.other_add_backs) || 0);

  const totalDeductions = (parseFloat(form.capital_allowances) || 0) +
    (parseFloat(form.allowable_expenses) || 0) +
    (parseFloat(form.other_deductions) || 0);

  const taxableIncome = Math.max(0, grossProfit + totalAddBacks - totalDeductions);

  // Nigerian CIT rate tiers (Finance Act 2019)
  const turnover = parseFloat(form.turnover) || 0;
  let citRate, companySize, sizeClass;
  if (turnover < 25000000) {
    citRate = 0;
    companySize = 'Small (< ₦25M turnover)';
    sizeClass = 'text-green-600';
  } else if (turnover <= 100000000) {
    citRate = 0.20;
    companySize = 'Medium (₦25M – ₦100M)';
    sizeClass = 'text-yellow-600';
  } else {
    citRate = 0.30;
    companySize = 'Large (> ₦100M)';
    sizeClass = 'text-red-600';
  }

  const citPayable = taxableIncome * citRate;

  // Minimum tax
  const grossAssets = parseFloat(form.gross_assets) || 0;
  const paidUpCapital = parseFloat(form.paid_up_capital) || 0;
  let minimumTax = turnover * 0.005;
  minimumTax = Math.max(minimumTax, grossAssets * 0.005);
  if (paidUpCapital > 0) {
    minimumTax = Math.max(minimumTax, paidUpCapital * 0.0025);
  }

  // Small companies exempt from minimum tax
  let taxDue;
  if (turnover < 25000000) {
    taxDue = 0;
  } else {
    taxDue = Math.max(citPayable, minimumTax);
  }

  const totalCredits = (parseFloat(form.withholding_tax) || 0) + (parseFloat(form.advance_tax) || 0);
  const balance = taxDue - totalCredits;

  return {
    revenue, cogs, grossProfit,
    totalAddBacks, totalDeductions, taxableIncome,
    citRate, citRatePercent: (citRate * 100).toFixed(0),
    companySize, sizeClass,
    citPayable, minimumTax, taxDue,
    totalCredits,
    balanceDue: balance > 0 ? balance : 0,
    balanceRefund: balance < 0 ? Math.abs(balance) : 0,
  };
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-NG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

const submit = () => {
  form.transform(data => ({
    ...data,
    status: 'submitted',
  })).post(route('business.cit.store'));
};

const saveDraft = () => {
  form.transform(data => ({
    ...data,
    status: 'draft',
  })).post(route('business.cit.store'));
};
</script>
