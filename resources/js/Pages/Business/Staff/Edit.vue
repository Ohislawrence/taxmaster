<template>
    <BusinessLayout>
        <Head title="Edit Staff Member" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">
            <Link href="/business/staff" class="text-blue-600 hover:underline">&larr; Back to Staff</Link>
            <h1 class="text-3xl font-bold text-gray-900 mt-4">Edit Staff Member</h1>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6 mt-6">
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Designation *</label>
                    <input
                        v-model="form.designation"
                        type="text"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="errors.designation" class="text-red-600 text-sm mt-1">{{ errors.designation[0] }}</p>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Monthly Salary *
                            <span
                                class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                                title="Update the gross monthly salary before deductions. PAYE calculations use this value."
                            >
                                i
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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Employment Type *
                            <span
                                class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                                title="Use the correct contract type for compliance and reporting."
                            >
                                i
                            </span>
                        </label>
                        <select
                            v-model="form.employment_type"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="contract">Contract</option>
                        </select>
                        <p v-if="errors.employment_type" class="text-red-600 text-sm mt-1">{{ errors.employment_type[0] }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Date Employed *
                        <span
                            class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                            title="Confirm the official start date used for payroll and statutory filings."
                        >
                            i
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

                <div class="flex gap-4">
                    <button
                        type="submit"
                        :disabled="processing"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        {{ processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <Link :href="`/business/staff/${staff.id}`" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition">
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

const props = defineProps({
    staff: Object,
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const processing = ref(false);
const form = ref({
    first_name: props.staff?.first_name || '',
    last_name: props.staff?.last_name || '',
    email: props.staff?.email || '',
    phone: props.staff?.phone || '',
    designation: props.staff?.designation || '',
    monthly_salary: props.staff?.monthly_salary || '',
    employment_type: props.staff?.employment_type || 'full_time',
    date_employed: props.staff?.date_employed || '',
});

const submitForm = () => {
    processing.value = true;
    router.put(`/business/staff/${props.staff.id}`, form.value, {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
