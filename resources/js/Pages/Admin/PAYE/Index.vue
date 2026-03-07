<template>
    <AdminLayout title="PAYE Returns">
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">PAYE Returns Dashboard</h1>
            <div class="grid grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm">Total Returns</div>
                    <div class="text-2xl font-bold text-blue-700 mt-2">{{ stats.total_returns }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm">Total Tax Collected</div>
                    <div class="text-2xl font-bold text-green-700 mt-2">₦{{ stats.total_tax_collected.toLocaleString() }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm">Pending Returns</div>
                    <div class="text-2xl font-bold text-yellow-700 mt-2">{{ stats.pending_returns }}</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">All PAYE Returns</h2>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Business</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Period</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Staff Count</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Total Tax</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="returnItem in returns.data" :key="returnItem.id">
                            <td class="px-4 py-2">{{ returnItem.business.name }}</td>
                            <td class="px-4 py-2">{{ returnItem.period }}</td>
                            <td class="px-4 py-2">{{ returnItem.staff_count }}</td>
                            <td class="px-4 py-2">₦{{ returnItem.total_tax_deducted.toLocaleString() }}</td>
                            <td class="px-4 py-2">
                                <span :class="statusClass(returnItem.status)">{{ returnItem.status }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <Link :href="route('admin.paye.show', returnItem.id)" class="text-indigo-600 hover:underline">View</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="returns.links" class="mt-4">
                    <Pagination :links="returns.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link } from '@inertiajs/vue3';

export default {
    components: { AdminLayout, Pagination, Link },
    props: {
        returns: Object,
        stats: Object,
    },
    methods: {
        statusClass(status) {
            const map = {
                draft: 'bg-gray-100 text-gray-800 px-2 py-1 rounded',
                filed: 'bg-blue-100 text-blue-800 px-2 py-1 rounded',
                paid: 'bg-green-100 text-green-800 px-2 py-1 rounded',
                overdue: 'bg-red-100 text-red-800 px-2 py-1 rounded',
            };
            return map[status] || 'bg-gray-100 text-gray-800 px-2 py-1 rounded';
        },
    },
};
</script>
