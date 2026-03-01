<template>
    <AdminLayout>
        <Head :title="`PAYE Return - ${payeReturn.period}`" />

        <div class="space-y-8">
            <!-- Header -->
            <div class="flex justify-between items-start mb-8">
                <div>
                    <Link href="/admin/paye-returns" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
                        ← Back to PAYE Returns
                    </Link>
                    <h1 class="text-4xl font-bold text-gray-900 mt-4">PAYE Return - {{ payeReturn.period }}</h1>
                    <p class="text-gray-600 mt-2">{{ payeReturn.business.name }}</p>
                </div>
                <span
                    :class="{
                        'bg-yellow-100 text-yellow-800': payeReturn.status === 'pending',
                        'bg-green-100 text-green-800': payeReturn.status === 'filed',
                        'bg-blue-100 text-blue-800': payeReturn.status === 'paid',
                    }"
                    class="px-4 py-2 rounded-full text-sm font-semibold"
                >
                    {{ payeReturn.status }}
                </span>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Staff Members</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.totalStaff }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Gross Income</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">₦{{ formatCurrency(stats.totalGross) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Relief</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">₦{{ formatCurrency(stats.totalRelief) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total PAYE Tax</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">₦{{ formatCurrency(stats.totalPayeTax) }}</p>
                </div>
            </div>

            <!-- Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Return Information</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600 text-sm">Period</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ payeReturn.period }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Status</p>
                        <p class="text-gray-900 font-semibold mt-1 capitalize">{{ payeReturn.status }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Filed Date</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ payeReturn.filed_date || 'Not filed' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Created At</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ new Date(payeReturn.created_at).toLocaleDateString() }}</p>
                    </div>
                </div>
            </div>

            <!-- Staff Schedules -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Staff Schedules</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Staff Name</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">Gross Income</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">Relief Amount</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">PAYE Tax</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="schedule in payeReturn.schedules" :key="schedule.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">{{ schedule.staff.first_name }} {{ schedule.staff.last_name }}</td>
                                <td class="px-6 py-4 text-right">₦{{ formatCurrency(schedule.gross_income) }}</td>
                                <td class="px-6 py-4 text-right">₦{{ formatCurrency(schedule.relief_amount) }}</td>
                                <td class="px-6 py-4 text-right font-semibold">₦{{ formatCurrency(schedule.paye_tax) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    payeReturn: Object,
    stats: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0);
};
</script>
