<template>
    <BusinessLayout>
        <Head title="Invoices" />

        <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                        Invoices
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Manage and track all your sales invoices
                    </p>
                </div>
                <div>
                    <Link
                        :href="route('business.invoices.create')"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-sm hover:shadow-md active:scale-95"
                    >
                        Create Invoice
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl border border-gray-200/50 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Invoices</p>
                        <p class="text-2xl font-bold text-gray-900">{{ filteredInvoices.length }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200/50 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Value</p>
                        <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(totalValue) }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200/50 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Paid</p>
                        <p class="text-2xl font-bold text-green-600">{{ paidCount }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200/50 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Overdue</p>
                        <p class="text-2xl font-bold text-amber-600">{{ overdueCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="bg-white rounded-xl border border-gray-200/50 shadow-sm mb-6 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select
                            v-model="filters.status"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="viewed">Viewed</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <!-- Date Range Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">From Date</label>
                        <input
                            type="date"
                            v-model="filters.from_date"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">To Date</label>
                        <input
                            type="date"
                            v-model="filters.to_date"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                    </div>

                    <!-- Search Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Search Invoice</label>
                        <input
                            type="text"
                            v-model="filters.search"
                            placeholder="Invoice number..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-gray-100">
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        Clear Filters
                    </button>
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                    <h2 class="text-base font-semibold text-gray-900">
                        All Invoices
                    </h2>
                </div>

                <div v-if="paginatedInvoices.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice #</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Issue Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="invoice in paginatedInvoices" :key="invoice.id" class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ invoice.invoice_number }}</span>
                                        <span v-if="invoice.type" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                            {{ invoice.type }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(invoice.invoice_date) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-600">{{ formatDate(invoice.due_date) }}</span>
                                        <span v-if="isOverdue(invoice.due_date, invoice.status)" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                                            Overdue
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">₦{{ formatCurrency(invoice.total) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                                            getStatusBadgeClass(invoice.status)
                                        ]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="getStatusDotClass(invoice.status)"></span>
                                        {{ formatStatus(invoice.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <select
                                            :value="invoice.status"
                                            @change="changeStatus(invoice.id, $event)"
                                            :disabled="savingStatus[invoice.id]"
                                            class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        >
                                            <option value="draft">Draft</option>
                                            <option value="sent">Sent</option>
                                            <option value="viewed">Viewed</option>
                                            <option value="paid">Paid</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                        <div class="flex items-center gap-1">
                                            <Link
                                                :href="route('business.invoices.edit', invoice.id)"
                                                class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                                title="Edit"
                                            >
                                                Edit
                                            </Link>
                                            <Link
                                                :href="route('business.invoices.show', invoice.id)"
                                                class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                                title="View"
                                            >
                                                View
                                            </Link>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="p-12 text-center">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl flex items-center justify-center">
                        <span class="text-4xl text-gray-400">📄</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No invoices found</h3>
                    <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                        {{ filters.status || filters.search || filters.from_date || filters.to_date ? 'No invoices match your filter criteria.' : 'Create your first sales invoice to start tracking your business transactions' }}
                    </p>
                    <Link
                        v-if="!filters.status && !filters.search && !filters.from_date && !filters.to_date"
                        :href="route('business.invoices.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-xl transition-all shadow-sm"
                    >
                        Create Invoice
                    </Link>
                    <button
                        v-else
                        @click="resetFilters"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-xl transition-all shadow-sm"
                    >
                        Clear Filters
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="filteredInvoices.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50/30">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            Showing <span class="font-medium">{{ paginationStart }}</span> to
                            <span class="font-medium">{{ paginationEnd }}</span> of
                            <span class="font-medium">{{ filteredInvoices.length }}</span> invoices
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="currentPage--"
                                :disabled="currentPage === 1"
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                                    currentPage === 1
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                                ]"
                            >
                                Previous
                            </button>
                            <button
                                v-for="page in totalPages"
                                :key="page"
                                @click="currentPage = page"
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                                    currentPage === page
                                        ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm'
                                        : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                                ]"
                            >
                                {{ page }}
                            </button>
                            <button
                                @click="currentPage++"
                                :disabled="currentPage === totalPages"
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                                    currentPage === totalPages
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                                ]"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    invoices: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            from: 0,
            to: 0,
            total: 0
        })
    },
});

// Filters state
const filters = ref({
    status: '',
    from_date: '',
    to_date: '',
    search: ''
});

// Pagination state
const currentPage = ref(1);
const itemsPerPage = 10;

// Filtered invoices
const filteredInvoices = computed(() => {
    let filtered = [...props.invoices.data];

    // Filter by status
    if (filters.value.status) {
        filtered = filtered.filter(invoice => invoice.status === filters.value.status);
    }

    // Filter by date range
    if (filters.value.from_date) {
        filtered = filtered.filter(invoice => {
            return new Date(invoice.invoice_date) >= new Date(filters.value.from_date);
        });
    }

    if (filters.value.to_date) {
        filtered = filtered.filter(invoice => {
            return new Date(invoice.invoice_date) <= new Date(filters.value.to_date);
        });
    }

    // Filter by search (invoice number)
    if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase();
        filtered = filtered.filter(invoice =>
            invoice.invoice_number.toLowerCase().includes(searchTerm)
        );
    }

    return filtered;
});

// Paginated invoices
const paginatedInvoices = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredInvoices.value.slice(start, end);
});

// Pagination calculations
const totalPages = computed(() => {
    return Math.ceil(filteredInvoices.value.length / itemsPerPage);
});

const paginationStart = computed(() => {
    if (filteredInvoices.value.length === 0) return 0;
    return (currentPage.value - 1) * itemsPerPage + 1;
});

const paginationEnd = computed(() => {
    const end = currentPage.value * itemsPerPage;
    return Math.min(end, filteredInvoices.value.length);
});

// Reset pagination when filters change
watch(filteredInvoices, () => {
    currentPage.value = 1;
});

// Stats calculations
const totalValue = computed(() => {
    return filteredInvoices.value.reduce((sum, invoice) => {
        if (invoice.status !== 'cancelled') {
            return sum + (invoice.total || 0);
        }
        return sum;
    }, 0);
});

const paidCount = computed(() => {
    return filteredInvoices.value.filter(invoice => invoice.status === 'paid').length;
});

const overdueCount = computed(() => {
    return filteredInvoices.value.filter(invoice =>
        invoice.status !== 'paid' &&
        invoice.status !== 'cancelled' &&
        new Date(invoice.due_date) < new Date()
    ).length;
});

// Status badge styling
const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-700',
        sent: 'bg-blue-100 text-blue-700',
        viewed: 'bg-indigo-100 text-indigo-700',
        paid: 'bg-green-100 text-green-700',
        cancelled: 'bg-red-100 text-red-700'
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};

const getStatusDotClass = (status) => {
    const classes = {
        draft: 'bg-gray-500',
        sent: 'bg-blue-500',
        viewed: 'bg-indigo-500',
        paid: 'bg-green-500',
        cancelled: 'bg-red-500'
    };
    return classes[status] || 'bg-gray-500';
};

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount || 0);
};

const formatDate = (d) => {
    if (!d) return '';
    return new Date(d).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const isOverdue = (dueDate, status) => {
    if (!dueDate) return false;
    if (status === 'paid' || status === 'cancelled') return false;
    return new Date(dueDate) < new Date();
};

// Filter actions
const applyFilters = () => {
    currentPage.value = 1;
};

const resetFilters = () => {
    filters.value = {
        status: '',
        from_date: '',
        to_date: '',
        search: ''
    };
    currentPage.value = 1;
};

// Status update
const savingStatus = ref({});

const changeStatus = (invoiceId, event) => {
    const status = event.target.value;
    savingStatus.value[invoiceId] = true;

    router.patch(route('business.invoices.update-status', { invoice: invoiceId }), { status }, {
        preserveState: true,
        onFinish: () => {
            savingStatus.value[invoiceId] = false;
        },
        onError: () => {
            savingStatus.value[invoiceId] = false;
        },
    });
};
</script>

<style scoped>
/* Smooth transitions */
tr {
    transition: background-color 0.2s ease;
}

/* Custom select styling */
select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.2em;
    padding-right: 2rem;
    appearance: none;
}

select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

select:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Input focus styles */
input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

/* Disabled button styles */
button:disabled {
    cursor: not-allowed;
}
</style>
