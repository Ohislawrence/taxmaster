<template>
    <AdminLayout>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Subscription Plans</h1>
        <p class="mt-2 text-gray-600">Manage subscription plans and pricing tiers</p>
      </div>
      <Link href="/admin/plans/create" class="btn btn-primary">
        Create Plan
      </Link>
    </div>

    <!-- Success Message -->
    <div v-if="page.props.flash?.success" class="alert alert-success">
      {{ page.props.flash.success }}
    </div>

    <!-- Error Message -->
    <div v-if="page.props.flash?.error" class="alert alert-error">
      {{ page.props.flash.error }}
    </div>

    <!-- Plans Table -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Plan Name</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Monthly Price</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Features</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Subscriptions</th>
            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="plan in plans.data" :key="plan.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <div>
                <p class="font-semibold text-gray-900">{{ plan.name }}</p>
                <p class="text-sm text-gray-500">{{ plan.slug }}</p>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-900">
              <span class="font-semibold">₦{{ formatPrice(plan.monthly_price) }}</span>
              <span v-if="plan.annual_price" class="text-sm text-gray-500 ml-2">
                (₦{{ formatPrice(plan.annual_price) }}/year)
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1">
                <span v-if="plan.ai_analysis_included" class="badge badge-sm badge-blue">AI Analysis</span>
                <span v-if="plan.payment_automation" class="badge badge-sm badge-blue">Payment Auto</span>
                <span v-if="plan.priority_support" class="badge badge-sm badge-blue">Priority Support</span>
                <span v-if="plan.custom_branding" class="badge badge-sm badge-blue">Custom Branding</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <span v-if="plan.is_active" class="badge badge-success">Active</span>
              <span v-else class="badge badge-gray">Inactive</span>
            </td>
            <td class="px-6 py-4 text-gray-900">
              {{ plan.subscriptions_count || 0 }}
            </td>
            <td class="px-6 py-4 text-right space-x-3">
              <Link :href="`/admin/plans/${plan.id}/edit`" class="text-blue-600 hover:text-blue-900 font-medium">
                Edit
              </Link>
              <button
                @click="deletePlan(plan.id)"
                class="text-red-600 hover:text-red-900 font-medium"
              >
                Delete
              </button>
            </td>
          </tr>
          <tr v-if="plans.data.length === 0">
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
              No plans found. <Link href="/admin/plans/create" class="text-blue-600">Create one</Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="plans.links" class="flex items-center justify-between">
      <div class="text-sm text-gray-700">
        Showing {{ plans.from }} to {{ plans.to }} of {{ plans.total }} plans
      </div>
      <div class="space-x-2">
        <Link
          v-for="link in plans.links"
          :key="link.label"
          :href="link.url"
          :class="[
            'px-3 py-2 rounded text-sm font-medium',
            link.active
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
          v-html="link.label"
        />
      </div>
    </div>
  </div>
</AdminLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const page = usePage();
const plans = computed(() => page.props.plans || { data: [], links: null, from: 0, to: 0, total: 0 });

const formatPrice = (price) => {
  if (!price && price !== 0) return '0';
  return parseFloat(price).toLocaleString('en-NG', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
};

const deletePlan = (planId) => {
  if (confirm('Are you sure you want to delete this plan?')) {
    // Make delete request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/plans/${planId}`;
    form.innerHTML = `
      <input type="hidden" name="_token" value="${page.props.csrf_token || ''}">
      <input type="hidden" name="_method" value="DELETE">
    `;
    document.body.appendChild(form);
    form.submit();
  }
};
</script>

<style scoped>
.badge {
  @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
}

.badge-sm {
  @apply px-2 py-1;
}

.badge-success {
  @apply bg-green-100 text-green-800;
}

.badge-blue {
  @apply bg-blue-100 text-blue-800;
}

.badge-gray {
  @apply bg-gray-100 text-gray-800;
}

.btn {
  @apply px-4 py-2 rounded-lg font-semibold text-sm transition;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}

.alert {
  @apply px-4 py-3 rounded-lg;
}

.alert-success {
  @apply bg-green-50 text-green-800 border border-green-200;
}

.alert-error {
  @apply bg-red-50 text-red-800 border border-red-200;
}
</style>
