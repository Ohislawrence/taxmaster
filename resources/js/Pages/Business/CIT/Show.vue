<template>
  <BusinessLayout>
    <Head title="CIT Return Details" />

    <div class="space-y-6 max-w-4xl mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">CIT Return - {{ citReturn.period }}</h1>
          <p class="mt-2 text-gray-600">View and manage your corporate income tax return</p>
        </div>
        <div class="flex items-center space-x-3">
          <Link
            v-if="citReturn.status === 'draft'"
            :href="route('business.cit.edit', citReturn.id)"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            <i class="fas fa-edit mr-2"></i>
            Edit
          </Link>
          <Link
            v-if="citReturn.status === 'submitted'"
            href="#"
            @click.prevent="generateRRR"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700"
          >
            <i class="fas fa-receipt mr-2"></i>
            Generate Payment RRR
          </Link>
        </div>
      </div>

      <!-- Status Card -->
      <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Current Status</p>
            <div class="mt-2">
              <span :class="getStatusClass(citReturn.status)" class="px-4 py-2 rounded-full text-sm font-medium">
                {{ formatStatus(citReturn.status) }}
              </span>
            </div>
          </div>
          <div class="text-right">
            <p class="text-gray-600 text-sm font-medium">Due Date</p>
            <p class="text-lg font-bold text-gray-900 mt-1">{{ formatDate(citReturn.due_date) }}</p>
          </div>
        </div>
      </div>

      <!-- Financial Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white shadow rounded-lg p-6">
          <p class="text-gray-600 text-sm font-medium">Gross Profit</p>
          <p class="text-2xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(citReturn.gross_profit) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
          <p class="text-gray-600 text-sm font-medium">Taxable Income</p>
          <p class="text-2xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(citReturn.taxable_income) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
          <p class="text-gray-600 text-sm font-medium">CIT Due</p>
          <p class="text-2xl font-bold text-blue-600 mt-2">₦{{ formatCurrency(citReturn.tax_due) }}</p>
        </div>
      </div>

      <!-- Tax Details -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-bold text-gray-900">Tax Calculation Details</h2>
        </div>
        <div class="px-6 py-6">
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Gross Profit</span>
              <span class="text-gray-900">₦{{ formatCurrency(citReturn.gross_profit) }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Adjustments (Other Deductions)</span>
              <span class="text-gray-900">₦{{ formatCurrency(citReturn.other_deductions) }}</span>
            </div>
            <div class="border-t pt-4 flex justify-between items-center">
              <span class="font-medium text-gray-900">Taxable Income</span>
              <span class="font-bold text-gray-900">₦{{ formatCurrency(citReturn.taxable_income) }}</span>
            </div>
            <div class="mt-6 space-y-3 bg-gray-50 p-4 rounded">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">30% CIT Rate on ₦{{ formatCurrency(citReturn.taxable_income) }}</span>
                <span class="text-gray-900">₦{{ formatCurrency(citReturn.cit_payable) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Minimum Tax (0.5%)</span>
                <span class="text-gray-900">₦{{ formatCurrency(citReturn.minimum_tax_amount) }}</span>
              </div>
              <div class="border-t pt-3 flex justify-between items-center">
                <span class="font-medium text-gray-900">Final CIT Due</span>
                <span class="text-lg font-bold text-blue-600">₦{{ formatCurrency(citReturn.tax_due) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviewer Info -->
      <div class="bg-white shadow rounded-lg px-6 py-4" v-if="citReturn.reviewer">
        <h3 class="text-sm font-medium text-gray-900">Accountant Review</h3>
        <div class="mt-2">
          <p class="text-sm text-gray-600">{{ citReturn.reviewer.name }}</p>
          <p class="text-sm text-gray-500">{{ citReturn.reviewer.email }}</p>
        </div>
      </div>

      <!-- Notes -->
      <div class="bg-white shadow rounded-lg px-6 py-4" v-if="citReturn.notes">
        <h3 class="text-sm font-medium text-gray-900">Notes</h3>
        <p class="mt-2 text-sm text-gray-600">{{ citReturn.notes }}</p>
      </div>

      <!-- Payment History -->
      <div class="bg-white shadow rounded-lg" v-if="citReturn.governmentPayments && citReturn.governmentPayments.length > 0">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-bold text-gray-900">Payment History</h2>
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
                <td class="px-6 py-4 text-sm text-gray-600">{{ payment.rrr }}</td>
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
import { Head, Link } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

defineProps({
  citReturn: Object,
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-NG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

const formatDate = (date) => {
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
  // To be implemented
  alert('RRR generation to be implemented');
};
</script>
