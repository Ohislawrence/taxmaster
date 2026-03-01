<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ deadline.type_label }}</h1>
                    <p class="text-gray-600 mt-1">{{ deadline.period }}</p>
                </div>
                <Link
                    href="/admin/compliance"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition"
                >
                    ← Back to Deadlines
                </Link>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 space-y-6">
                    <!-- Deadline Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Deadline Information</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Due Date</p>
                                <p class="text-lg font-bold text-gray-900 mt-1">{{ deadline.due_date }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Frequency</p>
                                <p class="text-lg font-bold text-gray-900 mt-1">{{ deadline.frequency }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Days Until/Overdue</p>
                                <p :class="deadline.days_until < 0 ? 'text-red-600' : 'text-gray-900'" class="font-bold text-lg mt-1">
                                    {{ deadline.days_until }} days
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Period</p>
                                <p class="text-gray-900 font-medium mt-1">{{ deadline.period }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Status & Details</h2>
                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <span
                                    :class="getStatusClass(deadline.status)"
                                    class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-2 capitalize"
                                >
                                    {{ deadline.status }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Urgency Level</p>
                                <span
                                    :class="getUrgencyClass(deadline.urgency)"
                                    class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-2 capitalize"
                                >
                                    {{ deadline.urgency }}
                                </span>
                            </div>
                        </div>
                        <div v-if="deadline.description" class="border-t pt-4">
                            <p class="text-sm text-gray-600 mb-2">Description</p>
                            <p class="text-gray-900">{{ deadline.description }}</p>
                        </div>
                    </div>

                    <!-- Important Dates -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Important Dates & Reminders</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Completed At</p>
                                <p class="text-gray-900 font-medium mt-1">{{ deadline.completed_at || 'Not completed' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Reminder Count</p>
                                <p class="text-gray-900 font-medium mt-1">{{ deadline.reminder_count || 0 }}</p>
                            </div>
                            <div v-if="deadline.reminded_at">
                                <p class="text-sm text-gray-600">Last Reminder Sent At</p>
                                <p class="text-gray-900 font-medium mt-1">{{ deadline.reminded_at }}</p>
                            </div>
                            <div v-if="deadline.notes" class="pt-4 border-t">
                                <p class="text-sm text-gray-600">Notes</p>
                                <p class="text-gray-900 mt-1">{{ deadline.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Business Information</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Business Name</p>
                                <p class="text-gray-900 font-medium mt-1">{{ deadline.business.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="text-gray-900 font-medium mt-1">{{ deadline.business.email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Owner</p>
                                <p class="text-gray-900 font-medium mt-1">{{ deadline.business.owner.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">TIN</p>
                                <p class="text-gray-900 font-medium mt-1">{{ deadline.business.tin || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Summary Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Summary</h3>
                        <div class="space-y-3 text-sm">
                            <div class="border-b border-blue-200 pb-3">
                                <p class="text-gray-600">Tax Type</p>
                                <p class="font-bold text-gray-900">{{ deadline.type_label }}</p>
                            </div>
                            <div class="border-b border-blue-200 pb-3">
                                <p class="text-gray-600">Status</p>
                                <span
                                    :class="getStatusClass(deadline.status)"
                                    class="inline-block px-2 py-1 rounded text-xs font-medium capitalize"
                                >
                                    {{ deadline.status }}
                                </span>
                            </div>
                            <div>
                                <p class="text-gray-600">Created At</p>
                                <p class="font-medium text-gray-900">{{ deadline.created_at }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Management</h3>
                        <div class="text-sm text-gray-600 p-4 bg-blue-50 rounded">
                            <p>View and manage completion status through the business user interface.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    deadline: Object,
})

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
