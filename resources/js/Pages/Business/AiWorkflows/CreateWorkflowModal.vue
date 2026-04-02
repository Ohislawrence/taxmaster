<template>
  <teleport to="body">
    <transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="close"></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-white flex items-center">
                  <i class="fas fa-robot mr-2"></i>
                  Start AI Workflow
                </h3>
                <button @click="close" class="text-white hover:text-gray-200">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>

            <!-- Body -->
            <form @submit.prevent="submit">
              <div class="bg-white px-6 py-4 space-y-4">
                <!-- Workflow Type -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Workflow Type <span class="text-red-500">*</span>
                  </label>
                  <div class="space-y-2">
                    <div
                      v-for="type in types"
                      :key="type.value"
                      @click="form.workflow_type = type.value"
                      :class="[
                        'border-2 rounded-lg p-4 cursor-pointer transition',
                        form.workflow_type === type.value
                          ? 'border-blue-500 bg-blue-50'
                          : 'border-gray-200 hover:border-blue-300'
                      ]"
                    >
                      <div class="flex items-start">
                        <div class="flex-shrink-0">
                          <i :class="type.icon" class="text-2xl mt-1"></i>
                        </div>
                        <div class="ml-3 flex-1">
                          <h4 class="text-sm font-medium text-gray-900">{{ type.label }}</h4>
                          <p class="text-xs text-gray-500 mt-1">{{ type.description }}</p>
                          <div class="flex flex-wrap gap-2 mt-2">
                            <span v-for="step in type.steps" :key="step" class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                              {{ step }}
                            </span>
                          </div>
                        </div>
                        <div class="ml-2">
                          <div
                            :class="[
                              'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                              form.workflow_type === type.value
                                ? 'border-blue-500 bg-blue-500'
                                : 'border-gray-300'
                            ]"
                          >
                            <i v-if="form.workflow_type === type.value" class="fas fa-check text-white text-xs"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="form.errors.workflow_type" class="text-red-500 text-xs mt-1">
                    {{ form.errors.workflow_type }}
                  </div>
                </div>

                <!-- Tax Period -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tax Period <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="month"
                    v-model="form.tax_period"
                    :max="maxPeriod"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    required
                  />
                  <p class="text-xs text-gray-500 mt-1">Select the month you want to process</p>
                  <div v-if="form.errors.tax_period" class="text-red-500 text-xs mt-1">
                    {{ form.errors.tax_period }}
                  </div>
                </div>

                <!-- Data Availability Check -->
                <div v-if="form.workflow_type && form.tax_period" class="space-y-2">
                  <!-- Loading State -->
                  <div v-if="availability.checking" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                      <i class="fas fa-spinner fa-spin text-gray-400 mr-2"></i>
                      <span class="text-sm text-gray-600">Checking data availability...</span>
                    </div>
                  </div>

                  <!-- Available -->
                  <div v-else-if="availability.result && availability.result.available" class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                    <div class="flex items-start">
                      <i class="fas fa-check-circle text-green-600 mr-2 mt-0.5"></i>
                      <div class="text-sm text-green-700 flex-1">
                        <p class="font-medium">Ready to process</p>
                        <div v-if="availability.result.data_counts" class="mt-2 space-y-1">
                          <div v-for="(count, key) in availability.result.data_counts" :key="key">
                            <div v-if="key !== 'note'" class="flex items-center text-xs">
                              <i class="fas fa-database mr-2 text-green-500"></i>
                              <span>{{ formatDataCountLabel(key) }}: <strong>{{ count }}</strong></span>
                            </div>
                          </div>
                          <div v-if="availability.result.data_counts.note" class="mt-2 p-2 bg-blue-50 rounded text-xs text-blue-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ availability.result.data_counts.note }}
                          </div>
                        </div>
                        <p v-if="availability.result.period_formatted" class="text-xs mt-2 text-green-600">
                          Period: {{ availability.result.period_formatted }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Not Available -->
                  <div v-else-if="availability.result && !availability.result.available" class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded">
                    <div class="flex items-start">
                      <i class="fas fa-exclamation-triangle text-amber-600 mr-2 mt-0.5"></i>
                      <div class="text-sm text-amber-700 flex-1">
                        <p class="font-medium">Missing required data</p>
                        <ul class="mt-2 space-y-1 list-disc list-inside">
                          <li v-for="(missing, index) in availability.result.missing" :key="index" class="text-xs">
                            {{ missing }}
                          </li>
                        </ul>
                        <div v-if="availability.result.data_counts && Object.keys(availability.result.data_counts).length > 0" class="mt-2 space-y-1">
                          <p class="text-xs font-medium">Current data:</p>
                          <div v-for="(count, key) in availability.result.data_counts" :key="key">
                            <div v-if="key !== 'note'" class="flex items-center text-xs ml-4">
                              <i class="fas fa-database mr-2 text-amber-500"></i>
                              <span>{{ formatDataCountLabel(key) }}: <strong>{{ count }}</strong></span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Requirements Info (Collapsible) -->
                  <div v-if="availability.result && availability.result.requirements" class="border border-gray-200 rounded-lg">
                    <button
                      type="button"
                      @click="showRequirements = !showRequirements"
                      class="w-full px-4 py-2 text-left flex items-center justify-between hover:bg-gray-50 rounded-lg"
                    >
                      <span class="text-xs font-medium text-gray-700">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Data Requirements
                      </span>
                      <i :class="['fas', showRequirements ? 'fa-chevron-up' : 'fa-chevron-down', 'text-gray-400 text-xs']"></i>
                    </button>
                    <div v-show="showRequirements" class="px-4 pb-3 text-xs text-gray-600 space-y-2">
                      <p class="font-medium">{{ availability.result.requirements.name }}</p>
                      <p>{{ availability.result.requirements.description }}</p>
                      <div v-if="availability.result.requirements.required_data && availability.result.requirements.required_data.length > 0">
                        <p class="font-medium text-gray-700 mt-2">Required:</p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                          <li v-for="(req, index) in availability.result.requirements.required_data" :key="index">
                            <strong>{{ req.type }}</strong>: {{ req.description }}
                            <span v-if="req.minimum > 0"> (min: {{ req.minimum }})</span>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Processing Mode -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Processing Mode
                  </label>
                  <div class="grid grid-cols-2 gap-3">
                    <div
                      @click="form.async = true"
                      :class="[
                        'border-2 rounded-lg p-3 cursor-pointer transition',
                        form.async === true
                          ? 'border-blue-500 bg-blue-50'
                          : 'border-gray-200 hover:border-blue-300'
                      ]"
                    >
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="text-sm font-medium text-gray-900">Background</h5>
                          <p class="text-xs text-gray-500 mt-1">Process in queue</p>
                        </div>
                        <i class="fas fa-clock text-blue-600"></i>
                      </div>
                    </div>
                    <div
                      @click="form.async = false"
                      :class="[
                        'border-2 rounded-lg p-3 cursor-pointer transition',
                        form.async === false
                          ? 'border-blue-500 bg-blue-50'
                          : 'border-gray-200 hover:border-blue-300'
                      ]"
                    >
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="text-sm font-medium text-gray-900">Immediate</h5>
                          <p class="text-xs text-gray-500 mt-1">Wait for result</p>
                        </div>
                        <i class="fas fa-bolt text-amber-600"></i>
                      </div>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Background processing is recommended for complex workflows
                  </p>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
                  <div class="flex">
                    <i class="fas fa-lightbulb text-blue-600 mr-2 mt-0.5"></i>
                    <div class="text-xs text-blue-700">
                      <p class="font-medium">What happens next?</p>
                      <ul class="list-disc list-inside mt-1 space-y-1">
                        <li>AI agents will analyze your {{ getSelectedTypeLabel() }} data</li>
                        <li>Each step will be validated with confidence scores</li>
                        <li>You'll receive recommendations and warnings</li>
                        <li>Review and approve before final submission</li>
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
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="!canSubmit"
                  :class="[
                    'px-4 py-2 text-sm font-medium text-white rounded-lg flex items-center',
                    canSubmit
                      ? 'bg-blue-600 hover:bg-blue-700'
                      : 'bg-gray-400 cursor-not-allowed'
                  ]"
                >
                  <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                  <i v-else-if="availability.result && !availability.result.available" class="fas fa-exclamation-triangle mr-2"></i>
                  <i v-else class="fas fa-play mr-2"></i>
                  {{ form.processing ? 'Starting...' : (availability.result && !availability.result.available ? 'Data Required' : 'Start Workflow') }}
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
import { reactive, computed, watch, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  types: Array,
});

const emit = defineEmits(['close', 'created']);

const form = reactive({
  workflow_type: '',
  tax_period: getCurrentPeriod(),
  async: true,
  processing: false,
  errors: {},
});

const availability = reactive({
  checking: false,
  result: null,
});

const showRequirements = ref(false);

const maxPeriod = computed(() => {
  const date = new Date();
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
});

const canSubmit = computed(() => {
  return (
    !form.processing &&
    form.workflow_type &&
    form.tax_period &&
    (!availability.result || availability.result.available)
  );
});

// Watch for changes to workflow type and tax period
watch(
  () => [form.workflow_type, form.tax_period],
  ([workflowType, taxPeriod]) => {
    if (workflowType && taxPeriod) {
      checkDataAvailability(workflowType, taxPeriod);
    } else {
      availability.result = null;
    }
  },
  { immediate: false }
);

async function checkDataAvailability(workflowType, taxPeriod) {
  availability.checking = true;
  availability.result = null;

  try {
    // Parse month and year from tax_period (format: YYYY-MM)
    const [year, month] = taxPeriod.split('-');

    const response = await axios.post(route('business.ai-workflows.check-availability'), {
      workflow_type: workflowType,
      month: parseInt(month),
      year: parseInt(year),
    });

    availability.result = response.data;
  } catch (error) {
    console.error('Error checking data availability:', error);

    // Try to extract error message from API response
    let errorMessage = 'Unable to check data availability. Please try again.';
    if (error.response && error.response.data) {
      if (error.response.data.message) {
        errorMessage = error.response.data.message;
      } else if (error.response.data.error) {
        errorMessage = error.response.data.error;
      }
    }

    availability.result = {
      available: false,
      missing: [errorMessage],
      requirements: {},
      data_counts: {},
    };
  } finally {
    availability.checking = false;
  }
}

function formatDataCountLabel(key) {
  const labels = {
    transactions: 'Transactions',
    invoices: 'Invoices',
    staff: 'Employees',
    payroll_records: 'Payroll Records',
    wht_transactions: 'WHT Transactions',
    wht_categorized: 'Pre-categorized WHT',
    transactions_in_period: 'Transactions in Period',
    vat_returns: 'VAT Returns',
    total_transactions: 'Total Transactions',
    total_invoices: 'Total Invoices',
    all_transactions: 'All Transactions',
  };
  return labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function getCurrentPeriod() {
  const date = new Date();
  date.setMonth(date.getMonth() - 1); // Default to previous month
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
}

function getSelectedTypeLabel() {
  const selected = props.types.find(t => t.value === form.workflow_type);
  return selected ? selected.label.toLowerCase() : 'tax';
}

function close() {
  if (!form.processing) {
    emit('close');
    resetForm();
  }
}

function resetForm() {
  form.workflow_type = '';
  form.tax_period = getCurrentPeriod();
  form.async = true;
  form.errors = {};
  availability.result = null;
  availability.checking = false;
  showRequirements.value = false;
}

function submit() {
  // Prevent submission if data is not available
  if (availability.result && !availability.result.available) {
    return;
  }

  form.processing = true;
  form.errors = {};

  router.post(route('business.ai-workflows.store'), {
    workflow_type: form.workflow_type,
    tax_period: form.tax_period,
    async: form.async,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      emit('created');
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
