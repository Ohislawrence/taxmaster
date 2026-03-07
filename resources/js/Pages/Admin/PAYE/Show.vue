<template>
    <AdminLayout :title="`PAYE Return #${payeReturn.id}`">
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">PAYE Return Details</h1>
            <div class="grid grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm">Business</div>
                    <div class="text-lg font-bold text-blue-700 mt-2">{{ payeReturn.business.name }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm">Period</div>
                    <div class="text-lg font-bold text-gray-900 mt-2">{{ payeReturn.period }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm">Status</div>
                    <span :class="statusClass(payeReturn.status)" class="text-lg font-bold mt-2">{{ payeReturn.status }}</span>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Staff Schedules</h2>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Staff</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Gross Pay</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Allowances</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Reliefs</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Taxable Income</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">PAYE Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="schedule in payeReturn.schedules" :key="schedule.id">
                            <td class="px-4 py-2">{{ schedule.staff?.full_name || schedule.business_staff_id }}</td>
                            <td class="px-4 py-2">₦{{ schedule.gross_pay.toLocaleString() }}</td>
                            <td class="px-4 py-2">₦{{ schedule.total_allowances?.toLocaleString() || 0 }}</td>
                            <td class="px-4 py-2">₦{{ schedule.tax_reliefs ? Object.values(schedule.tax_reliefs).reduce((a,b)=>a+b,0).toLocaleString() : 0 }}</td>
                            <td class="px-4 py-2">₦{{ schedule.taxable_income.toLocaleString() }}</td>
                            <td class="px-4 py-2">₦{{ schedule.paye_due.toLocaleString() }}</td>
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
        payeReturn: Object,
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
