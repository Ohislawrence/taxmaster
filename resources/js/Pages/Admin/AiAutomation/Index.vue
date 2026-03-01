<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">AI Automation Dashboard</h2>
          <p class="mt-1 text-sm text-gray-600">Manage AI-generated suggestions for transactions, compliance, and payments</p>
        </div>
        <div class="flex gap-2">
          <button @click="exportSuggestions" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export
          </button>
        </div>
      </div>
    </template>

    <!-- Stats Cards -->
    <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-6">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Total Suggestions</dt>
          <dd class="mt-1 text-3xl font-extrabold text-gray-900">{{ stats.total_suggestions }}</dd>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Pending Review</dt>
          <dd class="mt-1 text-3xl font-extrabold text-yellow-600">{{ stats.pending }}</dd>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Applied</dt>
          <dd class="mt-1 text-3xl font-extrabold text-green-600">{{ stats.applied }}</dd>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Categorizations</dt>
          <dd class="mt-1 text-3xl font-extrabold text-blue-600">{{ stats.categorizations }}</dd>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Compliance</dt>
          <dd class="mt-1 text-3xl font-extrabold text-purple-600">{{ stats.compliance_reminders }}</dd>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Recovery</dt>
          <dd class="mt-1 text-3xl font-extrabold text-red-600">{{ stats.payment_recoveries }}</dd>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white overflow-hidden shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <label for="type-filter" class="block text-sm font-medium text-gray-700">Filter by Type</label>
            <select
              id="type-filter"
              v-model="filters.type"
              @change="applyFilters"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Types</option>
              <option value="categorization">Transaction Categorization</option>
              <option value="compliance_reminder">Compliance Reminders</option>
              <option value="payment_recovery">Payment Recovery</option>
            </select>
          </div>

          <div>
            <label for="status-filter" class="block text-sm font-medium text-gray-700">Filter by Status</label>
            <select
              id="status-filter"
              v-model="filters.status"
              @change="applyFilters"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="applied">Applied</option>
              <option value="dismissed">Dismissed</option>
              <option value="reviewed">Reviewed</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Suggestions Table -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Confidence</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
            <th class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="suggestion in suggestions.data" :key="suggestion.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getTypeBadgeClass(suggestion.type)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ formatType(suggestion.type) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusBadgeClass(suggestion.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ formatStatus(suggestion.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <div class="w-full bg-gray-200 rounded-full h-2 max-w-xs">
                <div :style="{ width: (suggestion.confidence * 100) + '%' }" :class="getConfidenceColor(suggestion.confidence)" class="h-2 rounded-full"></div>
              </div>
              {{ Math.round(suggestion.confidence * 100) }}%
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(suggestion.created_at) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <Link :href="route('admin.ai-automation.show', suggestion.id)" class="text-blue-600 hover:text-blue-900">
                View
              </Link>
              <button
                v-if="suggestion.status === 'pending'"
                @click="applySuggestion(suggestion.id)"
                class="ml-4 text-green-600 hover:text-green-900"
              >
                Apply
              </button>
              <button
                v-if="suggestion.status === 'pending'"
                @click="dismissSuggestion(suggestion.id)"
                class="ml-4 text-red-600 hover:text-red-900"
              >
                Dismiss
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="suggestions.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <Link
            v-if="suggestions.current_page > 1"
            :href="route('admin.ai-automation.index', { type: filters.type, status: filters.status, page: suggestions.current_page - 1 })"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >Previous</Link>
          <Link
            v-if="suggestions.current_page < suggestions.last_page"
            :href="route('admin.ai-automation.index', { type: filters.type, status: filters.status, page: suggestions.current_page + 1 })"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >Next</Link>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">Showing <span class="font-medium">{{ (suggestions.current_page - 1) * 20 + 1 }}</span> to <span class="font-medium">{{ Math.min(suggestions.current_page * 20, suggestions.total) }}</span> of <span class="font-medium">{{ suggestions.total }}</span> results</p>
          </div>
        </div>
      </div>

      <div v-if="suggestions.data.length === 0" class="px-6 py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No suggestions</h3>
        <p class="mt-1 text-sm text-gray-500">No AI suggestions match your current filters.</p>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

export default {
  components: {
    AdminLayout,
    Link,
  },
  props: {
    suggestions: Object,
    stats: Object,
    filters: Object,
  },
  data() {
    return {
      filters: {
        type: this.filters?.type || '',
        status: this.filters?.status || '',
      },
    };
  },
  methods: {
    formatType(type) {
      const types = {
        categorization: 'Transaction Categorization',
        compliance_reminder: 'Compliance Reminder',
        payment_recovery: 'Payment Recovery',
      };
      return types[type] || type;
    },
    formatStatus(status) {
      const statuses = {
        pending: 'Pending Review',
        applied: 'Applied',
        dismissed: 'Dismissed',
        reviewed: 'Reviewed',
      };
      return statuses[status] || status;
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      });
    },
    getTypeBadgeClass(type) {
      const classes = {
        categorization: 'bg-blue-100 text-blue-800',
        compliance_reminder: 'bg-purple-100 text-purple-800',
        payment_recovery: 'bg-red-100 text-red-800',
      };
      return classes[type] || 'bg-gray-100 text-gray-800';
    },
    getStatusBadgeClass(status) {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        applied: 'bg-green-100 text-green-800',
        dismissed: 'bg-red-100 text-red-800',
        reviewed: 'bg-blue-100 text-blue-800',
      };
      return classes[status] || 'bg-gray-100 text-gray-800';
    },
    getConfidenceColor(confidence) {
      if (confidence >= 0.9) return 'bg-green-500';
      if (confidence >= 0.7) return 'bg-yellow-500';
      return 'bg-red-500';
    },
    applySuggestion(id) {
      if (confirm('Apply this suggestion?')) {
        this.$inertia.post(route('admin.ai-automation.apply', id), {}, {
          onSuccess: () => {
            this.$page.props.flash = { success: 'Suggestion applied successfully!' };
            window.location.reload();
          },
        });
      }
    },
    dismissSuggestion(id) {
      if (confirm('Dismiss this suggestion?')) {
        this.$inertia.post(route('admin.ai-automation.dismiss', id), {}, {
          onSuccess: () => {
            this.$page.props.flash = { success: 'Suggestion dismissed!' };
            window.location.reload();
          },
        });
      }
    },
    applyFilters() {
      this.$inertia.get(route('admin.ai-automation.index', {
        type: this.filters.type,
        status: this.filters.status,
      }));
    },
    exportSuggestions() {
      alert('Export feature coming soon!');
    },
  },
};
</script>
