<template>
    <BusinessLayout title="Subscription Management">
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow p-6">
                <h1 class="text-3xl font-bold text-gray-900">Subscription & Billing</h1>
                <p class="text-gray-600 mt-1">Manage your plan, upgrade, and view billing details</p>
            </div>

            <!-- Current Plan -->
            <div v-if="currentSubscription" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    Current Plan
                    <span
                        class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                        title="Your active plan controls feature access, limits, and billing cycle."
                    >
                        i
                    </span>
                </h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Plan Name</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ getPlanName(currentSubscription.plan_type) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Status</p>
                        <span :class="getStatusClass(currentSubscription.status)"
                            class="px-3 py-1 rounded-full text-sm font-medium inline-block mt-1">
                            {{ currentSubscription.status }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Billing Cycle</p>
                        <p class="text-lg font-semibold text-gray-900">{{ currentSubscription.billing_cycle }}</p>
                    </div>
                    <div v-if="currentSubscription?.trial_ends_at">
                        <p class="text-gray-600 text-sm font-medium">Trial Ends</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ new Date(currentSubscription?.trial_ends_at).toLocaleDateString() }}
                        </p>
                    </div>
                </div>

                <!-- Features -->
                <div class="mt-6 border-t border-gray-200 pt-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Included Features</h3>
                    <ul class="space-y-2">
                        <li v-if="currentSubscription?.ai_analysis_included" class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            AI Tax Analysis
                        </li>
                        <li v-if="currentSubscription?.payment_automation" class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Payment Automation
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Max Staff: {{ currentSubscription?.max_staff_members }}
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Max Returns/Year: {{ currentSubscription?.max_returns_per_year }}
                        </li>
                    </ul>
                </div>

                <!-- Renewal Info -->
                <div v-if="currentSubscription?.renews_at" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-900">
                        Next billing date: <strong>{{ new Date(currentSubscription?.renews_at).toLocaleDateString() }}</strong>
                    </p>
                </div>
            </div>

            <!-- No Active Subscription -->
            <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <p class="text-yellow-900">You don't have an active subscription. Please select a plan below to get started.</p>
            </div>

            <!-- Available Plans -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">
                    Available Plans
                    <span
                        class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                        title="Select a plan to upgrade or switch billing cycles. Changes take effect immediately unless otherwise stated."
                    >
                        i
                    </span>
                </h2>

                <div class="grid grid-cols-3 gap-6">
                    <div
                        v-for="(plan, planKey) in availablePlans"
                        :key="planKey"
                        :class="[
                            'border-2 rounded-lg p-6 transition',
                            currentSubscription?.plan_type === planKey
                                ? 'border-blue-500 bg-blue-50'
                                : 'border-gray-200 hover:border-blue-300'
                        ]"
                    >
                        <!-- Plan Header -->
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-gray-900">{{ plan.name }}</h3>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                ₦{{ formatNumber(plan.monthly_price) }}
                                <span class="text-sm text-gray-600 font-normal">/month</span>
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                or ₦{{ formatNumber(plan.annual_price) }}/year
                            </p>
                        </div>

                        <!-- Limits -->
                        <div class="space-y-2 mb-6 pb-6 border-b border-gray-200">
                            <div class="text-sm">
                                <span class="font-semibold text-gray-900">Staff:</span>
                                <span class="text-gray-600">Up to {{ plan.max_staff }} members</span>
                            </div>
                            <div class="text-sm">
                                <span class="font-semibold text-gray-900">Returns:</span>
                                <span class="text-gray-600">{{ plan.max_returns_per_year }}/year</span>
                            </div>
                        </div>

                        <!-- Features -->
                        <ul class="space-y-2 mb-6">
                            <li v-if="plan.features.ai_analysis" class="flex items-center text-sm text-gray-700">
                                <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                AI Analysis
                            </li>
                            <li v-if="plan.features.payment_automation" class="flex items-center text-sm text-gray-700">
                                <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Payment Automation
                            </li>
                            <li v-if="plan.features.staff_management" class="flex items-center text-sm text-gray-700">
                                <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Staff Management
                            </li>
                            <li v-if="plan.features.priority_support" class="flex items-center text-sm text-gray-700">
                                <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Priority Support
                            </li>
                            <li v-if="plan.features.api_access" class="flex items-center text-sm text-gray-700">
                                <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                API Access
                            </li>
                            <li v-if="plan.features.custom_branding" class="flex items-center text-sm text-gray-700">
                                <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Custom Branding
                            </li>
                        </ul>

                        <!-- Action Button -->
                        <button
                            v-if="currentSubscription?.plan_type !== planKey"
                            @click="upgradePlan(planKey)"
                            :disabled="isUpgrading === planKey"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 font-medium transition"
                        >
                            {{ isUpgrading === planKey ? 'Processing...' : 'Upgrade to ' + plan.name }}
                        </button>

                        <div v-else class="w-full px-4 py-2 bg-green-100 text-green-800 rounded-lg text-center font-medium">
                            Current Plan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Billing Information</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div v-if="currentSubscription">
                            <p class="text-sm text-gray-600 font-medium">Monthly Cost</p>
                            <p class="text-lg font-semibold text-gray-900">
                                ₦{{ formatNumber(currentSubscription.monthly_price) }}
                            </p>
                        </div>
                        <div v-else>
                            <p class="text-sm text-gray-600 font-medium">Monthly Cost</p>
                            <p class="text-gray-500">No active subscription</p>
                        </div>
                        <div v-if="currentSubscription">
                            <p class="text-sm text-gray-600 font-medium">Annual Cost</p>
                            <p class="text-lg font-semibold text-gray-900">
                                ₦{{ formatNumber(currentSubscription.annual_price) }}
                            </p>
                        </div>
                        <div v-else>
                            <p class="text-sm text-gray-600 font-medium">Annual Cost</p>
                            <p class="text-gray-500">No active subscription</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="font-semibold text-blue-900 mb-2">Need Help?</h3>
                <p class="text-sm text-blue-800 mb-3">
                    Have questions about your subscription or need a custom plan? Contact our support team.
                </p>
                <a href="mailto:support@taxmaster.ng" class="text-blue-600 hover:underline font-medium">
                    support@taxmaster.ng
                </a>
            </div>
        </div>
    </BusinessLayout>
</template>

<script>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { router, usePage } from '@inertiajs/vue3';

export default {
    components: { BusinessLayout },
    props: {
        currentSubscription: Object,
        availablePlans: Object,
    },
    data() {
        return {
            isUpgrading: null,
        };
    },
    methods: {
        async upgradePlan(planKey) {
            if (!confirm('Upgrade to this plan?')) return;

            this.isUpgrading = planKey;
            try {
                const page = usePage();
                const csrfToken = page.props.csrf_token;

                const response = await fetch(route('business.subscription.upgrade-plan'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        plan_type: planKey,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // If payment URL is provided, redirect to Paystack
                    if (data.payment_url) {
                        window.location.href = data.payment_url;
                    } else if (data.redirect) {
                        // Free plan, redirect to subscription page
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message || 'Plan upgraded successfully!');
                        router.reload();
                    }
                } else {
                    alert(data.error || 'Failed to upgrade plan');
                }
            } catch (error) {
                console.error('Upgrade error:', error);
                alert('An error occurred while processing your upgrade. Please try again.');
            } finally {
                this.isUpgrading = null;
            }
        },

        getPlanName(planType) {
            const plans = {
                free: 'Free',
                basic: 'Basic',
                professional: 'Professional',
                enterprise: 'Enterprise',
            };
            return plans[planType] || 'Unknown Plan';
        },

        getStatusClass(status) {
            const classes = {
                active: 'bg-green-100 text-green-800',
                cancelled: 'bg-red-100 text-red-800',
                suspended: 'bg-orange-100 text-orange-800',
                pending: 'bg-yellow-100 text-yellow-800',
                pending_payment: 'bg-blue-100 text-blue-800',
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },

        formatNumber(value) {
            return new Intl.NumberFormat('en-NG').format(value || 0);
        },
    },
};
</script>
