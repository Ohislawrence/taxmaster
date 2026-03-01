<template>
    <BusinessLayout>
        <Head title="Create Tax Return" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <div class="mb-8">
                <Link href="/business/tax-returns" class="text-blue-600 hover:underline">&larr; Back to Tax Returns</Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-4">Create New Tax Return</h1>
                <p class="text-gray-600 mt-1">File a new tax return for your business</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6">
                <!-- Return Type -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Return Type</label>
                    <select v-model="form.return_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Return Type</option>
                        <option value="PAYE">PAYE (Pay As You Earn)</option>
                        <option value="CIT">CIT (Corporate Income Tax)</option>
                        <option value="VAT">VAT (Value Added Tax)</option>
                        <option value="WHT">WHT (Withholding Tax)</option>
                    </select>
                    <p v-if="errors.return_type" class="text-red-600 text-sm mt-1">{{ errors.return_type[0] }}</p>
                </div>

                <!-- Tax Period -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tax Period (Start)</label>
                        <input 
                            v-model="form.tax_period_start" 
                            type="date" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p v-if="errors.tax_period_start" class="text-red-600 text-sm mt-1">{{ errors.tax_period_start[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tax Period (End)</label>
                        <input 
                            v-model="form.tax_period_end" 
                            type="date" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p v-if="errors.tax_period_end" class="text-red-600 text-sm mt-1">{{ errors.tax_period_end[0] }}</p>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                    <input 
                        v-model="form.due_date" 
                        type="date" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="errors.due_date" class="text-red-600 text-sm mt-1">{{ errors.due_date[0] }}</p>
                </div>

                <!-- Staff Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Staff Members</label>
                    <div class="space-y-2">
                        <div v-if="staff.length === 0" class="text-gray-500 text-sm py-4">
                            No staff members available. <Link href="/business/staff/create" class="text-blue-600 hover:underline">Add staff first</Link>
                        </div>
                        <label v-for="staffMember in staff" :key="staffMember.id" class="flex items-center">
                            <input 
                                type="checkbox" 
                                :value="staffMember.id"
                                v-model="form.staff_ids"
                                class="rounded border-gray-300 text-blue-600"
                            />
                            <span class="ml-2 text-gray-700">
                                {{ staffMember.full_name }} - ₦{{ formatCurrency(staffMember.monthly_salary) }}/month
                            </span>
                        </label>
                    </div>
                    <p v-if="errors.staff_ids" class="text-red-600 text-sm mt-1">{{ errors.staff_ids[0] }}</p>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea 
                        v-model="form.description" 
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Add any notes or references..."
                    ></textarea>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button 
                        type="submit"
                        :disabled="processing"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        {{ processing ? 'Creating...' : 'Create Tax Return' }}
                    </button>
                    <Link href="/business/tax-returns" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

defineProps({
    business: Object,
    staff: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const processing = ref(false);

const form = ref({
    return_type: '',
    tax_period_start: '',
    tax_period_end: '',
    due_date: '',
    staff_ids: [],
    description: '',
});

const submitForm = () => {
    processing.value = true;
    router.post('/business/tax-returns', form.value, {
        onFinish: () => {
            processing.value = false;
        },
    });
};

const formatCurrency = (value) => {
    if (!value) return '0.00'
    return parseFloat(value).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
</script>
