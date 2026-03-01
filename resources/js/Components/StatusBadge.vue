<template>
  <span :class="statusClass" class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap">
    {{ statusLabel }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
});

const statusClass = computed(() => {
  const classMap = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-blue-100 text-blue-800',
    accepted: 'bg-purple-100 text-purple-800',
    paid: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    refund_pending: 'bg-indigo-100 text-indigo-800',
    overdue: 'bg-red-100 text-red-800',
    completed: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    cancelled: 'bg-gray-100 text-gray-800',
    failed: 'bg-red-100 text-red-800',
  };
  return classMap[props.status] || 'bg-gray-100 text-gray-800';
});

const statusLabel = computed(() => {
  const labelMap = {
    draft: 'Draft',
    submitted: 'Submitted',
    accepted: 'Accepted',
    paid: 'Paid',
    rejected: 'Rejected',
    refund_pending: 'Refund Pending',
    overdue: 'Overdue',
    completed: 'Completed',
    pending: 'Pending',
    cancelled: 'Cancelled',
    failed: 'Failed',
  };
  return labelMap[props.status] || props.status.charAt(0).toUpperCase() + props.status.slice(1);
});
</script>
