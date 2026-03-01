<template>
    <BusinessLayout>
        <Head title="Tax Returns" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Tax Returns</h1>
                <Link href="/business/tax-returns/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                    + New Return
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search by period..."
                        class="border border-gray-300 rounded px-3 py-2"
                    />
                    <select v-model="filters.status" class="border border-gray-300 rounded px-3 py-2">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                    </select>
                    <button @click="applyFilters" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded font-medium">
                        Filter
                    </button>
                </div>
            </div>

            <!-- Returns Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="taxReturns.data.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Period</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Type</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Gross Income</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Tax Due</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Balance</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="taxReturn in taxReturns.data" :key="taxReturn.id" class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ taxReturn.tax_period }}</td>
                                <td class="py-3 px-4 capitalize">{{ taxReturn.return_type }}</td>
                                <td class="py-3 px-4">₦{{ formatCurrency(taxReturn.gross_income) }}</td>
                                <td class="py-3 px-4 font-semibold">₦{{ formatCurrency(taxReturn.total_tax_due) }}</td>
                                <td class="py-3 px-4 font-semibold" :class="taxReturn.balance > 0 ? 'text-red-600' : 'text-green-600'">
                                    ₦{{ formatCurrency(taxReturn.balance) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span :class="statusClass(taxReturn.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                                        {{ taxReturn.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <Link :href="`/business/tax-returns/${taxReturn.id}`" class="text-blue-600 hover:underline">View</Link>
                                    <span class="mx-2 text-gray-400">|</span>
                                    <Link v-if="taxReturn.status === 'draft'" :href="`/business/tax-returns/${taxReturn.id}/edit`" class="text-green-600 hover:underline">Edit</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-8 text-center text-gray-500">
                    <p>No tax returns found.</p>
                    <Link href="/business/tax-returns/create" class="text-blue-600 hover:underline">Create your first return</Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="taxReturns.last_page > 1" class="mt-6 flex justify-center gap-2">
                <Link v-for="page in pages" :key="page" :href="`?page=${page}`" class="px-3 py-2 border rounded" :class="page === taxReturns.current_page ? 'bg-blue-600 text-white' : ''">
                    {{ page }}
                </Link>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

defineProps({
    taxReturns: Object,
});

const filters = ref({
    search: '',
    status: '',
});

const pages = computed(() => {
    const pages = [];
    for (let i = 1; i <= props.taxReturns.last_page; i++) {
        pages.push(i);
    }
    return pages;
});

function formatCurrency(value) {
    return new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2 }).format(value);
}

function statusClass(status) {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        submitted: 'bg-blue-100 text-blue-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        paid: 'bg-green-100 text-green-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function applyFilters() {
    // Filter will be applied via query params
    window.location.href = `/business/tax-returns?search=${filters.value.search}&status=${filters.value.status}`;
}
</script>
