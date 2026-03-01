<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Compliance Deadlines</h1>
                    <p class="text-gray-600 mt-1">Monitor tax compliance deadlines across all businesses</p>
                </div>
                <Link
                    href="/admin/compliance/reports/overdue"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-medium transition flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Overdue Report</span>
                </Link>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Total Deadlines</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_deadlines }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Overdue</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ stats.overdue }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Due This Week</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ stats.due_this_week }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Due This Month</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">{{ stats.due_this_month }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-gray-600 text-sm font-medium">Completed</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ stats.completed }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Type</label>
                        <select
                            v-model="filters.deadline_type"
                            @change="applyFilters"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                        >
                            <option value="">All Tax Types</option>
                            <option v-for="(label, type) in deadlineTypes" :key="type" :value="type">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                        >
                            <option value="">All Statuses</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business</label>
                        <select
                            v-model="filters.business_id"
                            @change="applyFilters"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                        >
                            <option value="">All Businesses</option>
                            <option v-for="business in businesses" :key="business.id" :value="business.id">
                                {{ business.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="resetFilters"
                            class="w-full px-4 py-2 border border-gray-300 hover:bg-gray-50 rounded-lg font-medium transition"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Compliance Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="deadlines.data && deadlines.data.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Business</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Tax Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Filing Deadline</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Days Until</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Urgency</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="deadline in deadlines.data"
                                :key="deadline.id"
                                class="hover:bg-gray-50 transition"
                            >
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ deadline.business.name }}</p>
                                        <p class="text-xs text-gray-600">{{ deadline.business.owner_name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ deadline.type_label }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">{{ deadline.period }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ deadline.due_date }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <p
                                        :class="deadline.days_until < 0 ? 'text-red-600 font-bold' : deadline.days_until <= 7 ? 'text-yellow-600 font-bold' : 'text-gray-600'"
                                    >
                                        {{ deadline.days_until }} days
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="getUrgencyClass(deadline.urgency)"
                                        class="px-3 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ deadline.urgency }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="getStatusClass(deadline.status)"
                                        class="px-3 py-1 rounded-full text-xs font-medium capitalize"
                                    >
                                        {{ deadline.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="`/admin/compliance/${deadline.id}`"
                                        class="text-blue-600 hover:text-blue-700 font-medium text-sm"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-4 text-gray-600">No compliance deadlines found</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="deadlines.links && deadlines.links.length > 3" class="flex justify-center space-x-2">
                <Link
                    v-for="link in deadlines.links"
                    :key="link.url"
                    :href="link.url"
                    :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-900 border border-gray-300 hover:bg-gray-50'"
                    class="px-4 py-2 rounded-lg transition"
                    v-html="link.label"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    deadlines: Object,
    stats: Object,
    businesses: Array,
    deadlineTypes: Object,
    filters: Object,
})

const filters = ref({
    deadline_type: props.filters?.deadline_type || '',
    status: props.filters?.status || '',
    business_id: props.filters?.business_id || '',
    year: props.filters?.year || '',
})

const applyFilters = () => {
    window.location.search = new URLSearchParams(filters.value).toString()
}

const resetFilters = () => {
    filters.value = {
        deadline_type: '',
        status: '',
        business_id: '',
        year: '',
    }
    applyFilters()
}

const getStatusClass = (status) => {
    const classes = {
        'completed': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'overdue': 'bg-red-100 text-red-800',
        'dismissed': 'bg-gray-100 text-gray-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getUrgencyClass = (urgency) => {
    const classes = {
        'critical': 'bg-red-100 text-red-800',
        'urgent': 'bg-orange-100 text-orange-800',
        'high': 'bg-yellow-100 text-yellow-800',
        'medium': 'bg-blue-100 text-blue-800',
        'low': 'bg-gray-100 text-gray-800',
    }
    return classes[urgency] || 'bg-gray-100 text-gray-800'
}
</script>
