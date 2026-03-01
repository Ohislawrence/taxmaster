<template>
  <BusinessLayout>
    <Head :title="`Edit VAT Return - ${vat.period}`" />

    <div class="space-y-6">
      <!-- Page Header -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Edit VAT Return - {{ vat.period }}</h1>
        <p class="mt-2 text-gray-600">Update your Value Added Tax return (Form 002)</p>
      </div>

      <!-- Status Alert -->
      <div v-if="vat.status !== 'draft'" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
          </div>
          <div class="ml-3">
            <p class="text-sm text-yellow-800">
              This return is published and cannot be edited. Only draft returns can be modified.
            </p>
          </div>
        </div>
      </div>

      <form v-else @submit.prevent="submit" class="space-y-6">
        <!-- Form Sections (Same as Create) -->
        <div class="bg-white rounded-lg shadow">
          <!-- Period & Form Type Section -->
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Return Details</h2>
          </div>

          <div class="px-6 py-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Period -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Period <span class="text-red-600">*</span>
                </label>
                <input
                  v-model="form.period"
                  type="month"
                  :disabled="vat.status !== 'draft'"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100"
                />
                <div v-if="errors.period" class="text-red-600 text-sm mt-1">{{ errors.period }}</div>
              </div>

              <!-- Form Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Form Type <span class="text-red-600">*</span>
                </label>
                <select
                  v-model="form.form_type"
                  :disabled="vat.status !== 'draft'"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100"
                >
                  <option value="">Select Form Type</option>
                  <option value="Form 002">Form 002 (VAT Return)</option>
                  <option value="Form 001">Form 001 (Sales Register)</option>
                </select>
                <div v-if="errors.form_type" class="text-red-600 text-sm mt-1">{{ errors.form_type }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Reporting Period -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Reporting Period <span class="text-red-600">*</span>
                </label>
                <select
                  v-model="form.reporting_period"
                  :disabled="vat.status !== 'draft'"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100"
                >
                  <option value="">Select Period</option>
                  <option value="monthly">Monthly</option>
                  <option value="quarterly">Quarterly</option>
                </select>
                <div v-if="errors.reporting_period" class="text-red-600 text-sm mt-1">{{ errors.reporting_period }}</div>
              </div>

              <!-- Reviewer (Accountant) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Assign to Accountant
                </label>
                <select
                  v-model="form.reviewed_by"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Select Accountant</option>
                  <option v-for="accountant in accountants" :key="accountant.id" :value="accountant.id">
                    {{ accountant.name }}
                  </option>
                </select>
                <div v-if="errors.reviewed_by" class="text-red-600 text-sm mt-1">{{ errors.reviewed_by }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sales Section -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Sales Information</h2>
          </div>

          <div class="px-6 py-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Sales Turnover -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Total Sales Turnover <span class="text-red-600">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.sales_turnover"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div v-if="errors.sales_turnover" class="text-red-600 text-sm mt-1">{{ errors.sales_turnover }}</div>
              </div>

              <!-- Exempt Sales -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Exempt Sales (No VAT)
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.exempt_sales"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Zero-Rated Sales -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Zero-Rated Sales (0% VAT)
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.zero_rated_sales"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Export Sales -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Export Sales (0% VAT)
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.export_sales"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Purchases Section -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Purchases Information</h2>
          </div>

          <div class="px-6 py-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Total Purchases -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Total Purchases
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.purchases_turnover"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Capital Goods Purchases -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Capital Goods Purchases (Eligible for Input VAT)
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.capital_goods_purchases"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Services Purchases -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Services Purchases (Eligible for Input VAT)
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.services_purchases"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Input VAT Adjustment -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Input VAT Adjustment
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.input_vat_adjustment"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Adjustments Section -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Adjustments & Credits</h2>
          </div>

          <div class="px-6 py-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Bad Debt Relief -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Bad Debt Relief
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.bad_debt_relief"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <p class="text-xs text-gray-500 mt-1">VAT relief on unpaid invoices</p>
              </div>

              <!-- Credit Notes Issued -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Credit Notes Issued
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.credit_notes_issued"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Credit Notes Received -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Credit Notes Received
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.credit_notes_received"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Prior Month Credit -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Prior Month Credit
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.prior_month_credit"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Advance Payment -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Advance Payment
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.advance_payment"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Withholding VAT -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Withholding VAT
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-2 text-gray-600">₦</span>
                  <input
                    v-model="form.withholding_vat"
                    @input="recalculate"
                    type="number"
                    step="0.01"
                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Calculation Summary Section -->
        <div class="bg-blue-50 rounded-lg border border-blue-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-6">VAT Calculation Summary</h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Output VAT -->
            <div class="bg-white rounded p-4 border border-blue-200">
              <p class="text-gray-600 text-sm font-medium">Output VAT (on Sales)</p>
              <p class="text-2xl font-bold text-blue-600 mt-2">
                ₦{{ formatCurrency(calculations.output_vat) }}
              </p>
              <p class="text-xs text-gray-500 mt-2">5% of ₦{{ formatCurrency(form.sales_turnover) }}</p>
            </div>

            <!-- Input VAT -->
            <div class="bg-white rounded p-4 border border-green-200">
              <p class="text-gray-600 text-sm font-medium">Input VAT (on Purchases)</p>
              <p class="text-2xl font-bold text-green-600 mt-2">
                ₦{{ formatCurrency(calculations.input_vat) }}
              </p>
              <p class="text-xs text-gray-500 mt-2">5% of eligible purchases</p>
            </div>

            <!-- Input Credit -->
            <div class="bg-white rounded p-4 border border-purple-200">
              <p class="text-gray-600 text-sm font-medium">Deductible Input Credit</p>
              <p class="text-2xl font-bold text-purple-600 mt-2">
                ₦{{ formatCurrency(calculations.input_credit) }}
              </p>
              <p class="text-xs text-gray-500 mt-2">After adjustments</p>
            </div>
          </div>

          <div class="border-t border-blue-200 mt-6 pt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- VAT Due -->
              <div class="bg-white rounded p-4 border-2 border-orange-300">
                <p class="text-gray-600 text-sm font-medium">VAT Due (Before Settlement)</p>
                <p class="text-3xl font-bold text-orange-600 mt-2">
                  ₦{{ formatCurrency(calculations.vat_due) }}
                </p>
              </div>

              <!-- Settlement Amount -->
              <div class="bg-white rounded p-4" :class="getSettlementClass()">
                <p class="text-gray-600 text-sm font-medium">Settlement Amount</p>
                <p class="text-3xl font-bold mt-2" :class="getSettlementColor()">
                  ₦{{ formatCurrency(calculations.settlement_amount) }}
                </p>
                <p class="text-xs mt-2" :class="getSettlementTextClass()">
                  {{ getSettlementDescription() }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-lg shadow p-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Notes
          </label>
          <textarea
            v-model="form.notes"
            rows="4"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Add any additional notes or details..."
          ></textarea>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center gap-4">
          <button
            type="submit"
            :disabled="processing || vat.status !== 'draft'"
            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
          >
            <i v-if="!processing" class="fas fa-save mr-2"></i>
            <i v-else class="fas fa-spinner fa-spin mr-2"></i>
            {{ processing ? 'Updating...' : 'Update Return' }}
          </button>

          <Link
            :href="route('business.vat.show', vat.id)"
            class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition"
          >
            <i class="fas fa-times mr-2"></i>
            Cancel
          </Link>
        </div>
      </form>
    </div>
  </BusinessLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
  vat: Object,
  accountants: Array,
  errors: Object,
});

const processing = ref(false);

const form = useForm({
  period: props.vat.period,
  form_type: props.vat.form_type,
  reporting_period: props.vat.reporting_period,
  reviewed_by: props.vat.reviewed_by,
  sales_turnover: props.vat.sales_turnover,
  exempt_sales: props.vat.exempt_sales,
  zero_rated_sales: props.vat.zero_rated_sales,
  export_sales: props.vat.export_sales,
  vat_on_sales: props.vat.vat_on_sales,
  purchases_turnover: props.vat.purchases_turnover,
  capital_goods_purchases: props.vat.capital_goods_purchases,
  services_purchases: props.vat.services_purchases,
  input_vat: props.vat.input_vat,
  input_vat_adjustment: props.vat.input_vat_adjustment,
  bad_debt_relief: props.vat.bad_debt_relief,
  credit_notes_issued: props.vat.credit_notes_issued,
  credit_notes_received: props.vat.credit_notes_received,
  vat_due: props.vat.vat_due,
  settlement_amount: props.vat.settlement_amount,
  settlement_type: props.vat.settlement_type,
  prior_month_credit: props.vat.prior_month_credit,
  advance_payment: props.vat.advance_payment,
  withholding_vat: props.vat.withholding_vat,
  notes: props.vat.notes,
});

const calculations = computed(() => {
  const salesTurnover = parseFloat(form.sales_turnover) || 0;
  const capitalGoods = parseFloat(form.capital_goods_purchases) || 0;
  const services = parseFloat(form.services_purchases) || 0;
  const badDebt = parseFloat(form.bad_debt_relief) || 0;
  const creditNotesIssued = parseFloat(form.credit_notes_issued) || 0;
  const creditNotesReceived = parseFloat(form.credit_notes_received) || 0;
  const adjustment = parseFloat(form.input_vat_adjustment) || 0;
  const priorCredit = parseFloat(form.prior_month_credit) || 0;
  const advancePayment = parseFloat(form.advance_payment) || 0;
  const withholdingVat = parseFloat(form.withholding_vat) || 0;

  // Output VAT (5% on sales only)
  const outputVat = salesTurnover * 0.05;

  // Input VAT (5% on eligible purchases)
  const inputVat = (capitalGoods + services) * 0.05;

  // Input Credit with adjustments
  const inputCredit = Math.max(0, inputVat + adjustment + (badDebt * 0.05) + (creditNotesReceived * 0.05));

  // VAT Due calculation
  const vatDue = outputVat - (creditNotesIssued * 0.05) - inputCredit;

  // Settlement calculation
  const settlementAmount = vatDue - priorCredit - advancePayment - withholdingVat;

  // Determine settlement type
  let settlementType = 'zero';
  if (settlementAmount > 0) settlementType = 'payment';
  if (settlementAmount < 0) settlementType = 'refund';

  return {
    output_vat: outputVat,
    input_vat: inputVat,
    input_credit: inputCredit,
    vat_due: vatDue,
    settlement_amount: Math.abs(settlementAmount),
    settlement_type: settlementType,
  };
});

const recalculate = () => {
  form.vat_on_sales = calculations.value.output_vat;
  form.input_vat = calculations.value.input_vat;
  form.vat_due = calculations.value.vat_due;
  form.settlement_amount = calculations.value.settlement_amount;
  form.settlement_type = calculations.value.settlement_type;
};

const formatCurrency = (value) => {
  if (!value || isNaN(value)) return '0.00';
  return parseFloat(value).toLocaleString('en-NG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const getSettlementClass = () => {
  const type = calculations.value.settlement_type;
  if (type === 'payment') return 'border-2 border-orange-300 bg-orange-50';
  if (type === 'refund') return 'border-2 border-green-300 bg-green-50';
  return 'border-2 border-gray-300 bg-gray-50';
};

const getSettlementColor = () => {
  const type = calculations.value.settlement_type;
  if (type === 'payment') return 'text-orange-600';
  if (type === 'refund') return 'text-green-600';
  return 'text-gray-600';
};

const getSettlementTextClass = () => {
  const type = calculations.value.settlement_type;
  if (type === 'payment') return 'text-orange-600';
  if (type === 'refund') return 'text-green-600';
  return 'text-gray-600';
};

const getSettlementDescription = () => {
  const type = calculations.value.settlement_type;
  if (type === 'payment') return 'Payment due to FIRS';
  if (type === 'refund') return 'Refund pending from FIRS';
  return 'No settlement required';
};

const submit = () => {
  processing.value = true;
  form.put(route('business.vat.update', props.vat.id), {
    onFinish: () => (processing.value = false),
  });
};
</script>
