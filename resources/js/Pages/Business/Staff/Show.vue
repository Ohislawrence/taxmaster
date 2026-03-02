<template>
    <BusinessLayout>
        <Head :title="`${staff.full_name} - Staff`" />

        <div class="py-4 sm:py-8 px-3 sm:px-4 lg:px-8 max-w-4xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6 sm:mb-8">
                <div class="flex-1">
                    <Link href="/business/staff" class="text-blue-600 hover:underline text-sm">&larr; Back to Staff</Link>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-4">{{ staff.full_name }}</h1>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">{{ staff.position }} {{ staff.department ? `• ${staff.department}` : '' }}</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <Link :href="`/business/staff/${staff.id}/edit`" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-center text-sm">
                        Edit
                    </Link>
                    <button 
                        @click="deleteStaff"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Personal Information</h2>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-600 text-xs sm:text-sm">Email</p>
                                    <p class="font-medium text-gray-900 mt-1 text-sm">{{ staff.email }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-xs sm:text-sm">Phone</p>
                                    <p class="font-medium text-gray-900 mt-1 text-sm">{{ staff.phone || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Employment Information</h2>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-600 text-sm">Position</p>
                                    <p class="font-medium text-gray-900 mt-1">{{ staff.position }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Department</p>
                                    <p class="font-medium text-gray-900 mt-1">{{ staff.department || 'General' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Hire Date</p>
                                    <p class="font-medium text-gray-900 mt-1">{{ formatDate(staff.hire_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Status</p>
                                    <p class="font-medium mt-1" :class="staff.status === 'active' ? 'text-green-600' : 'text-red-600'">
                                        {{ staff.status.charAt(0).toUpperCase() + staff.status.slice(1) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Compensation -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Compensation</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-600 text-sm">Monthly Salary</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(staff.monthly_salary) }}</p>
                                <p class="text-xs text-gray-500 mt-2">Gross per month</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-600 text-sm">Annual Salary</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(staff.monthly_salary * 12) }}</p>
                                <p class="text-xs text-gray-500 mt-2">Gross per year</p>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                                <p class="text-gray-600 text-sm">Monthly Tax (Est.)</p>
                                <p class="text-2xl font-bold text-orange-600 mt-1">₦{{ formatCurrency(monthlyTax) }}</p>
                                <p class="text-xs text-gray-500 mt-2">PAYE estimate</p>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                                <p class="text-gray-600 text-sm">Annual Tax (Est.)</p>
                                <p class="text-2xl font-bold text-orange-600 mt-1">₦{{ formatCurrency(annualTax) }}</p>
                                <p class="text-xs text-gray-500 mt-2">PAYE estimate</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="space-y-6">
                    <!-- Summary Card -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="font-semibold text-blue-900 mb-4">Quick Summary</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-blue-700">Years Employed</span>
                                <span class="font-medium text-blue-900">{{ yearsEmployed }} year{{ yearsEmployed !== 1 ? 's' : '' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-blue-700">Net Monthly</span>
                                <span class="font-medium text-blue-900">₦{{ formatCurrency(staff.monthly_salary - monthlyTax) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-blue-700">Annual Deduction</span>
                                <span class="font-medium text-blue-900">{{ ((monthlyTax / staff.monthly_salary) * 100).toFixed(1) }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-2">
                            <Link :href="`/business/staff/${staff.id}/tax-analysis`" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-900 px-4 py-2 rounded-lg text-center font-medium transition">
                                View Tax Analysis
                            </Link>
                            <button 
                                @click="generatePayslip"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-900 px-4 py-2 rounded-lg text-center font-medium transition"
                            >
                                Generate Payslip
                            </button>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div :class="[
                        'rounded-lg p-6',
                        staff.status === 'active' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'
                    ]">
                        <p :class="staff.status === 'active' ? 'text-green-900' : 'text-red-900'" class="font-semibold mb-2">
                            {{ staff.status === 'active' ? 'Active Employee' : 'Inactive Employee' }}
                        </p>
                        <p :class="staff.status === 'active' ? 'text-green-700' : 'text-red-700'" class="text-sm">
                            {{ staff.status === 'active' 
                                ? 'This employee is included in all tax calculations.'
                                : 'This employee is not included in tax calculations.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

const props = defineProps({
    staff: Object,
    monthlyTax: Number,
    annualTax: Number,
});

const yearsEmployed = computed(() => {
    if (!props.staff?.hire_date) return 0;
    const hired = new Date(props.staff.hire_date);
    const now = new Date();
    return Math.floor((now - hired) / (1000 * 60 * 60 * 24 * 365));
});

const formatCurrency = (value) => {
    if (!value) return '0.00'
    return parseFloat(value).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const deleteStaff = () => {
    if (confirm('Are you sure you want to remove this staff member? This action cannot be undone.')) {
        router.delete(`/business/staff/${props.staff.id}`);
    }
};

const generatePayslip = () => {
    alert('Payslip generation coming soon');
};
</script>
