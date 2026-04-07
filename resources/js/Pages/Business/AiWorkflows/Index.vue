<template>
  <BusinessLayout>
    <Head title="AI Tax Workflows" />

    <div class="space-y-4 sm:space-y-6 px-3 sm:px-0">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">AI Tax Workflows</h1>
          <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Automated tax processing with AI-powered analysis and compliance checks</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="w-full sm:w-auto text-center inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
        >
          <i class="fas fa-robot mr-2"></i>
          Start AI Workflow
        </button>
      </div>

      <!-- Info Banner -->
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 p-3 sm:p-4 rounded-lg">
        <div class="flex gap-3">
          <div class="flex-shrink-0">
            <i class="fas fa-brain text-blue-600 text-xl"></i>
          </div>
          <div class="flex-1">
            <p class="text-xs sm:text-sm text-blue-900 font-medium">AI-Powered Tax Automation</p>
            <p class="text-xs sm:text-sm text-blue-700 mt-1">
              Our AI agents automatically analyze transactions, calculate taxes, generate returns, and assess compliance.
              Each workflow provides confidence scores and recommendations for review.
            </p>

          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-lg shadow p-3 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex-1">
              <p class="text-gray-600 text-xs sm:text-sm font-medium">Total Workflows</p>
              <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1 sm:mt-2">{{ statistics.total_workflows || 0 }}</p>
            </div>
            <div class="bg-blue-100 rounded-lg p-2 sm:p-3 w-fit">
              <i class="fas fa-tasks text-blue-600 text-sm sm:text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex-1">
              <p class="text-gray-600 text-xs sm:text-sm font-medium">Success Rate</p>
              <p class="text-2xl sm:text-3xl font-bold text-green-600 mt-1 sm:mt-2">{{ statistics.completion_rate || 0 }}%</p>
            </div>
            <div class="bg-green-100 rounded-lg p-2 sm:p-3 w-fit">
              <i class="fas fa-check-circle text-green-600 text-sm sm:text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex-1">
              <p class="text-gray-600 text-xs sm:text-sm font-medium">Avg Confidence</p>
              <p class="text-2xl sm:text-3xl font-bold text-indigo-600 mt-1 sm:mt-2">{{ statistics.average_confidence || 0 }}%</p>
            </div>
            <div class="bg-indigo-100 rounded-lg p-2 sm:p-3 w-fit">
              <i class="fas fa-chart-line text-indigo-600 text-sm sm:text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex-1">
              <p class="text-gray-600 text-xs sm:text-sm font-medium">Awaiting Review</p>
              <p class="text-2xl sm:text-3xl font-bold text-amber-600 mt-1 sm:mt-2">{{ awaitingReviewCount }}</p>
            </div>
            <div class="bg-amber-100 rounded-lg p-2 sm:p-3 w-fit">
              <i class="fas fa-exclamation-triangle text-amber-600 text-sm sm:text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Workflow Type</label>
            <select v-model="filters.type" @change="filterWorkflows" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
              <option value="">All Types</option>
              <option value="monthly_vat">Monthly VAT</option>
              <option value="monthly_paye">Monthly PAYE</option>
              <option value="monthly_wht">Monthly WHT</option>
              <option value="compliance_assessment">Compliance Assessment</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.status" @change="filterWorkflows" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="running">Running</option>
              <option value="awaiting_review">Awaiting Review</option>
              <option value="completed">Completed</option>
              <option value="failed">Failed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tax Period</label>
            <input type="month" v-model="filters.period" @change="filterWorkflows" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
          </div>
        </div>
      </div>

      <!-- Workflows List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Confidence</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 200px;">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="workflows.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                  <i class="fas fa-robot text-4xl text-gray-300 mb-3"></i>
                  <p class="text-sm">No AI workflows found. Start your first automated tax workflow!</p>
                </td>
              </tr>
              <tr v-for="workflow in workflows.data" :key="workflow.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-mono text-gray-900">{{ workflow.reference }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <i :class="getWorkflowTypeIcon(workflow.workflow_type)" class="mr-2"></i>
                    <span class="text-sm text-gray-900">{{ formatWorkflowType(workflow.workflow_type) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatTaxPeriod(workflow.tax_period) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusBadgeClass(workflow.status)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ formatStatus(workflow.status) }}
                  </span>
                  <!-- Debug: Uncomment to see raw status -->
                  <!-- <div class="text-xs text-gray-400 mt-1">{{ workflow.status }}</div> -->
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                      <div :class="getProgressBarClass(workflow.progress_percentage)" class="h-2 rounded-full" :style="`width: ${workflow.progress_percentage}%`"></div>
                    </div>
                    <span class="text-xs text-gray-600">{{ workflow.progress_percentage }}%</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center" v-if="workflow.average_confidence">
                    <span :class="getConfidenceClass(workflow.average_confidence)" class="text-sm font-medium">
                      {{ workflow.average_confidence }}%
                    </span>
                  </div>
                  <span v-else class="text-sm text-gray-400">N/A</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(workflow.started_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center justify-end gap-3">
                    <!-- View - Always visible with context-aware text -->
                    <Link
                      :href="route('business.ai-workflows.show', workflow.id)"
                      class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-900 hover:underline transition-colors"
                      :title="workflow.status === 'completed' ? 'View Generated Return' : (workflow.status === 'running' || workflow.status === 'pending' ? 'View Status' : 'View Details')"
                    >
                      <i :class="workflow.status === 'completed' ? 'fas fa-file-invoice' : (workflow.status === 'running' || workflow.status === 'pending' ? 'fas fa-spinner' : 'fas fa-eye')" class="text-sm"></i>
                      <span class="text-xs font-medium">
                        {{ workflow.status === 'completed' ? 'View Return' : (workflow.status === 'running' || workflow.status === 'pending' ? 'View Status' : 'View Details') }}
                      </span>
                    </Link>

                    <!-- Retry for failed workflows -->
                    <button
                      v-if="workflow.status === 'failed'"
                      @click.stop="retryWorkflow(workflow.id)"
                      class="inline-flex items-center gap-1 text-green-600 hover:text-green-900 hover:underline transition-colors"
                      title="Retry Workflow"
                      type="button"
                    >
                      <i class="fas fa-redo text-sm"></i>
                      <span class="text-xs font-medium">Retry</span>
                    </button>

                    <!-- Redo for completed workflows -->
                    <button
                      v-if="workflow.status === 'completed'"
                      @click.stop="redoWorkflow(workflow)"
                      class="inline-flex items-center gap-1 text-purple-600 hover:text-purple-900 hover:underline transition-colors"
                      title="Run Again with Same Parameters"
                      type="button"
                    >
                      <i class="fas fa-redo-alt text-sm"></i>
                      <span class="text-xs font-medium">Redo</span>
                    </button>

                    <!-- Cancel for running workflows -->
                    <button
                      v-if="workflow.status === 'running'"
                      @click.stop="cancelWorkflow(workflow.id)"
                      class="inline-flex items-center gap-1 text-orange-600 hover:text-orange-900 hover:underline transition-colors"
                      title="Cancel Workflow"
                      type="button"
                    >
                      <i class="fas fa-times-circle text-sm"></i>
                      <span class="text-xs font-medium">Cancel</span>
                    </button>

                    <!-- Review indicator -->
                    <button
                      v-if="workflow.requires_human_review && !workflow.reviewed_at"
                      @click.stop="router.visit(route('business.ai-workflows.show', workflow.id))"
                      class="inline-flex items-center gap-1 text-amber-600 hover:text-amber-900 hover:underline transition-colors"
                      title="Review Required"
                      type="button"
                    >
                      <i class="fas fa-exclamation-circle text-sm"></i>
                      <span class="text-xs font-medium">Review</span>
                    </button>

                    <!-- Delete for failed/completed -->
                    <button
                      v-if="['failed', 'completed', 'cancelled'].includes(workflow.status)"
                      @click.stop="deleteWorkflow(workflow.id)"
                      class="inline-flex items-center gap-1 text-red-600 hover:text-red-900 hover:underline transition-colors"
                      title="Delete Workflow"
                      type="button"
                    >
                      <i class="fas fa-trash text-sm"></i>
                      <span class="text-xs font-medium">Delete</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="workflows.links && workflows.links.length > 3" class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ workflows.from }} to {{ workflows.to }} of {{ workflows.total }} workflows
            </div>
            <div class="flex gap-2">
              <Link
                v-for="(link, index) in workflows.links"
                :key="index"
                :href="link.url"
                v-html="link.label"
                :class="[
                  'px-3 py-2 text-sm rounded-md',
                  link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                  !link.url ? 'opacity-50 cursor-not-allowed' : ''
                ]"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Workflow Modal -->
    <CreateWorkflowModal
      :show="showCreateModal"
      :types="workflowTypes"
      @close="showCreateModal = false"
      @created="onWorkflowCreated"
    />
  </BusinessLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import CreateWorkflowModal from './CreateWorkflowModal.vue';

const props = defineProps({
  workflows: Object,
  statistics: Object,
  workflowTypes: Array,
});

// Debug: Log workflows to console (remove after debugging)
if (props.workflows && props.workflows.data) {
  console.log('AI Workflows loaded:', props.workflows.data.length);
  if (props.workflows.data.length > 0) {
    console.log('First workflow:', props.workflows.data[0]);
  }
}

const showCreateModal = ref(false);

const filters = ref({
  type: '',
  status: '',
  period: '',
});

const awaitingReviewCount = computed(() => {
  return props.workflows.data.filter(w => w.requires_human_review && !w.reviewed_at).length;
});

const filterWorkflows = () => {
  router.get(route('business.ai-workflows.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const retryWorkflow = (id) => {
  if (confirm('Are you sure you want to retry this workflow?')) {
    router.post(route('business.ai-workflows.retry', id), {}, {
      onSuccess: () => {
        router.reload({ only: ['workflows', 'statistics'] });
      }
    });
  }
};

const redoWorkflow = (workflow) => {
  if (confirm(`Run ${formatWorkflowType(workflow.workflow_type)} again for ${formatTaxPeriod(workflow.tax_period)}?`)) {
    router.post(route('business.ai-workflows.store'), {
      workflow_type: workflow.workflow_type,
      tax_period: workflow.tax_period,
      async: true
    }, {
      onSuccess: () => {
        router.reload({ only: ['workflows', 'statistics'] });
      }
    });
  }
};

const deleteWorkflow = (id) => {
  if (confirm('Are you sure you want to delete this workflow? This action cannot be undone.')) {
    router.delete(route('business.ai-workflows.destroy', id), {
      onSuccess: () => {
        router.reload({ only: ['workflows', 'statistics'] });
      }
    });
  }
};

const cancelWorkflow = (id) => {
  if (confirm('Are you sure you want to cancel this running workflow?')) {
    router.post(route('business.ai-workflows.cancel', id), {}, {
      onSuccess: () => {
        router.reload({ only: ['workflows', 'statistics'] });
      }
    });
  }
};

const onWorkflowCreated = () => {
  showCreateModal.value = false;
  router.reload();
};

const getWorkflowTypeIcon = (type) => {
  const icons = {
    monthly_vat: 'fas fa-receipt text-blue-600',
    monthly_paye: 'fas fa-users text-green-600',
    monthly_wht: 'fas fa-hand-holding-usd text-purple-600',
    compliance_assessment: 'fas fa-shield-alt text-red-600',
  };
  return icons[type] || 'fas fa-file text-gray-600';
};

const formatWorkflowType = (type) => {
  const labels = {
    monthly_vat: 'Monthly VAT',
    monthly_paye: 'Monthly PAYE',
    monthly_wht: 'Monthly WHT',
    compliance_assessment: 'Compliance Check',
  };
  return labels[type] || type;
};

const formatTaxPeriod = (period) => {
  if (!period) return 'N/A';
  const date = new Date(period + '-01');
  return date.toLocaleDateString('en-NG', { year: 'numeric', month: 'long' });
};

const formatStatus = (status) => {
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-gray-100 text-gray-800',
    running: 'bg-blue-100 text-blue-800',
    awaiting_review: 'bg-amber-100 text-amber-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getProgressBarClass = (percentage) => {
  if (percentage === 100) return 'bg-green-500';
  if (percentage >= 50) return 'bg-blue-500';
  return 'bg-amber-500';
};

const getConfidenceClass = (confidence) => {
  if (confidence >= 85) return 'text-green-600';
  if (confidence >= 70) return 'text-amber-600';
  return 'text-red-600';
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-NG', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
