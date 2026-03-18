<template>
  <div v-if="risk" class="inline-flex items-center gap-3 px-3 py-2 rounded-lg" :class="bgClass">
    <span class="text-lg">{{ risk.emoji }}</span>
    <div class="text-sm">
      <div class="font-semibold">{{ label }}</div>
      <div class="text-xs text-gray-500">Score: {{ risk.score }}</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  risk: { type: Object, default: null },
});

const label = computed(() => {
  if (!props.risk) return '';
  if (props.risk.level === 'compliant') return 'Compliant';
  if (props.risk.level === 'at_risk') return 'At Risk';
  if (props.risk.level === 'high_risk') return 'High Risk';
  return props.risk.level;
});

const bgClass = computed(() => {
  if (!props.risk) return '';
  if (props.risk.level === 'compliant') return 'bg-emerald-50 border border-emerald-100 text-emerald-700';
  if (props.risk.level === 'at_risk') return 'bg-amber-50 border border-amber-100 text-amber-700';
  if (props.risk.level === 'high_risk') return 'bg-rose-50 border border-rose-100 text-rose-700';
  return '';
});
</script>

<style scoped>
.inline-flex { align-items: center; }
</style>
