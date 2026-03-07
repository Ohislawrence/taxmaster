<template>
    <BusinessLayout>
        <Head title="Staff Management" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Staff Management</h1>
                <div class="flex gap-3">
                    <Link href="/business/staff/bulk-upload" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Bulk Upload
                    </Link>
                    <Link href="/business/staff/create" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                        + Add Staff Member
                    </Link>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm font-medium">Total Staff</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_staff }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm font-medium">Active Staff</div>
                    <div class="text-2xl font-bold text-green-600 mt-1">{{ stats.active_staff }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm font-medium">Monthly Payroll</div>
                    <div class="text-2xl font-bold text-blue-600 mt-1">₦{{ formatCurrency(stats.total_monthly_payroll) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm font-medium">Monthly Tax</div>
                    <div class="text-2xl font-bold text-red-600 mt-1">₦{{ formatCurrency(stats.total_monthly_tax) }}</div>
                </div>
            </div>

            <!-- Staff Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="staff.data.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Name</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Email</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Designation</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Monthly Salary</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Employment Type</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="member in staff.data" :key="member.id" class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ member.full_name }}</td>
                                <td class="py-3 px-4 text-sm">{{ member.email }}</td>
                                <td class="py-3 px-4">{{ member.designation }}</td>
                                <td class="py-3 px-4 font-semibold">₦{{ formatCurrency(member.monthly_salary) }}</td>
                                <td class="py-3 px-4 capitalize text-sm">{{ member.employment_type.replace('_', ' ') }}</td>
                                <td class="py-3 px-4">
                                    <span :class="statusClass(member.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                                        {{ member.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <Link :href="`/business/staff/${member.id}`" class="text-blue-600 hover:underline">View</Link>
                                    <span class="mx-2 text-gray-400">|</span>
                                    <Link :href="`/business/staff/${member.id}/edit`" class="text-green-600 hover:underline">Edit</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-8 text-center text-gray-500">
                    <p>No staff members added yet.</p>
                    <Link href="/business/staff/create" class="text-green-600 hover:underline">Add your first staff member</Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="staff.last_page > 1" class="mt-6 flex justify-center gap-2">
                <Link v-for="page in pages" :key="page" :href="`?page=${page}`" class="px-3 py-2 border rounded" :class="page === staff.current_page ? 'bg-blue-600 text-white' : ''">
                    {{ page }}
                </Link>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

defineProps({
    staff: Object,
    stats: Object,
});

const pages = computed(() => {
    const pages = [];
    for (let i = 1; i <= props.staff.last_page; i++) {
        pages.push(i);
    }
    return pages;
});

function formatCurrency(value) {
    return new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2 }).format(value);
}

function statusClass(status) {
    const classes = {
        active: 'bg-green-100 text-green-800',
        on_leave: 'bg-yellow-100 text-yellow-800',
        terminated: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}
</script>
