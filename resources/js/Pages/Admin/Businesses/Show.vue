<template>
    <AdminLayout>
        <Head :title="`Business: ${business.name}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <Link href="/admin/businesses" class="text-blue-600 hover:underline">← Back to Businesses</Link>
                    <h1 class="text-3xl font-bold text-gray-900 mt-2">{{ business.name }}</h1>
                    <p class="text-gray-600">{{ business.registered_name }}</p>
                </div>
                <div class="text-right">
                    <span :class="statusBadgeClass(business.status)" class="inline-block px-4 py-2 rounded-full text-sm font-medium">
                        {{ business.status }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Business Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Business Details</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Business Type</p>
                                <p class="text-gray-900 font-medium">{{ business.business_type }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Industry</p>
                                <p class="text-gray-900 font-medium">{{ business.industry || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">TIN</p>
                                <p class="text-gray-900 font-medium">{{ business.tin || 'Not Provided' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Phone</p>
                                <p class="text-gray-900 font-medium">{{ business.phone }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="text-gray-900 font-medium">{{ business.email }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">Address</p>
                                <p class="text-gray-900 font-medium">{{ business.address }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Owner Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Owner Information</h2>
                        <div v-if="!editingOwner" class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                {{ business.owner?.name ? business.owner.name.charAt(0).toUpperCase() : (business.createdByAccountant?.name ? business.createdByAccountant.name.charAt(0).toUpperCase() : '?') }}
                            </div>
                            <div>
                                <template v-if="business.owner && business.owner.name">
                                    <p class="font-medium text-gray-900">{{ business.owner.name }}</p>
                                    <p class="text-sm text-gray-600">{{ business.owner.email || '-' }}</p>
                                </template>

                                <template v-else-if="(business.createdByAccountant && business.createdByAccountant.name) || business.created_by_accountant?.name">
                                    <p class="font-medium text-gray-900">{{ business.createdByAccountant?.name || business.created_by_accountant?.name }}</p>
                                    <p class="text-sm text-gray-600">Created by accountant</p>
                                </template>

                                <template v-else>
                                    <p class="font-medium text-gray-900">Unassigned</p>
                                    <p class="text-sm text-gray-600">No owner assigned</p>
                                </template>
                            </div>
                            <div class="ml-auto">
                                <button @click="editingOwner = true" class="text-sm text-blue-600">Edit Owner</button>
                            </div>
                        </div>

                        <div v-else>
                            <form :action="`/admin/businesses/${business.id}/assign-owner`" method="post" class="space-y-3">
                                <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                <div>
                                    <label class="block text-sm font-medium">Role to assign</label>
                                    <select name="assigned_role" v-model="assignedRole" class="w-full border rounded px-3 py-2">
                                        <option value="business">Business (owner)</option>
                                        <option value="accountant">Accountant (manager)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium">User</label>
                                    <select name="assigned_user_id" class="w-full border rounded px-3 py-2">
                                        <option value="">Select user</option>
                                        <optgroup label="Business users">
                                            <option v-for="u in owners" :key="'o-'+u.id" v-if="assignedRole === 'business'" :value="u.id">{{ u.name }}</option>
                                        </optgroup>
                                        <optgroup label="Accountants">
                                            <option v-for="u in accountants" :key="'a-'+u.id" v-if="assignedRole === 'accountant'" :value="u.id">{{ u.name }}</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="flex gap-2">
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                                    <button type="button" @click="cancelEdit" class="bg-gray-200 px-4 py-2 rounded">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Staff Overview -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Staff Overview</h2>
                            <Link :href="`/admin/businesses/${business.id}/staff`" class="text-blue-600 hover:underline text-sm">View All</Link>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-blue-50 rounded p-4">
                                <p class="text-sm text-gray-600">Total Staff</p>
                                <p class="text-2xl font-bold text-blue-600">{{ business.staff_count }}</p>
                            </div>
                            <div class="bg-green-50 rounded p-4">
                                <p class="text-sm text-gray-600">Tax Returns</p>
                                <p class="text-2xl font-bold text-green-600">{{ business.tax_returns_count }}</p>
                            </div>
                            <div class="bg-purple-50 rounded p-4">
                                <p class="text-sm text-gray-600">Payments Processed</p>
                                <p class="text-2xl font-bold text-purple-600">{{ business.payments_count }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Log -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                            <Link :href="`/admin/businesses/${business.id}/activity`" class="text-blue-600 hover:underline text-sm">View All</Link>
                        </div>
                        <div class="space-y-3">
                            <div v-for="log in recentActivity" :key="log.id" class="border-l-4 border-gray-300 pl-4 py-2">
                                <p class="text-sm text-gray-900">{{ log.action }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(log.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Subscription -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Active Subscription</h3>
                        <div class="bg-blue-50 rounded p-4 mb-4">
                            <p class="text-sm text-gray-600">Plan</p>
                            <p class="text-lg font-bold text-blue-600">{{ business.subscription?.plan_name || 'None' }}</p>
                        </div>
                        <div v-if="business.subscription" class="space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Renewal Date</span>
                                <span class="font-medium">{{ formatDate(business.subscription.renews_at) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Monthly Cost</span>
                                <span class="font-medium">₦{{ formatCurrency(business.subscription.amount) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Status</span>
                                <span :class="business.subscription.status === 'active' ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                    {{ business.subscription.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-2">
                            <button @click="toggleStatus" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium text-sm">
                                {{ business.status === 'active' ? 'Suspend' : 'Activate' }}
                            </button>
                            <Link :href="`/admin/businesses/${business.id}/edit`" class="w-full block bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded font-medium text-sm text-center">
                                Edit Business
                            </Link>
                            <button @click="sendNotification" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-medium text-sm">
                                Send Message
                            </button>
                        </div>
                    </div>

                    <!-- Joined Info -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <p class="text-xs text-gray-600 mb-2">JOINED</p>
                        <p class="text-lg font-semibold text-gray-900">{{ formatDate(business.created_at) }}</p>
                        <p class="text-xs text-gray-600 mt-4">Member for {{ memberDays }} days</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    business: Object,
    recentActivity: Array,
    owners: Array,
    accountants: Array,
});

const memberDays = computed(() => {
    const created = new Date(props.business.created_at);
    const now = new Date();
    return Math.floor((now - created) / (1000 * 60 * 60 * 24));
});

const toggleStatus = () => {
    // Implement status toggle
    console.log('Toggle status');
};

const sendNotification = () => {
    // Implement send notification
    console.log('Send notification');
};

const statusBadgeClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-gray-100 text-gray-800',
        suspended: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatCurrency = (amount) => {
    if (!amount) return '0.00';
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

import { ref } from 'vue';

const editingOwner = ref(false);
const assignedRole = ref('business');
const owners = props.owners || [];
const accountants = props.accountants || [];

const cancelEdit = () => {
    editingOwner.value = false;
};
</script>
