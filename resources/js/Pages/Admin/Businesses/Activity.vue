<template>
    <AdminLayout>
        <Head :title="`Activity Log: ${business.name}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <Link :href="`/admin/businesses/${business.id}`" class="text-blue-600 hover:underline">← Back to Business</Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-4">{{ business.name }} - Activity Log</h1>
                <p class="text-gray-600">Complete audit trail of all business actions</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Action Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                        <select v-model="filters.action" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2">
                            <option value="">All Actions</option>
                            <option value="created">Created</option>
                            <option value="updated">Updated</option>
                            <option value="deleted">Deleted</option>
                            <option value="payment_received">Payment Received</option>
                            <option value="tax_return_submitted">Tax Return Submitted</option>
                        </select>
                    </div>

                    <!-- From Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                        <input v-model="filters.from_date" type="date" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2" />
                    </div>

                    <!-- To Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <input v-model="filters.to_date" type="date" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2" />
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="space-y-4">
                <div v-for="log in activityLogs.data" :key="log.id" class="bg-white rounded-lg shadow p-6 border-l-4" :class="getActionColor(log.action)">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="inline-block px-3 py-1 bg-gray-100 rounded-full text-xs font-medium text-gray-700">
                                    {{ formatActionLabel(log.action) }}
                                </span>
                                <span class="text-sm text-gray-600">{{ log.description }}</span>
                            </div>
                            <p class="text-gray-900 font-medium mt-2">{{ log.details || 'No additional details' }}</p>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-sm font-medium text-gray-900">{{ formatDate(log.created_at) }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ formatTime(log.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Changed Fields (if applicable) -->
                    <div v-if="log.changes" class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-700 mb-2">CHANGES:</p>
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div v-for="(change, key) in log.changes" :key="key" class="flex justify-between">
                                <span class="text-gray-600">{{ key }}:</span>
                                <span class="text-gray-900">
                                    <span class="line-through text-red-600">{{ change.old }}</span>
                                    <span class="text-green-600">→ {{ change.new }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="activityLogs.links.length > 3" class="flex justify-center space-x-2 mt-8">
                <Link v-for="link in activityLogs.links" :key="link.url || link.label" :href="link.url || '#'" :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-600'" class="px-3 py-2 rounded text-sm">
                    {{ link.label }}
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    business: Object,
    activityLogs: Object,
});

const filters = ref({
    action: '',
    from_date: '',
    to_date: '',
});

const applyFilters = () => {
    // Implement filter logic
};

const getActionColor = (action) => {
    const colors = {
        created: 'border-green-500',
        updated: 'border-blue-500',
        deleted: 'border-red-500',
        payment_received: 'border-purple-500',
        tax_return_submitted: 'border-orange-500',
    };
    return colors[action] || 'border-gray-500';
};

const formatActionLabel = (action) => {
    const labels = {
        created: 'Created',
        updated: 'Updated',
        deleted: 'Deleted',
        payment_received: 'Payment',
        tax_return_submitted: 'Tax Submission',
    };
    return labels[action] || action;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('en-NG', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
};
</script>
