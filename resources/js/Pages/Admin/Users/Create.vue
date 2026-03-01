<template>
    <AdminLayout>
        <Head title="Create User" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <div class="mb-8">
                    <Link href="/admin/users" class="text-blue-600 hover:underline">← Back to Users</Link>
                    <h1 class="text-3xl font-bold text-gray-900 mt-4">Create New User</h1>
                </div>

                <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-8 space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="John Doe"
                        />
                        <span v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name[0] }}</span>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="john@example.com"
                        />
                        <span v-if="errors.email" class="text-red-600 text-sm mt-1">{{ errors.email[0] }}</span>
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select
                            id="role"
                            v-model="form.role"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select Role</option>
                            <option value="admin">Admin - Full system access</option>
                            <option value="business">Business - Manage own business</option>
                        </select>
                        <span v-if="errors.role" class="text-red-600 text-sm mt-1">{{ errors.role[0] }}</span>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="••••••••"
                        />
                        <p class="text-gray-600 text-sm mt-1">Minimum 8 characters with uppercase, lowercase, and number</p>
                        <span v-if="errors.password" class="text-red-600 text-sm mt-1">{{ errors.password[0] }}</span>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="••••••••"
                        />
                        <span v-if="errors.password_confirmation" class="text-red-600 text-sm mt-1">{{ errors.password_confirmation[0] }}</span>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            :disabled="processing"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded font-medium"
                        >
                            {{ processing ? 'Creating...' : 'Create User' }}
                        </button>
                        <Link href="/admin/users" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-medium text-center">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    roles: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

const errors = ref({});
const processing = ref(false);

function submit() {
    processing.value = true;
    form.post('/admin/users', {
        onError: (err) => {
            errors.value = err;
            processing.value = false;
        },
        onSuccess: () => {
            processing.value = false;
        },
    });
}
</script>
