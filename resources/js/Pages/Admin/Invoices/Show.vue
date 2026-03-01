<template>
    <AdminLayout :title="`Invoice ${invoice.invoice_number}`">
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

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                <div class="flex gap-3 flex-wrap">
                    <button
                        v-if="invoice.status === 'draft'"
                        @click="resendInvoice"
                        :disabled="isSending"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ isSending ? 'Sending...' : 'Send Invoice' }}
                    </button>

                    <button
                        v-if="invoice.status !== 'paid'"
                        @click="markPaidModal = true"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                    >
                        Mark as Paid
                    </button>

                    <button
                        v-if="invoice.status !== 'cancelled' && invoice.status !== 'paid'"
                        @click="cancelModal = true"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        Cancel Invoice
                    </button>

                    <a
                        v-if="invoice.pdf_path"
                        :href="route('admin.invoices.pdf.download', invoice.id)"
                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 inline-block"
                    >
                        Download PDF
                    </a>
                </div>
            </div>

            <!-- Mark as Paid Modal -->
            <div v-if="markPaidModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Mark Invoice as Paid</h3>
                    <form @submit.prevent="submitMarkPaid" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Reference</label>
                            <input
                                v-model="markPaidForm.payment_reference"
                                type="text"
                                placeholder="e.g., TRAN-123456"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>
                        <div class="flex gap-3">
                            <button
                                type="button"
                                @click="markPaidModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="isSavingPaid"
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                            >
                                {{ isSavingPaid ? 'Saving...' : 'Mark as Paid' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cancel Invoice Modal -->
            <div v-if="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Cancel Invoice</h3>
                    <form @submit.prevent="submitCancel" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                            <textarea
                                v-model="cancelForm.reason"
                                placeholder="Why is this invoice being cancelled?"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                rows="3"
                            />
                        </div>
                        <div class="flex gap-3">
                            <button
                                type="button"
                                @click="cancelModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Keep Invoice
                            </button>
                            <button
                                type="submit"
                                :disabled="isCancelling"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                            >
                                {{ isCancelling ? 'Cancelling...' : 'Cancel Invoice' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

export default {
    components: { AdminLayout, Link },
    props: ['invoice'],
    data() {
        return {
            markPaidModal: false,
            cancelModal: false,
            isSending: false,
            isSavingPaid: false,
            isCancelling: false,
            markPaidForm: {
                payment_reference: '',
            },
            cancelForm: {
                reason: '',
            },
        };
    },
    methods: {
        async resendInvoice() {
            this.isSending = true;
            try {
                const response = await fetch(route('admin.invoices.resend', this.invoice.id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                if (response.ok) {
                    alert('Invoice sent successfully');
                    router.reload();
                }
            } catch (error) {
                console.error('Error sending invoice:', error);
            } finally {
                this.isSending = false;
            }
        },
        async submitMarkPaid() {
            this.isSavingPaid = true;
            try {
                const response = await fetch(route('admin.invoices.mark-paid', this.invoice.id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(this.markPaidForm),
                });
                if (response.ok) {
                    alert('Invoice marked as paid');
                    router.reload();
                }
            } catch (error) {
                console.error('Error marking paid:', error);
            } finally {
                this.isSavingPaid = false;
            }
        },
        async submitCancel() {
            this.isCancelling = true;
            try {
                const response = await fetch(route('admin.invoices.cancel', this.invoice.id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(this.cancelForm),
                });
                if (response.ok) {
                    alert('Invoice cancelled');
                    router.reload();
                }
            } catch (error) {
                console.error('Error cancelling invoice:', error);
            } finally {
                this.isCancelling = false;
            }
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
