<template>
    <AdminLayout>
        <Head :title="`Edit User: ${user.name}`" />

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <div class="mb-8">
                    <Link :href="`/admin/users/${user.id}`" class="text-blue-600 hover:underline">← Back to User</Link>
                    <h1 class="text-3xl font-bold text-gray-900 mt-4">Edit User: {{ user.name }}</h1>
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
                            <option v-for="role in roles" :key="role" :value="role">
                                {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                            </option>
                        </select>
                        <span v-if="errors.role" class="text-red-600 text-sm mt-1">{{ errors.role[0] }}</span>
                    </div>

                    <!-- Current Role Info -->
                    <div v-if="form.role !== currentRole" class="bg-blue-50 border border-blue-200 rounded p-3">
                        <p class="text-sm text-blue-800">
                            Role will be changed from <strong>{{ currentRole }}</strong> to <strong>{{ form.role }}</strong>
                        </p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            :disabled="processing"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded font-medium"
                        >
                            {{ processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                        <Link :href="`/admin/users/${user.id}`" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-medium text-center">
                            Cancel
                        </Link>
                    </div>
                </form>

                <!-- Password Reset Section -->
                <div class="bg-white rounded-lg shadow p-8 mt-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Reset Password</h2>
                    <form @submit.prevent="updatePassword" class="space-y-6">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input
                                id="password"
                                v-model="passwordForm.password"
                                type="password"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="••••••••"
                            />
                            <p class="text-gray-600 text-sm mt-1">Minimum 8 characters with uppercase, lowercase, and number</p>
                            <span v-if="passwordErrors.password" class="text-red-600 text-sm mt-1">{{ passwordErrors.password[0] }}</span>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="••••••••"
                            />
                            <span v-if="passwordErrors.password_confirmation" class="text-red-600 text-sm mt-1">{{ passwordErrors.password_confirmation[0] }}</span>
                        </div>

                        <!-- Button -->
                        <button
                            type="submit"
                            :disabled="passwordProcessing"
                            class="bg-orange-600 hover:bg-orange-700 disabled:bg-gray-400 text-white px-4 py-2 rounded font-medium"
                        >
                            {{ passwordProcessing ? 'Updating...' : 'Update Password' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object,
    roles: Array,
    currentRole: String,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.currentRole,
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

const errors = ref({});
const passwordErrors = ref({});
const processing = ref(false);
const passwordProcessing = ref(false);

function submit() {
    processing.value = true;
    form.put(`/admin/users/${props.user.id}`, {
        onError: (err) => {
            errors.value = err;
            processing.value = false;
        },
        onSuccess: () => {
            processing.value = false;
        },
    });
}

function updatePassword() {
    passwordProcessing.value = true;
    passwordForm.put(`/admin/users/${props.user.id}/password`, {
        onError: (err) => {
            passwordErrors.value = err;
            passwordProcessing.value = false;
        },
        onSuccess: () => {
            passwordProcessing.value = false;
            passwordForm.password = '';
            passwordForm.password_confirmation = '';
        },
    });
}
</script>
