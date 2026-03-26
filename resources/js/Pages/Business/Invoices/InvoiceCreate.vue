<template>
    <BusinessLayout title="Create Invoice">
        <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header Section -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                                Create Sales Invoice
                            </h1>
                            <p class="text-sm text-gray-500 mt-1">
                                Generate a new sales invoice for your customer
                            </p>
                        </div>
                        <Link
                            :href="route('business.invoices.index')"
                            class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            Back to Invoices
                        </Link>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Invoice Details Card -->
                    <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                            <h2 class="text-base font-semibold text-gray-900">Invoice Details</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Buyer Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.buyer_name"
                                        type="text"
                                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        :class="{ 'border-red-500': errors.buyer_name }"
                                        required
                                    />
                                    <p v-if="errors.buyer_name" class="mt-1 text-xs text-red-500">{{ errors.buyer_name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Buyer TIN
                                        <span class="text-xs text-gray-400">(Optional)</span>
                                    </label>
                                    <input
                                        v-model="form.buyer_tin"
                                        type="text"
                                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        placeholder="Tax Identification Number"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Due Date
                                    </label>
                                    <input
                                        v-model="form.due_date"
                                        type="date"
                                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Bank Account
                                        <span class="text-xs text-gray-400">(Optional)</span>
                                    </label>
                                    <select
                                        v-model="form.bank_account_id"
                                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    >
                                        <option :value="null">Select bank account</option>
                                        <option v-for="acct in bankAccounts" :key="acct.id" :value="acct.id">
                                            {{ acct.bank_name }} - {{ acct.account_number }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items Card -->
                    <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white flex justify-between items-center">
                            <h2 class="text-base font-semibold text-gray-900">Line Items</h2>
                            <button
                                type="button"
                                @click="addItem"
                                class="px-3 py-1.5 text-sm bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                            >
                                Add Item
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50/50">
                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        <th class="px-4 py-3">Description</th>
                                        <th class="px-4 py-3 w-24">Quantity</th>
                                        <th class="px-4 py-3 w-32">Unit Price (₦)</th>
                                        <th class="px-4 py-3 w-24">Tax (%)</th>
                                        <th class="px-4 py-3 w-32 text-right">Line Total</th>
                                        <th class="px-4 py-3 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, idx) in items" :key="idx" class="border-b border-gray-100 hover:bg-gray-50/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <input
                                                v-model="item.description"
                                                type="text"
                                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="Item description"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                v-model.number="item.quantity"
                                                type="number"
                                                step="0.01"
                                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                v-model.number="item.unit_price"
                                                type="number"
                                                step="0.01"
                                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                v-model.number="item.tax_rate"
                                                type="number"
                                                step="0.01"
                                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            />
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                            {{ formatCurrency(lineTotal(item)) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button
                                                type="button"
                                                @click="removeItem(idx)"
                                                class="text-gray-400 hover:text-red-500 transition-colors"
                                                :disabled="items.length === 1"
                                            >
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Section -->
                        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/30">
                            <div class="flex flex-col items-end space-y-2">
                                <div class="flex justify-between w-64 text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium text-gray-900">{{ formatCurrency(subtotal) }}</span>
                                </div>
                                <div class="flex justify-between w-64 text-sm">
                                    <span class="text-gray-600">Tax:</span>
                                    <span class="font-medium text-gray-900">{{ formatCurrency(tax) }}</span>
                                </div>
                                <div class="flex justify-between w-64 pt-2 border-t border-gray-200">
                                    <span class="text-base font-semibold text-gray-900">Total:</span>
                                    <span class="text-xl font-bold text-blue-600">{{ formatCurrency(total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3">
                        <Link
                            :href="route('business.invoices.index')"
                            class="px-6 py-2.5 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-medium transition-all shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ isSubmitting ? 'Creating...' : 'Create Invoice' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </BusinessLayout>
</template>

<script>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { Link, router } from '@inertiajs/vue3';

export default {
    components: { BusinessLayout, Link },
    props: {
        bankAccounts: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            form: {
                buyer_name: '',
                buyer_tin: '',
                due_date: '',
                bank_account_id: null,
            },
            items: [
                { description: '', quantity: 1, unit_price: 0, tax_rate: 0 }
            ],
            errors: {},
            isSubmitting: false
        };
    },
    computed: {
        subtotal() {
            return this.items.reduce((sum, item) => {
                return sum + (Number(item.quantity || 0) * Number(item.unit_price || 0));
            }, 0);
        },
        tax() {
            return this.items.reduce((sum, item) => {
                const net = Number(item.quantity || 0) * Number(item.unit_price || 0);
                const taxAmount = net * (Number(item.tax_rate || 0) / 100);
                return sum + taxAmount;
            }, 0);
        },
        total() {
            return this.subtotal + this.tax;
        }
    },
    methods: {
        validateForm() {
            const errors = {};

            if (!this.form.buyer_name || this.form.buyer_name.trim() === '') {
                errors.buyer_name = 'Buyer name is required';
            }

            if (this.items.length === 0) {
                errors.items = 'At least one item is required';
            }

            for (let i = 0; i < this.items.length; i++) {
                const item = this.items[i];
                if (!item.description || item.description.trim() === '') {
                    errors[`item_${i}_description`] = 'Description is required';
                }
                if (!item.quantity || item.quantity <= 0) {
                    errors[`item_${i}_quantity`] = 'Valid quantity is required';
                }
                if (!item.unit_price || item.unit_price <= 0) {
                    errors[`item_${i}_price`] = 'Valid unit price is required';
                }
            }

            this.errors = errors;
            return Object.keys(errors).length === 0;
        },

        async submit() {
            if (!this.validateForm()) {
                // Scroll to first error
                const firstError = document.querySelector('.border-red-500');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            this.isSubmitting = true;

            try {
                // Prepare payload
                const payload = {
                    buyer_name: this.form.buyer_name,
                    buyer_tin: this.form.buyer_tin || null,
                    due_date: this.form.due_date || null,
                    bank_account_id: this.form.bank_account_id,
                    items: this.items.map(item => ({
                        description: item.description,
                        quantity: Number(item.quantity),
                        unit_price: Number(item.unit_price),
                        tax_rate: Number(item.tax_rate || 0),
                    }))
                };

                // Submit using Inertia for better handling
                router.post('/business/invoices', payload, {
                    onSuccess: () => {
                        // Success is handled by Inertia redirect
                        this.isSubmitting = false;
                    },
                    onError: (errors) => {
                        this.errors = errors;
                        this.isSubmitting = false;

                        // Scroll to first error
                        const firstError = document.querySelector('.border-red-500');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });

            } catch (error) {
                console.error('Error creating invoice:', error);
                this.errors.form = error.message || 'Failed to create invoice';
                this.isSubmitting = false;
            }
        },

        addItem() {
            this.items.push({
                description: '',
                quantity: 1,
                unit_price: 0,
                tax_rate: 0
            });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        lineTotal(item) {
            const quantity = Number(item.quantity || 0);
            const unitPrice = Number(item.unit_price || 0);
            const taxRate = Number(item.tax_rate || 0);

            const net = quantity * unitPrice;
            const tax = net * (taxRate / 100);

            return Math.round((net + tax) * 100) / 100;
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('en-NG', {
                style: 'currency',
                currency: 'NGN',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0);
        }
    }
};
</script>

<style scoped>
/* Smooth transitions */
button, input, select {
    transition: all 0.2s ease;
}

/* Remove spinner buttons from number inputs */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    opacity: 0.5;
}

input[type="number"]:hover::-webkit-inner-spin-button,
input[type="number"]:hover::-webkit-outer-spin-button {
    opacity: 1;
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

/* Form field focus styles */
input:focus, select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Disabled button styles */
button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>
