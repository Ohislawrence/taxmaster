<template>
    <AdminLayout>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-gray-900">
        {{ isEditing ? 'Edit Plan' : 'Create New Plan' }}
      </h1>
      <p class="mt-2 text-gray-600">
        {{ isEditing ? 'Update subscription plan details' : 'Add a new subscription plan' }}
      </p>
    </div>

    <!-- Form -->
    <form @submit.prevent="submitForm" class="bg-white shadow-sm rounded-lg p-6 space-y-6">
      <!-- Basic Information -->
      <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name *</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g., Professional"
            />
            <span v-if="errors.name" class="text-sm text-red-600">{{ errors.name }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
            <input
              v-model="form.slug"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g., professional"
            />
            <span v-if="errors.slug" class="text-sm text-red-600">{{ errors.slug }}</span>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Describe this plan..."
          />
          <span v-if="errors.description" class="text-sm text-red-600">{{ errors.description }}</span>
        </div>
      </div>

      <!-- Pricing -->
      <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Pricing</h2>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Price (₦) *</label>
            <input
              v-model="form.monthly_price"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="5000"
            />
            <span v-if="errors.monthly_price" class="text-sm text-red-600">{{ errors.monthly_price }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Annual Price (₦)</label>
            <input
              v-model="form.annual_price"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="50000"
            />
            <span v-if="errors.annual_price" class="text-sm text-red-600">{{ errors.annual_price }}</span>
          </div>
        </div>
      </div>

      <!-- Plan Limits -->
      <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Plan Limits</h2>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Max Staff Members *</label>
            <input
              v-model="form.max_staff_members"
              type="number"
              min="1"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="5"
            />
            <span v-if="errors.max_staff_members" class="text-sm text-red-600">{{ errors.max_staff_members }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Max Returns/Year *</label>
            <input
              v-model="form.max_returns_per_year"
              type="number"
              min="1"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="50"
            />
            <span v-if="errors.max_returns_per_year" class="text-sm text-red-600">{{ errors.max_returns_per_year }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Storage (GB) *</label>
            <input
              v-model="form.storage_gb"
              type="number"
              min="1"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="10"
            />
            <span v-if="errors.storage_gb" class="text-sm text-red-600">{{ errors.storage_gb }}</span>
          </div>
        </div>
      </div>

      <!-- Features -->
      <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Features</h2>

        <div class="space-y-3">
          <label class="flex items-center">
            <input v-model="form.ai_analysis_included" type="checkbox" class="rounded" />
            <span class="ml-3 text-sm text-gray-700">AI Analysis & Optimization</span>
          </label>

          <label class="flex items-center">
            <input v-model="form.payment_automation" type="checkbox" class="rounded" />
            <span class="ml-3 text-sm text-gray-700">Payment Automation</span>
          </label>

          <label class="flex items-center">
            <input v-model="form.priority_support" type="checkbox" class="rounded" />
            <span class="ml-3 text-sm text-gray-700">Priority Support</span>
          </label>

          <label class="flex items-center">
            <input v-model="form.custom_branding" type="checkbox" class="rounded" />
            <span class="ml-3 text-sm text-gray-700">Custom Branding</span>
          </label>
        </div>
      </div>

      <!-- Status & Display -->
      <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Display Settings</h2>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Display Order *</label>
            <input
              v-model="form.display_order"
              type="number"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="1"
            />
            <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
            <span v-if="errors.display_order" class="text-sm text-red-600">{{ errors.display_order }}</span>
          </div>

          <div>
            <label class="flex items-center mt-6">
              <input v-model="form.is_active" type="checkbox" class="rounded" />
              <span class="ml-3 text-sm text-gray-700">Active (show on pricing page)</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-between pt-6 border-t">
        <Link href="/admin/plans" class="text-gray-600 hover:text-gray-900 font-medium">
          Cancel
        </Link>

        <button
          type="submit"
          :disabled="processing"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400"
        >
          {{ processing ? 'Saving...' : (isEditing ? 'Update Plan' : 'Create Plan') }}
        </button>
      </div>
    </form>
  </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  plan: Object,
  isEditing: Boolean,
});

const processing = ref(false);
const errors = ref({});

const form = useForm({
  name: props.plan?.name || '',
  slug: props.plan?.slug || '',
  description: props.plan?.description || '',
  monthly_price: props.plan?.monthly_price || '',
  annual_price: props.plan?.annual_price || '',
  max_staff_members: props.plan?.max_staff_members || 1,
  max_returns_per_year: props.plan?.max_returns_per_year || 12,
  storage_gb: props.plan?.storage_gb || 10,
  ai_analysis_included: props.plan?.ai_analysis_included || false,
  payment_automation: props.plan?.payment_automation || false,
  priority_support: props.plan?.priority_support || false,
  custom_branding: props.plan?.custom_branding || false,
  features: props.plan?.features || [],
  is_active: props.plan?.is_active ?? true,
  display_order: props.plan?.display_order || 0,
});

// Auto-generate slug from name
watch(
  () => form.name,
  (newValue) => {
    if (!props.isEditing) {
      form.slug = newValue
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
    }
  }
);

const submitForm = () => {
  processing.value = true;
  errors.value = {};

  const url = props.isEditing
    ? `/admin/plans/${props.plan.id}`
    : '/admin/plans';

  const method = props.isEditing ? 'put' : 'post';

  form[method](url, {
    onSuccess: () => {
      processing.value = false;
    },
    onError: (err) => {
      processing.value = false;
      errors.value = err;
    },
  });
};
</script>
