<template>
  <teleport to="body">
    <transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="close"></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <!-- Header -->
            <div class="bg-gradient-to-r from-amber-600 to-orange-600 px-6 py-4">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-white flex items-center">
                  <i class="fas fa-user-check mr-2"></i>
                  Review Workflow
                </h3>
                <button @click="close" class="text-white hover:text-gray-200">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>

            <!-- Body -->
            <form @submit.prevent="submit">
              <div class="bg-white px-6 py-4 space-y-4">
                <!-- Workflow Summary -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                  <h4 class="text-sm font-semibold text-blue-900 mb-2">Workflow Summary</h4>
                  <div class="space-y-1 text-sm text-blue-800">
                    <div><strong>Type:</strong> {{ formatWorkflowType(workflow.workflow_type) }}</div>
                    <div><strong>Period:</strong> {{ formatTaxPeriod(workflow.tax_period) }}</div>
                    <div><strong>Progress:</strong> {{ workflow.progress_percentage }}% ({{ workflow.completed_steps }}/{{ workflow.total_steps }} steps)</div>
                    <div>
                      <strong>Confidence:</strong>
                      <span :class="getConfidenceClass(workflow.average_confidence)">
                        {{ workflow.average_confidence }}%
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Warnings -->
                <div v-if="workflow.warnings && workflow.warnings.length > 0" class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded">
                  <h4 class="text-sm font-semibold text-amber-900 mb-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Warnings Detected
                  </h4>
                  <ul class="list-disc list-inside space-y-1">
                    <li v-for="(warning, index) in workflow.warnings" :key="index" class="text-sm text-amber-700">
                      {{ warning }}
                    </li>
                  </ul>
                </div>

                <!-- Recommendations -->
                <div v-if="workflow.recommendations && workflow.recommendations.length > 0" class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                  <h4 class="text-sm font-semibold text-green-900 mb-2">
                    <i class="fas fa-lightbulb mr-1"></i>
                    AI Recommendations
                  </h4>
                  <ul class="list-disc list-inside space-y-1">
                    <li v-for="(recommendation, index) in workflow.recommendations" :key="index" class="text-sm text-green-700">
                      {{ recommendation }}
                    </li>
                  </ul>
                </div>

                <!-- Low Confidence Steps -->
                <div v-if="lowConfidenceSteps.length > 0" class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                  <h4 class="text-sm font-semibold text-red-900 mb-2">
                    <i class="fas fa-chart-line mr-1"></i>
                    Low Confidence Steps
                  </h4>
                  <div class="space-y-2">
                    <div v-for="step in lowConfidenceSteps" :key="step.id" class="text-sm">
                      <div class="font-medium text-red-800">Step {{ step.step_number }}: {{ step.step_name }}</div>
                      <div class="text-red-700">Confidence: {{ step.confidence_score }}%</div>
                    </div>
                  </div>
                </div>

                <!-- Review Decision -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">
                    Review Decision <span class="text-red-500">*</span>
                  </label>
                  <div class="grid grid-cols-2 gap-4">
                    <div
                      @click="form.approved = true"
                      :class="[
                        'border-2 rounded-lg p-4 cursor-pointer transition',
                        form.approved === true
                          ? 'border-green-500 bg-green-50'
                          : 'border-gray-200 hover:border-green-300'
                      ]"
                    >
                      <div class="flex items-center justify-between">
                        <div>
                          <i class="fas fa-check-circle text-2xl text-green-600"></i>
                          <h5 class="text-sm font-semibold text-gray-900 mt-2">Approve</h5>
                          <p class="text-xs text-gray-600 mt-1">Workflow results are acceptable</p>
                        </div>
                      </div>
                    </div>

                    <div
                      @click="form.approved = false"
                      :class="[
                        'border-2 rounded-lg p-4 cursor-pointer transition',
                        form.approved === false
                          ? 'border-red-500 bg-red-50'
                          : 'border-gray-200 hover:border-red-300'
                      ]"
                    >
                      <div class="flex items-center justify-between">
                        <div>
                          <i class="fas fa-times-circle text-2xl text-red-600"></i>
                          <h5 class="text-sm font-semibold text-gray-900 mt-2">Reject</h5>
                          <p class="text-xs text-gray-600 mt-1">Workflow needs correction</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="form.errors.approved" class="text-red-500 text-xs mt-1">
                    {{ form.errors.approved }}
                  </div>
                </div>

                <!-- Review Notes -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Review Notes <span class="text-gray-400">(Optional)</span>
                  </label>
                  <textarea
                    v-model="form.notes"
                    rows="4"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Add any comments, observations, or instructions for corrections..."
                  ></textarea>
                  <p class="text-xs text-gray-500 mt-1">
                    These notes will be saved with the workflow review record
                  </p>
                  <div v-if="form.errors.notes" class="text-red-500 text-xs mt-1">
                    {{ form.errors.notes }}
                  </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
                  <div class="flex">
                    <i class="fas fa-info-circle text-blue-600 mr-2 mt-0.5"></i>
                    <div class="text-xs text-blue-700">
                      <p class="font-medium">Review Guidelines</p>
                      <ul class="list-disc list-inside mt-1 space-y-1">
                        <li>Verify all AI calculations and recommendations are reasonable</li>
                        <li>Check that warnings have been addressed or documented</li>
                        <li>Ensure all low-confidence steps have been manually verified</li>
                        <li>Confirm tax rules and compliance requirements are met</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                <button
                  type="button"
                  @click="close"
                  :disabled="form.processing"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="form.processing || form.approved === null"
                  class="px-4 py-2 text-sm font-medium text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                  :class="form.approved ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
                >
                  <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                  <i v-else :class="form.approved ? 'fa-check' : 'fa-times'" class="fas mr-2"></i>
                  {{ form.processing ? 'Submitting...' : (form.approved ? 'Approve Workflow' : 'Reject Workflow') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  show: Boolean,
  workflow: Object,
});

const emit = defineEmits(['close', 'reviewed']);

const form = reactive({
  approved: null,
  notes: '',
  processing: false,
  errors: {},
});

const lowConfidenceSteps = computed(() => {
  return props.workflow.steps?.filter(step => step.confidence_score && step.confidence_score < 85) || [];
});

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

const getConfidenceClass = (confidence) => {
  if (confidence >= 85) return 'text-green-600 font-semibold';
  if (confidence >= 70) return 'text-amber-600 font-semibold';
  return 'text-red-600 font-semibold';
};

function close() {
  if (!form.processing) {
    emit('close');
    resetForm();
  }
}

function resetForm() {
  form.approved = null;
  form.notes = '';
  form.errors = {};
}

function submit() {
  if (form.approved === null) {
    form.errors.approved = 'Please select approve or reject';
    return;
  }

  form.processing = true;
  form.errors = {};

  router.post(route('business.ai-workflows.review', props.workflow.id), {
    approved: form.approved,
    notes: form.notes,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      emit('reviewed');
      resetForm();
    },
    onError: (errors) => {
      form.errors = errors;
    },
    onFinish: () => {
      form.processing = false;
    },
  });
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
