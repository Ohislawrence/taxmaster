<template>
    <BusinessLayout title="Subscription Management">
        <div class="py-6 lg:py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <BillingGuardNotice />

                <!-- Header -->
                <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm p-6 lg:p-8">
                    <h1 class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                        Subscription & Billing
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Manage your plan, upgrade, and view billing details</p>
                </div>

                <!-- Current Plan -->
                <div v-if="currentSubscription" class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                        <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                            Current Plan
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-600 cursor-help" title="Your active plan controls feature access, limits, and billing cycle.">
                                i
                            </span>
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 pb-6 border-b border-gray-200">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Plan Name</p>
                                <p class="text-xl lg:text-2xl font-bold text-gray-900">
                                    {{ getPlanName(currentSubscription.plan_type) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Status</p>
                                <span :class="getStatusClass(currentSubscription.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="getStatusDotClass(currentSubscription.status)"></span>
                                    {{ formatStatus(currentSubscription.status) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Billing Cycle</p>
                                <p class="text-base lg:text-lg font-semibold text-gray-900">{{ formatCycle(currentSubscription.billing_cycle) }}</p>
                            </div>
                            <div v-if="currentSubscription?.trial_ends_at">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Trial Ends</p>
                                <p class="text-base lg:text-lg font-semibold text-gray-900">
                                    {{ formatDate(currentSubscription.trial_ends_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Features -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                                Included Features
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div v-if="currentSubscription?.ai_analysis_included" class="flex items-center text-sm text-gray-700 bg-gray-50/50 rounded-lg px-3 py-2">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    AI Tax Analysis
                                </div>
                                <div v-if="currentSubscription?.payment_automation" class="flex items-center text-sm text-gray-700 bg-gray-50/50 rounded-lg px-3 py-2">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Payment Automation
                                </div>
                                <div class="flex items-center text-sm text-gray-700 bg-gray-50/50 rounded-lg px-3 py-2">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Max Staff: {{ currentSubscription?.max_staff_members || 0 }}
                                </div>
                                <div class="flex items-center text-sm text-gray-700 bg-gray-50/50 rounded-lg px-3 py-2">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Max Returns/Year: {{ currentSubscription?.max_returns_per_year || 0 }}
                                </div>
                            </div>
                        </div>

                        <!-- Renewal Info -->
                        <div v-if="currentSubscription?.renews_at && !isExpiringSoon && !isExpired" class="rounded-xl bg-blue-50/50 border border-blue-200/50 p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm text-blue-900">
                                    Next billing date: <strong>{{ formatDate(currentSubscription.renews_at) }}</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Expiration Warning -->
                        <div v-if="isExpiringSoon && currentSubscription?.plan_type !== 'free'"
                             :class="[
                                 'rounded-xl border p-4 animate-pulse',
                                 expirationWarningColor === 'red' ? 'bg-red-50/80 border-red-300' : '',
                                 expirationWarningColor === 'orange' ? 'bg-orange-50/80 border-orange-300' : '',
                                 expirationWarningColor === 'yellow' ? 'bg-yellow-50/80 border-yellow-300' : ''
                             ]">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5"
                                     :class="[
                                         expirationWarningColor === 'red' ? 'text-red-600' : '',
                                         expirationWarningColor === 'orange' ? 'text-orange-600' : '',
                                         expirationWarningColor === 'yellow' ? 'text-yellow-600' : ''
                                     ]"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="flex-1">
                                    <p :class="[
                                        'font-semibold mb-1',
                                        expirationWarningColor === 'red' ? 'text-red-900' : '',
                                        expirationWarningColor === 'orange' ? 'text-orange-900' : '',
                                        expirationWarningColor === 'yellow' ? 'text-yellow-900' : ''
                                    ]">
                                        ⚠️ Your subscription expires in {{ daysUntilExpiration }} {{ daysUntilExpiration === 1 ? 'day' : 'days' }}!
                                    </p>
                                    <p :class="[
                                        'text-sm mb-3',
                                        expirationWarningColor === 'red' ? 'text-red-800' : '',
                                        expirationWarningColor === 'orange' ? 'text-orange-800' : '',
                                        expirationWarningColor === 'yellow' ? 'text-yellow-800' : ''
                                    ]">
                                        Your account will be automatically downgraded to the <strong>Free plan</strong> on <strong>{{ formatDate(currentSubscription.renews_at) }}</strong>.
                                        You will lose access to premium features including AI analysis, bank integration, and advanced tax filing.
                                    </p>
                                    <a :href="route('business.subscription')"
                                       :class="[
                                           'inline-flex items-center px-4 py-2 rounded-lg font-medium text-sm transition-all',
                                           expirationWarningColor === 'red' ? 'bg-red-600 hover:bg-red-700 text-white' : '',
                                           expirationWarningColor === 'orange' ? 'bg-orange-600 hover:bg-orange-700 text-white' : '',
                                           expirationWarningColor === 'yellow' ? 'bg-yellow-600 hover:bg-yellow-700 text-white' : ''
                                       ]">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Renew Subscription Now
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Expired Notice -->
                        <div v-if="isExpired && currentSubscription?.plan_type !== 'free'"
                             class="rounded-xl bg-red-50/80 border border-red-300 p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="flex-1">
                                    <p class="font-semibold text-red-900 mb-1">
                                        Subscription Expired
                                    </p>
                                    <p class="text-sm text-red-800 mb-3">
                                        Your subscription has expired and will be automatically downgraded to the Free plan.
                                        Renew now to continue using premium features.
                                    </p>
                                    <a :href="route('business.subscription')"
                                       class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium text-sm transition-all">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Renew Subscription
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Active Subscription -->
                <div v-else class="rounded-2xl border border-amber-200 bg-amber-50/50 backdrop-blur-sm p-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-amber-900">You don't have an active subscription. Please select a plan below to get started.</p>
                    </div>
                </div>

                <!-- Available Plans -->
                <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                        <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                            Available Plans
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-600 cursor-help" title="Select a plan to upgrade or switch billing cycles. Changes take effect immediately unless otherwise stated.">
                                i
                            </span>
                        </h2>
                    </div>
                    <div class="p-6">
                        <!-- Billing Cycle Toggle -->
                        <div class="mb-8 flex flex-col sm:flex-row sm:items-center gap-4">
                            <span class="text-sm font-medium text-gray-700">Billing Cycle:</span>
                            <div class="flex gap-2 bg-gray-100/50 rounded-xl p-1">
                                <button
                                    @click="selectedBillingCycle = 'monthly'"
                                    :class="[
                                        'px-5 py-2 rounded-lg font-medium transition-all',
                                        selectedBillingCycle === 'monthly'
                                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    Monthly
                                </button>
                                <button
                                    @click="selectedBillingCycle = 'annual'"
                                    :class="[
                                        'px-5 py-2 rounded-lg font-medium transition-all relative',
                                        selectedBillingCycle === 'annual'
                                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    Annual
                                    <span class="absolute -top-2 -right-2 text-xs bg-gradient-to-r from-green-500 to-emerald-500 text-white px-2 py-0.5 rounded-full shadow-sm">
                                        Save 20%
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="(plan, planKey) in availablePlans"
                                :key="planKey"
                                :class="[
                                    'rounded-2xl border-2 transition-all hover:shadow-lg',
                                    currentSubscription?.plan_type === planKey
                                        ? 'border-blue-500 bg-gradient-to-br from-blue-50/30 to-white'
                                        : 'border-gray-200 hover:border-blue-300'
                                ]"
                            >
                                <div class="p-6">
                                    <!-- Plan Header -->
                                    <div class="mb-4">
                                        <h3 class="text-xl font-bold text-gray-900">{{ plan.name }}</h3>
                                        <div class="mt-3">
                                            <p class="text-3xl font-bold text-gray-900">
                                                ₦{{ formatNumber(selectedBillingCycle === 'annual' ? plan.annual_price : plan.monthly_price) }}
                                                <span class="text-sm text-gray-500 font-normal">/{{ selectedBillingCycle === 'annual' ? 'year' : 'month' }}</span>
                                            </p>
                                            <p v-if="selectedBillingCycle === 'annual'" class="text-xs text-green-600 mt-1 font-medium">
                                                Save ₦{{ formatNumber(plan.monthly_price * 12 - plan.annual_price) }}/year
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Limits -->
                                    <div class="space-y-2 mb-4 pb-4 border-b border-gray-200">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Staff Members</span>
                                            <span class="font-semibold text-gray-900">Up to {{ plan.max_staff }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Returns/Year</span>
                                            <span class="font-semibold text-gray-900">{{ plan.max_returns_per_year }}</span>
                                        </div>
                                    </div>

                                    <!-- Features -->
                                    <ul class="space-y-2 mb-6">
                                        <li v-if="plan.features.ai_analysis" class="flex items-center text-sm text-gray-700">
                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            AI Analysis
                                        </li>
                                        <li v-else class="flex items-center text-sm text-gray-400">
                                            <svg class="w-4 h-4 text-gray-300 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            AI Analysis
                                        </li>

                                        <li v-if="plan.features.payment_automation" class="flex items-center text-sm text-gray-700">
                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Payment Automation
                                        </li>
                                        <li v-else class="flex items-center text-sm text-gray-400">
                                            <svg class="w-4 h-4 text-gray-300 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            Payment Automation
                                        </li>

                                        <li v-if="plan.features.staff_management" class="flex items-center text-sm text-gray-700">
                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Staff Management
                                        </li>

                                        <li v-if="plan.features.priority_support" class="flex items-center text-sm text-gray-700">
                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Priority Support
                                        </li>

                                        <li v-if="plan.features.api_access" class="flex items-center text-sm text-gray-700">
                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            API Access
                                        </li>

                                        <li v-if="plan.features.custom_branding" class="flex items-center text-sm text-gray-700">
                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
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
                                        class="w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 font-medium transition-all shadow-sm hover:shadow-md"
                                    >
                                        {{ isUpgrading === planKey ? 'Processing...' : 'Upgrade to ' + plan.name }}
                                    </button>
                                    <div v-else class="w-full px-4 py-2.5 bg-green-100 text-green-800 rounded-xl text-center font-medium border border-green-200">
                                        Current Plan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Information -->
                <div class="bg-white rounded-2xl border border-gray-200/50 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50/50 to-white">
                        <h2 class="text-base font-semibold text-gray-900">Billing Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div v-if="currentSubscription">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Monthly Cost</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    ₦{{ formatNumber(currentSubscription.monthly_price) }}
                                </p>
                            </div>
                            <div v-else>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Monthly Cost</p>
                                <p class="text-gray-500">No active subscription</p>
                            </div>
                            <div v-if="currentSubscription">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Annual Cost</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    ₦{{ formatNumber(currentSubscription.annual_price) }}
                                </p>
                            </div>
                            <div v-else>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Annual Cost</p>
                                <p class="text-gray-500">No active subscription</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support -->
                <div class="rounded-2xl border border-blue-200 bg-gradient-to-r from-blue-50/50 to-indigo-50/50 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-blue-900 mb-1">Need Help?</h3>
                            <p class="text-sm text-blue-800">
                                Have questions about your subscription or need a custom plan? Contact our support team.
                            </p>
                        </div>
                        <a href="mailto:support@taxmaster.ng" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-blue-600 rounded-xl hover:bg-blue-50 transition-colors font-medium shadow-sm border border-blue-200 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            support@taxmaster.ng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import BillingGuardNotice from '@/Components/BillingGuardNotice.vue';
import { router, usePage } from '@inertiajs/vue3';

export default {
    components: { BusinessLayout, BillingGuardNotice },
    props: {
        currentSubscription: {
            type: Object,
            default: null
        },
        availablePlans: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            isUpgrading: null,
            selectedBillingCycle: 'monthly',
        };
    },
    computed: {
        daysUntilExpiration() {
            if (!this.currentSubscription?.renews_at) return null;
            const renewDate = new Date(this.currentSubscription.renews_at);
            const now = new Date();
            const diffTime = renewDate - now;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return diffDays;
        },
        isExpiringSoon() {
            return this.daysUntilExpiration !== null && this.daysUntilExpiration <= 7 && this.daysUntilExpiration > 0;
        },
        isExpired() {
            return this.daysUntilExpiration !== null && this.daysUntilExpiration <= 0;
        },
        expirationWarningColor() {
            if (this.daysUntilExpiration <= 1) return 'red';
            if (this.daysUntilExpiration <= 3) return 'orange';
            return 'yellow';
        },
    },
    methods: {
        async upgradePlan(planKey) {
            if (!confirm(`Upgrade to ${this.getPlanName(planKey)} plan (${this.selectedBillingCycle})?`)) return;

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
                        billing_cycle: this.selectedBillingCycle,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (data.payment_url) {
                        window.location.href = data.payment_url;
                    } else if (data.redirect) {
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
                active: 'bg-green-100 text-green-800 border-green-200',
                cancelled: 'bg-red-100 text-red-800 border-red-200',
                suspended: 'bg-orange-100 text-orange-800 border-orange-200',
                pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
                pending_payment: 'bg-blue-100 text-blue-800 border-blue-200',
            };
            return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
        },

        getStatusDotClass(status) {
            const classes = {
                active: 'bg-green-500',
                cancelled: 'bg-red-500',
                suspended: 'bg-orange-500',
                pending: 'bg-yellow-500',
                pending_payment: 'bg-blue-500',
            };
            return classes[status] || 'bg-gray-500';
        },

        formatStatus(status) {
            if (!status) return 'Unknown';
            return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },

        formatCycle(cycle) {
            if (!cycle) return 'N/A';
            return cycle.charAt(0).toUpperCase() + cycle.slice(1);
        },

        formatNumber(value) {
            return new Intl.NumberFormat('en-NG').format(value || 0);
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('en-NG', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },
    },
};
</script>

<style scoped>
/* Smooth transitions */
button, .transition-all {
    transition: all 0.2s ease;
}

/* Disabled button styles */
button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Hover effects for plan cards */
.rounded-2xl {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.rounded-2xl:hover {
    transform: translateY(-2px);
}
</style>
