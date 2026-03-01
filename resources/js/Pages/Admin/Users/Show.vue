<template>
    <AdminLayout>
        <Head :title="`User: ${user.name}`" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <Link href="/admin/users" class="text-blue-600 hover:underline">← Back to Users</Link>
                        <h1 class="text-3xl font-bold text-gray-900 mt-4">{{ user.name }}</h1>
                        <p class="text-gray-600 mt-1">{{ user.email }}</p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="`/admin/users/${user.id}/edit`" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-medium">
                            Edit User
                        </Link>
                        <button @click="deleteUser" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-medium">
                            Delete
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- User Info -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">User Information</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-600">Name</label>
                                <p class="text-gray-900">{{ user.name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Email</label>
                                <p class="text-gray-900">{{ user.email }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Status</label>
                                <p :class="user.email_verified_at ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                    {{ user.email_verified_at ? 'Active' : 'Suspended' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Joined</label>
                                <p class="text-gray-900">{{ formatDate(user.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Role & Permissions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Role & Permissions</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-600">Current Role</label>
                                <span :class="roleClass(userRole)" class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-1">
                                    {{ userRole || 'No Role' }}
                                </span>
                            </div>
                            <div v-if="userRole === 'admin'" class="bg-red-50 border border-red-200 rounded p-3">
                                <p class="text-sm text-red-800">
                                    <strong>Admin Role:</strong> Full system access including user management, business oversight, and financial reports.
                                </p>
                            </div>
                            <div v-if="userRole === 'business'" class="bg-blue-50 border border-blue-200 rounded p-3">
                                <p class="text-sm text-blue-800">
                                    <strong>Business Role:</strong> Can manage own business, tax returns, staff, and payments.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Info (if applicable) -->
                <div v-if="userRole === 'business' && businessInfo" class="bg-white rounded-lg shadow p-6 mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Associated Business</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 p-4 rounded">
                            <label class="text-sm font-medium text-gray-600">Business Name</label>
                            <p class="text-gray-900 font-medium">{{ businessInfo.name }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded">
                            <label class="text-sm font-medium text-gray-600">Business Type</label>
                            <p class="text-gray-900 capitalize">{{ businessInfo.business_type }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded">
                            <label class="text-sm font-medium text-gray-600">Staff Count</label>
                            <p class="text-gray-900 font-medium">{{ businessInfo.staff.length }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded">
                            <label class="text-sm font-medium text-gray-600">Total Returns</label>
                            <p class="text-gray-900 font-medium">{{ businessInfo.tax_returns.length }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                    <div class="space-y-3">
                        <button
                            v-if="user.email_verified_at"
                            @click="suspendUser"
                            class="block w-full text-left px-4 py-2 hover:bg-gray-50 border border-gray-200 rounded text-orange-600 font-medium"
                        >
                            Suspend User
                        </button>
                        <button
                            v-else
                            @click="reactivateUser"
                            class="block w-full text-left px-4 py-2 hover:bg-gray-50 border border-green-200 rounded text-green-600 font-medium"
                        >
                            Reactivate User
                        </button>
                        <a
                            :href="`mailto:${user.email}`"
                            class="block w-full text-left px-4 py-2 hover:bg-gray-50 border border-gray-200 rounded text-blue-600 font-medium"
                        >
                            Send Email
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object,
    userRole: String,
    businessInfo: Object,
});

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-NG');
}

function roleClass(role) {
    const classes = {
        admin: 'bg-red-100 text-red-800',
        business: 'bg-blue-100 text-blue-800',
    };
    return classes[role] || 'bg-gray-100 text-gray-800';
}

function suspendUser() {
    if (confirm('Are you sure you want to suspend this user?')) {
        router.post(`/admin/users/${props.user.id}/suspend`);
    }
}

function reactivateUser() {
    if (confirm('Are you sure you want to reactivate this user?')) {
        router.post(`/admin/users/${props.user.id}/reactivate`);
    }
}

function deleteUser() {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(`/admin/users/${props.user.id}`);
    }
}
</script>
