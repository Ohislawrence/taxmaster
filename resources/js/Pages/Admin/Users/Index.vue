<template>
    <AdminLayout>
        <Head title="Users Management" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Users Management</h1>
                <Link href="/admin/users/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                    + New User
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search by name or email..."
                        class="border border-gray-300 rounded px-3 py-2"
                    />
                    <select v-model="filters.role" class="border border-gray-300 rounded px-3 py-2">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="business">Business</option>
                    </select>
                    <button @click="applyFilters" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded font-medium">
                        Filter
                    </button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="users.data.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Name</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Email</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Role</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Joined</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id" class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ user.name }}</td>
                                <td class="py-3 px-4">{{ user.email }}</td>
                                <td class="py-3 px-4">
                                    <span :class="roleClass(user.roles[0]?.name)" class="px-3 py-1 rounded-full text-sm font-medium">
                                        {{ user.roles[0]?.name || 'No Role' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span :class="user.email_verified_at ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                        {{ user.email_verified_at ? 'Active' : 'Suspended' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm">{{ formatDate(user.created_at) }}</td>
                                <td class="py-3 px-4">
                                    <Link :href="`/admin/users/${user.id}`" class="text-blue-600 hover:underline">View</Link>
                                    <span class="mx-2 text-gray-400">|</span>
                                    <Link :href="`/admin/users/${user.id}/edit`" class="text-green-600 hover:underline">Edit</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-8 text-center text-gray-500">
                    <p>No users found.</p>
                    <Link href="/admin/users/create" class="text-blue-600 hover:underline">Create your first user</Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="mt-6 flex justify-center gap-2">
                <Link v-for="page in pages" :key="page" :href="`?page=${page}`" class="px-3 py-2 border rounded" :class="page === users.current_page ? 'bg-blue-600 text-white' : ''">
                    {{ page }}
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    users: Object,
});

const filters = ref({
    search: '',
    role: '',
});

const pages = computed(() => {
    const pages = [];
    for (let i = 1; i <= props.users.last_page; i++) {
        pages.push(i);
    }
    return pages;
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

function applyFilters() {
    window.location.href = `/admin/users?search=${filters.value.search}&role=${filters.value.role}`;
}
</script>
