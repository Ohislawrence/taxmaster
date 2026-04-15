<template>
    <BusinessLayout :title="`Invoice ${invoice.invoice_number}`">
        <Head :title="`Invoice ${invoice.invoice_number}`" />
        <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto space-y-6">
                <!-- Header Card -->
                <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
                            <div>
                                <h1 class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                                    {{ invoice.invoice_number }}
                                </h1>
                                <p class="text-gray-500 mt-1">{{ invoice.business.name }}</p>
                            </div>
                            <span :class="getStatusClass(invoice.status)" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="getStatusDotClass(invoice.status)"></span>
                                {{ formatStatus(invoice.status) }}
                            </span>
                        </div>

                        <!-- Invoice Summary Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6 pb-6 border-b border-gray-200">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Invoice Date</p>
                                <p class="text-base lg:text-lg font-semibold text-gray-900">
                                    {{ formatDate(invoice.invoice_date) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Due Date</p>
                                <p class="text-base lg:text-lg font-semibold text-gray-900" :class="{ 'text-amber-600': isOverdue }">
                                    {{ formatDate(invoice.due_date) }}
                                    <span v-if="isOverdue" class="ml-2 text-xs font-normal text-amber-600">(Overdue)</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Invoice Amount</p>
                                <p class="text-2xl lg:text-3xl font-bold text-blue-600">
                                    {{ formatCurrency(invoice.total) }}
                                </p>
                            </div>
                        </div>

                        <!-- Invoice Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                                    Bill To
                                </h3>
                                <div class="bg-gray-50/50 rounded-lg p-4">
                                    <p class="text-gray-900 font-medium">{{ invoice.data?.buyer_name || invoice.business.name }}</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        TIN: {{ invoice.data?.buyer_tin || 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                                    Billing Period
                                </h3>
                                <div class="bg-gray-50/50 rounded-lg p-4">
                                    <p class="text-gray-900">
                                        {{ formatDate(invoice.period_start) }} - {{ formatDate(invoice.period_end) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Items Card -->
                <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                        <h2 class="text-base font-semibold text-gray-900">Invoice Details</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50/50 border-b border-gray-200">
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-6 py-3">Description</th>
                                    <th class="px-6 py-3 text-right">Quantity</th>
                                    <th class="px-6 py-3 text-right">Unit Price</th>
                                    <th class="px-6 py-3 text-right">Tax Rate</th>
                                    <th class="px-6 py-3 text-right">Net Amount</th>
                                    <th class="px-6 py-3 text-right">Tax Amount</th>
                                    <th class="px-6 py-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="invoice.data && invoice.data.items && invoice.data.items.length > 0"
                                    v-for="(item, index) in invoice.data.items"
                                    :key="index"
                                    class="hover:bg-gray-50/30 transition-colors"
                                >
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ item.description }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-600">{{ formatNumber(item.quantity) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-600">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-600">{{ item.tax_rate }}%</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-600">{{ formatCurrency(item.line_net) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-600">{{ formatCurrency(item.line_tax) }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">{{ formatCurrency(item.line_total) }}</td>
                                </tr>
                                <tr v-else>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        No items found for this invoice
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Section -->
                    <div class="border-t border-gray-200 bg-gray-50/30">
                        <div class="max-w-sm ml-auto p-6">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Subtotal:</span>
                                    <span class="font-medium text-gray-900">{{ formatCurrency(invoice.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Tax (VAT):</span>
                                    <span class="font-medium text-gray-900">{{ formatCurrency(invoice.tax) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-200">
                                    <span class="text-base font-semibold text-gray-900">Total:</span>
                                    <span class="text-xl font-bold text-blue-600">{{ formatCurrency(invoice.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FIRS E-Invoicing Status Card -->
                <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-semibold text-gray-900">FIRS E-Invoicing Status</h2>
                            <button
                                v-if="firsStatus && firsStatus.status !== 'approved' && firsStatus.status !== 'not_authenticated'"
                                @click="submitToFirs"
                                :disabled="submittingToFirs"
                                class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50"
                            >
                                {{ submittingToFirs ? 'Submitting...' : 'Submit to FIRS' }}
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-if="loadingFirsStatus" class="text-center py-4">
                            <span class="text-gray-500">Loading FIRS status...</span>
                        </div>
                        <div v-else-if="firsStatus" class="space-y-4">
                            <!-- Not Authenticated Warning -->
                            <div v-if="firsStatus.status === 'not_authenticated'" class="border-l-4 border-amber-500 bg-amber-50 p-4 rounded-r-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h3 class="text-sm font-medium text-amber-800">FIRS API Not Configured</h3>
                                        <p class="mt-1 text-sm text-amber-700">
                                            FIRS API credentials are not configured. This invoice cannot be automatically submitted to FIRS.
                                        </p>
                                        <p class="mt-2 text-sm text-amber-700 font-medium">
                                            You can still download the invoice in FIRS-compliant formats for manual submission:
                                        </p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <a
                                                :href="route('business.invoices.export.ubl-xml', invoice.id)"
                                                class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-amber-300 text-amber-700 rounded-lg hover:bg-amber-50 transition-colors text-sm font-medium"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                                </svg>
                                                Download UBL XML
                                            </a>
                                            <a
                                                :href="route('business.invoices.export.ubl-json', invoice.id)"
                                                class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-amber-300 text-amber-700 rounded-lg hover:bg-amber-50 transition-colors text-sm font-medium"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                                </svg>
                                                Download UBL JSON
                                            </a>
                                        </div>
                                        <p class="mt-3 text-xs text-amber-600">
                                            💡 Upload these files manually to the FIRS e-invoicing portal: <a href="https://einvoice.firs.gov.ng" target="_blank" class="underline hover:text-amber-800">einvoice.firs.gov.ng</a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="flex items-start gap-3">
                                <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Status:</span>
                                <span
                                    :class="getFirsStatusClass(firsStatus.status)"
                                    class="px-2.5 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ getFirsStatusLabel(firsStatus.status) }}
                                </span>
                            </div>

                            <!-- FIRS Reference -->
                            <div v-if="firsStatus.reference" class="flex items-start gap-3">
                                <span class="text-sm font-medium text-gray-700 whitespace-nowrap">FIRS Reference:</span>
                                <span class="text-sm text-gray-900 font-mono">{{ firsStatus.reference }}</span>
                            </div>

                            <!-- Submission ID -->
                            <div v-if="firsStatus.submission_id" class="flex items-start gap-3">
                                <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Submission ID:</span>
                                <span class="text-sm text-gray-900 font-mono">{{ firsStatus.submission_id }}</span>
                            </div>

                            <!-- Timestamps -->
                            <div v-if="firsStatus.submitted_at" class="flex items-start gap-3">
                                <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Submitted:</span>
                                <span class="text-sm text-gray-900">{{ formatDate(firsStatus.submitted_at) }}</span>
                            </div>
                            <div v-if="firsStatus.approved_at" class="flex items-start gap-3">
                                <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Approved:</span>
                                <span class="text-sm text-gray-900">{{ formatDate(firsStatus.approved_at) }}</span>
                            </div>

                            <!-- Validation Errors -->
                            <div v-if="firsStatus.validation_errors" class="border-t pt-4">
                                <span class="text-sm font-medium text-red-700 block mb-2">Validation Errors:</span>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                    <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                                        <li v-if="typeof firsStatus.validation_errors === 'string'">
                                            {{ firsStatus.validation_errors }}
                                        </li>
                                        <li v-else-if="Array.isArray(firsStatus.validation_errors)"
                                            v-for="(error, idx) in firsStatus.validation_errors"
                                            :key="idx">
                                            {{ error }}
                                        </li>
                                        <li v-else-if="firsStatus.validation_errors.error">
                                            {{ firsStatus.validation_errors.error }}
                                        </li>
                                        <li v-else>
                                            {{ JSON.stringify(firsStatus.validation_errors) }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Help Text -->
                            <div class="pt-4 border-t">
                                <p class="text-xs text-gray-500">
                                    <span v-if="firsStatus.status === 'not_authenticated'">
                                        FIRS API credentials are not configured. Download the UBL XML or JSON files above to manually submit this invoice to the FIRS portal.
                                    </span>
                                    <span v-else-if="firsStatus.status === 'pending'">
                                        This invoice has not been submitted to FIRS yet. Click "Submit to FIRS" to submit it for compliance.
                                    </span>
                                    <span v-else-if="firsStatus.status === 'submitted'">
                                        This invoice has been submitted to FIRS and is pending approval.
                                    </span>
                                    <span v-else-if="firsStatus.status === 'approved'">
                                        This invoice has been approved by FIRS and is fully compliant with Nigerian e-invoicing regulations.
                                    </span>
                                    <span v-else-if="firsStatus.status === 'failed' || firsStatus.status === 'validation_failed'">
                                        This invoice failed FIRS validation. Please review the errors above and resubmit after corrections.
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QR Code & Actions Card -->
                <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                        <h2 class="text-base font-semibold text-gray-900">Invoice QR Code & Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row gap-6 items-center lg:items-start">
                            <div v-if="qrCodeData" class="flex flex-col items-center">
                                <img :src="qrCodeData" alt="Invoice QR Code" class="w-40 h-40 border border-gray-200 rounded-xl shadow-sm" />
                                <p class="text-xs text-gray-500 mt-2">Scan to verify invoice</p>
                            </div>
                            <div v-else class="flex flex-col items-center">
                                <div class="w-40 h-40 bg-gray-100 rounded-xl flex items-center justify-center">
                                    <span class="text-gray-400">Loading QR...</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="space-y-3">
                                    <a
                                        :href="route('business.invoices.jades', invoice.id)"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-sm"
                                        target="_blank"
                                    >
                                        Download Signed E-Invoice (JAdES JSON)
                                    </a>

                                    <button
                                        @click.prevent="downloadPdf"
                                        :disabled="downloadingPdf"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 text-white rounded-xl hover:bg-gray-900 transition-all shadow-sm"
                                    >
                                        <span v-if="!downloadingPdf">Download PDF</span>
                                        <span v-else>Preparing...</span>
                                    </button>

                                    <p class="text-xs text-gray-500">
                                        This is a legally binding electronic invoice signed with a digital signature.
                                        The JAdES JSON file contains the complete invoice data and cryptographic signature.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Actions -->
                <div class="flex justify-between gap-3">
                    <Link
                        :href="route('business.invoices.index')"
                        class="px-6 py-2.5 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium"
                    >
                        Back to Invoices
                    </Link>
                    <div class="flex gap-3">
                        <Link
                            v-if="invoice.status !== 'paid' && invoice.status !== 'cancelled'"
                            :href="route('business.invoices.edit', invoice.id)"
                            class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl font-medium transition-all shadow-sm"
                        >
                            Edit Invoice
                        </Link>
                        <button
                            v-if="invoice.status === 'draft'"
                            @click="markAsSent"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-medium transition-all shadow-sm"
                        >
                            Mark as Sent
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

export default {
    components: { BusinessLayout, Link },
    props: {
        invoice: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            qrCodeData: null,
            downloadingPdf: false,
            firsStatus: null,
            loadingFirsStatus: false,
            submittingToFirs: false,
        };
    },
    computed: {
        isOverdue() {
            if (!this.invoice.due_date) return false;
            if (this.invoice.status === 'paid' || this.invoice.status === 'cancelled') return false;
            return new Date(this.invoice.due_date) < new Date();
        }
    },
    mounted() {
        this.fetchQrCode();
        this.fetchFirsStatus();
    },
    methods: {
        formatCurrency(value) {
            return new Intl.NumberFormat('en-NG', {
                style: 'currency',
                currency: 'NGN',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0);
        },
        formatNumber(value) {
            return new Intl.NumberFormat('en-NG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0);
        },
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('en-NG', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },
        formatStatus(status) {
            if (!status) return 'Draft';
            return status.charAt(0).toUpperCase() + status.slice(1);
        },
        getStatusClass(status) {
            const classes = {
                draft: 'bg-gray-100 text-gray-800 border-gray-200',
                sent: 'bg-blue-100 text-blue-800 border-blue-200',
                viewed: 'bg-purple-100 text-purple-800 border-purple-200',
                paid: 'bg-green-100 text-green-800 border-green-200',
                cancelled: 'bg-red-100 text-red-800 border-red-200',
            };
            return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
        },
        getStatusDotClass(status) {
            const classes = {
                draft: 'bg-gray-500',
                sent: 'bg-blue-500',
                viewed: 'bg-purple-500',
                paid: 'bg-green-500',
                cancelled: 'bg-red-500',
            };
            return classes[status] || 'bg-gray-500';
        },
        fetchQrCode() {
            fetch(`/business/invoices/${this.invoice.id}/qr`)
                .then(res => res.json())
                .then(data => {
                    this.qrCodeData = data.qr;
                })
                .catch(error => {
                    console.error('Failed to fetch QR code:', error);
                });
        },
        async downloadPdf() {
            if (this.downloadingPdf) return;
            this.downloadingPdf = true;

            try {
                // Use canonical business-level signed PDF route
                let signedRes = await fetch(route('business.invoices.pdf.signed.business', { invoice: this.invoice.id }), { credentials: 'same-origin' });
                // Fallback: try the direct invoices path
                if (!signedRes.ok) {
                    signedRes = await fetch(`/business/invoices/${this.invoice.id}/pdf/signed`, { credentials: 'same-origin' });
                }

                if (!signedRes.ok) {
                    // Attempt to extract server error message
                    let errMsg = 'Failed to get signed URL';
                    try {
                        const errJson = await signedRes.json();
                        errMsg = errJson.error || errJson.message || JSON.stringify(errJson);
                    } catch (e) {
                        // ignore
                    }
                    throw new Error(errMsg);
                }

                const signed = await signedRes.json();
                const url = signed.url;
                if (!url) throw new Error('Signed URL not returned');

                try {
                    const fileRes = await fetch(url);
                    if (!fileRes.ok) throw new Error('Failed to download PDF');
                    const blob = await fileRes.blob();
                    const blobUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = (this.invoice.invoice_number || this.invoice.id) + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(blobUrl);
                } catch (e) {
                    // Fallback to opening the signed URL in a new tab if fetch fails (CORS or remote URL)
                    window.open(url, '_blank');
                }
            } catch (err) {
                alert('Could not download PDF: ' + (err.message || err));
            } finally {
                this.downloadingPdf = false;
            }
        },
        markAsSent() {
            if (confirm('Mark this invoice as sent? This will notify the customer.')) {
                router.patch(route('business.invoices.update-status', { invoice: this.invoice.id }), {
                    status: 'sent'
                }, {
                    preserveState: true,
                    onSuccess: () => {
                        // Refresh the page to show updated status
                        router.reload();
                    }
                });
            }
        },
        async fetchFirsStatus() {
            this.loadingFirsStatus = true;
            try {
                const response = await fetch(route('business.invoices.firs-status', { invoice: this.invoice.id }), {
                    credentials: 'same-origin',
                });
                if (response.ok) {
                    this.firsStatus = await response.json();
                }
            } catch (error) {
                console.error('Failed to fetch FIRS status:', error);
            } finally {
                this.loadingFirsStatus = false;
            }
        },
        async submitToFirs() {
            if (!confirm('Submit this invoice to FIRS for e-invoicing compliance?')) {
                return;
            }

            this.submittingToFirs = true;
            try {
                const response = await fetch(route('business.invoices.submit-to-firs', { invoice: this.invoice.id }), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'same-origin',
                });

                if (response.ok) {
                    alert('Invoice submission to FIRS has been queued. Please refresh to see the status.');
                    // Refresh FIRS status after a short delay
                    setTimeout(() => {
                        this.fetchFirsStatus();
                    }, 2000);
                } else {
                    throw new Error('Submission failed');
                }
            } catch (error) {
                console.error('Failed to submit to FIRS:', error);
                alert('Failed to submit invoice to FIRS. Please try again.');
            } finally {
                this.submittingToFirs = false;
            }
        },
        getFirsStatusClass(status) {
            const classes = {
                pending: 'bg-yellow-100 text-yellow-800',
                submitted: 'bg-blue-100 text-blue-800',
                approved: 'bg-green-100 text-green-800',
                failed: 'bg-red-100 text-red-800',
                validation_failed: 'bg-red-100 text-red-800',
                error: 'bg-red-100 text-red-800',
                not_authenticated: 'bg-amber-100 text-amber-800',
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },
        getFirsStatusLabel(status) {
            const labels = {
                pending: 'Pending',
                submitted: 'Submitted',
                approved: 'Approved',
                failed: 'Failed',
                validation_failed: 'Validation Failed',
                error: 'Error',
                not_authenticated: 'Not Authenticated',
            };
            return labels[status] || status;
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('en-NG', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }).format(date);
        }
    },
};
</script>

<style scoped>
/* Smooth transitions */
tr {
    transition: background-color 0.2s ease;
}

/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Status badge styling */
.bg-gray-100, .bg-blue-100, .bg-purple-100, .bg-green-100, .bg-red-100 {
    backdrop-filter: blur(4px);
}

/* Loading state for QR code */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
