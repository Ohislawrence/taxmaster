<template>
    <BusinessLayout>
        <Head title="WHT Transactions" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Withholding Tax (WHT)</h1>
                    <p class="text-gray-600 mt-1">Record and manage withholding tax transactions</p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('business.wht.returns')"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center"
                    >
                        📊 View Returns
                    </Link>
                    <Link
                        :href="route('business.wht.create')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center"
                    >
                        <span class="mr-2">+</span> Record Transaction
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                    <p class="text-sm text-blue-600 font-medium">Total Transactions</p>
                    <p class="text-3xl font-bold text-blue-900">{{ stats.total_transactions }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                    <p class="text-sm text-green-600 font-medium">Total WHT Deducted</p>
                    <p class="text-2xl font-bold text-green-900">₦{{ formatCurrency(stats.total_wht_deducted) }}</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-6 border border-orange-200">
                    <p class="text-sm text-orange-600 font-medium">This Month WHT</p>
                    <p class="text-2xl font-bold text-orange-900">₦{{ formatCurrency(stats.this_month_wht) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                    <p class="text-sm text-purple-600 font-medium">Pending Returns</p>
                    <p class="text-3xl font-bold text-purple-900">{{ stats.pending_returns }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="grid md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                        <select
                            v-model="filters.type"
                            @change="applyFilters"
                            class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">All Types</option>
                            <option v-for="type in transactionTypes" :key="type.value" :value="type.value">
                                {{ type.label }} ({{ type.rate }}%)
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input
                            type="date"
                            v-model="filters.startDate"
                            @change="applyFilters"
                            class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input
                            type="date"
                            v-model="filters.endDate"
                            @change="applyFilters"
                            class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="clearFilters"
                            class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transactions List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">WHT Transactions</h2>
                </div>

                <div v-if="transactions.data.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross Amount</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">WHT Rate</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">WHT Amount</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net Amount</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(transaction.transaction_date) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ transaction.vendor_name }}</p>
                                        <p class="text-xs text-gray-500">{{ transaction.vendor_tin || 'No TIN' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                        {{ transaction.transaction_type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    ₦{{ formatCurrency(transaction.gross_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                    {{ transaction.wht_rate }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                    ₦{{ formatCurrency(transaction.wht_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    ₦{{ formatCurrency(transaction.net_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <Link
                                        :href="route('business.wht.show', transaction.id)"
                                        class="text-blue-600 hover:text-blue-800 mr-3"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-12 text-center">
                    <div class="text-gray-400 text-6xl mb-4">💰</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No WHT Transactions Yet</h3>
                    <p class="text-gray-600 mb-6">Record your first withholding tax transaction</p>
                    <Link
                        :href="route('business.wht.create')"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg"
                    >
                        <span class="mr-2">+</span> Record Transaction
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="transactions.data.length > 0" class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Showing {{ transactions.from }} to {{ transactions.to }} of {{ transactions.total }} transactions
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in transactions.links"
                                :key="link.label"
                                :href="link.url"
                                :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                class="px-4 py-2 rounded border text-sm font-medium"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    transactions: Object,
    stats: Object,
    transactionTypes: Array,
});

const filters = ref({
    type: '',
    startDate: '',
    endDate: '',
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const applyFilters = () => {
    router.get(route('business.wht.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        type: '',
        startDate: '',
        endDate: '',
    };
    router.get(route('business.wht.index'));
};
</script>
