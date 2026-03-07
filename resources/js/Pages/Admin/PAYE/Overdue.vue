<template>
    <AdminLayout title="Overdue PAYE Returns">
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Overdue PAYE Returns</h1>
            <div class="bg-white rounded-lg shadow p-6">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Business</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Period</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in overdue" :key="item.id">
                            <td class="px-4 py-2">{{ item.business.business_name }}</td>
                            <td class="px-4 py-2">{{ item.period }}</td>
                            <td class="px-4 py-2">
                                <span :class="statusClass(item.status)">{{ item.status }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: { AdminLayout },
    props: {
        overdue: Array,
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
