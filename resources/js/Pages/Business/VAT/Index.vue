<template>
  <BusinessLayout>
    <Head title="VAT Returns" />

    <div class="space-y-4 sm:space-y-6 px-3 sm:px-0">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">VAT Returns</h1>
          <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Manage your monthly Value Added Tax returns (Form 002)</p>
        </div>
        <Link
          v-if="can.fileVAT.value"
          :href="route('business.vat.create')"
          class="w-full sm:w-auto text-center inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
        >
          <i class="fas fa-plus mr-2"></i>
          New VAT Return
        </Link>
        <button
          v-else
          @click="$inertia.visit(route('business.plans.index'))"
          class="w-full sm:w-auto text-center inline-flex items-center justify-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition cursor-not-allowed text-sm font-medium"
          disabled
          :title="getUpgradeMessage('file_vat')"
        >
          <i class="fas fa-lock mr-2"></i>
          New VAT Return
        </button>
      </div>
      
      <!-- Upgrade Prompt (if feature locked) -->
      <UpgradePrompt
        v-if="!can.fileVAT.value"
        :show="true"
        feature="file_vat"
        :required-plan="getRequiredPlan('file_vat')"
        title="Upgrade to File VAT Returns"
        :message="getUpgradeMessage('file_vat')"
        variant="info"
      />

      <!-- Info Icon for VAT Context -->
      <div class="bg-blue-50 border-l-4 border-blue-400 p-3 sm:p-4 rounded">
        <div class="flex gap-3">
          <div class="flex-shrink-0">
            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 flex-shrink-0">i</span>
          </div>
          <div>
            <p class="text-xs sm:text-sm text-blue-700">
              <strong>VAT in Nigeria:</strong> Value Added Tax is 5% on goods and services. File Form 002 monthly within 21 days of month end.
              Calculated as: Output VAT (on sales) - Input VAT (on purchases).
            </p>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white rounded-lg shadow p-3 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
              <p class="text-gray-600 text-xs sm:text-sm font-medium">Total Returns</p>
              <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 sm:mt-2">{{ stats.total_returns }}</p>
            </div>
            <div class="bg-blue-100 rounded-lg p-2 sm:p-3 w-fit">
              <i class="fas fa-file-invoice text-blue-600 text-sm sm:text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
              <p class="text-gray-600 text-xs sm:text-sm font-medium">Total VAT Paid</p>
              <p class="text-lg sm:text-3xl font-bold text-gray-900 mt-1 sm:mt-2">₦{{ formatCurrency(stats.total_vat_paid) }}</p>
            </div>
            <div class="bg-green-100 rounded-lg p-2 sm:p-3 w-fit">
              <i class="fas fa-check-circle text-green-600 text-sm sm:text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
              <p class="text-gray-600 text-xs sm:text-sm font-medium">Pending Returns</p>
              <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 sm:mt-2">{{ stats.pending_returns }}</p>
            </div>
            <div class="bg-yellow-100 rounded-lg p-2 sm:p-3 w-fit">
              <i class="fas fa-hourglass-half text-yellow-600 text-sm sm:text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
              <p class="text-gray-600 text-xs sm:text-sm font-medium">Overdue</p>
              <p class="text-2xl sm:text-3xl font-bold text-red-600 mt-1 sm:mt-2">{{ stats.overdue_returns }}</p>
            </div>
            <div class="bg-red-100 rounded-lg p-2 sm:p-3 w-fit">
              <i class="fas fa-exclamation-circle text-red-600 text-sm sm:text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
              <p class="text-gray-600 text-xs sm:text-sm font-medium">Pending Refunds</p>
              <p class="text-2xl sm:text-3xl font-bold text-purple-600 mt-1 sm:mt-2">{{ stats.pending_refunds }}</p>
            </div>
            <div class="bg-purple-100 rounded-lg p-2 sm:p-3 w-fit">
              <i class="fas fa-arrow-left text-purple-600 text-sm sm:text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- VAT Returns Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-3 sm:px-6 py-4 border-b border-gray-200">
          <h2 class="text-base sm:text-lg font-semibold text-gray-900">Recent Returns</h2>
        </div>

        <!-- Desktop Table View -->
        <div v-if="returns.data.length > 0" class="hidden md:block overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Period</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Form Type</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Sales Turnover</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">VAT Due</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Due Date</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="vat in returns.data" :key="vat.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ vat.period }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">
                    {{ vat.form_type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  ₦{{ formatCurrency(vat.sales_turnover) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold" :class="getVatDueClass(vat)">
                  ₦{{ formatCurrency(vat.vat_due) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(vat.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                    {{ formatStatus(vat.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ vat.due_date ? formatDate(vat.due_date) : 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <Link
                    :href="route('business.vat.show', vat.id)"
                    class="text-blue-600 hover:text-blue-900 font-medium"
                  >
                    View
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card View -->
        <div v-if="returns.data.length > 0" class="md:hidden divide-y divide-gray-200">
          <div
            v-for="vat in returns.data"
            :key="vat.id"
            class="p-4 hover:bg-gray-50 space-y-3"
          >
            <div class="flex items-start justify-between gap-2">
              <div>
                <p class="font-semibold text-gray-900">{{ vat.period }}</p>
                <p class="text-xs text-gray-600">{{ formatDate(vat.due_date) }}</p>
              </div>
              <span :class="getStatusClass(vat.status)" class="px-2 py-1 rounded-full text-xs font-medium flex-shrink-0 whitespace-nowrap">
                {{ formatStatus(vat.status) }}
              </span>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-gray-600 text-xs">Sales Turnover</p>
                <p class="font-semibold text-gray-900">₦{{ formatCurrency(vat.sales_turnover) }}</p>
              </div>
              <div>
                <p class="text-gray-600 text-xs">VAT Due</p>
                <p class="font-semibold" :class="getVatDueClass(vat)">₦{{ formatCurrency(vat.vat_due) }}</p>
              </div>
            </div>
            <div class="pt-2 border-t border-gray-100">
              <Link
                :href="route('business.vat.show', vat.id)"
                class="text-blue-600 hover:text-blue-900 font-medium text-sm"
              >
                View Details →
              </Link>
            </div>
          </div>
        </div>

        <div v-else class="px-4 sm:px-6 py-8 sm:py-12 text-center">
          <i class="fas fa-inbox text-gray-400 text-3xl sm:text-4xl mb-3 sm:mb-4 block"></i>
          <p class="text-gray-600 mb-3 sm:mb-4 text-sm sm:text-base">No VAT returns yet</p>
          <Link
            :href="route('business.vat.create')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
          >
            <i class="fas fa-plus mr-2"></i>
            Create First Return
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="returns.links && returns.data.length > 0" class="px-3 sm:px-6 py-3 sm:py-4 border-t border-gray-200">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-xs sm:text-sm text-gray-600">
              Showing <span class="font-semibold">{{ returns.from }}</span> to
              <span class="font-semibold">{{ returns.to }}</span> of
              <span class="font-semibold">{{ returns.total }}</span> returns
            </p>
          </div>
        </div>
      </div>
    </div>
  </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import UpgradePrompt from '@/Components/UpgradePrompt.vue';
import { useSubscription } from '@/composables/useSubscription';

defineProps({
  returns: Object,
  stats: Object,
});

const { can, getUpgradeMessage, getRequiredPlan } = useSubscription();

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

const getVatDueClass = (vat) => {
  if (vat.settlement_type === 'refund') return 'text-green-600';
  if (vat.settlement_type === 'payment') return 'text-orange-600';
  return 'text-gray-900';
};
</script>
