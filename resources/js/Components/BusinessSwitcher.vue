<template>
  <div class="inline-block">
    <select v-model="selected" @change="switchBusiness" class="select select-bordered">
      <option v-if="!businesses || businesses.length===0" disabled>No businesses</option>
      <option v-for="b in businesses" :key="b.id" :value="b.id">{{ b.name }}</option>
    </select>
  </div>
</template>

<script>
import { Inertia } from '@inertiajs/inertia';

export default {
  props: {
    businesses: {
      type: Array,
      default: () => [],
    },
    currentBusiness: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      selected: this.currentBusiness ? this.currentBusiness.id : (this.businesses[0]?.id ?? null),
    };
  },
  methods: {
    switchBusiness() {
      Inertia.post(route('business.switch'), { business_id: this.selected });
    },
  },
};
</script>
