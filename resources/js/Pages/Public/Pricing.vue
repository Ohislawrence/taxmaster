<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
});

const billingCycle = ref('monthly');

// Format currency
const formatPrice = (amount) => {
    if (!amount || amount === 0) return 'Free';
    return '₦' + Number(amount).toLocaleString();
};

// Get price based on billing cycle
const getPrice = (plan) => {
    if (plan.monthly_price === 0) return 'Free';
    if (billingCycle.value === 'annual') {
        return formatPrice(plan.annual_price);
    }
    return formatPrice(plan.monthly_price);
};

// Get period label
const getPeriod = (plan) => {
    if (plan.monthly_price === 0) return 'forever';
    return billingCycle.value === 'annual' ? '/year' : '/month';
};

// Annual savings
const getSavings = (plan) => {
    if (plan.monthly_price === 0 || !plan.annual_price) return null;
    const monthlyCost = plan.monthly_price * 12;
    const annualCost = plan.annual_price;
    const saved = monthlyCost - annualCost;
    if (saved <= 0) return null;
    return formatPrice(saved);
};

// Identify featured plan (Professional)
const isFeatured = (plan) => plan.slug === 'professional';

// CTA text
const getCta = (plan) => {
    if (plan.slug === 'free') return 'Get started free';
    if (plan.slug === 'enterprise') return 'Contact sales';
    return 'Get started';
};

// CTA link
const getCtaLink = (plan) => {
    if (plan.slug === 'enterprise') return '/contact';
    return route('register');
};

// Key highlights for each plan card (compact summary instead of full feature list)
const getHighlights = (plan) => {
    const staff = plan.max_staff_members >= 999 ? 'Unlimited staff' : `${plan.max_staff_members} staff member${plan.max_staff_members > 1 ? 's' : ''}`;
    const returns = plan.max_returns_per_year >= 9999 ? 'Unlimited returns' : `${plan.max_returns_per_year} returns/year`;
    const storage = `${plan.storage_gb} GB storage`;
    const highlights = [staff, returns, storage];
    if (plan.ai_analysis_included) highlights.push('AI tax analysis');
    if (plan.payment_automation) highlights.push('Payment automation');
    if (plan.priority_support) highlights.push('Priority support');
    if (plan.custom_branding) highlights.push('Custom branding');
    return highlights;
};

// Feature comparison table data
const comparisonFeatures = [
    { category: 'Tax Filing', features: [
        { name: 'PAYE Filing', free: true, basic: true, professional: true, enterprise: true },
        { name: 'VAT Returns', free: true, basic: true, professional: true, enterprise: true },
        { name: 'WHT Remittance', free: false, basic: true, professional: true, enterprise: true },
        { name: 'CIT Filing', free: false, basic: false, professional: true, enterprise: true },
        { name: 'Multi-state PAYE', free: false, basic: false, professional: true, enterprise: true },
    ]},
    { category: 'Returns & Staff', features: [
        { name: 'Returns per year', free: '5', basic: '50', professional: '500', enterprise: 'Unlimited' },
        { name: 'Staff members', free: '1', basic: '3', professional: '10', enterprise: 'Unlimited' },
        { name: 'Bank accounts', free: '0', basic: '1', professional: '3', enterprise: 'Unlimited' },
        { name: 'Storage', free: '1 GB', basic: '5 GB', professional: '50 GB', enterprise: '500 GB' },
    ]},
    { category: 'Automation & AI', features: [
        { name: 'AI Tax Analysis', free: false, basic: true, professional: true, enterprise: true },
        { name: 'Payment Automation', free: false, basic: false, professional: true, enterprise: true },
        { name: 'Remita Integration', free: false, basic: false, professional: true, enterprise: true },
        { name: 'Auto-categorisation', free: false, basic: true, professional: true, enterprise: true },
    ]},
    { category: 'Reporting', features: [
        { name: 'Basic Reports', free: true, basic: true, professional: true, enterprise: true },
        { name: 'CSV/PDF Export', free: false, basic: true, professional: true, enterprise: true },
        { name: 'Advanced Analytics', free: false, basic: false, professional: true, enterprise: true },
        { name: 'Custom Reports', free: false, basic: false, professional: false, enterprise: true },
    ]},
    { category: 'Support & Security', features: [
        { name: 'Community Support', free: true, basic: true, professional: true, enterprise: true },
        { name: 'Email Support', free: false, basic: true, professional: true, enterprise: true },
        { name: 'Priority Support (24/7)', free: false, basic: false, professional: true, enterprise: true },
        { name: 'Dedicated Account Manager', free: false, basic: false, professional: false, enterprise: true },
        { name: 'API Access', free: false, basic: false, professional: true, enterprise: true },
        { name: 'Custom Branding', free: false, basic: false, professional: false, enterprise: true },
        { name: 'SLA Guarantee', free: false, basic: false, professional: false, enterprise: true },
        { name: 'Audit Trail & RBAC', free: false, basic: false, professional: true, enterprise: true },
    ]},
];

// FAQs
const faqs = [
    {
        q: 'Is the Free plan really free forever?',
        a: 'Yes. The Free plan has no time limit and no credit card required. You can use it for as long as you need. Upgrade only when you outgrow it.',
    },
    {
        q: 'Can I switch plans at any time?',
        a: 'Absolutely. You can upgrade or downgrade your plan at any time. When upgrading, you only pay the prorated difference. Downgrade takes effect at the end of your current billing cycle.',
    },
    {
        q: 'What payment methods do you accept?',
        a: 'We accept payments via Paystack — including debit/credit cards (Visa, Mastercard, Verve) and bank transfers. All transactions are processed securely.',
    },
    {
        q: 'Do you offer annual billing?',
        a: 'Yes. Annual billing gives you a significant discount — typically 2 months free compared to monthly billing. Toggle the billing switch above to see annual pricing.',
    },
    {
        q: 'What happens if I exceed my plan limits?',
        a: 'We\'ll notify you when you\'re approaching your limits. You can upgrade at any time, or we\'ll gently restrict new filings until you do. Your existing data is never affected.',
    },
    {
        q: 'Is my data secure?',
        a: 'Absolutely. We\'re NDPA compliant with encrypted TIN storage, full audit trails, and role-based access controls. Your data is stored on secure Nigerian servers.',
    },
];

const openFaq = ref(null);
const toggleFaq = (i) => {
    openFaq.value = openFaq.value === i ? null : i;
};

// Intersection Observer for scroll reveals
onMounted(() => {
    nextTick(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
        );
        document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
    });
});
</script>

<template>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-32 sm:pb-16 lg:pt-40 lg:pb-20">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="mx-auto max-w-3xl text-center">
                <div class="reveal reveal-up mb-5 inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-4 py-1.5 text-sm font-medium text-gray-600">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    Transparent pricing
                </div>
                <h1 class="reveal reveal-up text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl lg:text-[3.5rem] lg:leading-[1.1]">
                    Simple pricing,<br class="hidden sm:inline" /> powerful features
                </h1>
                <p class="reveal reveal-up mt-4 text-base leading-relaxed text-gray-500 sm:mt-6 sm:text-lg">
                    Start free — no time limit, no credit card. Upgrade as your business grows.
                </p>

                <!-- Billing toggle -->
                <div class="reveal reveal-up mt-8 flex items-center justify-center gap-3">
                    <span class="text-sm font-medium" :class="billingCycle === 'monthly' ? 'text-gray-900' : 'text-gray-400'">Monthly</span>
                    <button
                        @click="billingCycle = billingCycle === 'monthly' ? 'annual' : 'monthly'"
                        class="relative inline-flex h-7 w-[52px] items-center rounded-full transition-colors"
                        :class="billingCycle === 'annual' ? 'bg-gray-900' : 'bg-gray-200'"
                    >
                        <span
                            class="inline-block h-5 w-5 rounded-full bg-white shadow-sm transition-transform"
                            :class="billingCycle === 'annual' ? 'translate-x-[28px]' : 'translate-x-1'"
                        ></span>
                    </button>
                    <span class="text-sm font-medium" :class="billingCycle === 'annual' ? 'text-gray-900' : 'text-gray-400'">
                        Annual
                        <span class="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-semibold text-green-700">Save 17%</span>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Cards -->
    <section class="pb-16 sm:pb-20 lg:pb-28 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto grid max-w-6xl gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="reveal reveal-up relative flex flex-col rounded-2xl border p-6 sm:p-8 transition-all duration-300 hover:shadow-lg"
                    :class="isFeatured(plan) ? 'border-gray-900 ring-2 ring-gray-900 shadow-lg' : 'border-gray-200 bg-white hover:border-gray-300'"
                >
                    <!-- Featured badge -->
                    <div v-if="isFeatured(plan)" class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                        <span class="rounded-full bg-gray-900 px-4 py-1 text-[11px] font-semibold text-white whitespace-nowrap">
                            Most Popular
                        </span>
                    </div>

                    <!-- Plan name -->
                    <h3 class="text-base font-semibold text-gray-900">{{ plan.name }}</h3>

                    <!-- Price -->
                    <div class="mt-4 flex items-baseline gap-1">
                        <span class="text-3xl font-bold text-gray-900 sm:text-4xl">{{ getPrice(plan) }}</span>
                        <span v-if="plan.monthly_price > 0" class="text-sm text-gray-500">{{ getPeriod(plan) }}</span>
                    </div>

                    <!-- Annual savings -->
                    <p v-if="billingCycle === 'annual' && getSavings(plan)" class="mt-1.5 text-[12px] font-medium text-green-600">
                        Save {{ getSavings(plan) }}/year
                    </p>

                    <!-- Description -->
                    <p class="mt-3 text-sm text-gray-500 leading-relaxed">{{ plan.description }}</p>

                    <!-- Key highlights -->
                    <ul class="mt-6 flex-1 space-y-2 sm:mt-8">
                        <li v-for="h in getHighlights(plan)" :key="h" class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="h-4 w-4 flex-shrink-0 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ h }}
                        </li>
                    </ul>

                    <!-- Compare link -->
                    <a href="#compare" class="mt-4 inline-flex items-center gap-1 text-xs font-medium text-gray-400 hover:text-gray-600 transition-colors">
                        Compare all features
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                    </a>

                    <!-- CTA -->
                    <Link
                        :href="getCtaLink(plan)"
                        class="mt-6 block rounded-lg py-3 text-center text-sm font-semibold transition-all sm:mt-8 active:scale-[0.98]"
                        :class="isFeatured(plan)
                            ? 'bg-gray-900 text-white hover:bg-black shadow-lg shadow-gray-900/20'
                            : 'border border-gray-200 text-gray-700 hover:border-gray-300 hover:bg-gray-50'"
                    >
                        {{ getCta(plan) }}
                    </Link>
                </div>
            </div>

            <!-- Subtext -->
            <p class="reveal reveal-up mt-8 text-center text-sm text-gray-400">
                All plans include NDPA-compliant data protection · Prices in Nigerian Naira
            </p>
        </div>
    </section>

    <!-- Feature Comparison Table -->
    <section id="compare" class="py-16 sm:py-20 lg:py-28 bg-gray-50 relative overflow-hidden scroll-mt-20">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] opacity-40"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up mx-auto max-w-2xl text-center mb-10 sm:mb-16">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Compare</p>
                <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl lg:text-4xl">
                    Feature comparison
                </h2>
                <p class="mt-3 text-base text-gray-500 sm:text-lg">
                    See exactly what's included in each plan.
                </p>
            </div>

            <!-- Desktop table -->
            <div class="reveal reveal-up hidden lg:block">
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="px-6 py-5 text-left text-sm font-semibold text-gray-900 w-[280px]">Feature</th>
                                <th v-for="plan in plans" :key="plan.id" class="px-4 py-5 text-center text-sm font-semibold"
                                    :class="isFeatured(plan) ? 'text-gray-900 bg-gray-50' : 'text-gray-600'">
                                    <div>{{ plan.name }}</div>
                                    <div class="mt-1 text-xs font-normal text-gray-400">{{ getPrice(plan) }}<span v-if="plan.monthly_price > 0" class="text-gray-400">{{ getPeriod(plan) }}</span></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="section in comparisonFeatures" :key="section.category">
                                <!-- Category header -->
                                <tr class="border-t border-gray-100 bg-gray-50/50">
                                    <td colspan="5" class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        {{ section.category }}
                                    </td>
                                </tr>
                                <!-- Feature rows -->
                                <tr v-for="feature in section.features" :key="feature.name" class="border-t border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3.5 text-sm text-gray-700">{{ feature.name }}</td>
                                    <td class="px-4 py-3.5 text-center">
                                        <template v-if="typeof feature.free === 'boolean'">
                                            <svg v-if="feature.free" class="mx-auto h-5 w-5 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span v-else class="inline-block h-0.5 w-4 bg-gray-200 rounded"></span>
                                        </template>
                                        <span v-else class="text-sm text-gray-600">{{ feature.free }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <template v-if="typeof feature.basic === 'boolean'">
                                            <svg v-if="feature.basic" class="mx-auto h-5 w-5 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span v-else class="inline-block h-0.5 w-4 bg-gray-200 rounded"></span>
                                        </template>
                                        <span v-else class="text-sm text-gray-600">{{ feature.basic }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center" :class="isFeatured(plans[2]) ? 'bg-gray-50/50' : ''">
                                        <template v-if="typeof feature.professional === 'boolean'">
                                            <svg v-if="feature.professional" class="mx-auto h-5 w-5 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span v-else class="inline-block h-0.5 w-4 bg-gray-200 rounded"></span>
                                        </template>
                                        <span v-else class="text-sm font-medium text-gray-900">{{ feature.professional }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <template v-if="typeof feature.enterprise === 'boolean'">
                                            <svg v-if="feature.enterprise" class="mx-auto h-5 w-5 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span v-else class="inline-block h-0.5 w-4 bg-gray-200 rounded"></span>
                                        </template>
                                        <span v-else class="text-sm text-gray-600">{{ feature.enterprise }}</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile comparison cards -->
            <div class="reveal reveal-up lg:hidden space-y-8">
                <div v-for="plan in plans" :key="plan.id"
                    class="rounded-2xl border bg-white p-5 sm:p-6"
                    :class="isFeatured(plan) ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-200'">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">{{ plan.name }}</h3>
                            <p class="text-sm text-gray-500">{{ getPrice(plan) }} <span v-if="plan.monthly_price > 0">{{ getPeriod(plan) }}</span></p>
                        </div>
                        <span v-if="isFeatured(plan)" class="rounded-full bg-gray-900 px-3 py-0.5 text-[10px] font-semibold text-white">Popular</span>
                    </div>
                    <div v-for="section in comparisonFeatures" :key="section.category" class="mb-3 last:mb-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 mb-2">{{ section.category }}</p>
                        <ul class="space-y-1.5">
                            <li v-for="feature in section.features" :key="feature.name" class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">{{ feature.name }}</span>
                                <template v-if="typeof feature[plan.slug] === 'boolean'">
                                    <svg v-if="feature[plan.slug]" class="h-4 w-4 text-gray-900 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span v-else class="inline-block h-0.5 w-3.5 bg-gray-200 rounded flex-shrink-0"></span>
                                </template>
                                <span v-else class="text-sm font-medium text-gray-900 flex-shrink-0">{{ feature[plan.slug] }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 sm:py-20 lg:py-28 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <div class="reveal reveal-up text-center mb-10 sm:mb-16">
                    <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">FAQ</p>
                    <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl lg:text-4xl">
                        Frequently asked questions
                    </h2>
                </div>

                <div class="reveal reveal-up space-y-3">
                    <div
                        v-for="(faq, i) in faqs"
                        :key="i"
                        class="rounded-2xl border border-gray-200 bg-white transition-all hover:border-gray-300"
                        :class="openFaq === i ? 'shadow-sm' : ''"
                    >
                        <button
                            @click="toggleFaq(i)"
                            class="flex w-full items-center justify-between p-5 sm:p-6 text-left"
                        >
                            <span class="text-sm font-semibold text-gray-900 sm:text-base pr-4">{{ faq.q }}</span>
                            <svg
                                class="h-5 w-5 flex-shrink-0 text-gray-400 transition-transform duration-200"
                                :class="openFaq === i ? 'rotate-180' : ''"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition-all duration-300 ease-out"
                            enter-from-class="opacity-0 max-h-0"
                            enter-to-class="opacity-100 max-h-96"
                            leave-active-class="transition-all duration-200 ease-in"
                            leave-from-class="opacity-100 max-h-96"
                            leave-to-class="opacity-0 max-h-0"
                        >
                            <div v-show="openFaq === i" class="overflow-hidden">
                                <p class="px-5 pb-5 text-sm leading-relaxed text-gray-500 sm:px-6 sm:pb-6">
                                    {{ faq.a }}
                                </p>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 sm:py-20 lg:py-24 bg-gray-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 h-px w-1/2 bg-gradient-to-r from-transparent via-gray-700 to-transparent"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up mx-auto max-w-2xl text-center">
                <h2 class="text-2xl font-bold text-white sm:text-3xl lg:text-4xl">
                    Start managing your taxes today
                </h2>
                <p class="mt-4 text-base text-gray-400 sm:text-lg">
                    Join 1,200+ Nigerian businesses. Free forever for small teams.
                </p>
                <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center sm:gap-4">
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3.5 text-sm font-semibold text-gray-900 shadow-lg transition-all sm:px-8 sm:py-4 sm:text-base hover:bg-gray-100 active:scale-[0.98]"
                    >
                        Get started — it's free
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    <Link
                        href="/contact"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-700 px-6 py-3.5 text-sm font-semibold text-gray-300 transition-all sm:px-8 sm:py-4 sm:text-base hover:border-gray-500 hover:text-white"
                    >
                        Talk to sales
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.reveal {
    opacity: 0;
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.reveal.reveal-up {
    transform: translateY(30px);
}
.reveal.is-visible {
    opacity: 1;
    transform: translateY(0) translateX(0);
}
</style>
