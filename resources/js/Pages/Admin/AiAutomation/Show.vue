<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">AI Suggestion Details</h2>
          <p class="mt-1 text-sm text-gray-600">Review and manage this AI-generated suggestion</p>
        </div>
        <Link
          :href="route('admin.ai-automation.index')"
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
          ← Back
        </Link>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Main Card -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <!-- Status and Type -->
          <div class="flex items-center justify-between mb-6">
            <div class="flex gap-3">
              <span :class="getTypeBadgeClass(suggestion.type)" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ formatType(suggestion.type) }}
              </span>
              <span :class="getStatusBadgeClass(suggestion.status)" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ formatStatus(suggestion.status) }}
              </span>
            </div>
            <span class="text-sm text-gray-600">ID: {{ suggestion.id }}</span>
          </div>

          <!-- Confidence Score -->
          <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Confidence Score</h3>
            <div class="flex items-center gap-4">
              <div class="flex-1">
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div :style="{ width: (suggestion.confidence * 100) + '%' }" :class="getConfidenceColor(suggestion.confidence)" class="h-3 rounded-full"></div>
                </div>
              </div>
              <span class="text-2xl font-bold" :class="getConfidenceTextColor(suggestion.confidence)">
                {{ Math.round(suggestion.confidence * 100) }}%
              </span>
            </div>
            <p v-if="suggestion.confidence >= 0.9" class="mt-2 text-sm text-green-600">✓ High confidence - safe to auto-apply</p>
            <p v-else-if="suggestion.confidence >= 0.7" class="mt-2 text-sm text-yellow-600">⚠ Medium confidence - review before applying</p>
            <p v-else class="mt-2 text-sm text-red-600">✗ Low confidence - human review recommended</p>
          </div>

          <!-- Data Section -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Suggestion Data</h3>

            <!-- Categorization -->
            <template v-if="suggestion.type === 'categorization'">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-700">Suggested Category</dt>
                  <dd class="mt-1 text-sm font-semibold text-blue-600">{{ suggestion.data.category }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-700">Transaction Amount</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ suggestion.data.amount }}</dd>
                </div>
              </div>
              <div class="mt-6 grid grid-cols-1 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-700 mb-2">Reasoning</dt>
                  <dd class="text-sm text-gray-600 bg-gray-50 p-3 rounded">{{ suggestion.data.reasoning }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-700 mb-2">Tax Implications</dt>
                  <dd class="text-sm text-gray-600 bg-gray-50 p-3 rounded">{{ suggestion.data.tax_implications }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-700 mb-2">Suggested Action</dt>
                  <dd class="text-sm text-gray-600 bg-blue-50 p-3 rounded border border-blue-200">{{ suggestion.data.suggested_action }}</dd>
                </div>
              </div>
            </template>

            <!-- Compliance Reminder -->
            <template v-else-if="suggestion.type === 'compliance_reminder'">
              <div class="grid grid-cols-1 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-700">Deadline</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ suggestion.data.deadline_name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-700">Priority Level</dt>
                  <dd class="mt-1">
                    <span :class="getPriorityBadgeClass(suggestion.data.priority)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                      {{ formatPriority(suggestion.data.priority) }}
                    </span>
                  </dd>
                </div>
              </div>

              <div class="mt-6 grid grid-cols-1 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-700 mb-3">Recommended Actions</dt>
                  <ul class="space-y-2">
                    <li v-for="(action, idx) in suggestion.data.recommended_actions" :key="idx" class="flex items-start gap-3 text-sm text-gray-600 bg-gray-50 p-3 rounded">
                      <span class="text-blue-600 font-bold">{{ idx + 1 }}.</span>
                      <span>{{ action }}</span>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="mt-6 grid grid-cols-1 gap-6">
                <div>
                  <dt class="text-sm font-medium text-gray-700 mb-3">Documents Needed</dt>
                  <ul class="space-y-1">
                    <li v-for="(doc, idx) in suggestion.data.documents_needed" :key="idx" class="flex items-center gap-2 text-sm text-gray-600">
                      <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 012-2h6a1 1 0 01.82.4l2.763 3.684A1 1 0 0116 6h2a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z"/>
                      </svg>
                      {{ doc }}
                    </li>
                  </ul>
                </div>
              </div>

              <div v-if="suggestion.data.common_mistakes" class="mt-6">
                <dt class="text-sm font-medium text-gray-700 mb-3">⚠ Common Mistakes to Avoid</dt>
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                  <p class="text-sm text-yellow-800">{{ suggestion.data.common_mistakes }}</p>
                </div>
              </div>
            </template>

            <!-- Payment Recovery -->
            <template v-else-if="suggestion.type === 'payment_recovery'">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-700">Payment Failures</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ suggestion.data.payment_failure_count }} attempt(s)</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-700">Customer Type</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ suggestion.data.customer_type }}</dd>
                </div>
              </div>

              <div class="mt-6">
                <dt class="text-sm font-medium text-gray-700 mb-2">Suggested Strategy</dt>
                <div :class="getStrategyColor(suggestion.data.strategy)" class="p-4 rounded border-l-4">
                  <p class="font-semibold">{{ formatStrategy(suggestion.data.strategy) }}</p>
                  <p class="text-sm mt-2">{{ suggestion.data.reason }}</p>
                </div>
              </div>

              <div v-if="suggestion.data.strategy === 'discount'" class="mt-6 bg-blue-50 border border-blue-200 rounded p-4">
                <dt class="text-sm font-medium text-gray-700 mb-2">Offer Details</dt>
                <p class="text-sm text-gray-600">{{ suggestion.data.discount_percentage }}% discount for next billing cycle</p>
              </div>

              <div v-if="suggestion.data.strategy === 'payment_plan'" class="mt-6 bg-blue-50 border border-blue-200 rounded p-4">
                <dt class="text-sm font-medium text-gray-700 mb-2">Payment Plan</dt>
                <p class="text-sm text-gray-600">{{ suggestion.data.months }} months, {{ suggestion.data.first_payment_fraction }}% of first payment</p>
              </div>

              <div class="mt-6">
                <dt class="text-sm font-medium text-gray-700 mb-3">Recovery Actions</dt>
                <ul class="space-y-2">
                  <li v-for="(action, idx) in suggestion.data.recovery_actions" :key="idx" class="flex items-start gap-3 text-sm text-gray-600 bg-gray-50 p-3 rounded">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ action }}
                  </li>
                </ul>
              </div>
            </template>

            <!-- Generic JSON display fallback -->
            <template v-else>
              <pre class="bg-gray-50 p-4 rounded text-xs overflow-auto">{{ JSON.stringify(suggestion.data, null, 2) }}</pre>
            </template>
          </div>

          <!-- Metadata -->
          <div class="border-t border-gray-200 mt-6 pt-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-700">Created At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(suggestion.created_at) }}</dd>
              </div>
              <div v-if="suggestion.applied_at">
                <dt class="text-sm font-medium text-gray-700">Applied At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(suggestion.applied_at) }}</dd>
              </div>
            </div>
          </div>

          <!-- Feedback Section -->
          <div v-if="suggestion.user_feedback" class="border-t border-gray-200 mt-6 pt-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">User Feedback</h3>
            <div class="bg-blue-50 border border-blue-200 rounded p-3">
              <p class="text-sm text-gray-900">{{ suggestion.user_feedback }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions Card -->
      <div v-if="suggestion.status === 'pending'" class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Take Action</h3>
          <div class="space-y-3">
            <button
              @click="applySuggestion()"
              class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition"
            >
              ✓ Apply Suggestion
            </button>
            <button
              @click="showFeedbackModal = true"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition"
            >
              💭 Add Feedback & Apply
            </button>
            <button
              @click="dismissSuggestion()"
              class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition"
            >
              ✗ Dismiss
            </button>
          </div>
        </div>
      </div>

      <!-- Feedback Modal -->
      <div v-if="showFeedbackModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Add Feedback</h3>
          </div>
          <div class="px-6 py-4">
            <textarea
              v-model="feedbackText"
              placeholder="What do you think of this suggestion? Is it accurate? What could improve it?"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              rows="4"
            ></textarea>
          </div>
          <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3 rounded-b-lg">
            <button
              @click="showFeedbackModal = false"
              class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium"
            >
              Cancel
            </button>
            <button
              @click="submitFeedback()"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg"
            >
              Submit & Apply
            </button>
          </div>
        </div>
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
    suggestion: Object,
  },
  data() {
    return {
      showFeedbackModal: false,
      feedbackText: '',
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
    formatPriority(priority) {
      const priorities = {
        critical: '🔴 Critical',
        high: '🟠 High',
        normal: '🟡 Normal',
        low: '🟢 Low',
      };
      return priorities[priority] || priority;
    },
    formatStrategy(strategy) {
      const strategies = {
        discount: 'Offer Discount',
        payment_plan: 'Payment Plan',
        pause_service: 'Pause Service',
        gentle_reminder: 'Gentle Reminder',
      };
      return strategies[strategy] || strategy;
    },
    formatDateTime(dateTime) {
      return new Date(dateTime).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
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
    getPriorityBadgeClass(priority) {
      const classes = {
        critical: 'bg-red-100 text-red-800',
        high: 'bg-orange-100 text-orange-800',
        normal: 'bg-yellow-100 text-yellow-800',
        low: 'bg-green-100 text-green-800',
      };
      return classes[priority] || 'bg-gray-100 text-gray-800';
    },
    getConfidenceColor(confidence) {
      if (confidence >= 0.9) return 'bg-green-500';
      if (confidence >= 0.7) return 'bg-yellow-500';
      return 'bg-red-500';
    },
    getConfidenceTextColor(confidence) {
      if (confidence >= 0.9) return 'text-green-600';
      if (confidence >= 0.7) return 'text-yellow-600';
      return 'text-red-600';
    },
    getStrategyColor(strategy) {
      const colors = {
        discount: 'bg-blue-50 border-blue-200 text-blue-800',
        payment_plan: 'bg-purple-50 border-purple-200 text-purple-800',
        pause_service: 'bg-yellow-50 border-yellow-200 text-yellow-800',
        gentle_reminder: 'bg-gray-50 border-gray-200 text-gray-800',
      };
      return colors[strategy] || 'bg-gray-50 border-gray-200';
    },
    applySuggestion() {
      if (confirm('Apply this suggestion?')) {
        this.$inertia.post(route('admin.ai-automation.apply', this.suggestion.id), {}, {
          onSuccess: () => this.$inertia.get(route('admin.ai-automation.index')),
        });
      }
    },
    dismissSuggestion() {
      if (confirm('Dismiss this suggestion?')) {
        this.$inertia.post(route('admin.ai-automation.dismiss', this.suggestion.id), {}, {
          onSuccess: () => this.$inertia.get(route('admin.ai-automation.index')),
        });
      }
    },
    submitFeedback() {
      if (!this.feedbackText.trim()) {
        alert('Please enter feedback');
        return;
      }
      this.$inertia.post(route('admin.ai-automation.feedback', this.suggestion.id), {
        feedback: this.feedbackText,
      }, {
        onSuccess: () => {
          this.showFeedbackModal = false;
          this.$inertia.get(route('admin.ai-automation.index'));
        },
      });
    },
  },
};
</script>
