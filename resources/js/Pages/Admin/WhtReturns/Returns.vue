<template>
    <AdminLayout>
        <Head title="WHT Returns" />

        <div class="space-y-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">WHT Returns Management</h1>
                <p class="text-gray-600 mt-2">Monitor all WHT returns across all businesses</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Returns</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Filed Returns</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ stats.filed }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Pending Returns</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">{{ stats.pending }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total WHT</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">₦{{ formatCurrency(stats.totalWht) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">WHT Returns</h2>
                    <div class="flex gap-2">
                        <Link
                            :href="route('admin.wht-returns.export-returns')"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm"
                        >
                            Export
                        </Link>
                        <Link
                            :href="route('admin.wht-returns.index')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm"
                        >
                            View Transactions
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <!-- Search -->
                    <input
                        v-model="searchForm.search"
                        @keyup.debounce="handleFilter"
                        type="text"
                        placeholder="Search business name..."
                        class="border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                    />

                    <!-- Business Filter -->
                    <select
                        v-model="searchForm.business_id"
                        @change="handleFilter"
                        class="border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">All Businesses</option>
                        <option v-for="b in businesses" :key="b.id" :value="b.id">
                            {{ b.name }}
                        </option>
                    </select>

                    <!-- Status Filter -->
                    <select
                        v-model="searchForm.status"
                        @change="handleFilter"
                        class="border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="filed">Filed</option>
                        <option value="paid">Paid</option>
                    </select>

                    <!-- Period Filter -->
                    <input
                        v-model="searchForm.period"
                        @change="handleFilter"
                        type="month"
                        class="border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Period</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Business</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">Transactions</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">Total WHT</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="ret in returns.data" :key="ret.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">{{ ret.period }}</td>
                                <td class="px-6 py-4">{{ ret.business.name }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': ret.status === 'pending',
                                            'bg-green-100 text-green-800': ret.status === 'filed',
                                            'bg-blue-100 text-blue-800': ret.status === 'paid',
                                        }"
                                        class="px-3 py-1 rounded-full text-xs font-semibold"
                                    >
                                        {{ ret.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">{{ ret.schedules_count || 0 }}</td>
                                <td class="px-6 py-4 text-right font-semibold">₦{{ formatCurrency(ret.total_wht_amount) }}</td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="route('admin.wht-returns.show-return', ret.id)"
                                        class="text-blue-600 hover:text-blue-800 font-medium"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex justify-center">
                    <nav v-if="returns.links" class="flex gap-2">
                        <template v-for="link in returns.links">
                            <Link
                                v-if="link.url"
                                :key="link.url"
                                :href="link.url"
                                v-html="link.label"
                                :class="{
                                    'bg-blue-600 text-white': link.active,
                                    'bg-white text-gray-700 border': !link.active,
                                }"
                                class="px-3 py-2 rounded-lg text-sm font-medium"
                            />
                            <span
                                v-else
                                :key="link.label"
                                class="px-3 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-400 cursor-not-allowed"
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { reactive } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    returns: Object,
    stats: Object,
    businesses: Array,
    filters: Object,
});

const searchForm = reactive({
    search: '',
    business_id: '',
    status: '',
    period: '',
});

const handleFilter = () => {
    Link.visit(route('admin.wht-returns.returns', {
        search: searchForm.search,
        business_id: searchForm.business_id,
        status: searchForm.status,
        period: searchForm.period,
    }));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0);
};
</script>
