<template>
  <BusinessLayout>
    <Head title="CIT Return Details" />

    <div class="space-y-4 sm:space-y-6 px-3 sm:px-0 max-w-4xl mx-auto">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">CIT Return - {{ citReturn.period }}</h1>
          <p class="mt-2 text-sm sm:text-base text-gray-600">View and manage your corporate income tax return</p>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
          <Link
            v-if="citReturn.status === 'draft'"
            :href="route('business.cit.edit', citReturn.id)"
            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            <i class="fas fa-edit mr-2"></i>
            Edit
          </Link>
          <Link
            v-if="citReturn.status === 'submitted'"
            href="#"
            @click.prevent="generateRRR"
            class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700"
          >
            <i class="fas fa-receipt mr-2"></i>
            Generate Payment RRR
          </Link>
        </div>
      </div>

      <!-- Status & Due Date Card -->
      <div class="bg-white shadow rounded-lg p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <p class="text-gray-600 text-sm font-medium">Current Status</p>
            <div class="mt-2">
              <span :class="getStatusClass(citReturn.status)" class="px-4 py-2 rounded-full text-sm font-medium">
                {{ formatStatus(citReturn.status) }}
              </span>
            </div>
          </div>
          <div class="text-left sm:text-right">
            <p class="text-gray-600 text-sm font-medium">Due Date</p>
            <p class="text-lg font-bold text-gray-900 mt-1">{{ formatDate(citReturn.due_date) }}</p>
          </div>
        </div>
      </div>

      <!-- Financial Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white shadow rounded-lg p-5">
          <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Turnover</p>
          <p class="text-xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(citReturn.turnover) }}</p>
          <p class="text-xs mt-1" :class="companySizeClass">{{ companySize }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
          <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Gross Profit</p>
          <p class="text-xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(citReturn.gross_profit) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
          <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Taxable Income</p>
          <p class="text-xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(citReturn.taxable_income) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
          <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Tax Due</p>
          <p class="text-xl font-bold text-blue-600 mt-2">₦{{ formatCurrency(citReturn.tax_due) }}</p>
        </div>
      </div>

      <!-- Income Breakdown -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
          <h2 class="text-lg font-bold text-gray-900">
            <i class="fas fa-chart-line mr-2 text-green-600"></i>Income Breakdown
          </h2>
        </div>
        <div class="px-6 py-6 space-y-3">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Revenue / Sales</span>
            <span class="text-gray-900 font-medium">₦{{ formatCurrency(citReturn.revenue) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Less: Cost of Sales / COGS</span>
            <span class="text-red-600">(₦{{ formatCurrency(citReturn.cost_of_goods_sold) }})</span>
          </div>
          <div class="border-t pt-3 flex justify-between">
            <span class="font-semibold text-gray-900">Gross Profit</span>
            <span class="font-bold text-gray-900">₦{{ formatCurrency(citReturn.gross_profit) }}</span>
          </div>
        </div>
      </div>

      <!-- Tax Adjustments -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
          <h2 class="text-lg font-bold text-gray-900">
            <i class="fas fa-sliders-h mr-2 text-orange-600"></i>Tax Adjustments
          </h2>
        </div>
        <div class="px-6 py-6 space-y-4">
          <!-- Add-backs -->
          <div>
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
              <i class="fas fa-plus-circle text-red-500 mr-1"></i> Add-backs (Disallowable)
            </h3>
            <div class="space-y-2 ml-4">
              <div class="flex justify-between text-sm" v-if="citReturn.depreciation">
                <span class="text-gray-600">Depreciation</span>
                <span class="text-gray-900">₦{{ formatCurrency(citReturn.depreciation) }}</span>
              </div>
              <div class="flex justify-between text-sm" v-if="citReturn.amortization">
                <span class="text-gray-600">Amortization</span>
                <span class="text-gray-900">₦{{ formatCurrency(citReturn.amortization) }}</span>
              </div>
              <div class="flex justify-between text-sm" v-if="citReturn.other_add_backs">
                <span class="text-gray-600">Other Disallowable Expenses</span>
                <span class="text-gray-900">₦{{ formatCurrency(citReturn.other_add_backs) }}</span>
              </div>
              <div class="flex justify-between text-sm font-medium border-t pt-2">
                <span class="text-gray-900">Total Add-backs</span>
                <span class="text-gray-900">₦{{ formatCurrency(totalAddBacks) }}</span>
              </div>
            </div>
          </div>

          <hr />

          <!-- Deductions -->
          <div>
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
              <i class="fas fa-minus-circle text-green-500 mr-1"></i> Deductions (Allowable)
            </h3>
            <div class="space-y-2 ml-4">
              <div class="flex justify-between text-sm" v-if="citReturn.capital_allowances">
                <span class="text-gray-600">Capital Allowances</span>
                <span class="text-gray-900">₦{{ formatCurrency(citReturn.capital_allowances) }}</span>
              </div>
              <div class="flex justify-between text-sm" v-if="citReturn.allowable_expenses">
                <span class="text-gray-600">Allowable Expenses</span>
                <span class="text-gray-900">₦{{ formatCurrency(citReturn.allowable_expenses) }}</span>
              </div>
              <div class="flex justify-between text-sm" v-if="citReturn.other_deductions">
                <span class="text-gray-600">Other Deductions</span>
                <span class="text-gray-900">₦{{ formatCurrency(citReturn.other_deductions) }}</span>
              </div>
              <div class="flex justify-between text-sm font-medium border-t pt-2">
                <span class="text-gray-900">Total Deductions</span>
                <span class="text-green-700">(₦{{ formatCurrency(totalDeductions) }})</span>
              </div>
            </div>
          </div>

          <!-- Taxable Income -->
          <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mt-4">
            <div class="flex justify-between items-center">
              <span class="font-semibold text-blue-900">Taxable Profit (Assessable Profit)</span>
              <span class="text-xl font-bold text-blue-700">₦{{ formatCurrency(citReturn.taxable_income) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tax Calculation -->
      <div class="bg-white shadow rounded-lg border-2 border-blue-200">
        <div class="px-6 py-4 border-b border-blue-200 bg-blue-50 rounded-t-lg">
          <h2 class="text-lg font-bold text-blue-900">
            <i class="fas fa-calculator mr-2"></i>Tax Calculation
          </h2>
        </div>
        <div class="px-6 py-6 space-y-3">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="bg-gray-50 p-3 rounded">
              <p class="text-xs text-gray-500 uppercase">Company Size</p>
              <p class="font-semibold" :class="companySizeClass">{{ companySize }}</p>
            </div>
            <div class="bg-gray-50 p-3 rounded">
              <p class="text-xs text-gray-500 uppercase">CIT Rate</p>
              <p class="font-semibold text-gray-900">{{ citRatePercent }}%</p>
            </div>
          </div>

          <div class="flex justify-between text-sm">
            <span class="text-gray-600">CIT on Taxable Profit ({{ citRatePercent }}%)</span>
            <span class="text-gray-900">₦{{ formatCurrency(citReturn.cit_payable) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Minimum Tax (0.5% of Turnover)</span>
            <span class="text-gray-900">₦{{ formatCurrency(citReturn.minimum_tax_amount) }}</span>
          </div>
          <div class="flex justify-between text-sm font-bold border-t pt-2">
            <span class="text-gray-900">Tax Due (Higher of CIT or Min Tax)</span>
            <span class="text-blue-600">₦{{ formatCurrency(citReturn.tax_due) }}</span>
          </div>

          <div v-if="(citReturn.withholding_tax || citReturn.advance_tax)" class="space-y-2 mt-3 pt-3 border-t">
            <div class="flex justify-between text-sm" v-if="citReturn.withholding_tax">
              <span class="text-gray-600">Less: WHT Credits</span>
              <span class="text-green-600">(₦{{ formatCurrency(citReturn.withholding_tax) }})</span>
            </div>
            <div class="flex justify-between text-sm" v-if="citReturn.advance_tax">
              <span class="text-gray-600">Less: Advance Tax / Instalments</span>
              <span class="text-green-600">(₦{{ formatCurrency(citReturn.advance_tax) }})</span>
            </div>
          </div>

          <div class="border-t-2 border-blue-300 pt-3 mt-3">
            <div class="flex justify-between">
              <span class="text-lg font-bold text-gray-900">Balance Due to FIRS</span>
              <span class="text-xl font-bold" :class="balanceDue > 0 ? 'text-red-600' : 'text-green-600'">
                ₦{{ formatCurrency(Math.abs(balanceDue)) }}
                <span v-if="balanceDue < 0" class="text-sm">(Refund)</span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Company Info -->
      <div class="bg-white shadow rounded-lg" v-if="citReturn.gross_assets || citReturn.paid_up_capital">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
          <h2 class="text-lg font-bold text-gray-900">
            <i class="fas fa-building mr-2 text-gray-500"></i>Company Financial Info
          </h2>
        </div>
        <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <p class="text-xs text-gray-500 uppercase">Annual Turnover</p>
            <p class="font-semibold text-gray-900">₦{{ formatCurrency(citReturn.turnover) }}</p>
          </div>
          <div v-if="citReturn.gross_assets">
            <p class="text-xs text-gray-500 uppercase">Gross Assets</p>
            <p class="font-semibold text-gray-900">₦{{ formatCurrency(citReturn.gross_assets) }}</p>
          </div>
          <div v-if="citReturn.paid_up_capital">
            <p class="text-xs text-gray-500 uppercase">Paid-up Capital</p>
            <p class="font-semibold text-gray-900">₦{{ formatCurrency(citReturn.paid_up_capital) }}</p>
          </div>
        </div>
      </div>

      <!-- Reviewer Info -->
      <div class="bg-white shadow rounded-lg px-6 py-4" v-if="citReturn.reviewer">
        <h3 class="text-sm font-medium text-gray-900">
          <i class="fas fa-user-tie mr-1 text-gray-500"></i> Accountant Review
        </h3>
        <div class="mt-2">
          <p class="text-sm text-gray-600">{{ citReturn.reviewer.name }}</p>
          <p class="text-sm text-gray-500">{{ citReturn.reviewer.email }}</p>
        </div>
      </div>

      <!-- Notes -->
      <div class="bg-white shadow rounded-lg px-6 py-4" v-if="citReturn.notes">
        <h3 class="text-sm font-medium text-gray-900">
          <i class="fas fa-sticky-note mr-1 text-gray-500"></i> Notes
        </h3>
        <p class="mt-2 text-sm text-gray-600 whitespace-pre-line">{{ citReturn.notes }}</p>
      </div>

      <!-- Payment History -->
      <div class="bg-white shadow rounded-lg" v-if="citReturn.governmentPayments && citReturn.governmentPayments.length > 0">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-bold text-gray-900">
            <i class="fas fa-history mr-2 text-gray-500"></i>Payment History
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RRR</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="payment in citReturn.governmentPayments" :key="payment.id">
                <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(payment.created_at) }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ payment.rrr }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">₦{{ formatCurrency(payment.amount) }}</td>
                <td class="px-6 py-4 text-sm">
                  <span :class="getPaymentStatusClass(payment.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                    {{ formatPaymentStatus(payment.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Back Button -->
      <div class="flex items-center space-x-3">
        <Link href="/business/cit" class="text-blue-600 hover:text-blue-900">
          <i class="fas fa-arrow-left mr-2"></i>
          Back to CIT Returns
        </Link>
      </div>
    </div>
  </BusinessLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
  citReturn: { type: Object, required: true },
});

const totalAddBacks = computed(() => {
  return (parseFloat(props.citReturn.depreciation) || 0) +
    (parseFloat(props.citReturn.amortization) || 0) +
    (parseFloat(props.citReturn.other_add_backs) || 0);
});

const totalDeductions = computed(() => {
  return (parseFloat(props.citReturn.capital_allowances) || 0) +
    (parseFloat(props.citReturn.allowable_expenses) || 0) +
    (parseFloat(props.citReturn.other_deductions) || 0);
});

const companySize = computed(() => {
  const t = parseFloat(props.citReturn.turnover) || 0;
  if (t < 25000000) return 'Small (< ₦25M)';
  if (t <= 100000000) return 'Medium (₦25M – ₦100M)';
  return 'Large (> ₦100M)';
});

const companySizeClass = computed(() => {
  const t = parseFloat(props.citReturn.turnover) || 0;
  if (t < 25000000) return 'text-green-600';
  if (t <= 100000000) return 'text-yellow-600';
  return 'text-red-600';
});

const citRatePercent = computed(() => {
  const t = parseFloat(props.citReturn.turnover) || 0;
  if (t < 25000000) return '0';
  if (t <= 100000000) return '20';
  return '30';
});

const balanceDue = computed(() => {
  const taxDue = parseFloat(props.citReturn.tax_due) || 0;
  const wht = parseFloat(props.citReturn.withholding_tax) || 0;
  const advanceTax = parseFloat(props.citReturn.advance_tax) || 0;
  return taxDue - wht - advanceTax;
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-NG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-NG');
};

const formatStatus = (status) => {
  const statuses = {
    draft: 'Draft',
    submitted: 'Submitted',
    approved: 'Approved',
    paid: 'Paid',
    overdue: 'Overdue',
  };
  return statuses[status] || status;
};

const formatPaymentStatus = (status) => {
  const statuses = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    failed: 'Failed',
  };
  return statuses[status] || status;
};

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-blue-100 text-blue-800',
    approved: 'bg-purple-100 text-purple-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const generateRRR = () => {
  if (confirm('Generate a Remita Retrieval Reference (RRR) for this CIT return?')) {
    router.post(route('business.cit.generate-rrr', props.citReturn.id));
  }
};
</script>
