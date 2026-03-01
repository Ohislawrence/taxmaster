<template>
  <BusinessLayout>
    <Head title="Edit CIT Return" />

    <div class="space-y-6 max-w-3xl mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Edit CIT Return</h1>
          <p class="mt-2 text-gray-600">Update your corporate income tax return ({{ citReturn.period }})</p>
        </div>
      </div>

      <!-- Info Card -->
      <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
          <div class="flex-shrink-0">
            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">i</span>
          </div>
          <div class="ml-3">
            <p class="text-sm text-blue-700">
              <strong>Note:</strong> Only draft returns can be edited. Submit the changes when complete.
            </p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white shadow rounded-lg">
        <div class="px-6 py-8 space-y-6">
          <!-- Period -->
          <div>
            <label for="period" class="block text-sm font-medium text-gray-700">Period <span class="text-red-500">*</span></label>
            <input
              v-model="form.period"
              type="month"
              id="period"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.period }"
            />
            <p v-if="errors.period" class="mt-1 text-sm text-red-600">{{ errors.period }}</p>
          </div>

          <!-- Gross Profit -->
          <div>
            <label for="gross_profit" class="block text-sm font-medium text-gray-700">Gross Profit <span class="text-red-500">*</span></label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₦</span>
              <input
                v-model="form.gross_profit"
                type="number"
                id="gross_profit"
                placeholder="0.00"
                step="0.01"
                class="pl-8 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.gross_profit }"
                @input="calculateTax"
              />
            </div>
            <p v-if="errors.gross_profit" class="mt-1 text-sm text-red-600">{{ errors.gross_profit }}</p>
          </div>

          <!-- Adjustments -->
          <div>
            <label for="adjustments" class="block text-sm font-medium text-gray-700">Adjustments</label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₦</span>
              <input
                v-model="form.adjustments"
                type="number"
                id="adjustments"
                placeholder="0.00"
                step="0.01"
                class="pl-8 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
                @input="calculateTax"
              />
            </div>
            <p class="mt-1 text-xs text-gray-500">Capital allowances, losses, etc.</p>
          </div>

          <!-- Assessable Income (Auto-calculated) -->
          <div>
            <label for="assessable_income" class="block text-sm font-medium text-gray-700">Taxable Income</label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₦</span>
              <input
                v-model="calculatedTax.taxableIncome"
                type="number"
                id="assessable_income"
                disabled
                class="pl-8 block w-full bg-gray-100 border-gray-300 rounded-md text-gray-600"
              />
            </div>
            <p class="mt-1 text-xs text-gray-500">Auto-calculated: Gross Profit - Deductions</p>
          </div>

          <!-- Divider -->
          <hr class="my-6" />

          <!-- Tax Calculation Preview -->
          <div class="bg-gray-50 p-4 rounded">
            <h3 class="text-sm font-medium text-gray-900 mb-4">Tax Calculation</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">30% CIT on ₦{{ formatCurrency(calculatedTax.taxableIncome) }}</span>
                <span class="font-medium text-gray-900">₦{{ formatCurrency(calculatedTax.cit) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Minimum Tax (0.5% of Gross)</span>
                <span class="font-medium text-gray-900">₦{{ formatCurrency(calculatedTax.minimumTax) }}</span>
              </div>
              <div class="border-t pt-3">
                <div class="flex justify-between">
                  <span class="text-gray-900 font-medium">Tax Due (Higher of 30% CIT or Minimum Tax)</span>
                  <span class="text-lg font-bold text-blue-600">₦{{ formatCurrency(calculatedTax.taxDue) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Reviewer (Accountant) -->
          <div>
            <label for="reviewer_id" class="block text-sm font-medium text-gray-700">Reviewer (Accountant)</label>
            <select
              v-model="form.reviewer_id"
              id="reviewer_id"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">-- Select Accountant (Optional) --</option>
              <option v-for="accountant in accountants" :key="accountant.id" :value="accountant.id">
                {{ accountant.name }}
              </option>
            </select>
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea
              v-model="form.notes"
              id="notes"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="Add any additional notes or context..."
            ></textarea>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex items-center justify-between space-x-3">
          <Link :href="route('business.cit.show', citReturn.id)" class="text-gray-700 hover:text-gray-900">Cancel</Link>
          <button
            type="submit"
            :disabled="processing"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
          >
            <i v-if="processing" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-save mr-2"></i>
            {{ processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { computed } from 'vue';

const props = defineProps({
  citReturn: Object,
  accountants: Array,
});

const form = useForm({
  period: props.citReturn.period,
  gross_profit: props.citReturn.gross_profit,
  adjustments: props.citReturn.adjustments,
  assessable_income: props.citReturn.assessable_income,
  reviewer_id: props.citReturn.reviewer_id || '',
  notes: props.citReturn.notes || '',
});

const errors = computed(() => form.errors);

const calculatedTax = computed(() => {
  const grossProfit = parseFloat(form.gross_profit) || 0;
  const adjustments = parseFloat(form.adjustments) || 0;
  const taxableIncome = grossProfit + adjustments;

  const cit = taxableIncome * 0.30;
  const minimumTax = grossProfit * 0.005;
  const taxDue = Math.max(cit, minimumTax);

  return {
    taxableIncome: taxableIncome,
    cit: cit,
    minimumTax: minimumTax,
    taxDue: taxDue,
  };
});

const calculateTax = () => {
  // Tax calculations happen automatically via computed property
  // This function is called on input change for form reactivity
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-NG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

const submit = () => {
  form.put(route('business.cit.update', props.citReturn.id));
};
</script>
