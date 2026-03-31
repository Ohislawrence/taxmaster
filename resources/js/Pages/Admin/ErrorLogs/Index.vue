<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    errors: Object,
    stats: Object,
    filters: Object,
});

const selectedErrors = ref([]);
const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const severityFilter = ref(props.filters?.severity || '');

const selectAll = () => {
    if (selectedErrors.value.length === props.errors.data.length) {
        selectedErrors.value = [];
    } else {
        selectedErrors.value = props.errors.data.map(e => e.id);
    }
};

const applyFilters = () => {
    router.get(route('admin.error-logs.index'), {
        search: searchQuery.value,
        status: statusFilter.value,
        severity: severityFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const bulkResolve = () => {
    if (!selectedErrors.value.length) return;

    if (confirm(`Mark ${selectedErrors.value.length} error(s) as resolved?`)) {
        router.post(route('admin.error-logs.bulk-resolve'), {
            ids: selectedErrors.value,
        }, {
            onSuccess: () => {
                selectedErrors.value = [];
            }
        });
    }
};

const bulkDelete = () => {
    if (!selectedErrors.value.length) return;

    if (confirm(`Delete ${selectedErrors.value.length} error(s)? This cannot be undone.`)) {
        router.post(route('admin.error-logs.bulk-delete'), {
            ids: selectedErrors.value,
        }, {
            onSuccess: () => {
                selectedErrors.value = [];
            }
        });
    }
};

const clearResolved = () => {
    if (confirm('Delete all resolved errors? This cannot be undone.')) {
        router.post(route('admin.error-logs.clear-resolved'));
    }
};

const getSeverityClass = (severity) => {
    const classes = {
        critical: 'bg-red-100 text-red-800',
        error: 'bg-orange-100 text-orange-800',
        warning: 'bg-yellow-100 text-yellow-800',
    };
    return classes[severity] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('en-NG');
};

const truncate = (str, length = 80) => {
    return str.length > length ? str.substring(0, length) + '...' : str;
};
</script>

<template>
  <Head title="Error Logs" />

  <div class="relative min-h-screen bg-gray-50">
    <!-- Background grid -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

    <div class="relative py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
          <Link :href="route('admin.dashboard')" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium mb-4 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
          </Link>
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Error Logs</h1>
              <p class="mt-2 text-gray-600">Monitor and track application errors</p>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Total Errors</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Unresolved</p>
                <p class="text-2xl font-bold text-orange-600">{{ stats.unresolved }}</p>
              </div>
              <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Critical</p>
                <p class="text-2xl font-bold text-red-600">{{ stats.critical }}</p>
              </div>
              <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Today</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.today }}</p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters & Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <input
              v-model="searchQuery"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Search errors..."
              class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <select v-model="statusFilter" @change="applyFilters" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">All Status</option>
              <option value="unresolved">Unresolved</option>
              <option value="resolved">Resolved</option>
            </select>

            <select v-model="severityFilter" @change="applyFilters" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">All Severity</option>
              <option value="critical">Critical</option>
              <option value="error">Error</option>
              <option value="warning">Warning</option>
            </select>

            <button @click="applyFilters" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-all">
              Apply Filters
            </button>
          </div>

          <div v-if="selectedErrors.length > 0" class="flex gap-3 pt-4 border-t border-gray-200">
            <button @click="bulkResolve" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all">
              Mark as Resolved ({{ selectedErrors.length }})
            </button>
            <button @click="bulkDelete" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all">
              Delete ({{ selectedErrors.length }})
            </button>
            <button @click="clearResolved" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all ml-auto">
              Clear All Resolved
            </button>
          </div>
        </div>

        <!-- Error Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div v-if="errors.data.length > 0" class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="py-3 px-4">
                    <input type="checkbox" @change="selectAll" :checked="selectedErrors.length === errors.data.length" class="rounded border-gray-300">
                  </th>
                  <th class="text-left py-3 px-4 font-medium text-gray-600 text-sm">Severity</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-600 text-sm">Exception</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-600 text-sm">Message</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-600 text-sm">User</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-600 text-sm">Time</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-600 text-sm">Status</th>
                  <th class="text-left py-3 px-4 font-medium text-gray-600 text-sm">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="error in errors.data" :key="error.id" class="border-b border-gray-100 hover:bg-gray-50">
                  <td class="py-3 px-4">
                    <input type="checkbox" v-model="selectedErrors" :value="error.id" class="rounded border-gray-300">
                  </td>
                  <td class="py-3 px-4">
                    <span :class="getSeverityClass(error.severity)" class="px-3 py-1 rounded-full text-xs font-medium capitalize">
                      {{ error.severity }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-sm font-mono text-gray-700">
                    {{ error.exception_class.split('\\').pop() }}
                  </td>
                  <td class="py-3 px-4 text-sm text-gray-700">
                    {{ truncate(error.message) }}
                  </td>
                  <td class="py-3 px-4 text-sm text-gray-700">
                    {{ error.user?.name || 'Guest' }}
                  </td>
                  <td class="py-3 px-4 text-sm text-gray-500">
                    {{ formatDate(error.created_at) }}
                  </td>
                  <td class="py-3 px-4">
                    <span v-if="error.resolved_at" class="text-green-600 text-sm font-medium">✓ Resolved</span>
                    <span v-else class="text-orange-600 text-sm font-medium">⚠ Open</span>
                  </td>
                  <td class="py-3 px-4">
                    <Link :href="route('admin.error-logs.show', error.id)" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                      View Details
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="p-12 text-center text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-lg font-medium mb-2">No errors found</p>
            <p class="text-sm">Your application is running smoothly!</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="errors.last_page > 1" class="mt-6 flex justify-center gap-2">
          <Link
            v-for="page in errors.last_page"
            :key="page"
            :href="route('admin.error-logs.index', { page, search: searchQuery, status: statusFilter, severity: severityFilter })"
            :class="[
              'px-4 py-2 border rounded-lg text-sm font-medium transition-all',
              page === errors.current_page
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
