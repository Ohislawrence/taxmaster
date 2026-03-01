<template>
    <BusinessLayout>
        <Head title="Create PAYE Return" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('business.paye.index')" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
                    ← Back to PAYE Returns
                </Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-4">Create PAYE Return</h1>
                <p class="text-gray-600 mt-1">Generate monthly PAYE return for your staff</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Period Selection -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Period</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Month
                                <span
                                    class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                                    title="Select the payroll month you are filing PAYE for. This should match the month your staff were paid."
                                >
                                    i
                                </span>
                            </label>
                            <select
                                v-model="form.month"
                                @change="updatePeriod"
                                class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option value="">Select month</option>
                                <option v-for="(month, index) in months" :key="index" :value="index + 1">
                                    {{ month }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Year
                                <span
                                    class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                                    title="Choose the year for the PAYE period. The return will be created for this month and year combination."
                                >
                                    i
                                </span>
                            </label>
                            <select
                                v-model="form.year"
                                @change="updatePeriod"
                                class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option value="">Select year</option>
                                <option v-for="year in availableYears" :key="year" :value="year">
                                    {{ year }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div v-if="form.period" class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>Period:</strong> {{ form.period }} ({{ getPeriodLabel() }})
                        </p>
                    </div>
                </div>

                <!-- Staff Selection -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Staff Members
                        <span
                            class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                            title="Select the staff members included in this PAYE return. Salaries and reliefs will be used to calculate the tax."
                        >
                            i
                        </span>
                    </h2>

                    <div v-if="staff.length > 0" class="space-y-3">
                        <div
                            v-for="member in staff"
                            :key="member.id"
                            class="flex items-center p-4 border rounded-lg hover:bg-gray-50"
                        >
                            <input
                                type="checkbox"
                                :id="`staff-${member.id}`"
                                :value="member.id"
                                v-model="form.staff_ids"
                                @change="toggleStaff(member)"
                                class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500"
                            >
                            <label :for="`staff-${member.id}`" class="ml-3 flex-1 cursor-pointer">
                                <p class="font-medium text-gray-900">{{ member.first_name }} {{ member.last_name }}</p>
                                <p class="text-sm text-gray-600">{{ member.job_title }} • ₦{{ formatCurrency(member.monthly_salary) }}/month</p>
                            </label>
                        </div>

                        <div v-if="selectedStaff.length > 0" class="mt-4 p-4 bg-green-50 rounded-lg">
                            <p class="text-sm text-green-800 font-medium">
                                {{ selectedStaff.length }} staff member(s) selected
                            </p>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <p class="text-gray-600 mb-4">No staff members found</p>
                        <Link :href="route('business.staff.create')" class="text-blue-600 hover:text-blue-800">
                            + Add Staff Members
                        </Link>
                    </div>
                </div>

                <!-- Staff Schedules (if calculated) -->
                <div v-if="calculatedData && calculatedData.schedules" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">PAYE Calculation Preview</h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross Pay</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reliefs</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Taxable Income</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">PAYE Due</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="schedule in calculatedData.schedules" :key="schedule.staff_id">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ schedule.staff_name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900">
                                        ₦{{ formatCurrency(schedule.gross_pay) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-600">
                                        ₦{{ formatCurrency(schedule.total_reliefs) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900">
                                        ₦{{ formatCurrency(schedule.taxable_income) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-green-600">
                                        ₦{{ formatCurrency(schedule.paye_due) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900">TOTAL</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">
                                        ₦{{ formatCurrency(calculatedData.total_gross_pay) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-gray-600">
                                        ₦{{ formatCurrency(calculatedData.total_reliefs) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">
                                        ₦{{ formatCurrency(calculatedData.total_taxable) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-green-600">
                                        ₦{{ formatCurrency(calculatedData.total_tax_deducted) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-lg shadow p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (Optional)
                        <span
                            class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                            title="Add any internal notes about this PAYE return, such as adjustments or payroll exceptions. These notes are not shared outside your business."
                        >
                            i
                        </span>
                    </label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Add any notes about this return..."
                    ></textarea>
                </div>

                <!-- Actions -->
                <div class="flex justify-between items-center">
                    <button
                        type="button"
                        @click="calculatePreview"
                        :disabled="!canCalculate || calculating"
                        class="px-6 py-3 bg-gray-600 hover:bg-gray-700 disabled:bg-gray-300 text-white font-medium rounded-lg"
                    >
                        {{ calculating ? 'Calculating...' : 'Calculate PAYE' }}
                    </button>

                    <div class="flex gap-3">
                        <Link
                            :href="route('business.paye.index')"
                            class="px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="!calculatedData || processing"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium rounded-lg"
                        >
                            {{ processing ? 'Creating...' : 'Create Return' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import axios from 'axios';

const props = defineProps({
    staff: Array,
});

const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const currentYear = new Date().getFullYear();
const availableYears = Array.from({ length: 5 }, (_, i) => currentYear - i);

const form = ref({
    month: '',
    year: '',
    period: '',
    staff_ids: [],
    notes: '',
});

const selectedStaff = ref([]);
const calculatedData = ref(null);
const calculating = ref(false);
const processing = ref(false);

const canCalculate = computed(() => {
    return form.value.period && form.value.staff_ids.length > 0;
});

const updatePeriod = () => {
    if (form.value.month && form.value.year) {
        const monthStr = String(form.value.month).padStart(2, '0');
        form.value.period = `${form.value.year}-${monthStr}`;
    }
};

const getPeriodLabel = () => {
    if (form.value.month && form.value.year) {
        return `${months[form.value.month - 1]} ${form.value.year}`;
    }
    return '';
};

const toggleStaff = (member) => {
    const index = selectedStaff.value.findIndex(s => s.id === member.id);
    if (index === -1) {
        selectedStaff.value.push(member);
    } else {
        selectedStaff.value.splice(index, 1);
    }
};

const calculatePreview = async () => {
    if (!canCalculate.value) return;

    calculating.value = true;
    calculatedData.value = null;

    try {
        const response = await axios.post(route('business.paye.calculate-preview'), {
            period: form.value.period,
            staff_ids: form.value.staff_ids,
        });

        calculatedData.value = response.data;
    } catch (error) {
        console.error('Calculation error:', error);
        alert('Failed to calculate PAYE. Please try again.');
    } finally {
        calculating.value = false;
    }
};

const submitForm = () => {
    if (!calculatedData.value) {
        alert('Please calculate PAYE first');
        return;
    }

    processing.value = true;

    router.post(route('business.paye.store'), {
        period: form.value.period,
        staff_ids: form.value.staff_ids,
        schedules: calculatedData.value.schedules,
        total_gross_pay: calculatedData.value.total_gross_pay,
        total_tax_deducted: calculatedData.value.total_tax_deducted,
        notes: form.value.notes,
    }, {
        onFinish: () => {
            processing.value = false;
        },
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount || 0);
};
</script>
