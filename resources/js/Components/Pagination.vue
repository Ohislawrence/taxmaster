<template>
  <nav class="flex items-center gap-1">
    <template v-for="link in links" :key="`${link.label}-${link.url}`">
      <!-- Previous Button -->
      <Link
        v-if="link.label === '&laquo; Previous'"
        :href="link.url"
        :disabled="!link.url"
        class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
      >
        <i class="fas fa-chevron-left"></i>
      </Link>

      <!-- Next Button -->
      <Link
        v-else-if="link.label === 'Next &raquo;'"
        :href="link.url"
        :disabled="!link.url"
        class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
      >
        <i class="fas fa-chevron-right"></i>
      </Link>

      <!-- Page Numbers -->
      <template v-else>
        <Link
          v-if="link.url"
          :href="link.url"
          :class="link.active ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'"
          class="px-3 py-2 rounded-lg text-sm font-medium transition"
        >
          {{ extractPageNumber(link.label) }}
        </Link>
        <span
          v-else
          :class="link.active ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700'"
          class="px-3 py-2 rounded-lg text-sm font-medium"
        >
          {{ extractPageNumber(link.label) }}
        </span>
      </template>
    </template>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: {
    type: Array,
    required: true,
  },
});

const extractPageNumber = (label) => {
  const number = parseInt(label);
  return isNaN(number) ? label : number;
};
</script>
