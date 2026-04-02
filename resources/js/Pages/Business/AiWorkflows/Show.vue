<template>
  <BusinessLayout>
    <Head :title="`Workflow ${workflow.reference}`" />

    <div class="space-y-4 sm:space-y-6 px-3 sm:px-0">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <Link :href="route('business.ai-workflows.index')" class="text-gray-600 hover:text-gray-900">
              <i class="fas fa-arrow-left"></i>
            </Link>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ workflow.reference }}</h1>
            <span :class="getStatusBadgeClass(workflow.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
              {{ formatStatus(workflow.status) }}
            </span>
          </div>
          <p class="mt-1 text-sm sm:text-base text-gray-600">
            {{ formatWorkflowType(workflow.workflow_type) }} • {{ formatTaxPeriod(workflow.tax_period) }}
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            v-if="workflow.status === 'failed'"
            @click="retryWorkflow"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium"
          >
            <i class="fas fa-redo mr-2"></i>
            Retry Workflow
          </button>
          <button
            v-if="workflow.status === 'running'"
            @click="cancelWorkflow"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium"
          >
            <i class="fas fa-times-circle mr-2"></i>
            Cancel
          </button>
          <button
            v-if="workflow.requires_human_review && !workflow.reviewed_at"
            @click="showReviewModal = true"
            class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition text-sm font-medium animate-pulse"
          >
            <i class="fas fa-user-check mr-2"></i>
            Review & Approve
          </button>
        </div>
      </div>

      <!-- Progress Overview -->
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Progress Overview</h3>
          <div class="text-right">
            <div class="text-2xl font-bold text-blue-600">{{ workflow.progress_percentage }}%</div>
            <div class="text-xs text-gray-600">{{ workflow.completed_steps }} of {{ workflow.total_steps }} steps</div>
          </div>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
          <div
            :class="getProgressBarClass(workflow.progress_percentage)"
            class="h-4 rounded-full transition-all duration-500"
            :style="`width: ${workflow.progress_percentage}%`"
          ></div>
        </div>
        <div v-if="workflow.current_step" class="mt-3 text-sm text-gray-700">
          <i class="fas fa-cog fa-spin mr-2 text-blue-600"></i>
          Current: {{ workflow.current_step }}
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Confidence Score</p>
              <p :class="getConfidenceClass(workflow.average_confidence)" class="text-2xl font-bold mt-1">
                {{ workflow.average_confidence || 'N/A' }}{{ workflow.average_confidence ? '%' : '' }}
              </p>
            </div>
            <div class="bg-indigo-100 rounded-lg p-3">
              <i class="fas fa-chart-line text-indigo-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Execution Time</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatExecutionTime(workflow.execution_time_seconds) }}</p>
            </div>
            <div class="bg-blue-100 rounded-lg p-3">
              <i class="fas fa-clock text-blue-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">AI Provider</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ workflow.ai_provider || 'N/A' }}</p>
            </div>
            <div class="bg-purple-100 rounded-lg p-3">
              <i class="fas fa-brain text-purple-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Total Cost</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(totalCost) }}</p>
            </div>
            <div class="bg-green-100 rounded-lg p-3">
              <i class="fas fa-money-bill text-green-600 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Warnings & Alerts -->
      <div v-if="workflow.warnings && workflow.warnings.length > 0" class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg">
        <div class="flex">
          <i class="fas fa-exclamation-triangle text-amber-600 mr-3 mt-1"></i>
          <div class="flex-1">
            <h4 class="text-sm font-semibold text-amber-900 mb-2">Warnings Detected</h4>
            <ul class="list-disc list-inside space-y-1">
              <li v-for="(warning, index) in workflow.warnings" :key="index" class="text-sm text-amber-700">
                {{ warning }}
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Review Required Alert -->
      <div v-if="workflow.requires_human_review && !workflow.reviewed_at" class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
        <div class="flex items-start">
          <i class="fas fa-user-shield text-red-600 text-xl mr-3 mt-1"></i>
          <div class="flex-1">
            <h4 class="text-sm font-semibold text-red-900 mb-1">Human Review Required</h4>
            <p class="text-sm text-red-700">
              This workflow has low confidence scores or detected issues that require your review before proceeding.
              Please carefully review all steps and AI decisions below.
            </p>
          </div>
          <button
            @click="showReviewModal = true"
            class="ml-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium"
          >
            Review Now
          </button>
        </div>
      </div>

      <!-- Workflow Steps -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Workflow Steps</h3>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div
              v-for="(step, index) in workflow.steps"
              :key="step.id"
              class="border rounded-lg overflow-hidden"
              :class="getStepBorderClass(step.status)"
            >
              <!-- Step Header -->
              <div
                @click="toggleStep(step.id)"
                class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50"
                :class="getStepHeaderClass(step.status)"
              >
                <div class="flex items-center flex-1">
                  <div class="flex-shrink-0 mr-4">
                    <div :class="getStepIconClass(step.status)" class="w-10 h-10 rounded-full flex items-center justify-center">
                      <i :class="getStepIcon(step.status)" class="text-lg"></i>
                    </div>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center gap-3">
                      <h4 class="text-sm font-semibold text-gray-900">Step {{ step.step_number }}: {{ step.step_name }}</h4>
                      <span :class="getStatusBadgeClass(step.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                        {{ formatStatus(step.status) }}
                      </span>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">Agent: {{ step.agent_name }}</p>
                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                      <span v-if="step.confidence_score">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Confidence: <span :class="getConfidenceClass(step.confidence_score)">{{ step.confidence_score }}%</span>
                      </span>
                      <span v-if="step.execution_time_seconds">
                        <i class="fas fa-clock mr-1"></i>
                        {{ formatExecutionTime(step.execution_time_seconds) }}
                      </span>
                      <span v-if="step.tokens_used">
                        <i class="fas fa-microchip mr-1"></i>
                        {{ step.tokens_used.toLocaleString() }} tokens
                      </span>
                      <span v-if="step.cost">
                        <i class="fas fa-money-bill-wave mr-1"></i>
                        ₦{{ formatCurrency(step.cost) }}
                      </span>
                    </div>
                  </div>
                  <div>
                    <i :class="expandedSteps.includes(step.id) ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-gray-400"></i>
                  </div>
                </div>
              </div>

              <!-- Step Details (Expandable) -->
              <div v-if="expandedSteps.includes(step.id)" class="border-t bg-gray-50">
                <div class="p-4 space-y-4">
                  <!-- AI Prompt -->
                  <div v-if="step.prompt">
                    <h5 class="text-xs font-semibold text-gray-700 mb-2">AI Prompt</h5>
                    <div class="bg-white border rounded p-3">
                      <pre class="text-xs text-gray-700 whitespace-pre-wrap font-mono">{{ step.prompt }}</pre>
                    </div>
                  </div>

                  <!-- AI Response -->
                  <div v-if="step.parsed_response">
                    <h5 class="text-xs font-semibold text-gray-700 mb-2">AI Analysis</h5>
                    <div class="bg-white border rounded p-3">
                      <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ JSON.stringify(step.parsed_response, null, 2) }}</pre>
                    </div>
                  </div>

                  <!-- Validations -->
                  <div v-if="step.validations && step.validations.length > 0">
                    <h5 class="text-xs font-semibold text-gray-700 mb-2">Validations</h5>
                    <div class="space-y-2">
                      <div v-for="(validation, vIndex) in step.validations" :key="vIndex" class="flex items-start">
                        <i :class="validation.passed ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600'" class="fas mr-2 mt-0.5"></i>
                        <span class="text-xs" :class="validation.passed ? 'text-green-700' : 'text-red-700'">
                          {{ validation.message }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Warnings -->
                  <div v-if="step.warnings && step.warnings.length > 0">
                    <h5 class="text-xs font-semibold text-gray-700 mb-2">Step Warnings</h5>
                    <ul class="list-disc list-inside space-y-1">
                      <li v-for="(warning, wIndex) in step.warnings" :key="wIndex" class="text-xs text-amber-700">
                        {{ warning }}
                      </li>
                    </ul>
                  </div>

                  <!-- Error Message -->
                  <div v-if="step.error_message" class="bg-red-50 border border-red-200 rounded p-3">
                    <h5 class="text-xs font-semibold text-red-700 mb-1">Error</h5>
                    <p class="text-xs text-red-600">{{ step.error_message }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- AI Recommendations -->
      <div v-if="workflow.recommendations && workflow.recommendations.length > 0" class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
          AI Recommendations
        </h3>
        <ul class="space-y-3">
          <li v-for="(recommendation, index) in workflow.recommendations" :key="index" class="flex items-start">
            <i class="fas fa-arrow-right text-blue-600 mr-3 mt-1"></i>
            <span class="text-sm text-gray-700">{{ recommendation }}</span>
          </li>
        </ul>
      </div>

      <!-- AI Decisions -->
      <div v-if="workflow.ai_decisions && Object.keys(workflow.ai_decisions).length > 0" class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <i class="fas fa-brain text-purple-600 mr-2"></i>
          AI Decisions
        </h3>
        <div class="space-y-2">
          <div v-for="(value, key) in workflow.ai_decisions" :key="key" class="flex justify-between items-center py-2 border-b">
            <span class="text-sm font-medium text-gray-700">{{ formatDecisionKey(key) }}</span>
            <span class="text-sm text-gray-900">{{ formatDecisionValue(value) }}</span>
          </div>
        </div>
      </div>

      <!-- Review Information -->
      <div v-if="workflow.reviewed_at" class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
        <div class="flex items-start">
          <i class="fas fa-check-circle text-green-600 text-xl mr-3 mt-1"></i>
          <div>
            <h4 class="text-sm font-semibold text-green-900 mb-1">Reviewed & Approved</h4>
            <p class="text-sm text-green-700">
              Reviewed by {{ workflow.reviewer?.name || 'Unknown' }} on {{ formatDate(workflow.reviewed_at) }}
            </p>
            <p v-if="workflow.review_notes" class="text-sm text-green-700 mt-2 italic">
              "{{ workflow.review_notes }}"
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <ReviewModal
      :show="showReviewModal"
      :workflow="workflow"
      @close="showReviewModal = false"
      @reviewed="onReviewed"
    />
  </BusinessLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import ReviewModal from './ReviewModal.vue';

const props = defineProps({
  workflow: Object,
});

const showReviewModal = ref(false);
const expandedSteps = ref([]);

const totalCost = computed(() => {
  return props.workflow.steps?.reduce((sum, step) => sum + (step.cost || 0), 0) || 0;
});

const toggleStep = (stepId) => {
  const index = expandedSteps.value.indexOf(stepId);
  if (index > -1) {
    expandedSteps.value.splice(index, 1);
  } else {
    expandedSteps.value.push(stepId);
  }
};

const retryWorkflow = () => {
  if (confirm('Are you sure you want to retry this workflow?')) {
    router.post(route('business.ai-workflows.retry', props.workflow.id));
  }
};

const cancelWorkflow = () => {
  if (confirm('Are you sure you want to cancel this running workflow?')) {
    router.post(route('business.ai-workflows.cancel', props.workflow.id));
  }
};

const onReviewed = () => {
  showReviewModal.value = false;
  router.reload();
};

const formatWorkflowType = (type) => {
  const labels = {
    monthly_vat: 'Monthly VAT Processing',
    monthly_paye: 'Monthly PAYE Processing',
    monthly_wht: 'Monthly WHT Processing',
    compliance_assessment: 'Compliance Assessment',
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

const formatExecutionTime = (seconds) => {
  if (!seconds) return 'N/A';
  if (seconds < 60) return `${seconds}s`;
  const minutes = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${minutes}m ${secs}s`;
};

const formatCurrency = (amount) => {
  if (!amount) return '0.00';
  return amount.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-NG', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatDecisionKey = (key) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatDecisionValue = (value) => {
  if (typeof value === 'object') return JSON.stringify(value);
  return String(value);
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

const getStepBorderClass = (status) => {
  const classes = {
    completed: 'border-green-300',
    running: 'border-blue-300',
    failed: 'border-red-300',
    pending: 'border-gray-300',
  };
  return classes[status] || 'border-gray-300';
};

const getStepHeaderClass = (status) => {
  const classes = {
    completed: 'bg-green-50',
    running: 'bg-blue-50',
    failed: 'bg-red-50',
  };
  return classes[status] || '';
};

const getStepIconClass = (status) => {
  const classes = {
    completed: 'bg-green-100 text-green-600',
    running: 'bg-blue-100 text-blue-600',
    failed: 'bg-red-100 text-red-600',
    pending: 'bg-gray-100 text-gray-600',
  };
  return classes[status] || 'bg-gray-100 text-gray-600';
};

const getStepIcon = (status) => {
  const icons = {
    completed: 'fas fa-check',
    running: 'fas fa-spinner fa-spin',
    failed: 'fas fa-times',
    pending: 'fas fa-clock',
  };
  return icons[status] || 'fas fa-circle';
};
</script>
