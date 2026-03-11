<template>
  <BusinessLayout>
    <Head :title="`VAT Return - ${vat.period}`" />

    <div class="space-y-4 sm:space-y-6 px-3 sm:px-0">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">VAT Return - {{ vat.period }}</h1>
          <p class="mt-2 text-sm sm:text-base text-gray-600">{{ formatFormType(vat.form_type) }}</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
          <Link
            v-if="vat.status === 'draft'"
            :href="route('business.vat.edit', vat.id)"
            class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm"
          >
            <i class="fas fa-edit mr-2"></i>
            Edit
          </Link>
          <Link
            v-if="['submitted', 'accepted'].includes(vat.status)"
            :href="generatePaymentRRR"
            class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm"
          >
            <i class="fas fa-credit-card mr-2"></i>
            Pay VAT
          </Link>
          <Link
            :href="route('business.vat.index')"
            class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm"
          >
            <i class="fas fa-arrow-left mr-2"></i>
            Back
          </Link>
          <Link
            :href="route('business.vat.export.form002') + '?format=csv'"
            class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm"
          >
            Export Form 002 (CSV)
          </Link>
          <Link
            :href="route('business.vat.export.form002') + '?format=xml'"
            class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm"
          >
            Export Form 002 (XML)
          </Link>
        </div>
      </div>

      <!-- Status & Timeline -->
      <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
              <span :class="getStatusClass(vat.status)" class="px-4 py-2 rounded-full text-sm font-semibold">
                {{ formatStatus(vat.status) }}
              </span>
              <span v-if="vat.due_date" class="text-sm sm:text-base text-gray-600">
                Due: {{ formatDate(vat.due_date) }}
              </span>
            </div>
          </div>
          <div class="text-left sm:text-right">
            <p v-if="vat.submitted_at" class="text-xs sm:text-sm text-gray-600">
              Submitted: {{ formatDate(vat.submitted_at) }}
            </p>
            <p v-if="vat.filed_at" class="text-xs sm:text-sm text-gray-600">
              Filed: {{ formatDate(vat.filed_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- VAT Calculation Summary -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-gray-600 text-sm font-medium">Output VAT</p>
          <p class="text-2xl font-bold text-blue-600 mt-2">₦{{ formatCurrency(vat.vat_on_sales) }}</p>
          <p class="text-xs text-gray-500 mt-2">5% of sales</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-gray-600 text-sm font-medium">Input VAT</p>
          <p class="text-2xl font-bold text-green-600 mt-2">₦{{ formatCurrency(vat.input_vat) }}</p>
          <p class="text-xs text-gray-500 mt-2">5% of eligible purchases</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-gray-600 text-sm font-medium">VAT Due</p>
          <p class="text-2xl font-bold text-orange-600 mt-2">₦{{ formatCurrency(vat.vat_due) }}</p>
          <p class="text-xs text-gray-500 mt-2">After input credit</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6" :class="getSettlementCardClass(vat)">
          <p class="text-gray-600 text-sm font-medium">Settlement</p>
          <p class="text-2xl font-bold mt-2" :class="getSettlementColor(vat)">
            ₦{{ formatCurrency(vat.settlement_amount) }}
          </p>
          <p class="text-xs mt-2" :class="getSettlementTextClass(vat)">
            {{ formatSettlementType(vat.settlement_type) }}
          </p>
        </div>
      </div>

      <!-- Sales Section -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">Sales Information</h2>
        </div>
        <div class="px-6 py-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Total Sales Turnover</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.sales_turnover) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Exempt Sales (No VAT)</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.exempt_sales) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Zero-Rated Sales</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.zero_rated_sales) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Export Sales</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.export_sales) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Purchases Section -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">Purchases Information</h2>
        </div>
        <div class="px-6 py-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Total Purchases</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.purchases_turnover) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Capital Goods Purchases</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.capital_goods_purchases) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Services Purchases</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.services_purchases) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Input VAT Adjustment</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.input_vat_adjustment) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Adjustments & Credits Section -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">Adjustments & Credits</h2>
        </div>
        <div class="px-6 py-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Bad Debt Relief</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.bad_debt_relief) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Credit Notes Issued</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.credit_notes_issued) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Credit Notes Received</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.credit_notes_received) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Prior Month Credit</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.prior_month_credit) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Advance Payment</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.advance_payment) }}</p>
            </div>
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm">Withholding VAT</p>
              <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(vat.withholding_vat) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviewer Information -->
      <div v-if="vat.reviewer" class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Reviewer Information</h2>
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <i class="fas fa-user text-blue-600"></i>
          </div>
          <div>
            <p class="font-semibold text-gray-900">{{ vat.reviewer.name }}</p>
            <p class="text-gray-600 text-sm">{{ vat.reviewer.email }}</p>
            <p v-if="vat.reviewed_at" class="text-gray-600 text-xs mt-1">
              Reviewed on {{ formatDate(vat.reviewed_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- FIRS Reference -->
      <div v-if="vat.firs_reference" class="bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex items-center gap-3">
          <i class="fas fa-check-circle text-green-600 text-xl"></i>
          <div>
            <p class="font-semibold text-green-900">FIRS Reference</p>
            <p class="text-green-800 font-mono">{{ vat.firs_reference }}</p>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="vat.notes" class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Notes</h2>
        <p class="text-gray-600 whitespace-pre-wrap">{{ vat.notes }}</p>
      </div>

      <!-- Payment History -->
      <div v-if="vat.government_payments && vat.government_payments.length > 0" class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">Payment History</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">RRR</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="payment in vat.government_payments" :key="payment.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(payment.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                  ₦{{ formatCurrency(payment.amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                  {{ payment.rrr }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getPaymentStatusClass(payment.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                    {{ formatPaymentStatus(payment.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- No Payments Message -->
      <div v-else class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
        <i class="fas fa-info-circle text-gray-400 text-2xl mb-3 block"></i>
        <p class="text-gray-600">No payments recorded for this return</p>
      </div>
    </div>
  </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

defineProps({
  vat: Object,
});

const formatCurrency = (value) => {
  if (!value) return '0.00';
  return parseFloat(value).toLocaleString('en-NG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-NG', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatFormType = (type) => {
  const types = {
    'Form 002': 'VAT Return (Form 002)',
    'Form 001': 'Sales Register (Form 001)',
  };
  return types[type] || type;
};

const formatStatus = (status) => {
  const statuses = {
    draft: 'Draft',
    submitted: 'Submitted',
    accepted: 'Accepted',
    paid: 'Paid',
    rejected: 'Rejected',
    refund_pending: 'Refund Pending',
    overdue: 'Overdue',
  };
  return statuses[status] || status;
};

const formatPaymentStatus = (status) => {
  const statuses = {
    pending: 'Pending',
    completed: 'Completed',
    failed: 'Failed',
    cancelled: 'Cancelled',
  };
  return statuses[status] || status;
};

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-blue-100 text-blue-800',
    accepted: 'bg-purple-100 text-purple-800',
    paid: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    refund_pending: 'bg-indigo-100 text-indigo-800',
    overdue: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getSettlementCardClass = (vat) => {
  if (vat.settlement_type === 'refund') return 'bg-green-50';
  if (vat.settlement_type === 'payment') return 'bg-orange-50';
  return 'bg-gray-50';
};

const getSettlementColor = (vat) => {
  if (vat.settlement_type === 'refund') return 'text-green-600';
  if (vat.settlement_type === 'payment') return 'text-orange-600';
  return 'text-gray-600';
};

const getSettlementTextClass = (vat) => {
  if (vat.settlement_type === 'refund') return 'text-green-600';
  if (vat.settlement_type === 'payment') return 'text-orange-600';
  return 'text-gray-600';
};

const formatSettlementType = (type) => {
  const types = {
    payment: 'Payment due to FIRS',
    refund: 'Refund pending from FIRS',
    zero: 'No settlement required',
  };
  return types[type] || type;
};

const generatePaymentRRR = () => {
  // This will be handled by the route in the button
  return route('business.vat.generate-rrr', vat.id);
};
</script>
