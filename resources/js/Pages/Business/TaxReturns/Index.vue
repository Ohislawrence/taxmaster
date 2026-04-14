<template>
    <BusinessLayout>
        <Head title="Tax Returns" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Tax Returns</h1>

                <!-- New Return dropdown -->
                <div class="relative" ref="dropdownRef">
                    <button
                        @click="showNewMenu = !showNewMenu"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium inline-flex items-center gap-2"
                    >
                        + New Return
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="showNewMenu"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                        >
                            <Link
                                v-for="item in taxReturnRoutes"
                                :key="item.label"
                                :href="item.href"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"
                                @click="showNewMenu = false"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </transition>
                </div>
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
                    <p class="mb-3">No tax returns found.</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <Link
                            v-for="item in taxReturnRoutes"
                            :key="item.label"
                            :href="item.href"
                            class="text-sm text-blue-600 border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-50"
                        >
                            {{ item.label }}
                        </Link>
                    </div>
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

defineProps({
    taxReturns: Object,
});

const taxReturnRoutes = [
    { label: 'PAYE Return',          href: '/business/paye/create' },
    { label: 'VAT Return',           href: '/business/vat/create' },
    { label: 'Withholding Tax (WHT)', href: '/business/wht/create' },
    { label: 'Company Income Tax',   href: '/business/cit/create' },
];

const showNewMenu = ref(false);
const dropdownRef = ref(null);

function handleOutsideClick(e) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        showNewMenu.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', handleOutsideClick));
onUnmounted(() => document.removeEventListener('mousedown', handleOutsideClick));

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
