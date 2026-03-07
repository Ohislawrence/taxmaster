<template>
    <GuestLayout>
        <Head title="Complete Business Setup" />

        <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 flex flex-col items-center justify-center p-4 relative overflow-hidden">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
                <div class="absolute top-1/2 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
                <div class="absolute bottom-0 left-1/3 w-96 h-96 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
            </div>

            <!-- Content Container -->
            <div class="relative w-full max-w-4xl z-10">
                <!-- Header -->
                <div class="text-center mb-10">
                    <div class="mb-6 flex justify-center">
                        <div class="p-3 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold text-white mb-3">Complete Your Business Profile</h1>
                    <p class="text-gray-300 text-lg">Provide your business details to get started with TaxMaster</p>
                </div>

                <!-- Form Card -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-2xl p-8 md:p-12 border border-white/20">
                    <form @submit.prevent="submitForm">
                        <!-- Error Banner -->
                        <div v-if="hasErrors" class="mb-8 bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-400 mt-0.5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h3 class="text-red-300 font-semibold text-sm">Please fix the following errors:</h3>
                                    <ul class="mt-2 space-y-1">
                                        <li v-for="(msg, field) in pageErrors" :key="field" class="text-red-400 text-sm">{{ msg }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Steps -->
                        <div class="mb-10 hidden md:flex justify-between">
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-10 h-10 bg-blue-500 rounded-full text-white font-bold">1</div>
                                <span class="ml-2 text-sm font-medium text-gray-300">Business Info</span>
                            </div>
                            <div class="flex-1 h-1 bg-gray-600 mx-4 mt-5"></div>
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-10 h-10 bg-blue-500 rounded-full text-white font-bold">2</div>
                                <span class="ml-2 text-sm font-medium text-gray-300">Contact & Address</span>
                            </div>
                            <div class="flex-1 h-1 bg-gray-600 mx-4 mt-5"></div>
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-10 h-10 bg-blue-500 rounded-full text-white font-bold">3</div>
                                <span class="ml-2 text-sm font-medium text-gray-300">Submit</span>
                            </div>
                        </div>

                        <!-- Business Information Section -->
                        <div class="mb-10">
                            <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                                <span class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3 text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"></path></svg>
                                </span>
                                Business Information
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Business Name -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-200 mb-2">Business Name *</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Enter your business name"
                                        required
                                        :class="{'border-red-500 ring-1 ring-red-500': pageErrors.name}"
                                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    />
                                    <p v-if="pageErrors.name" class="text-red-400 text-sm mt-1">{{ pageErrors.name }}</p>
                                </div>

                                <!-- Business Type -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-200 mb-2">Business Type *</label>
                                    <select
                                        v-model="form.business_type"
                                        required
                                        :class="{'border-red-500 ring-1 ring-red-500': pageErrors.business_type}"
                                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    >
                                        <option value="" class="bg-gray-800 text-white">Select business type</option>
                                        <option value="sole_proprietor" class="bg-gray-800 text-white">Sole Proprietor</option>
                                        <option value="partnership" class="bg-gray-800 text-white">Partnership</option>
                                        <option value="limited_liability" class="bg-gray-800 text-white">Limited Liability Company</option>
                                        <option value="corporation" class="bg-gray-800 text-white">Corporation</option>
                                    </select>
                                    <p v-if="pageErrors.business_type" class="text-red-400 text-sm mt-1">{{ pageErrors.business_type }}</p>
                                </div>
                            </div>

                            <!-- Tax ID and Registration -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Tax ID -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-200 mb-2">Tax ID (TIN) *</label>
                                    <input
                                        v-model="form.tax_identification_number"
                                        type="text"
                                        placeholder="e.g., 00000000000"
                                        required
                                        :class="{'border-red-500 ring-1 ring-red-500': pageErrors.tax_identification_number}"
                                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    />
                                    <p v-if="pageErrors.tax_identification_number" class="text-red-400 text-sm mt-1">{{ pageErrors.tax_identification_number }}</p>
                                </div>

                                <!-- Registration Number -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-200 mb-2">Registration Number *</label>
                                    <input
                                        v-model="form.registration_number"
                                        type="text"
                                        placeholder="e.g., RC123456"
                                        required
                                        :class="{'border-red-500 ring-1 ring-red-500': pageErrors.registration_number}"
                                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    />
                                    <p v-if="pageErrors.registration_number" class="text-red-400 text-sm mt-1">{{ pageErrors.registration_number }}</p>
                                </div>
                            </div>

                            <!-- Industry -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-200 mb-2">Industry</label>
                                    <select
                                        v-model="form.industry"
                                        :class="{'border-red-500 ring-1 ring-red-500': pageErrors.industry}"
                                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    >
                                        <option value="" class="bg-gray-800 text-white">Select industry</option>
                                        <option value="technology" class="bg-gray-800 text-white">Technology</option>
                                        <option value="healthcare" class="bg-gray-800 text-white">Healthcare</option>
                                        <option value="retail" class="bg-gray-800 text-white">Retail & Commerce</option>
                                        <option value="manufacturing" class="bg-gray-800 text-white">Manufacturing</option>
                                        <option value="finance" class="bg-gray-800 text-white">Finance & Banking</option>
                                        <option value="education" class="bg-gray-800 text-white">Education</option>
                                        <option value="hospitality" class="bg-gray-800 text-white">Hospitality & Tourism</option>
                                        <option value="transportation" class="bg-gray-800 text-white">Transportation & Logistics</option>
                                        <option value="real_estate" class="bg-gray-800 text-white">Real Estate</option>
                                        <option value="entertainment" class="bg-gray-800 text-white">Entertainment & Media</option>
                                        <option value="consulting" class="bg-gray-800 text-white">Consulting & Professional Services</option>
                                        <option value="agriculture" class="bg-gray-800 text-white">Agriculture & Mining</option>
                                        <option value="other" class="bg-gray-800 text-white">Other</option>
                                    </select>
                                    <p v-if="pageErrors.industry" class="text-red-400 text-sm mt-1">{{ pageErrors.industry }}</p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-200 mb-2">Business Description</label>
                                <textarea
                                    v-model="form.description"
                                    placeholder="Brief description of your business (optional)"
                                    rows="3"
                                    :class="{'border-red-500 ring-1 ring-red-500': pageErrors.description}"
                                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
                                ></textarea>
                                <p v-if="pageErrors.description" class="text-red-400 text-sm mt-1">{{ pageErrors.description }}</p>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="mb-10 border-t border-white/10 pt-10">
                            <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                                <span class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3 text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </span>
                                Contact Information
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-200 mb-2">Business Email *</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="business@example.com"
                                        required
                                        :class="{'border-red-500 ring-1 ring-red-500': pageErrors.email}"
                                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    />
                                    <p v-if="pageErrors.email" class="text-red-400 text-sm mt-1">{{ pageErrors.email }}</p>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-200 mb-2">Business Phone *</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        placeholder="+234 (0) 123 456 7890"
                                        required
                                        :class="{'border-red-500 ring-1 ring-red-500': pageErrors.phone}"
                                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    />
                                    <p v-if="pageErrors.phone" class="text-red-400 text-sm mt-1">{{ pageErrors.phone }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Address Section -->
                        <div class="mb-10 border-t border-white/10 pt-10">
                            <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                                <span class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3 text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </span>
                                Business Address
                            </h2>

                            <!-- Full Address -->
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-200 mb-2">Street Address *</label>
                                <input
                                    v-model="form.address"
                                    type="text"
                                    placeholder="e.g., 123 Business Street"
                                    required
                                    :class="{'border-red-500 ring-1 ring-red-500': pageErrors.address}"
                                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                />
                                <p v-if="pageErrors.address" class="text-red-400 text-sm mt-1">{{ pageErrors.address }}</p>
                            </div>

                            <!-- City, State -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- City -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-200 mb-2">City *</label>
                                    <input
                                        v-model="form.city"
                                        type="text"
                                        placeholder="e.g., Lagos"
                                        required
                                        :class="{'border-red-500 ring-1 ring-red-500': pageErrors.city}"
                                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    />
                                    <p v-if="pageErrors.city" class="text-red-400 text-sm mt-1">{{ pageErrors.city }}</p>
                                </div>

                                <!-- State -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-200 mb-2">State *</label>
                                    <select
                                        v-model="form.state"
                                        required
                                        :class="{'border-red-500 ring-1 ring-red-500': pageErrors.state}"
                                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    >
                                        <option value="" class="bg-gray-800 text-white">Select state</option>
                                        <option v-for="state in states" :key="state" :value="state" class="bg-gray-800 text-white">
                                            {{ state }}
                                        </option>
                                    </select>
                                    <p v-if="pageErrors.state" class="text-red-400 text-sm mt-1">{{ pageErrors.state }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="border-t border-white/10 pt-10">
                            <button
                                type="submit"
                                :disabled="processing"
                                class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 disabled:from-gray-500 disabled:to-gray-600 text-white font-bold py-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 disabled:transform-none disabled:hover:scale-100"
                            >
                                <span v-if="processing" class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Setting up your business...
                                </span>
                                <span v-else class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    Complete Setup & Continue
                                </span>
                            </button>
                        </div>

                        <!-- Progress Info -->
                        <p class="text-center text-gray-400 text-sm mt-6">
                            {{ processing ? 'Creating your business profile...' : '✓ You\'ll be redirected to your dashboard after completing this setup.' }}
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const page = usePage()

const props = defineProps({
    states: Array,
});

const pageErrors = computed(() => page.props.errors || {})
const hasErrors = computed(() => Object.keys(pageErrors.value).length > 0)

const processing = ref(false);
const form = ref({
    name: '',
    business_type: '',
    tax_identification_number: '',
    registration_number: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    description: '',
    industry: '',
});

const submitForm = () => {
    processing.value = true;
    router.post('/business-setup', form.value, {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<style scoped>
@keyframes blob {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
