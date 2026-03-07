<template>
    <BusinessLayout :title="`Invoice ${invoice.invoice_number}`">
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ invoice.invoice_number }}</h1>
                        <p class="text-gray-600 mt-1">{{ invoice.business.name }}</p>
                    </div>
                    <span :class="getStatusClass(invoice.status)" class="px-4 py-2 rounded-lg text-sm font-medium">
                        {{ invoice.status }}
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-6 mb-6 border-b border-gray-200 pb-6">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Invoice Date</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ new Date(invoice.invoice_date).toLocaleDateString() }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Due Date</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ new Date(invoice.due_date).toLocaleDateString() }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Invoice Amount</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ formatCurrency(invoice.total) }}
                        </p>
                    </div>
                </div>

                <!-- Invoice Details -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Bill To</h3>
                        <p class="text-gray-900 font-medium">{{ invoice.business.name }}</p>
                        <p class="text-gray-600">{{ invoice.business.email }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Billing Period</h3>
                        <p class="text-gray-900">
                            {{ new Date(invoice.period_start).toLocaleDateString() }} to
                            {{ new Date(invoice.period_end).toLocaleDateString() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="border-b border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Invoice Details</h2>
                </div>

                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="invoice.data && invoice.data.items" v-for="(item, index) in invoice.data.items" :key="index">
                            <td class="px-6 py-4 text-gray-900">{{ item.description }}</td>
                            <td class="px-6 py-4 text-right text-gray-900 font-semibold">
                                {{ formatCurrency(item.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="border-t border-gray-200 p-6 bg-gray-50">
                    <div class="max-w-sm ml-auto space-y-2">
                        <div class="flex justify-between text-gray-900">
                            <span>Subtotal:</span>
                            <span>{{ formatCurrency(invoice.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-900">
                            <span>Tax (VAT):</span>
                            <span>{{ formatCurrency(invoice.tax) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-300 pt-2">
                            <span>Total:</span>
                            <span>{{ formatCurrency(invoice.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Code & Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice QR Code & Actions</h2>
                <div class="flex flex-col sm:flex-row gap-6 items-center">
                    <div v-if="qrCodeData">
                        <img :src="qrCodeData" alt="Invoice QR Code" class="w-40 h-40 border rounded" />
                        <p class="text-xs text-gray-500 mt-2">Scan to verify invoice</p>
                    </div>
                    <div class="flex flex-col gap-3">
                        <a
                            :href="route('business.invoices.jades', invoice.id)"
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 inline-block"
                            target="_blank"
                        >
                            Download Signed E-Invoice (JAdES JSON)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { Link, router } from '@inertiajs/vue3';

export default {
    components: { BusinessLayout, Link },
    props: ['invoice'],
    data() {
        return {
            qrCodeData: null,
        };
    },
    mounted() {
        this.fetchQrCode();
    },
    methods: {
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
        fetchQrCode() {
            fetch(`/business/invoices/${this.invoice.id}/qr`)
                .then(res => res.json())
                .then(data => {
                    this.qrCodeData = data.qr;
                });
        },
    },
};
</script>
