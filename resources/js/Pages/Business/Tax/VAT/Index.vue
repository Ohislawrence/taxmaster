<template>
    <BusinessLayout>
        <Head title="VAT" />
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">VAT Management</h1>
                    <p class="text-gray-600 mt-1">Track and manage your VAT returns and payments</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <p class="text-sm text-blue-600 font-medium">Current Rate</p>
                    <p class="text-3xl font-bold text-blue-900">7.5%</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <p class="text-sm text-green-600 font-medium">Total Collected</p>
                    <p class="text-3xl font-bold text-green-900">₦{{ formatCurrency(totalCollected) }}</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                    <p class="text-sm text-orange-600 font-medium">Total Paid</p>
                    <p class="text-3xl font-bold text-orange-900">₦{{ formatCurrency(totalPaid) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <p class="text-sm text-purple-600 font-medium">Balance Due</p>
                    <p class="text-3xl font-bold text-purple-900">₦{{ formatCurrency(balanceDue) }}</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 flex gap-8">
                <button
                    @click="activeTab = 'returns'"
                    :class="activeTab === 'returns' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 hover:text-gray-900'"
                    class="pb-4 font-medium transition"
                >
                    VAT Returns
                </button>
                <button
                    @click="activeTab = 'calculator'"
                    :class="activeTab === 'calculator' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 hover:text-gray-900'"
                    class="pb-4 font-medium transition"
                >
                    Calculate VAT
                </button>
            </div>

            <!-- VAT Returns Tab -->
            <div v-if="activeTab === 'returns'" class="space-y-4">
                <div v-if="vatReturns.length > 0" class="space-y-4">
                    <div
                        v-for="vat in vatReturns"
                        :key="vat.id"
                        class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ vat.period_start }} - {{ vat.period_end }}
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">Filing Status: <span class="font-medium">{{ vat.filing_status }}</span></p>
                            </div>
                            <span
                                :class="getStatusBadge(vat.filing_status)"
                                class="px-3 py-1 rounded-full text-sm font-bold"
                            >
                                {{ vat.filing_status.toUpperCase() }}
                            </span>
                        </div>

                        <!-- VAT Numbers -->
                        <div class="grid md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded">
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">VAT Collected</p>
                                <p class="text-lg font-bold text-gray-900">₦{{ formatCurrency(vat.vat_collected) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">VAT Paid (Input)</p>
                                <p class="text-lg font-bold text-gray-900">₦{{ formatCurrency(vat.vat_paid) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">Net VAT Due</p>
                                <p class="text-lg font-bold text-green-600">₦{{ formatCurrency(vat.net_vat) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 uppercase tracking-wide">Payment Status</p>
                                <p class="text-sm font-bold"
                                   :class="vat.payment_status === 'paid' ? 'text-green-600' : 'text-orange-600'"
                                >
                                    {{ vat.payment_status === 'paid' ? '✓ Paid' : 'Not Paid' }}
                                </p>
                            </div>
                        </div>

                        <!-- Form 002 Reference -->
                        <div v-if="vat.form_002_reference" class="mb-4 p-3 bg-blue-50 rounded flex items-center justify-between">
                            <div>
                                <p class="text-xs text-blue-600 uppercase tracking-wide">Form 002</p>
                                <p class="font-mono text-sm text-blue-900">{{ vat.form_002_reference }}</p>
                            </div>
                            <a
                                :href="`/business/vat/${vat.id}/form-002`"
                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-medium transition"
                            >
                                Download PDF
                            </a>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-if="vat.filing_status !== 'submitted'"
                                @click="viewVATDetails(vat)"
                                class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded text-sm font-medium transition"
                            >
                                Edit
                            </button>
                            <button
                                v-if="vat.filing_status === 'draft' && vat.payment_status !== 'paid'"
                                @click="submitVAT(vat)"
                                :disabled="submittingId === vat.id"
                                class="px-4 py-2 bg-green-50 text-green-600 hover:bg-green-100 rounded text-sm font-medium transition disabled:opacity-50"
                            >
                                {{ submittingId === vat.id ? 'Submitting...' : 'Submit Return' }}
                            </button>
                            <button
                                v-if="vat.payment_status !== 'paid'"
                                class="px-4 py-2 bg-orange-50 text-orange-600 hover:bg-orange-100 rounded text-sm font-medium transition"
                            >
                                Payment Info
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-lg shadow p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No VAT returns yet</h3>
                    <p class="text-gray-600 mb-6">Your VAT returns will be generated based on your transaction data</p>
                    <button
                        @click="activeTab = 'calculator'"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        Calculate VAT Now
                    </button>
                </div>
            </div>

            <!-- Calculator Tab -->
            <div v-if="activeTab === 'calculator'" class="grid md:grid-cols-2 gap-6">
                <!-- Input Section -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        Calculate VAT
                        <span
                            class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                            title="Use this calculator to estimate VAT for a period. You can create a draft return from the result and update it before submission."
                        >
                            i
                        </span>
                    </h2>

                    <div class="space-y-4">
                        <!-- Period Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Period
                                <span
                                    class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                                    title="Choose how often you file VAT. This sets the period for the return you are creating."
                                >
                                    i
                                </span>
                            </label>
                            <select
                                v-model="calculator.period"
                                @change="updateCalculatorYear"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                            >
                                <option value="">Select Period...</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>

                        <!-- Taxable Sales -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Taxable Sales
                                <span
                                    class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                                    title="Enter the total value of VATable sales for the period. This drives the output VAT calculation."
                                >
                                    i
                                </span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-2 text-gray-600">₦</span>
                                <input
                                    v-model.number="calculator.salesAmount"
                                    type="number"
                                    placeholder="0.00"
                                    @input="calculateVAT"
                                    class="w-full px-4 py-2 pl-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                        </div>

                        <!-- Input VAT -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Input VAT (Invoices)
                                <span
                                    class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                                    title="Enter VAT paid on business purchases supported by valid invoices. This will be deducted from your output VAT."
                                >
                                    i
                                </span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-2 text-gray-600">₦</span>
                                <input
                                    v-model.number="calculator.inputVAT"
                                    type="number"
                                    placeholder="0.00"
                                    @input="calculateVAT"
                                    class="w-full px-4 py-2 pl-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Notes (Optional)
                                <span
                                    class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                                    title="Add context about exemptions, adjustments, or reconciliations used in this calculation."
                                >
                                    i
                                </span>
                            </label>
                            <textarea
                                v-model="calculator.notes"
                                rows="3"
                                placeholder="Any special calculations or exemptions..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            ></textarea>
                        </div>

                        <button
                            @click="saveCalculation"
                            :disabled="!calculator.period || !calculator.salesAmount || savingCalculation"
                            class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition disabled:opacity-50"
                        >
                            {{ savingCalculation ? 'Creating Return...' : 'Create VAT Return' }}
                        </button>
                    </div>
                </div>

                <!-- Results Section -->
                <div v-if="calculation" class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg shadow p-6 border border-green-200">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">VAT Summary</h2>

                    <div class="space-y-4">
                        <div class="p-4 bg-white rounded">
                            <p class="text-sm text-gray-600">Taxable Sales</p>
                            <p class="text-2xl font-bold text-gray-900">₦{{ formatCurrency(calculation.salesAmount) }}</p>
                        </div>

                        <div class="p-4 bg-white rounded border-l-4 border-blue-600">
                            <p class="text-sm text-gray-600">Output VAT (7.5%)</p>
                            <p class="text-2xl font-bold text-blue-600">₦{{ formatCurrency(calculation.outputVAT) }}</p>
                        </div>

                        <div class="p-4 bg-white rounded border-l-4 border-orange-600">
                            <p class="text-sm text-gray-600">Input VAT (Deductible)</p>
                            <p class="text-2xl font-bold text-orange-600">-₦{{ formatCurrency(calculation.inputVAT) }}</p>
                        </div>

                        <div class="h-px bg-gray-300 my-2"></div>

                        <div class="p-4 bg-gradient-to-r from-green-100 to-green-50 rounded border-l-4 border-green-600">
                            <p class="text-sm text-gray-700 font-semibold">Net VAT Due to FIRS</p>
                            <p class="text-3xl font-bold text-green-600">₦{{ formatCurrency(calculation.netVAT) }}</p>
                        </div>

                        <div v-if="calculation.discount" class="p-4 bg-white rounded">
                            <p class="text-sm text-gray-600">Less: Early Payment Discount (2%)</p>
                            <p class="text-lg font-bold text-green-600">-₦{{ formatCurrency(calculation.discount) }}</p>
                        </div>

                        <div class="p-4 bg-white rounded border border-gray-200 text-center">
                            <p class="text-xs text-gray-600 uppercase tracking-wide mb-1">Amount to Remit</p>
                            <p class="text-3xl font-bold text-blue-600">₦{{ formatCurrency(calculation.amountToRemit) }}</p>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-6 p-4 bg-blue-100 border border-blue-400 rounded">
                        <h3 class="font-bold text-blue-900 mb-2">
                            Payment Instructions
                            <span
                                class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-200 text-[10px] font-bold text-blue-900"
                                title="Use these steps after you submit the return. Keep proof of payment for audit purposes."
                            >
                                i
                            </span>
                        </h3>
                        <ol class="text-sm text-blue-900 space-y-1 list-decimal list-inside">
                            <li>Log into FIRS online portal</li>
                            <li>Use Form 002 reference to file return</li>
                            <li>Pay using FIRS payment channels</li>
                            <li>Keep proof of payment</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <Teleport to="#app">
                <div
                    v-if="successMessage"
                    class="fixed top-4 right-4 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg shadow-lg"
                >
                    {{ successMessage }}
                </div>
            </Teleport>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

const props = defineProps({
    vatReturns: Array,
})

const activeTab = ref('returns')
const calculator = ref({
    period: '',
    salesAmount: null,
    inputVAT: null,
    notes: '',
})
const calculation = ref(null)
const savingCalculation = ref(false)
const submittingId = ref(null)
const successMessage = ref('')

const VAT_RATE = 0.075 // 7.5%

const vatReturns = computed(() => props.vatReturns || [])

const totalCollected = computed(() => {
    return vatReturns.value.reduce((sum, vat) => sum + (vat.vat_collected || 0), 0)
})

const totalPaid = computed(() => {
    return vatReturns.value.reduce((sum, vat) => sum + (vat.vat_paid || 0), 0)
})

const balanceDue = computed(() => {
    return totalCollected.value - totalPaid.value
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0)
}

const calculateVAT = () => {
    if (!calculator.value.salesAmount) {
        calculation.value = null
        return
    }

    const sales = calculator.value.salesAmount
    const inputVAT = calculator.value.inputVAT || 0
    const outputVAT = sales * VAT_RATE
    const netVAT = Math.max(0, outputVAT - inputVAT)
    const discount = netVAT >= 100000 ? netVAT * 0.02 : 0 // 2% discount for amounts >= 100k

    calculation.value = {
        salesAmount: sales,
        outputVAT: outputVAT,
        inputVAT: inputVAT,
        netVAT: netVAT,
        discount: discount,
        amountToRemit: netVAT - discount,
    }
}

const updateCalculatorYear = () => {
    calculator.value.salesAmount = null
    calculator.value.inputVAT = null
    calculation.value = null
}

const saveCalculation = () => {
    if (!calculator.value.period || !calculator.value.salesAmount || !calculation.value) {
        return
    }

    savingCalculation.value = true

    fetch('/business/vat/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
        },
        body: JSON.stringify({
            period: calculator.value.period,
            vat_collected: calculation.value.outputVAT,
            vat_paid: calculation.value.inputVAT,
            net_vat: calculation.value.netVAT,
            notes: calculator.value.notes,
        }),
    })
    .then(() => {
        successMessage.value = 'VAT return created successfully'
        calculator.value = { period: '', salesAmount: null, inputVAT: null, notes: '' }
        calculation.value = null
        setTimeout(() => {
            window.location.reload()
        }, 2000)
    })
    .catch(() => {
        successMessage.value = 'Failed to create VAT return'
    })
    .finally(() => {
        savingCalculation.value = false
    })
}

const submitVAT = (vat) => {
    submittingId.value = vat.id

    fetch(`/business/vat/${vat.id}/submit`, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
        },
    })
    .then(() => {
        successMessage.value = 'VAT return submitted'
        setTimeout(() => {
            window.location.reload()
        }, 1500)
    })
    .finally(() => {
        submittingId.value = null
    })
}

const viewVATDetails = (vat) => {
    // Would navigate to detailed VAT page
    window.location.href = `/business/vat/${vat.id}`
}

const getStatusBadge = (status) => {
    const badges = {
        'draft': 'bg-gray-100 text-gray-800',
        'submitted': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
    }
    return badges[status] || 'bg-gray-100 text-gray-800'
}
</script>
