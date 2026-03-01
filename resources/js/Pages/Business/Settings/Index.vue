<template>
    <BusinessLayout>
        <Head title="Settings" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <!-- Tabs -->
            <div class="mb-8">
                <div class="flex gap-4 border-b border-gray-200">
                    <button 
                        @click="activeTab = 'profile'"
                        :class="[
                            'py-2 px-4 font-medium border-b-2 transition',
                            activeTab === 'profile' 
                                ? 'border-blue-600 text-blue-600' 
                                : 'border-transparent text-gray-600 hover:text-gray-900'
                        ]"
                    >
                        Business Profile
                    </button>
                    <button 
                        @click="activeTab = 'subscription'"
                        :class="[
                            'py-2 px-4 font-medium border-b-2 transition',
                            activeTab === 'subscription' 
                                ? 'border-blue-600 text-blue-600' 
                                : 'border-transparent text-gray-600 hover:text-gray-900'
                        ]"
                    >
                        Subscription
                    </button>

                    <button 
                        @click="activeTab = 'activity'"
                        :class="[
                            'py-2 px-4 font-medium border-b-2 transition',
                            activeTab === 'activity' 
                                ? 'border-blue-600 text-blue-600' 
                                : 'border-transparent text-gray-600 hover:text-gray-900'
                        ]"
                    >
                        Activity Log
                    </button>
                </div>
            </div>

            <!-- Business Profile Tab -->
            <div v-if="activeTab === 'profile'" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Business Profile</h2>
                <form @submit.prevent="updateProfile" class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Business Name *</label>
                                <input 
                                    v-model="businessForm.name" 
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Business Type *</label>
                                <select 
                                    v-model="businessForm.business_type"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                >
                                    <option value="">Select business type</option>
                                    <option value="sole_proprietor">Sole Proprietor</option>
                                    <option value="partnership">Partnership</option>
                                    <option value="limited_liability">Limited Liability Company</option>
                                    <option value="corporation">Corporation</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Industry</label>
                                <select 
                                    v-model="businessForm.industry"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Select industry</option>
                                    <option value="technology">Technology</option>
                                    <option value="healthcare">Healthcare</option>
                                    <option value="retail">Retail & Commerce</option>
                                    <option value="manufacturing">Manufacturing</option>
                                    <option value="finance">Finance & Banking</option>
                                    <option value="education">Education</option>
                                    <option value="hospitality">Hospitality & Tourism</option>
                                    <option value="transportation">Transportation & Logistics</option>
                                    <option value="real_estate">Real Estate</option>
                                    <option value="entertainment">Entertainment & Media</option>
                                    <option value="consulting">Consulting & Professional Services</option>
                                    <option value="agriculture">Agriculture & Mining</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea 
                                    v-model="businessForm.description" 
                                    rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Tax & Registration Information -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tax & Registration</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tax ID (TIN)</label>
                                <input 
                                    v-model="businessForm.tax_identification_number" 
                                    type="text"
                                    placeholder="e.g., 00000000000"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                                <input 
                                    v-model="businessForm.registration_number" 
                                    type="text"
                                    placeholder="e.g., RC123456"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Annual Revenue</label>
                                <input 
                                    v-model="businessForm.annual_revenue" 
                                    type="number"
                                    placeholder="Enter annual revenue"
                                    min="0"
                                    step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                <input 
                                    v-model="businessForm.email" 
                                    type="email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                <input 
                                    v-model="businessForm.phone" 
                                    type="tel"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Location</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Street Address *</label>
                                <input 
                                    v-model="businessForm.address" 
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">State *</label>
                                    <input 
                                        v-model="businessForm.state" 
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                                    <input 
                                        v-model="businessForm.city" 
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <button 
                        type="submit"
                        :disabled="processingProfile"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        {{ processingProfile ? 'Saving...' : 'Save Changes' }}
                    </button>
                </form>
            </div>

            <!-- Subscription Tab -->
            <div v-if="activeTab === 'subscription'" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Subscription Details</h2>
                
                <div v-if="subscription" class="space-y-6">
                    <div class="grid grid-cols-2 gap-6 pb-6 border-b">
                        <div>
                            <p class="text-gray-600 text-sm">Current Plan</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1 capitalize">{{ subscription.plan_type }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Billing Cycle</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1 capitalize">{{ subscription.billing_cycle }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Monthly Price</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ formatCurrency(subscription.monthly_price) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Status</p>
                            <p class="text-lg font-semibold mt-1" :class="subscription.status === 'active' ? 'text-green-600' : 'text-red-600'">
                                {{ subscription.status.charAt(0).toUpperCase() + subscription.status.slice(1) }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 pb-6 border-b">
                        <div>
                            <p class="text-gray-600 text-sm">Max Staff Members</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1">{{ subscription.max_staff_members }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Max Returns/Year</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1">{{ subscription.max_returns_per_year }}</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">Features Included</h3>
                        <ul class="space-y-2">
                            <li class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                AI Tax Analysis
                            </li>
                            <li v-if="subscription.payment_automation" class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Payment Automation
                            </li>
                            <li class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Priority Support
                            </li>
                        </ul>
                    </div>

                    <button 
                        @click="upgradePlan"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition"
                    >
                        Upgrade Plan
                    </button>
                </div>
            </div>



            <!-- Activity Log Tab -->
            <div v-if="activeTab === 'activity'" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Activity Log</h2>
                <div class="space-y-4">
                    <div v-if="props.activityLog && props.activityLog.length > 0">
                        <div v-for="log in props.activityLog" :key="log.id" class="p-4 border rounded-lg hover:bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-gray-900">{{ log.action }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ log.description }}</p>
                                </div>
                                <span class="text-sm text-gray-500">{{ formatDate(log.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No activity recorded yet
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

const props = defineProps({
    business: Object,
    subscription: Object,
    activityLog: Array,
});

const activeTab = ref('profile');
const processingProfile = ref(false);

const businessForm = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    state: '',
    city: '',
    business_type: '',
    tax_identification_number: '',
    registration_number: '',
    annual_revenue: '',
    industry: '',
    description: '',
});

// Initialize form with business data
onMounted(() => {
    if (props.business) {
        businessForm.value = {
            name: props.business.name || '',
            email: props.business.email || '',
            phone: props.business.phone || '',
            address: props.business.address || '',
            state: props.business.state || '',
            city: props.business.city || '',
            business_type: props.business.business_type || '',
            tax_identification_number: props.business.tax_identification_number || '',
            registration_number: props.business.registration_number || '',
            annual_revenue: props.business.annual_revenue || '',
            industry: props.business.industry || '',
            description: props.business.description || '',
        };
    }


});

const updateProfile = () => {
    processingProfile.value = true;
    router.put('/business/settings', businessForm.value, {
        onFinish: () => {
            processingProfile.value = false;
        },
    });
};

const upgradePlan = () => {
    router.get('/business/subscription');
};

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
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
