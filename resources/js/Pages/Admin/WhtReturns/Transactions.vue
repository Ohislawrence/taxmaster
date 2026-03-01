<template>
    <AdminLayout>
        <Head title="WHT Transactions" />

        <div class="space-y-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">WHT Transactions Management</h1>
                <p class="text-gray-600 mt-2">Monitor all WHT transactions across all businesses</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Transactions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Gross Amount</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">₦{{ formatCurrency(stats.totalGross) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm font-medium">Total WHT</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">₦{{ formatCurrency(stats.totalWht) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">WHT Transactions</h2>
                    <div class="flex gap-2">
                        <Link
                            :href="route('admin.wht-returns.export-transactions')"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm"
                        >
                            Export
                        </Link>
                        <Link
                            :href="route('admin.wht-returns.returns')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm"
                        >
                            View Returns
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <!-- Search -->
                    <input
                        v-model="searchForm.search"
                        @keyup.debounce="handleFilter"
                        type="text"
                        placeholder="Search vendor name..."
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

                    <!-- Transaction Type Filter -->
                    <select
                        v-model="searchForm.transaction_type"
                        @change="handleFilter"
                        class="border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">All Types</option>
                        <option value="contractors">Contractors</option>
                        <option value="management_fees">Management Fees</option>
                        <option value="rent">Rent</option>
                        <option value="consultancy_services">Consultancy Services</option>
                        <option value="transport">Transport</option>
                        <option value="advertising">Advertising</option>
                        <option value="entertainment">Entertainment</option>
                        <option value="publishing">Publishing</option>
                        <option value="commission">Commission</option>
                        <option value="other_payments">Other Payments</option>
                    </select>

                    <!-- Date Range -->
                    <div class="flex gap-2">
                        <input
                            v-model="searchForm.start_date"
                            @change="handleFilter"
                            type="date"
                            class="flex-1 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                        />
                        <input
                            v-model="searchForm.end_date"
                            @change="handleFilter"
                            type="date"
                            class="flex-1 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Date</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Business</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Vendor</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Type</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">Gross Amount</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-900">WHT</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="txn in transactions.data" :key="txn.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">{{ formatDate(txn.transaction_date) }}</td>
                                <td class="px-6 py-4">{{ txn.business.name }}</td>
                                <td class="px-6 py-4">{{ txn.vendor_name }}</td>
                                <td class="px-6 py-4 capitalize">{{ txn.transaction_type.replace('_', ' ') }}</td>
                                <td class="px-6 py-4 text-right">₦{{ formatCurrency(txn.gross_amount) }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-red-600">₦{{ formatCurrency(txn.wht_amount) }}</td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="route('admin.wht-returns.show-transaction', txn.id)"
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
                    <nav v-if="transactions.links" class="flex gap-2">
                        <Link
                            v-for="link in transactions.links"
                            :key="link.label"
                            :href="link.url"
                            v-html="link.label"
                            :class="{
                                'bg-blue-600 text-white': link.active,
                                'bg-white text-gray-700 border': !link.active,
                            }"
                            class="px-3 py-2 rounded-lg text-sm font-medium"
                        />
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
    transactions: Object,
    stats: Object,
    businesses: Array,
    filters: Object,
});

const searchForm = reactive({
    search: '',
    business_id: '',
    transaction_type: '',
    start_date: '',
    end_date: '',
});

const handleFilter = () => {
    Link.visit(route('admin.wht-returns.index', {
        search: searchForm.search,
        business_id: searchForm.business_id,
        transaction_type: searchForm.transaction_type,
        start_date: searchForm.start_date,
        end_date: searchForm.end_date,
    }));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG');
};
</script>
