<template>
    <div class="min-h-screen bg-gray-50">
        <slot />
        <CookieConsent />
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import CookieConsent from '@/Components/CookieConsent.vue';
</script>
