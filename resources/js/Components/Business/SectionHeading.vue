<template>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">{{ title }}</h2>
        <p v-if="description" class="text-gray-600 mt-2">{{ description }}</p>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: null,
    },
})
</script>
