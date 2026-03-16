<template>
  <div v-if="$page.props.current_business && $page.props.current_business.billing_managed_by_platform === false" class="mb-6">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <p class="font-semibold text-yellow-800">Billing managed externally</p>
          <p class="text-sm text-yellow-700">This business is billed outside the platform. To enable on-platform billing, the client must claim ownership or an admin must enable billing.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BillingGuardNotice',
};
</script>
