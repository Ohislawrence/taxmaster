<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">VAT Return Details</h1>
                    <p class="text-gray-600 mt-1">{{ vatReturn.period_label }}</p>
                </div>
                <Link
                    href="/admin/vat-returns"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition"
                >
                    ← Back to Returns
                </Link>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 space-y-6">
                    <!-- Return Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Return Details</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Period</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.period_label }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Due Date</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.due_date }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">Status</p>
                                <span
                                    :class="getStatusClass(vatReturn.status)"
                                    class="inline-block px-3 py-1 rounded-full text-sm font-medium capitalize mt-1"
                                >
                                    {{ vatReturn.status_label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- VAT Breakdown -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">VAT Breakdown</h2>
                        <div class="space-y-4">
                            <div class="p-4 bg-gray-50 rounded">
                                <p class="text-sm text-gray-600">VATable Sales</p>
                                <p class="text-xl font-bold text-gray-900">₦{{ formatCurrency(vatReturn.vat_sales) }}</p>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-blue-50 rounded">
                                <span class="text-gray-900 font-medium">Output VAT (7.5% on Sales)</span>
                                <span class="text-2xl font-bold text-blue-600">₦{{ formatCurrency(vatReturn.output_vat) }}</span>
                            </div>
                            <div class="p-4 bg-gray-50 rounded">
                                <p class="text-sm text-gray-600">VATable Expenses</p>
                                <p class="text-xl font-bold text-gray-900">₦{{ formatCurrency(vatReturn.vat_expenses) }}</p>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-green-50 rounded">
                                <span class="text-gray-900 font-medium">Input VAT (7.5% on Purchases)</span>
                                <span class="text-2xl font-bold text-green-600">₦{{ formatCurrency(vatReturn.input_vat) }}</span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-orange-50 rounded border-2 border-orange-300">
                                <span class="text-gray-900 font-bold text-lg">Net VAT Payable</span>
                                <span class="text-3xl font-bold text-orange-600">₦{{ formatCurrency(vatReturn.net_vat) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filing Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Filing & Payment Information</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Form 002 Reference</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.form_002_reference || 'Not generated' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Submitted At</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.submitted_at || 'Not submitted yet' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Paid At</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.paid_at || 'Not paid yet' }}</p>
                            </div>
                            <div v-if="vatReturn.payment_reference">
                                <p class="text-sm text-gray-600">Payment Reference</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.payment_reference }}</p>
                            </div>
                            <div v-if="vatReturn.notes" class="md:col-span-2">
                                <p class="text-sm text-gray-600">Notes</p>
                                <p class="text-gray-900 mt-1">{{ vatReturn.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Business Information</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Business Name</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.business.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.business.email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">TIN</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.business.tax_identification_number || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Owner</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.business.owner.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Owner Email</p>
                                <p class="text-gray-900 font-medium mt-1">{{ vatReturn.business.owner.email }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Summary Card -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Summary</h3>
                        <div class="space-y-3">
                            <div class="border-b border-purple-200 pb-3">
                                <p class="text-sm text-gray-600">Period</p>
                                <p class="font-bold text-gray-900">{{ vatReturn.period_label }}</p>
                            </div>
                            <div class="border-b border-purple-200 pb-3">
                                <p class="text-sm text-gray-600">Collection Rate</p>
                                <p class="font-bold text-gray-900">7.5% NGN</p>
                            </div>
                            <div class="border-b border-purple-200 pb-3">
                                <p class="text-sm text-gray-600">Status</p>
                                <span
                                    :class="getStatusClass(vatReturn.status)"
                                    class="inline-block px-2 py-1 rounded text-xs font-medium capitalize"
                                >
                                    {{ vatReturn.status_label }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Updated At</p>
                                <p class="font-medium text-gray-900">{{ vatReturn.updated_at }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-xs text-gray-600">Output</p>
                            <p class="font-bold text-blue-600">₦{{ formatCurrency(vatReturn.output_vat) }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-xs text-gray-600">Input</p>
                            <p class="font-bold text-green-600">₦{{ formatCurrency(vatReturn.input_vat) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    return: Object,
})

const vatReturn = props.return

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0)
}

const getStatusClass = (status) => {
    const classes = {
        'draft': 'bg-gray-100 text-gray-800',
        'submitted': 'bg-blue-100 text-blue-800',
        'paid': 'bg-green-100 text-green-800',
        'overdue': 'bg-red-100 text-red-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
