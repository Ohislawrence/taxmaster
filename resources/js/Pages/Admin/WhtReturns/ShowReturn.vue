<template>
    <AdminLayout>
        <Head :title="`WHT Return - ${whtReturn.period}`" />

        <div class="space-y-8">
            <!-- Header -->
            <div class="flex justify-between items-start mb-8">
                <div>
                    <Link href="/admin/wht-returns/returns" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
                        ← Back to WHT Returns
                    </Link>
                    <h1 class="text-4xl font-bold text-gray-900 mt-4">WHT Return - {{ whtReturn.period }}</h1>
                    <p class="text-gray-600 mt-2">{{ whtReturn.business.name }}</p>
                </div>
                <span
                    :class="{
                        'bg-yellow-100 text-yellow-800': whtReturn.status === 'pending',
                        'bg-green-100 text-green-800': whtReturn.status === 'filed',
                        'bg-blue-100 text-blue-800': whtReturn.status === 'paid',
                    }"
                    class="px-4 py-2 rounded-full text-sm font-semibold"
                >
                    {{ whtReturn.status }}
                </span>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Transactions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.totalTransactions }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Gross Amount</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">₦{{ formatCurrency(stats.totalGrossAmount) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total WHT</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">₦{{ formatCurrency(stats.totalWht) }}</p>
                </div>
            </div>

            <!-- Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Return Information</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600 text-sm">Period</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ whtReturn.period }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Status</p>
                        <p class="text-gray-900 font-semibold mt-1 capitalize">{{ whtReturn.status }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Filed Date</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ whtReturn.filed_date || 'Not filed' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Created At</p>
                        <p class="text-gray-900 font-semibold mt-1">{{ new Date(whtReturn.created_at).toLocaleDateString() }}</p>
                    </div>
                </div>
            </div>

            <!-- WHT Schedules by Type -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">WHT Breakdown by Transaction Type</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Transaction Type</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">Count</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">Gross Amount</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">WHT Rate</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">WHT Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="schedule in whtReturn.schedules" :key="schedule.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 capitalize">{{ schedule.transaction_type.replace('_', ' ') }}</td>
                                <td class="px-6 py-4 text-right">{{ schedule.transaction_count }}</td>
                                <td class="px-6 py-4 text-right">₦{{ formatCurrency(schedule.gross_amount) }}</td>
                                <td class="px-6 py-4 text-right">{{ schedule.wht_rate }}%</td>
                                <td class="px-6 py-4 text-right font-semibold">₦{{ formatCurrency(schedule.wht_amount) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr class="font-semibold">
                                <td class="px-6 py-3">Total</td>
                                <td class="px-6 py-3 text-right">{{ whtReturn.schedules.reduce((sum, s) => sum + s.transaction_count, 0) }}</td>
                                <td class="px-6 py-3 text-right">₦{{ formatCurrency(whtReturn.schedules.reduce((sum, s) => sum + s.gross_amount, 0)) }}</td>
                                <td class="px-6 py-3"></td>
                                <td class="px-6 py-3 text-right text-red-600">₦{{ formatCurrency(stats.totalWht) }}</td>
                            </tr>
                        </tfoot>
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
    whtReturn: Object,
    stats: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0);
};
</script>
