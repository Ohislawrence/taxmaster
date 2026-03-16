<template>
  <AccountantLayout>
    <Head title="Accountant Dashboard" />

    <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8 space-y-6 lg:space-y-8">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Client Companies</h1>
          <p class="text-sm text-gray-500 mt-1">Overview of companies you manage and their compliance statuses</p>
        </div>

        <!-- Export Button -->
        <button
          @click="downloadCsv"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm text-sm font-medium group"
        >
          <svg class="w-4 h-4 text-gray-500 group-hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4 4-4-4M12 12V4" />
          </svg>
          Export CSV
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="bg-white rounded-xl border border-gray-200/50 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
          <label class="text-sm font-medium text-gray-700">Filter:</label>
          <select
            v-model="listFilter"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
          >
            <option value="all">All Companies</option>
            <option value="overdue">Overdue Deadlines</option>
            <option value="next7">Due in Next 7 Days</option>
          </select>
        </div>

        <!-- Summary Stats -->
        <div class="flex items-center gap-4 text-sm">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-green-500"></span>
            <span class="text-gray-600">{{ summaries.length }} total</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            <span class="text-gray-600">{{ summaries.filter(b => b.next_deadline && new Date(b.next_deadline) < new Date()).length }} overdue</span>
          </div>
        </div>
      </div>

      <!-- Companies Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-6">
        <div
          v-for="b in summaries"
          :key="b.id"
          class="bg-white rounded-xl border border-gray-200/50 hover:border-gray-200 hover:shadow-lg transition-all duration-200 overflow-hidden group"
        >
          <!-- Company Header -->
          <div class="p-5 border-b border-gray-100">
            <div class="flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-gray-900 truncate">{{ b.name }}</h3>
                <p class="text-sm text-gray-500 truncate mt-0.5">{{ b.description || 'No description provided' }}</p>
              </div>
              <Link
                :href="route('accountant.businesses.show', b.id)"
                class="ml-3 flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 bg-gray-50 hover:bg-blue-50 text-gray-600 hover:text-blue-600 rounded-lg text-sm font-medium transition-colors"
              >
                Open
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                </svg>
              </Link>
            </div>
          </div>

          <!-- Tax Status Grid -->
          <div class="p-5 bg-gray-50/50">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Tax Compliance Status</p>
            <div class="grid grid-cols-2 gap-3">
              <Link
                :href="route('accountant.businesses.show', { business: b.id, view: 'vat' })"
                class="block bg-white rounded-lg border border-gray-100 p-3 hover:border-blue-200 hover:shadow-sm transition-all"
              >
                <div class="text-xs text-gray-500 mb-1">VAT</div>
                <div :class="statusClass(b.vat_status)" class="text-sm font-semibold flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(b.vat_status)"></span>
                  {{ formatStatus(b.vat_status) }}
                </div>
              </Link>

              <Link
                :href="route('accountant.businesses.show', { business: b.id, view: 'paye' })"
                class="block bg-white rounded-lg border border-gray-100 p-3 hover:border-blue-200 hover:shadow-sm transition-all"
              >
                <div class="text-xs text-gray-500 mb-1">PAYE</div>
                <div :class="statusClass(b.paye_status)" class="text-sm font-semibold flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(b.paye_status)"></span>
                  {{ formatStatus(b.paye_status) }}
                </div>
              </Link>

              <Link
                :href="route('accountant.businesses.show', { business: b.id, view: 'wht' })"
                class="block bg-white rounded-lg border border-gray-100 p-3 hover:border-blue-200 hover:shadow-sm transition-all"
              >
                <div class="text-xs text-gray-500 mb-1">WHT</div>
                <div :class="statusClass(b.wht_status)" class="text-sm font-semibold flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(b.wht_status)"></span>
                  {{ formatStatus(b.wht_status) }}
                </div>
              </Link>

              <Link
                :href="route('accountant.businesses.show', { business: b.id, view: 'cit' })"
                class="block bg-white rounded-lg border border-gray-100 p-3 hover:border-blue-200 hover:shadow-sm transition-all"
              >
                <div class="text-xs text-gray-500 mb-1">CIT</div>
                <div :class="statusClass(b.cit_status)" class="text-sm font-semibold flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(b.cit_status)"></span>
                  {{ formatStatus(b.cit_status) }}
                </div>
              </Link>
            </div>
          </div>

          <!-- Deadlines Section -->
          <div class="p-5">
            <div class="flex items-center justify-between mb-3">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Upcoming Deadlines</p>
              <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-semibold bg-blue-50 text-blue-700 rounded-full">
                {{ b.upcoming_deadlines_count }}
              </span>
            </div>

            <div v-if="b.next_deadline" class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">{{ formatDate(b.next_deadline) }}</p>
                <p :class="deadlineClass(b.next_deadline)" class="text-xs font-medium mt-0.5">
                  {{ daysUntil(b.next_deadline) }}
                </p>
              </div>
              <div class="w-16 h-1 bg-gray-100 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all"
                  :class="deadlineProgressClass(b.next_deadline)"
                  :style="{ width: deadlineProgress(b.next_deadline) + '%' }"
                ></div>
              </div>
            </div>

            <div v-else class="flex items-center gap-2 text-sm text-gray-500">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
              </svg>
              <span>No deadlines in next 30 days</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="summaries.length === 0" class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No companies found</h3>
        <p class="text-sm text-gray-500">No companies match your current filter criteria.</p>
      </div>
    </div>
  </AccountantLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const allSummaries = computed(() => page.props.managedSummaries || []);
const listFilter = ref('all');

const summaries = computed(() => {
  if (listFilter.value === 'all') return allSummaries.value;
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (listFilter.value === 'overdue') {
    return allSummaries.value.filter(b => {
      if (!b.next_deadline) return false;
      const dueDate = new Date(b.next_deadline);
      dueDate.setHours(0, 0, 0, 0);
      return dueDate < today;
    });
  }

  if (listFilter.value === 'next7') {
    const until = new Date();
    until.setHours(0, 0, 0, 0);
    until.setDate(until.getDate() + 7);

    return allSummaries.value.filter(b => {
      if (!b.next_deadline) return false;
      const dueDate = new Date(b.next_deadline);
      dueDate.setHours(0, 0, 0, 0);
      return dueDate >= today && dueDate <= until;
    });
  }

  return allSummaries.value;
});

function formatDate(date) {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-NG', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
}

function daysUntil(date) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const due = new Date(date);
  due.setHours(0, 0, 0, 0);
  const diff = Math.ceil((due - today) / (1000 * 60 * 60 * 24));

  if (diff < 0) return 'Overdue';
  if (diff === 0) return 'Due Today';
  if (diff === 1) return 'Due Tomorrow';
  return `${diff} days remaining`;
}

function deadlineProgress(date) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const due = new Date(date);
  due.setHours(0, 0, 0, 0);

  // If overdue, show 100%
  if (due < today) return 100;

  // Calculate progress based on 30-day window
  const daysUntil = Math.ceil((due - today) / (1000 * 60 * 60 * 24));
  const totalDays = 30;
  const progress = ((totalDays - daysUntil) / totalDays) * 100;
  return Math.min(100, Math.max(0, progress));
}

function statusClass(status) {
  if (!status || status === 'none') return 'text-gray-600';
  if (['paid','filed','submitted'].includes(status)) return 'text-green-600';
  if (['overdue'].includes(status)) return 'text-red-600';
  return 'text-yellow-600';
}

function statusDotClass(status) {
  if (!status || status === 'none') return 'bg-gray-400';
  if (['paid','filed','submitted'].includes(status)) return 'bg-green-500';
  if (['overdue'].includes(status)) return 'bg-red-500';
  return 'bg-yellow-500';
}

function formatStatus(status) {
  if (!status || status === 'none') return 'Not Filed';
  return status.charAt(0).toUpperCase() + status.slice(1);
}

function deadlineClass(date) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const due = new Date(date);
  due.setHours(0, 0, 0, 0);
  const diff = Math.ceil((due - today) / (1000 * 60 * 60 * 24));

  if (diff < 0) return 'text-red-600 font-semibold';
  if (diff <= 3) return 'text-orange-600 font-semibold';
  return 'text-gray-500';
}

function deadlineProgressClass(date) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const due = new Date(date);
  due.setHours(0, 0, 0, 0);

  if (due < today) return 'bg-red-500';
  if (due <= new Date(today.setDate(today.getDate() + 3))) return 'bg-orange-500';
  return 'bg-blue-500';
}

const downloadCsv = () => {
  const headers = ['Business', 'VAT Status', 'PAYE Status', 'WHT Status', 'CIT Status', 'Upcoming Deadlines', 'Next Deadline'];
  const rows = summaries.value.map(b => [
    b.name,
    formatStatus(b.vat_status),
    formatStatus(b.paye_status),
    formatStatus(b.wht_status),
    formatStatus(b.cit_status),
    b.upcoming_deadlines_count,
    b.next_deadline ? formatDate(b.next_deadline) : ''
  ]);

  const csv = [headers, ...rows]
    .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
    .join('\n');

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `client_companies_${new Date().toISOString().split('T')[0]}.csv`;
  a.click();
  URL.revokeObjectURL(url);
};
</script>

<style scoped>
/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>
