<template>
    <span
        :class="[
            'inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide',
            colorClass,
        ]"
    >
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    variant: {
        type: String,
        enum: ['urgency', 'payment', 'filing', 'sync'],
        default: 'urgency',
    },
})

const colorClass = computed(() => {
    if (props.variant === 'urgency') {
        const urgencyMap = {
            'overdue': 'bg-red-100 text-red-800',
            'urgent': 'bg-orange-100 text-orange-800',
            'this-week': 'bg-yellow-100 text-yellow-800',
            'upcoming': 'bg-blue-100 text-blue-800',
            'today': 'bg-red-100 text-red-800',
        }
        return urgencyMap[props.status] || 'bg-gray-100 text-gray-800'
    }

    if (props.variant === 'payment') {
        const paymentMap = {
            'paid': 'bg-green-100 text-green-800',
            'pending': 'bg-yellow-100 text-yellow-800',
            'overdue': 'bg-red-100 text-red-800',
            'failed': 'bg-red-100 text-red-800',
        }
        return paymentMap[props.status] || 'bg-gray-100 text-gray-800'
    }

    if (props.variant === 'filing') {
        const filingMap = {
            'draft': 'bg-gray-100 text-gray-800',
            'submitted': 'bg-blue-100 text-blue-800',
            'completed': 'bg-green-100 text-green-800',
            'rejected': 'bg-red-100 text-red-800',
        }
        return filingMap[props.status] || 'bg-gray-100 text-gray-800'
    }

    if (props.variant === 'sync') {
        const syncMap = {
            'synced': 'bg-green-100 text-green-800',
            'syncing': 'bg-blue-100 text-blue-800',
            'pending': 'bg-yellow-100 text-yellow-800',
            'error': 'bg-red-100 text-red-800',
        }
        return syncMap[props.status] || 'bg-gray-100 text-gray-800'
    }

    return 'bg-gray-100 text-gray-800'
})

const label = computed(() => {
    return props.status.replace(/-/g, ' ').toUpperCase()
})
</script>
