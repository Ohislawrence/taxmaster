<template>
  <BusinessLayout>
    <Head title="CIT Returns" />

    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">CIT Returns</h1>
          <p class="mt-2 text-gray-600">Manage your Corporate Income Tax returns</p>
        </div>
        <Link
          v-if="can.fileCIT.value"
          :href="route('business.cit.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <i class="fas fa-plus mr-2"></i>
          New CIT Return
        </Link>
        <button
          v-else
          @click="$inertia.visit(route('business.plans.index'))"
          class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition cursor-not-allowed"
          disabled
          :title="getUpgradeMessage('file_cit')"
        >
          <i class="fas fa-lock mr-2"></i>
          New CIT Return
        </button>
      </div>
      
      <!-- Upgrade Prompt (if feature locked) -->
      <UpgradePrompt
        v-if="!can.fileCIT.value"
        :show="true"
        feature="file_cit"
        :required-plan="getRequiredPlan('file_cit')"
        title="Upgrade to File CIT Returns"
        :message="getUpgradeMessage('file_cit')"
        variant="info"
      />

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total Returns</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total_returns }}</p>
            </div>
            <div class="bg-blue-100 rounded-lg p-3">
              <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total CIT Paid</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(stats.total_cit_paid) }}</p>
            </div>
            <div class="bg-green-100 rounded-lg p-3">
              <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Pending Returns</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.pending_returns }}</p>
            </div>
            <div class="bg-yellow-100 rounded-lg p-3">
              <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">This Year's CIT</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(stats.this_year_tax) }}</p>
            </div>
            <div class="bg-purple-100 rounded-lg p-3">
              <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Latest Return Alert -->
      <div v-if="latestReturn && latestReturn.isOverdue" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-start">
          <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-1"></i>
          <div>
            <h3 class="font-semibold text-red-900">Overdue CIT Return</h3>
            <p class="text-red-700 text-sm mt-1">
              Your {{ latestReturn.period }} CIT return was due on {{ formatDate(latestReturn.due_date) }}
            </p>
          </div>
        </div>
      </div>

      <!-- CIT Returns Table -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Recent Returns</h2>
        </div>

        <div v-if="returns.data.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Period</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Taxable Income</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tax Due</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Due Date</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="citReturn in returns.data" :key="citReturn.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ citReturn.period }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium capitalize">
                    {{ citReturn.return_type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  ₦{{ formatCurrency(citReturn.taxable_income) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                  ₦{{ formatCurrency(citReturn.tax_due) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <StatusBadge :status="citReturn.status" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ citReturn.due_date ? formatDate(citReturn.due_date) : 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <Link
                    :href="route('business.cit.show', citReturn.id)"
                    class="text-blue-600 hover:text-blue-900 font-medium"
                  >
                    View
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="px-6 py-12 text-center">
          <i class="fas fa-inbox text-gray-400 text-4xl mb-4 block"></i>
          <p class="text-gray-600 mb-4">No CIT returns yet</p>
          <Link
            :href="route('business.cit.create')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <i class="fas fa-plus mr-2"></i>
            Create First Return
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="returns.links" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">
              Showing <span class="font-semibold">{{ returns.from }}</span> to
              <span class="font-semibold">{{ returns.to }}</span> of
              <span class="font-semibold">{{ returns.total }}</span> returns
            </p>
            <Pagination :links="returns.links" />
          </div>
        </div>
      </div>
    </div>
  </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Pagination from '@/Components/Pagination.vue';
import UpgradePrompt from '@/Components/UpgradePrompt.vue';
import { useSubscription } from '@/composables/useSubscription';

defineProps({
  returns: Object,
  stats: Object,
  latestReturn: Object,
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
</script>
