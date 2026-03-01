<template>
    <BusinessLayout>
        <Head title="ComplianceDashboard" />

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Tax Compliance Dashboard</h2>

            <!-- Compliance Rate -->
            <div class="mb-8 p-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium opacity-90">Overall Compliance Rate</h3>
                        <p class="text-4xl font-bold mt-2">{{ complianceStatus?.compliance_rate || 0 }}%</p>
                    </div>
                    <div class="text-6xl opacity-20">
                        ✓
                    </div>
                </div>
            </div>

            <!-- Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-600 font-medium">Paid</p>
                            <p class="text-2xl font-bold text-green-700 mt-1">{{ complianceStatus?.paid_count || 0 }}</p>
                        </div>
                        <div class="text-3xl text-green-500">✓</div>
                    </div>
                </div>

                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-yellow-600 font-medium">Pending</p>
                            <p class="text-2xl font-bold text-yellow-700 mt-1">{{ complianceStatus?.pending_count || 0 }}</p>
                        </div>
                        <div class="text-3xl text-yellow-500">⏳</div>
                    </div>
                </div>

                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-red-600 font-medium">Overdue</p>
                            <p class="text-2xl font-bold text-red-700 mt-1">{{ complianceStatus?.overdue_count || 0 }}</p>
                        </div>
                        <div class="text-3xl text-red-500">!</div>
                    </div>
                </div>

                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-600 font-medium">Upcoming</p>
                            <p class="text-2xl font-bold text-blue-700 mt-1">{{ complianceStatus?.upcoming_count || 0 }}</p>
                        </div>
                        <div class="text-3xl text-blue-500">📅</div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Deadlines -->
            <div class="mt-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Upcoming Deadlines (Next 30 Days)</h3>
                
                <div v-if="!upcomingDeadlines || upcomingDeadlines.length === 0" class="text-gray-500 text-center py-8">
                    <p>No upcoming deadlines</p>
                </div>

                <div v-else class="space-y-3">
                    <div 
                        v-for="deadline in upcomingDeadlines" 
                        :key="deadline.id"
                        class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50"
                        :class="{
                            'border-red-300 bg-red-50': deadline.days_until < 7,
                            'border-yellow-300 bg-yellow-50': deadline.days_until >= 7 && deadline.days_until < 14,
                            'border-gray-200': deadline.days_until >= 14
                        }"
                    >
                        <div>
                            <h4 class="font-medium text-gray-900">{{ deadline.tax_type?.name }}</h4>
                            <p class="text-sm text-gray-600">
                                {{ deadline.filing_type }} Filing
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Due: {{ formatDate(deadline.due_date) }} ({{ deadline.days_until }} days)
                            </p>
                        </div>
                        <div>
                            <span 
                                v-if="deadline.days_until < 7"
                                class="px-3 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full"
                            >
                                Urgent
                            </span>
                            <span 
                                v-else-if="deadline.days_until < 14"
                                class="px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-700 rounded-full"
                            >
                                Soon
                            </span>
                            <span 
                                v-else
                                class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full"
                            >
                                Upcoming
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reminders -->
            <div v-if="reminders && reminders.length > 0" class="mt-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Active Reminders</h3>
                <div class="space-y-2">
                    <div 
                        v-for="reminder in reminders" 
                        :key="reminder.id"
                        class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded"
                    >
                        <div class="flex items-center">
                            <span class="text-blue-500 mr-3">🔔</span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ reminder.message }}</p>
                                <p class="text-xs text-gray-600">Reminder on: {{ formatDate(reminder.reminder_date) }}</p>
                            </div>
                        </div>
                        <button 
                            @click="dismissReminder(reminder.id)"
                            class="text-sm text-gray-500 hover:text-gray-700"
                        >
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

defineProps({
    complianceStatus: {
        type: Object,
        default: () => ({}),
    },
    upcomingDeadlines: {
        type: Array,
        default: () => [],
    },
    reminders: {
        type: Array,
        default: () => [],
    },
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-NG', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};

const dismissReminder = (reminderId) => {
    // Implement dismiss functionality
    console.log('Dismiss reminder:', reminderId);
};
</script>
