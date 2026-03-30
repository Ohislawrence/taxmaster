<template>
    <BusinessLayout>
        <Head title="Business Settings" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Business Settings</h1>
                <p class="text-gray-600 mt-1">Manage your business tax and compliance settings</p>
            </div>

            <!-- Success Message -->
            <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-green-800 font-medium">{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <!-- VAT Exempt Status Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                            VAT Exempt Status
                            <span class="px-2 py-1 text-xs font-bold rounded"
                                  :class="business.is_vat_exempt ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'">
                                {{ business.is_vat_exempt ? 'EXEMPT' : 'VAT REGISTERED' }}
                            </span>
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Per Nigerian VAT Act and Finance Acts 2019/2020
                        </p>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm text-blue-800">
                            <strong>Automated Exemption:</strong> Businesses with annual turnover below ₦25 million are automatically exempt from VAT registration per Finance Act 2020.
                            <br><strong>Manual Exemption:</strong> Businesses dealing exclusively in exempt goods/services can mark themselves as VAT exempt.
                        </div>
                    </div>
                </div>

                <form @submit.prevent="updateVatExemptStatus" class="space-y-6">
                    <!-- Toggle Switch -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-gray-900">
                                Mark Business as VAT Exempt
                            </label>
                            <p class="text-xs text-gray-600 mt-1">
                                Enable this if your business deals exclusively in VAT-exempt goods or services
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="form.is_vat_exempt = !form.is_vat_exempt"
                            :class="form.is_vat_exempt ? 'bg-blue-600' : 'bg-gray-300'"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <span
                                :class="form.is_vat_exempt ? 'translate-x-6' : 'translate-x-1'"
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                            />
                        </button>
                    </div>

                    <!-- Exempt Category Selection (shown when exempt) -->
                    <div v-if="form.is_vat_exempt" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                VAT Exempt Category <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.vat_exempt_category"
                                required
                                class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Select exempt category...</option>
                                <optgroup label="Exempt Goods">
                                    <option v-for="(label, value) in exemptGoods" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </optgroup>
                                <optgroup label="Exempt Services">
                                    <option v-for="(label, value) in exemptServices" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </optgroup>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for Exemption
                            </label>
                            <textarea
                                v-model="form.vat_exempt_reason"
                                rows="3"
                                class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Explain why your business qualifies for this VAT exemption..."
                            ></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                Provide details about your business activities and how they align with the selected exempt category
                            </p>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button
                            type="submit"
                            :disabled="processing"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium rounded-lg transition"
                        >
                            {{ processing ? 'Saving...' : 'Save VAT Settings' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- VAT Exempt Categories Reference -->
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">VAT Exempt Categories Reference</h3>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Exempt Goods -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Exempt Goods</h4>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li v-for="(label, value) in exemptGoods" :key="value" class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ label }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Exempt Services -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Exempt Services</h4>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li v-for="(label, value) in exemptServices" :key="value" class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ label }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-white rounded-lg border border-gray-200">
                    <h5 class="text-sm font-semibold text-gray-900 mb-2">Basic Food Items (Detailed)</h5>
                    <p class="text-xs text-gray-600">
                        Per Finance Act 2019: Honey, bread, cereals, cooking oils, herbs, fish, flour, fruits, meat, milk, nuts, pulses, roots (yam, cassava), salt, vegetables, water (natural & table water)
                    </p>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    business: Object,
    exemptGoods: Object,
    exemptServices: Object,
});

const form = ref({
    is_vat_exempt: props.business.is_vat_exempt || false,
    vat_exempt_category: props.business.vat_exempt_category || '',
    vat_exempt_reason: props.business.vat_exempt_reason || '',
});

const processing = ref(false);

const updateVatExemptStatus = () => {
    processing.value = true;

    router.post(route('business.settings.update-vat-exempt'), form.value, {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
