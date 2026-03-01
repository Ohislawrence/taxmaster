<template>
    <AdminLayout title="Bank Sync Failures">
        <div class="bg-white rounded-lg shadow">
            <!-- Header -->
            <div class="border-b border-gray-200 p-6">
                <h1 class="text-3xl font-bold text-gray-900">Bank Sync Failures</h1>
                <p class="text-gray-600 mt-1">Monitor and resolve failed bank synchronization attempts</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 divide-x divide-gray-200 border-b border-gray-200">
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Failures</p>
                    <p class="text-3xl font-bold text-gray-900">{{ failureStats.total_failures }}</p>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Unresolved</p>
                    <p class="text-3xl font-bold text-red-600">{{ failureStats.unresolved }}</p>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Resolved</p>
                    <p class="text-3xl font-bold text-green-600">{{ failureStats.resolved }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="border-b border-gray-200 p-6 bg-gray-50">
                <form @submit.prevent="applyFilters" class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Bank name or business name..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="form.resolved"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All</option>
                            <option value="unresolved">Unresolved</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Filter
                    </button>
                </form>
            </div>

            <!-- Failures Table -->
            <div class="p-6">
                <div v-if="failures && failures.length" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Bank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Business</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Error</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Attempts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="failure in failures" :key="failure.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ failure.bank_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ failure.business_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 truncate" :title="failure.error_message">
                                    {{ failure.error_message.substring(0, 40) }}...
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ failure.attempt_count }}/3</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ new Date(failure.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span :class="failure.is_read ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        class="px-3 py-1 rounded-full text-xs font-medium">
                                        {{ failure.is_read ? 'Resolved' : 'Unresolved' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <Link :href="route('admin.sync-failures.show', failure.id)" class="text-blue-600 hover:underline">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12">
                    <p class="text-gray-500">No sync failures found</p>
                </div>

                <!-- Pagination -->
                <div v-if="failures && failures.length && meta && meta.last_page > 1" class="flex justify-center gap-2 mt-6">
                    <button
                        v-for="page in Array.from({length: meta.last_page}, (_, i) => i + 1)"
                        :key="page"
                        @click="goToPage(page)"
                        :class="[
                            'px-4 py-2 rounded border',
                            page === meta.current_page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'
                        ]"
                    >
                        {{ page }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

export default {
    components: { AdminLayout, Link },
    props: ['failures', 'failureStats', 'filters', 'meta'],
    data() {
        return {
            form: {
                search: this.filters?.search || '',
                resolved: this.filters?.resolved || '',
            },
        };
    },
    methods: {
        applyFilters() {
            router.get(route('admin.sync-failures.index'), this.form);
        },
        goToPage(page) {
            this.form.page = page;
            this.applyFilters();
        },
    },
};
</script>
