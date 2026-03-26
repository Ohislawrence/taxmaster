<template>
  <component :is="Forward" v-bind="$attrs" v-on="$listeners" />
</template>

<script>
import Forward from '@/Pages/Business/Invoices/InvoiceShow.vue';
export default Forward;
</script>
