<template>
  <AdminLayout>
    <div class="space-y-6 max-w-4xl">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Affiliate Commission Settings</h1>
        <p class="mt-2 text-gray-600">Configure global affiliate commission rules and per-plan overrides.</p>
      </div>

      <div v-if="page.props.flash?.success" class="alert alert-success">{{ page.props.flash.success }}</div>
      <div v-if="page.props.flash?.error" class="alert alert-error">{{ page.props.flash.error }}</div>

      <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">Applies To</label>
            <div class="mt-2 grid grid-cols-2 gap-4">
              <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition" :class="form.applies_to === 'global' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                <input v-model="form.applies_to" type="radio" value="global" class="w-4 h-4 accent-blue-600" />
                <div class="ml-3">
                  <p class="font-semibold text-gray-900">Global</p>
                  <p class="text-sm text-gray-600">Applies to all plans unless overridden</p>
                </div>
              </label>

              <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition" :class="form.applies_to === 'plan' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                <input v-model="form.applies_to" type="radio" value="plan" class="w-4 h-4 accent-blue-600" />
                <div class="ml-3">
                  <p class="font-semibold text-gray-900">Specific Plan</p>
                  <p class="text-sm text-gray-600">Apply this rule only to a selected plan</p>
                </div>
              </label>
            </div>
          </div>

          <div v-if="form.applies_to === 'plan'">
            <label class="block text-sm font-medium text-gray-700">Plan</label>
            <select v-model="form.plan_slug" class="mt-1 block w-full rounded border-gray-200">
              <option value="">Select a plan</option>
              <option v-for="p in plans" :key="p.slug" :value="p.slug">{{ p.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Type</label>
            <select v-model="form.type" class="mt-1 block w-full rounded border-gray-200">
              <option value="percentage">Percentage</option>
              <option value="fixed">Fixed Amount</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Mode</label>
            <select v-model="form.mode" class="mt-1 block w-full rounded border-gray-200">
              <option value="one_off">One-off</option>
              <option value="recurring_1_year">Recurring (1 year)</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Value</label>
            <input type="number" step="0.01" v-model.number="form.value" class="mt-1 block w-full rounded border-gray-200" />
            <p class="text-xs text-gray-500 mt-1">If type is percentage, enter percent (e.g. 10 for 10%). If fixed, enter amount.</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Description (optional)</label>
            <textarea v-model="form.description" class="mt-1 block w-full rounded border-gray-200" rows="3"></textarea>
          </div>

          <div class="flex items-center space-x-2">
            <input type="checkbox" id="active" v-model="form.active" />
            <label for="active" class="text-sm text-gray-700">Active</label>
          </div>

          <div class="flex gap-4 pt-4 border-t">
            <button type="submit" :disabled="processing" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400">{{ processing ? 'Saving...' : 'Save Settings' }}</button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const page = usePage();
const rule = page.props.rule || {};
const plans = page.props.plans || [];

const form = ref({
  applies_to: rule.applies_to || 'global',
  plan_slug: rule.plan_slug || '',
  type: rule.type || 'percentage',
  mode: rule.mode || 'one_off',
  value: Number(rule.value || 0),
  active: rule.active ?? true,
  description: rule.meta?.description || '',
});

const processing = ref(false);

const submit = async () => {
  processing.value = true;
  try {
    await axios.put('/admin/affiliate/settings', form.value);
    window.location.reload();
  } catch (e) {
    console.error(e);
    processing.value = false;
  }
};

const plansRef = ref(plans);
</script>

<style scoped>
.alert { @apply px-4 py-3 rounded-lg; }
.alert-success { @apply bg-green-50 text-green-800 border border-green-200; }
.alert-error { @apply bg-red-50 text-red-800 border border-red-200; }
</style>
