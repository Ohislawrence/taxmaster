<template>
    <BusinessLayout>
        <Head title="CAC Annual Return Forms" />

        <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto space-y-8">
                <!-- Header Section -->
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                        CAC Annual Return Forms
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Generate Form AR (Annual Return) and Notice of Situation
                    </p>
                </div>

                <!-- Main Form Card -->
                <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <!-- Form Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white flex justify-between items-center">
                        <h2 class="text-base font-semibold text-gray-900">Company Information</h2>
                        <button
                            @click="downloadPdf"
                            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-medium transition-all shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                            :disabled="downloading"
                        >
                            <span v-if="downloading">Preparing PDF...</span>
                            <span v-else>Download PDF</span>
                        </button>
                    </div>

                    <!-- Company Details Section -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                            Company Details
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Company Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.company_name"
                                    type="text"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    :class="{ 'border-red-500': errors.company_name }"
                                    placeholder="Enter company name"
                                />
                                <p v-if="errors.company_name" class="mt-1 text-xs text-red-500">{{ errors.company_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    RC Number <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.rc_number"
                                    type="text"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    :class="{ 'border-red-500': errors.rc_number }"
                                    placeholder="e.g., RC 1234567"
                                />
                                <p v-if="errors.rc_number" class="mt-1 text-xs text-red-500">{{ errors.rc_number }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Incorporation Date
                                </label>
                                <input
                                    v-model="form.incorporation_date"
                                    type="date"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Share Capital
                                </label>
                                <input
                                    v-model="form.share_capital"
                                    type="text"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="e.g., ₦10,000,000"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                            Contact Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Registered Address
                                </label>
                                <input
                                    v-model="form.registered_address"
                                    type="text"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="Full registered office address"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Address
                                </label>
                                <input
                                    v-model="form.business_address"
                                    type="text"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="Business operations address"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="company@example.com"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="+234 XXX XXX XXXX"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nature of Business
                                </label>
                                <input
                                    v-model="form.nature_of_business"
                                    type="text"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="Describe the primary business activity"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Company Secretary Section -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                            Company Secretary
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Secretary Name
                                </label>
                                <input
                                    v-model="form.secretary_name"
                                    type="text"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="Full name of company secretary"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Secretary Address
                                </label>
                                <input
                                    v-model="form.secretary_address"
                                    type="text"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="Secretary's contact address"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Directors Section -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                                Directors
                            </h3>
                            <button
                                @click="addDirector"
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1"
                            >
                                Add Director
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="(director, index) in form.directors"
                                :key="index"
                                class="border border-gray-200 rounded-xl p-4 hover:border-blue-200 transition-colors"
                            >
                                <div class="flex justify-between items-start mb-3">
                                    <span class="text-xs font-medium text-gray-500">Director {{ index + 1 }}</span>
                                    <button
                                        @click="removeDirector(index)"
                                        class="text-xs text-red-500 hover:text-red-700"
                                        :disabled="form.directors.length === 1"
                                    >
                                        Remove
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <input
                                        v-model="director.name"
                                        type="text"
                                        placeholder="Full name"
                                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    <input
                                        v-model="director.address"
                                        type="text"
                                        placeholder="Residential address"
                                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shareholders Section -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                                Shareholders
                            </h3>
                            <button
                                @click="addShareholder"
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1"
                            >
                                Add Shareholder
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="(shareholder, index) in form.shareholders"
                                :key="index"
                                class="border border-gray-200 rounded-xl p-4 hover:border-blue-200 transition-colors"
                            >
                                <div class="flex justify-between items-start mb-3">
                                    <span class="text-xs font-medium text-gray-500">Shareholder {{ index + 1 }}</span>
                                    <button
                                        @click="removeShareholder(index)"
                                        class="text-xs text-red-500 hover:text-red-700"
                                        :disabled="form.shareholders.length === 1"
                                    >
                                        Remove
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <input
                                        v-model="shareholder.name"
                                        type="text"
                                        placeholder="Full name"
                                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    <input
                                        v-model="shareholder.shares"
                                        type="text"
                                        placeholder="Number of shares / Percentage"
                                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notice of Situation Section -->
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                            Notice of Situation
                        </h3>
                        <div class="bg-blue-50/30 rounded-xl p-4 mb-4 border border-blue-200/50">
                            <p class="text-sm text-blue-800">
                                Notice of Situation is a formal notification to CAC regarding the location of the company's registered office.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Registered Office Address
                                </label>
                                <input
                                    v-model="form.notice_registered_address"
                                    type="text"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="Full registered office address for notice"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Effective Date
                                </label>
                                <input
                                    v-model="form.notice_effective_date"
                                    type="date"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3">
                    <button
                        @click="resetForm"
                        class="px-6 py-2.5 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium"
                    >
                        Reset Form
                    </button>
                    <button
                        @click="downloadPdf"
                        class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-medium transition-all shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="downloading"
                    >
                        {{ downloading ? 'Generating PDF...' : 'Generate & Download PDF' }}
                    </button>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    business: {
        type: Object,
        default: () => ({})
    },
    defaults: {
        type: Object,
        default: () => ({
            company_name: '',
            rc_number: '',
            incorporation_date: '',
            registered_address: '',
            business_address: '',
            email: '',
            phone: '',
            nature_of_business: '',
            share_capital: '',
            secretary_name: '',
            secretary_address: '',
            directors: [],
            shareholders: [],
            notice_registered_address: '',
            notice_effective_date: ''
        })
    }
});

const downloading = ref(false);
const errors = ref({});

const form = reactive({
    company_name: props.defaults.company_name || '',
    rc_number: props.defaults.rc_number || '',
    incorporation_date: props.defaults.incorporation_date || '',
    registered_address: props.defaults.registered_address || '',
    business_address: props.defaults.business_address || '',
    email: props.defaults.email || '',
    phone: props.defaults.phone || '',
    nature_of_business: props.defaults.nature_of_business || '',
    share_capital: props.defaults.share_capital || '',
    secretary_name: props.defaults.secretary_name || '',
    secretary_address: props.defaults.secretary_address || '',
    directors: props.defaults.directors && props.defaults.directors.length
        ? [...props.defaults.directors]
        : [{ name: '', address: '' }],
    shareholders: props.defaults.shareholders && props.defaults.shareholders.length
        ? [...props.defaults.shareholders]
        : [{ name: '', shares: '' }],
    notice_registered_address: props.defaults.notice_registered_address || '',
    notice_effective_date: props.defaults.notice_effective_date || '',
});

const validateForm = () => {
    const newErrors = {};

    if (!form.company_name || form.company_name.trim() === '') {
        newErrors.company_name = 'Company name is required';
    }

    if (!form.rc_number || form.rc_number.trim() === '') {
        newErrors.rc_number = 'RC number is required';
    }

    errors.value = newErrors;
    return Object.keys(newErrors).length === 0;
};

const addDirector = () => {
    form.directors.push({ name: '', address: '' });
};

const removeDirector = (index) => {
    if (form.directors.length > 1) {
        form.directors.splice(index, 1);
    }
};

const addShareholder = () => {
    form.shareholders.push({ name: '', shares: '' });
};

const removeShareholder = (index) => {
    if (form.shareholders.length > 1) {
        form.shareholders.splice(index, 1);
    }
};

const resetForm = () => {
    if (confirm('Reset all form fields? This cannot be undone.')) {
        form.company_name = props.defaults.company_name || '';
        form.rc_number = props.defaults.rc_number || '';
        form.incorporation_date = props.defaults.incorporation_date || '';
        form.registered_address = props.defaults.registered_address || '';
        form.business_address = props.defaults.business_address || '';
        form.email = props.defaults.email || '';
        form.phone = props.defaults.phone || '';
        form.nature_of_business = props.defaults.nature_of_business || '';
        form.share_capital = props.defaults.share_capital || '';
        form.secretary_name = props.defaults.secretary_name || '';
        form.secretary_address = props.defaults.secretary_address || '';
        form.directors = props.defaults.directors && props.defaults.directors.length
            ? [...props.defaults.directors]
            : [{ name: '', address: '' }];
        form.shareholders = props.defaults.shareholders && props.defaults.shareholders.length
            ? [...props.defaults.shareholders]
            : [{ name: '', shares: '' }];
        form.notice_registered_address = props.defaults.notice_registered_address || '';
        form.notice_effective_date = props.defaults.notice_effective_date || '';

        errors.value = {};
    }
};

const downloadPdf = async () => {
    if (!validateForm()) {
        // Scroll to first error
        const firstError = document.querySelector('.border-red-500');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    downloading.value = true;
    try {
        const response = await axios.post(route('business.reports.cac-forms.pdf'), form, {
            responseType: 'blob',
        });

        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `cac-annual-return-${form.rc_number || 'form'}.pdf`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Failed to generate PDF:', error);
        alert('Failed to generate PDF. Please try again.');
    } finally {
        downloading.value = false;
    }
};
</script>

<style scoped>
/* Smooth transitions */
button, input {
    transition: all 0.2s ease;
}

/* Form field focus styles */
input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Disabled button styles */
button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Custom scrollbar for any overflow */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
