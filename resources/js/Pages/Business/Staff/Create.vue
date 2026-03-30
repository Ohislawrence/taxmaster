<template>
    <BusinessLayout>
        <Head title="Add Staff Member" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">
            <Link href="/business/staff" class="text-blue-600 hover:underline">&larr; Back to Staff</Link>
            <h1 class="text-3xl font-bold text-gray-900 mt-4">Add Staff Member</h1>

            <!-- Form -->
            <form @submit.prevent="submitForm" @click="activeTooltip = null" class="bg-white rounded-lg shadow p-6 mt-6">
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input
                            v-model="form.first_name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p v-if="errors.first_name" class="text-red-600 text-sm mt-1">{{ errors.first_name[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input
                            v-model="form.last_name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p v-if="errors.last_name" class="text-red-600 text-sm mt-1">{{ errors.last_name[0] }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="errors.email" class="text-red-600 text-sm mt-1">{{ errors.email[0] }}</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone (Optional)</label>
                    <input
                        v-model="form.phone"
                        type="tel"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tax Identification Number (Optional)
                        <span class="relative inline-block ml-1">
                            <button
                                type="button"
                                @click="toggleTooltip('tin')"
                                class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700 hover:bg-blue-200 cursor-pointer"
                            >i</button>
                            <div
                                v-if="activeTooltip === 'tin'"
                                class="absolute z-50 left-0 top-6 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 shadow-lg"
                            >
                                <p class="font-semibold mb-1">Tax Identification Number (TIN)</p>
                                <p>Enter the staff member's TIN issued by FIRS or the State Internal Revenue Service. Format: 10-12 digits (e.g., 12345678-0001). This links payroll records to official tax filings and is required for PAYE remittance.</p>
                                <div class="absolute -top-1.5 left-2 w-3 h-3 bg-gray-900 rotate-45"></div>
                            </div>
                        </span>
                    </label>
                    <input
                        v-model="form.tax_identification_number"
                        type="text"
                        placeholder="e.g., BN 4452133290"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="errors.tax_identification_number" class="text-red-600 text-sm mt-1">{{ errors.tax_identification_number[0] }}</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Monthly Salary *
                        <span class="relative inline-block ml-1">
                            <button
                                type="button"
                                @click="toggleTooltip('salary')"
                                class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700 hover:bg-blue-200 cursor-pointer"
                            >i</button>
                            <div
                                v-if="activeTooltip === 'salary'"
                                class="absolute z-50 left-0 top-6 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 shadow-lg"
                            >
                                <p class="font-semibold mb-1">Monthly Gross Salary</p>
                                <p>Enter the total gross salary before any deductions (tax, pension, NHF). PAYE tax, pension (8% employee + 10% employer), and NHF (2.5%) are automatically calculated from this amount. Include basic salary and all regular allowances.</p>
                                <div class="absolute -top-1.5 left-2 w-3 h-3 bg-gray-900 rotate-45"></div>
                            </div>
                        </span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-2 text-gray-700">₦</span>
                        <input
                            v-model="form.monthly_salary"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            class="w-full pl-8 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <p v-if="errors.monthly_salary" class="text-red-600 text-sm mt-1">{{ errors.monthly_salary[0] }}</p>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Employment Type *
                            <span class="relative inline-block ml-1">
                                <button
                                    type="button"
                                    @click="toggleTooltip('employment')"
                                    class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700 hover:bg-blue-200 cursor-pointer"
                                >i</button>
                                <div
                                    v-if="activeTooltip === 'employment'"
                                    class="absolute z-50 left-0 top-6 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 shadow-lg"
                                >
                                    <p class="font-semibold mb-1">Employment Type</p>
                                    <ul class="space-y-1">
                                        <li><strong>Full Time:</strong> Standard employment with full benefits, pension, and PAYE deductions.</li>
                                        <li><strong>Part Time:</strong> Reduced hours. Tax pro-rated based on actual earnings.</li>
                                        <li><strong>Contract:</strong> Fixed-term engagement. May be subject to WHT (5% for professional services, 2-5% for construction) instead of PAYE depending on terms.</li>
                                    </ul>
                                    <div class="absolute -top-1.5 left-2 w-3 h-3 bg-gray-900 rotate-45"></div>
                                </div>
                            </span>
                        </label>
                        <select
                            v-model="form.employment_type"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select Type</option>
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="contract">Contract</option>
                        </select>
                        <p v-if="errors.employment_type" class="text-red-600 text-sm mt-1">{{ errors.employment_type[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Designation *</label>
                        <input
                            v-model="form.designation"
                            type="text"
                            placeholder="e.g., Manager, Developer"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p v-if="errors.designation" class="text-red-600 text-sm mt-1">{{ errors.designation[0] }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Date Employed *
                        <span class="relative inline-block ml-1">
                            <button
                                type="button"
                                @click="toggleTooltip('date')"
                                class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700 hover:bg-blue-200 cursor-pointer"
                            >i</button>
                            <div
                                v-if="activeTooltip === 'date'"
                                class="absolute z-50 left-0 top-6 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 shadow-lg"
                            >
                                <p class="font-semibold mb-1">Date Employed</p>
                                <p>Enter the official start date from the employment letter or contract. This determines the tax year the employee's PAYE obligations begin and is used for prorating annual reliefs if employment started mid-year.</p>
                                <div class="absolute -top-1.5 left-2 w-3 h-3 bg-gray-900 rotate-45"></div>
                            </div>
                        </span>
                    </label>
                    <input
                        v-model="form.date_employed"
                        type="date"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="errors.date_employed" class="text-red-600 text-sm mt-1">{{ errors.date_employed[0] }}</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tax State (PAYE)
                        <span class="relative inline-block ml-1">
                            <button
                                type="button"
                                @click="toggleTooltip('taxstate')"
                                class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700 hover:bg-blue-200 cursor-pointer"
                            >i</button>
                            <div
                                v-if="activeTooltip === 'taxstate'"
                                class="absolute z-50 left-0 top-6 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 shadow-lg"
                            >
                                <p class="font-semibold mb-1">Tax State</p>
                                <p>Under PITA, PAYE is remitted to the State Internal Revenue Service (SIRS) where the employee works or resides. Select the state for this employee's PAYE remittance. If not set, defaults to your business state.</p>
                                <div class="absolute -top-1.5 left-2 w-3 h-3 bg-gray-900 rotate-45"></div>
                            </div>
                        </span>
                    </label>
                    <select
                        v-model="form.tax_state"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Use Business State (Default)</option>
                        <option v-for="(name, code) in nigerianStates" :key="code" :value="code">{{ name }}</option>
                    </select>
                    <p v-if="errors.tax_state" class="text-red-600 text-sm mt-1">{{ errors.tax_state[0] }}</p>
                </div>

                <div class="flex gap-4">
                    <button
                        type="submit"
                        :disabled="processing"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        {{ processing ? 'Adding...' : 'Add Staff Member' }}
                    </button>
                    <Link href="/business/staff" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition">
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
    errors: {
        type: Object,
        default: () => ({}),
    },
    nigerianStates: {
        type: Object,
        default: () => ({}),
    },
});

const processing = ref(false);
const activeTooltip = ref(null);

const toggleTooltip = (field) => {
    activeTooltip.value = activeTooltip.value === field ? null : field;
};

const form = ref({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    tax_identification_number: '',
    monthly_salary: '',
    employment_type: '',
    designation: '',
    date_employed: '',
    tax_state: '',
});

const submitForm = () => {
    processing.value = true;
    router.post('/business/staff', form.value, {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
