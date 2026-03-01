<template>
    <AdminLayout>
        <Head :title="`Edit Business: ${business.name}`" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div>
                <Link :href="`/admin/businesses/${business.id}`" class="text-blue-600 hover:underline">← Back to Business</Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-4">Edit Business: {{ business.name }}</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-8 space-y-8">
                <!-- Basic Information -->
                <div class="space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Business Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <span v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name[0] }}</span>
                        </div>

                        <!-- Registered Name -->
                        <div>
                            <label for="registered_name" class="block text-sm font-medium text-gray-700 mb-1">Registered Name</label>
                            <input
                                id="registered_name"
                                v-model="form.registered_name"
                                type="text"
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <span v-if="errors.registered_name" class="text-red-600 text-sm mt-1">{{ errors.registered_name[0] }}</span>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <span v-if="errors.email" class="text-red-600 text-sm mt-1">{{ errors.email[0] }}</span>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                required
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <span v-if="errors.phone" class="text-red-600 text-sm mt-1">{{ errors.phone[0] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Business Details -->
                <div class="pt-6 border-t border-gray-200 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900">Business Details</h2>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Business Type -->
                        <div>
                            <label for="business_type" class="block text-sm font-medium text-gray-700 mb-1">Business Type</label>
                            <select
                                id="business_type"
                                v-model="form.business_type"
                                required
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select Type</option>
                                <option value="sole_proprietorship">Sole Proprietorship</option>
                                <option value="partnership">Partnership</option>
                                <option value="limited_liability">Limited Liability Company</option>
                                <option value="corporation">Corporation</option>
                            </select>
                            <span v-if="errors.business_type" class="text-red-600 text-sm mt-1">{{ errors.business_type[0] }}</span>
                        </div>

                        <!-- Industry -->
                        <div>
                            <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                            <input
                                id="industry"
                                v-model="form.industry"
                                type="text"
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <span v-if="errors.industry" class="text-red-600 text-sm mt-1">{{ errors.industry[0] }}</span>
                        </div>

                        <!-- TIN -->
                        <div>
                            <label for="tin" class="block text-sm font-medium text-gray-700 mb-1">Tax Identification Number (TIN)</label>
                            <input
                                id="tin"
                                v-model="form.tin"
                                type="text"
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <span v-if="errors.tin" class="text-red-600 text-sm mt-1">{{ errors.tin[0] }}</span>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select
                                id="status"
                                v-model="form.status"
                                required
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <span v-if="errors.status" class="text-red-600 text-sm mt-1">{{ errors.status[0] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="pt-6 border-t border-gray-200 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900">Address</h2>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                        <input
                            id="address"
                            v-model="form.address"
                            type="text"
                            required
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <span v-if="errors.address" class="text-red-600 text-sm mt-1">{{ errors.address[0] }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <!-- City -->
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input
                                id="city"
                                v-model="form.city"
                                type="text"
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <span v-if="errors.city" class="text-red-600 text-sm mt-1">{{ errors.city[0] }}</span>
                        </div>

                        <!-- State -->
                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                            <input
                                id="state"
                                v-model="form.state"
                                type="text"
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <span v-if="errors.state" class="text-red-600 text-sm mt-1">{{ errors.state[0] }}</span>
                        </div>

                        <!-- Postal Code -->
                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                            <input
                                id="postal_code"
                                v-model="form.postal_code"
                                type="text"
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <span v-if="errors.postal_code" class="text-red-600 text-sm mt-1">{{ errors.postal_code[0] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button
                        type="submit"
                        :disabled="processing"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-3 rounded font-medium"
                    >
                        {{ processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <Link :href="`/admin/businesses/${business.id}`" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-3 rounded font-medium text-center">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    business: Object,
});

const form = useForm({
    name: props.business.name,
    registered_name: props.business.registered_name,
    email: props.business.email,
    phone: props.business.phone,
    business_type: props.business.business_type,
    industry: props.business.industry,
    tin: props.business.tin,
    status: props.business.status,
    address: props.business.address,
    city: props.business.city,
    state: props.business.state,
    postal_code: props.business.postal_code,
});

const errors = ref({});
const processing = ref(false);

function submit() {
    processing.value = true;
    form.put(`/admin/businesses/${props.business.id}`, {
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
