<template>
    <AdminLayout title="PAYE Statistics">
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">PAYE Statistics Dashboard</h1>
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
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: { AdminLayout },
    props: {
        stats: Object,
    },
};
</script>
