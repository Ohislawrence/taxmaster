<template>
    <BusinessLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Transactions</h1>
                    <p class="text-gray-600 mt-1">Review, categorize and manage your transactions</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 space-y-4 md:space-y-0 md:flex md:items-end md:gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input
                        v-model="filters.search"
                        @input="applyFilters"
                        type="text"
                        placeholder="Ref, description, amount..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select
                        v-model="filters.category"
                        @change="applyFilters"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                    >
                        <option value="">All Categories</option>
                        <option value="uncategorized">Uncategorized</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">
                            {{ cat }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select
                        v-model="filters.type"
                        @change="applyFilters"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                    >
                        <option value="">All Types</option>
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>

                <div>
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 border border-gray-300 hover:bg-gray-50 rounded-lg font-medium transition"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="transactions.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Confidence</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="transaction in transactions"
                                :key="transaction.id"
                                class="hover:bg-gray-50 transition"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ formatDate(transaction.transaction_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <p class="font-medium text-gray-900">{{ transaction.description }}</p>
                                    <p class="text-xs text-gray-500">Ref: {{ transaction.reference }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        v-if="transaction.category"
                                        class="inline-block px-3 py-1 rounded-full text-xs font-medium"
                                        :class="getCategoryColor(transaction.category)"
                                    >
                                        {{ transaction.category }}
                                    </span>
                                    <span v-else class="text-gray-400 text-xs">Uncategorized</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold">
                                    <span :class="transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'">
                                        {{ transaction.type === 'credit' ? '+' : '-' }}₦{{ formatCurrency(transaction.amount) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div v-if="transaction.category" class="flex items-center space-x-1">
                                        <svg
                                            :class="getConfidenceColor(transaction.ai_confidence)"
                                            class="w-4 h-4"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span class="text-xs">{{ Math.round(transaction.ai_confidence * 100) }}%</span>
                                    </div>
                                    <span v-else class="text-gray-400 text-xs">Not set</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button
                                        @click="editingId = transaction.id"
                                        class="text-blue-600 hover:text-blue-700 font-medium"
                                    >
                                        {{ transaction.category ? 'Edit' : 'Categorize' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No transactions found</h3>
                    <p class="text-gray-600">Connect your bank account to start importing transactions</p>
                </div>
            </div>

            <!-- Category Modal -->
            <Teleport to="#app">
                <div v-if="editingId" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold text-gray-900">Categorize Transaction</h2>
                            <button
                                @click="editingId = null"
                                class="text-gray-400 hover:text-gray-600"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div v-if="currentTransaction" class="mb-6 p-4 bg-gray-50 rounded">
                            <p class="text-sm text-gray-600">{{ currentTransaction.description }}</p>
                            <p class="text-lg font-bold text-gray-900 mt-1">
                                ₦{{ formatCurrency(currentTransaction.amount) }}
                            </p>
                        </div>

                        <div class="space-y-3 mb-6">
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 mb-2 block">Select Category</span>
                                <select
                                    v-model="selectedCategory"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                                >
                                    <option value="">Choose a category...</option>
                                    <option v-for="cat in categories" :key="cat" :value="cat">
                                        {{ cat }}
                                    </option>
                                </select>
                            </label>
                        </div>

                        <div class="flex gap-3">
                            <button
                                @click="editingId = null"
                                class="flex-1 px-4 py-2 border border-gray-300 hover:bg-gray-50 rounded-lg font-medium transition"
                            >
                                Cancel
                            </button>
                            <button
                                @click="saveCategory"
                                :disabled="!selectedCategory || savingCategory"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition disabled:opacity-50"
                            >
                                {{ savingCategory ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>

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
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

const props = defineProps({
    transactions: Object, // Paginated response from Laravel
    categories: Array,
})

const filters = ref({
    search: '',
    category: '',
    type: '',
})

const editingId = ref(null)
const selectedCategory = ref('')
const savingCategory = ref(false)
const successMessage = ref('')

const categories = computed(() => props.categories || [
    'Sales/Revenue',
    'Operating Expenses',
    'Staff Salaries',
    'Utilities',
    'Transport/Logistics',
    'Marketing',
    'Professional Services',
    'Equipment Purchase',
    'Other Expense',
    'Investment',
    'Loan Repayment',
    'Personal Withdrawal',
])

const currentTransaction = computed(() => {
    const transactionsData = props.transactions?.data || []
    return transactionsData.find(t => t.id === editingId.value)
})

const filteredTransactions = computed(() => {
    const transactionsData = props.transactions?.data || []
    return transactionsData.filter(t => {
        if (filters.value.search) {
            const search = filters.value.search.toLowerCase()
            if (!t.description.toLowerCase().includes(search) &&
                !t.reference.toLowerCase().includes(search) &&
                !t.amount.toString().includes(search)) {
                return false
            }
        }

        if (filters.value.category) {
            if (filters.value.category === 'uncategorized') {
                if (t.category) return false
            } else {
                if (t.category !== filters.value.category) return false
            }
        }

        if (filters.value.type && t.type !== filters.value.type) {
            return false
        }

        return true
    })
})

const transactions = computed(() => filteredTransactions.value)

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    })
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value)
}

const getCategoryColor = (category) => {
    const colors = {
        'Sales/Revenue': 'bg-green-100 text-green-800',
        'Operating Expenses': 'bg-red-100 text-red-800',
        'Staff Salaries': 'bg-orange-100 text-orange-800',
        'Utilities': 'bg-blue-100 text-blue-800',
        'Transport/Logistics': 'bg-purple-100 text-purple-800',
        'Marketing': 'bg-pink-100 text-pink-800',
        'Professional Services': 'bg-indigo-100 text-indigo-800',
        'Equipment Purchase': 'bg-yellow-100 text-yellow-800',
    }
    return colors[category] || 'bg-gray-100 text-gray-800'
}

const getConfidenceColor = (confidence) => {
    if (confidence >= 0.9) return 'text-green-500'
    if (confidence >= 0.7) return 'text-yellow-500'
    return 'text-orange-500'
}

const applyFilters = () => {
    // Filters are applied reactively
}

const resetFilters = () => {
    filters.value = {
        search: '',
        category: '',
        type: '',
    }
}

const saveCategory = () => {
    if (!selectedCategory.value || !currentTransaction.value) return

    savingCategory.value = true

    fetch(`/business/transactions/${currentTransaction.value.id}/category`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            category: selectedCategory.value,
        }),
    })
    .then((res) => {
        if (!res.ok) {
            throw new Error(`HTTP ${res.status}`)
        }
        // Update the transaction in-place without page reload
        const txn = props.transactions?.data?.find(t => t.id === currentTransaction.value.id)
        if (txn) {
            txn.category = selectedCategory.value
            txn.user_verified = true
            txn.ai_confidence = 1.0
        }
        successMessage.value = 'Transaction categorized successfully'
        editingId.value = null
        selectedCategory.value = ''
        setTimeout(() => {
            successMessage.value = ''
        }, 3000)
    })
    .catch((error) => {
        successMessage.value = 'Failed to save category: ' + error.message
        setTimeout(() => {
            successMessage.value = ''
        }, 3000)
    })
    .finally(() => {
        savingCategory.value = false
    })
}
</script>
