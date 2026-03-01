<template>
    <AdminLayout>
        <Head title="Admin Dashboard" />

        <div class="space-y-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-600 mt-2">Welcome back, {{ auth.user.name }}! Here's your platform overview.</p>
            </div>

            <!-- Key Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Businesses -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Total Businesses</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total_businesses }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5.581m0 0H9m5.581 0a2 2 0 100-4H9m0 4a2 2 0 110-4m0 0H7a2 2 0 00-2 2v3m2-3V7a2 2 0 012-2h5.581a2 2 0 011.915 1.264m0 0H20" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Active Users -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Total Users</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total_users }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.593M9 21h6" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Pending Payments -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Pending Payments</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(stats.pending_payments) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Total Revenue (30d)</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">₦{{ formatCurrency(stats.total_revenue) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8L5.257 19.393A2 2 0 005 18.21V4a2 2 0 012-2h10a2 2 0 012 2v14.211a2 2 0 01-.757 1.563z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Users -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Users</h2>
                        <Link href="/admin/users" class="text-blue-600 hover:underline text-sm">View All</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Name</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Email</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Role</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in recentUsers" :key="user.id" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium text-gray-900">{{ user.name }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ user.email }}</td>
                                    <td class="py-3 px-4">
                                        <span :class="user.roles[0]?.name === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'" class="px-3 py-1 rounded-full text-xs font-medium">
                                            {{ user.roles[0]?.name || 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 text-xs">{{ formatDate(user.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h2>
                    <div class="space-y-3">
                        <Link href="/admin/users/create" class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium text-center transition">
                            + New User
                        </Link>
                        <Link href="/admin/businesses" class="block w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium text-center transition">
                            View Businesses
                        </Link>
                        <Link href="/admin/reports/revenue" class="block w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg font-medium text-center transition">
                            View Reports
                        </Link>
                        <Link href="/admin/subscriptions" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg font-medium text-center transition">
                            Subscriptions
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Pending Tax Returns -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Pending Tax Returns</h2>
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">{{ stats.pending_returns }} Due</span>
                </div>
                <div v-if="pendingReturns.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Business</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Type</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Period</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Due Date</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tr in pendingReturns" :key="tr.id" class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-900">{{ tr.business.name }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ tr.return_type }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ tr.period }}</td>
                                <td class="py-3 px-4 text-gray-600 text-xs">{{ formatDate(tr.due_date) }}</td>
                                <td class="py-3 px-4">
                                    <span :class="getDueDateClass(tr.due_date)" class="px-3 py-1 rounded-full text-xs font-medium">
                                        {{ getDueDateStatus(tr.due_date) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    <p>No pending tax returns. Great work! 🎉</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const page = usePage();
const auth = computed(() => page.props.auth);

defineProps({
    stats: Object,
    recentUsers: Array,
    pendingReturns: Array,
});

const formatCurrency = (amount) => {
    if (!amount) return '0.00';
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getDueDateStatus = (dueDate) => {
    const now = new Date();
    const due = new Date(dueDate);
    const daysUntilDue = Math.floor((due - now) / (1000 * 60 * 60 * 24));

    if (daysUntilDue < 0) return 'Overdue';
    if (daysUntilDue <= 7) return `Due in ${daysUntilDue}d`;
    return 'Upcoming';
};

const getDueDateClass = (dueDate) => {
    const now = new Date();
    const due = new Date(dueDate);
    const daysUntilDue = Math.floor((due - now) / (1000 * 60 * 60 * 24));

    if (daysUntilDue < 0) return 'bg-red-100 text-red-800';
    if (daysUntilDue <= 7) return 'bg-orange-100 text-orange-800';
    return 'bg-green-100 text-green-800';
};
</script>
