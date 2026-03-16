<template>
    <AdminLayout>
        <Head title="Manage Businesses" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Businesses</h1>
                    <p class="text-gray-600 mt-1">Manage all active businesses on the platform</p>
                </div>
                <div>
                    <Link href="/admin/businesses/create" class="bg-blue-600 text-white px-4 py-2 rounded">Create Business</Link>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Business</label>
                        <input
                            v-model="filters.search"
                            @input="search"
                            type="text"
                            placeholder="Business name or email..."
                            class="w-full border border-gray-300 rounded px-4 py-2"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="filters.status" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>

                    <!-- Subscription Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subscription</label>
                        <select v-model="filters.subscription" @change="applyFilters" class="w-full border border-gray-300 rounded px-4 py-2">
                            <option value="">All Plans</option>
                            <option value="starter">Starter</option>
                            <option value="professional">Professional</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Businesses Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Business Name</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Owner</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Email</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Subscription</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Staff Count</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Joined</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="business in businesses.data" :key="business.id" class="hover:bg-gray-50">
                                <td class="py-4 px-6 font-medium text-gray-900">
                                    {{ business.name }}
                                </td>
                                <td class="py-4 px-6 text-gray-600">
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <template v-if="business.owner && business.owner.name">
                                                <span class="font-medium">{{ business.owner.name }}</span>
                                                <div class="text-xs text-gray-500">Owner</div>
                                            </template>

                                            <template v-else-if="business.created_by_accountant_id && (business.createdByAccountant?.name || business.created_by_accountant?.name)">
                                                <span class="font-medium">{{ business.createdByAccountant?.name || business.created_by_accountant?.name }}</span>
                                                <div class="text-xs text-gray-500">Created by accountant</div>
                                            </template>

                                            <template v-else>
                                                <span class="text-sm text-gray-700">No owner</span>
                                                <div class="text-xs text-gray-500">Unassigned</div>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-gray-600">
                                    {{ business.email }}
                                </td>
                                <td class="py-4 px-6">
                                    <span :class="statusBadgeClass(business.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                                        {{ business.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-gray-600">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        {{ business.subscription?.plan_name || 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-gray-600">
                                    {{ business.staff_count }}
                                </td>
                                <td class="py-4 px-6 text-gray-600 text-xs">
                                    {{ formatDate(business.created_at) }}
                                </td>
                                <td class="py-4 px-6 space-x-2">
                                    <Link :href="`/admin/businesses/${business.id}`" class="text-blue-600 hover:underline text-xs">View</Link>
                                    <button @click="toggleMenu(business.id)" class="text-gray-600 hover:text-gray-900 text-xs">•••</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="businesses.links.length > 3" class="bg-white px-6 py-4 border-t border-gray-200 flex justify-center space-x-2">
                    <template v-for="link in businesses.links" :key="link.url || link.label">
                        <Link v-if="link.url" :href="link.url" :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-600'" class="px-3 py-1 rounded">
                            {{ link.label }}
                        </Link>
                        <span v-else :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-400'" class="px-3 py-1 rounded cursor-not-allowed">
                            {{ link.label }}
                        </span>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    businesses: Object,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    subscription: props.filters?.subscription || '',
});

const openMenu = ref(null);

const search = () => {
    // Implement search logic
};

const applyFilters = () => {
    // Implement filter logic
};

const toggleMenu = (businessId) => {
    openMenu.value = openMenu.value === businessId ? null : businessId;
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
</script>
