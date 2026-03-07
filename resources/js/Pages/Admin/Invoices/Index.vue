<template>
    <AdminLayout title="Invoices">
        <div class="bg-white rounded-lg shadow">
            <!-- Header -->
            <div class="border-b border-gray-200 p-6">
                <h1 class="text-3xl font-bold text-gray-900">Invoices</h1>
                <p class="text-gray-600 mt-1">Manage customer invoices and billing</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-4 divide-x divide-gray-200 border-b border-gray-200">
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Total Invoices</p>
                    <p class="text-3xl font-bold text-gray-900">{{ invoiceStats.total_invoices }}</p>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Unpaid</p>
                    <p class="text-3xl font-bold text-red-600">{{ invoiceStats.unpaid }}</p>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Overdue</p>
                    <p class="text-3xl font-bold text-orange-600">{{ invoiceStats.overdue }}</p>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 text-sm font-medium">Outstanding (₦)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(invoiceStats.total_outstanding) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="border-b border-gray-200 p-6 bg-gray-50">
                <form @submit.prevent="applyFilters" class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Invoice number or business name..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="form.status"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="viewed">Viewed</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business</label>
                        <select
                            v-model="form.business_id"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Businesses</option>
                            <option v-for="business in businesses" :key="business.id" :value="business.id">
                                {{ business.name }}
                            </option>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Filter
                    </button>
                </form>
            </div>

            <!-- Invoices Table -->
            <div class="p-6">
                <div v-if="invoices.data.length" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Business</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-blue-600">
                                    <Link :href="route('admin.invoices.show', invoice.id)" class="hover:underline">
                                        {{ invoice.invoice_number }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ invoice.business.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ new Date(invoice.invoice_date).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    {{ formatCurrency(invoice.total) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span :class="getStatusClass(invoice.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                                        {{ invoice.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <Link :href="route('admin.invoices.show', invoice.id)" class="text-blue-600 hover:underline mr-3">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12">
                    <p class="text-gray-500">No invoices found</p>
                </div>

                <!-- Pagination -->
                <div v-if="invoices.links" class="flex justify-center gap-2 mt-6">
                    <template v-for="link in invoices.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[
                                'px-4 py-2 rounded border',
                                link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            :class="[
                                'px-4 py-2 rounded border cursor-not-allowed',
                                link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-400'
                            ]"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

export default {
    components: { AdminLayout, Link },
    props: ['invoices', 'invoiceStats', 'businesses', 'filters'],
    data() {
        return {
            form: {
                search: this.filters?.search || '',
                status: this.filters?.status || '',
                business_id: this.filters?.business_id || '',
            },
        };
    },
    methods: {
        applyFilters() {
            router.get(route('admin.invoices.index'), this.form);
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('en-NG', {
                style: 'currency',
                currency: 'NGN',
            }).format(value || 0);
        },
        getStatusClass(status) {
            const classes = {
                draft: 'bg-gray-100 text-gray-800',
                sent: 'bg-blue-100 text-blue-800',
                viewed: 'bg-purple-100 text-purple-800',
                paid: 'bg-green-100 text-green-800',
                cancelled: 'bg-red-100 text-red-800',
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },
    },
};
</script>
