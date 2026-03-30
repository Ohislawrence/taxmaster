<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

defineOptions({ layout: PublicLayout });

// Exhaustive feature list for businesses — used to render highlight cards
const features = ref([
    {
        title: 'Automated PAYE, VAT, WHT & CIT',
        description: 'Compute and prepare statutory PAYE, VAT, Withholding Tax and Corporate Income Tax returns with filing-ready schedules and supporting documents.',
        icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'
    },
    {
        title: 'Filing & Submission',
        description: 'Generate filing packages, submit to portals where available, and track submission receipts and acknowledgment references.',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
    },
    {
        title: 'Tax Calendar & Reminders',
        description: 'Centralised compliance calendar with filing deadlines, automated reminders and recurring schedules to avoid penalties.',
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
    },
    {
        title: 'Compliance Risk Scoring',
        description: 'Per-business compliance risk scoring and visibility (risk badges) to highlight overdue filings, missing documents, and tax exposures.',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
    },
    {
        title: 'Bank Integration (Mono)',
        description: 'Connect your bank to import transactions, reconcile receipts and expenses, and auto-suggest tax categorizations.',
        icon: 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4m-9 4v10'
    },
    {
        title: 'Payment Automation',
        description: 'Automate tax remittances and schedule payments; integrate card and bank payments to settle liabilities.',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    {
        title: 'Payroll & Employee Management',
        description: 'Manage payroll, statutory deductions, and generate PAYE reports with employee allowances and benefits handling.',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
    },
    {
        title: 'Bulk Operations & CSV Import',
        description: 'Import payroll, suppliers, transactions or product lists via CSV for fast bulk processing.',
        icon: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4'
    },
    {
        title: 'Document Storage & Evidence',
        description: 'Attach invoices, receipts and supporting documents to filings and store them securely for audits.',
        icon: 'M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z'
    },
    {
        title: 'Role-Based Access & Collaboration',
        description: 'Invite accountants, managers and staff with granular roles and per-business permissions.',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
    },
    {
        title: 'Invite an Accountant',
        description: 'Invite an accountant via email token to help manage filings and compliance—secure, revocable and auditable.',
        icon: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'
    },
    {
        title: 'Audit Trail & Activity Logs',
        description: 'Detailed logs for changes, approvals and submission history to support audits and regulatory enquiries.',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'
    },
    {
        title: 'Reports & Exports',
        description: 'Download CSV, XLSX and PDF reports for VAT, PAYE, WHT, CIT, reconciliations and financial analytics.',
        icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
    },
    {
        title: 'Analytics & Dashboards',
        description: 'Business dashboards with tax liabilities, filing status, cash-flow impact and historical trends.',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
    },
    {
        title: 'Multi-Entity & Multi-Branch Support',
        description: 'Manage multiple companies or branches from a single account with consolidated reporting.',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
    },
    {
        title: 'AI-Assisted Categorisation',
        description: 'AI suggestions for transaction categorisation, anomaly detection and return pre-filling (where included in plan).',
        icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
    },
    {
        title: 'Integrations & API',
        description: 'Open API for integrations, webhooks for events, and built-in connectors for banks and payment processors.',
        icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'
    },
    {
        title: 'Secure Data & NDPA Compliance',
        description: 'NDPA-aligned data handling, encrypted storage for sensitive identifiers and role-based data access.',
        icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'
    },
    {
        title: 'Team Billing & Subscriptions',
        description: 'Flexible subscription plans with seats, usage limits, and easy plan upgrades/downgrades.',
        icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'
    },
    {
        title: 'Support & Onboarding',
        description: 'Guided onboarding, documentation and priority support options for higher-tier plans.',
        icon: 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'
    },
    {
        title: 'Tax Calculator & Estimators',
        description: 'Built-in tax estimator for quick PAYE/VAT/CIT estimates and planning scenarios.',
        icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'
    },
    {
        title: 'Notifications & Approvals',
        description: 'Approval workflows with notifications for managers and accountants before filings are finalised.',
        icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'
    },
    {
        title: 'Offline & Manual Filing Support',
        description: 'Download filing packages when manual submission is required; capture and store official receipts.',
        icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'
    },
    {
        title: 'Compliance Remediation Tools',
        description: 'Step-by-step remediation guidance for late filings, penalties and reconciliations.',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
    },
    {
        title: 'FIRS E-Invoicing Compliance',
        description: 'Generate FIRS-compliant UBL 2.1 invoices with digital signatures. Automated submission to FIRS e-invoicing portal or manual export for offline filing.',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
    },
    {
        title: 'Invoice & Receipt Capture',
        description: 'Create professional invoices with buyer details and TIN validation. Download PDF invoices or export in FIRS-compliant formats (UBL XML/JSON).',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
    },
    {
        title: 'Vendor & Supplier WHT Management',
        description: 'Manage supplier withholding tax, certificates and credits across filings.',
        icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'
    },
]);

// Split features for staggered display
const firstTwoFeatures = computed(() => features.value.slice(0, 2));
const remainingFeatures = computed(() => features.value.slice(2));

// Animation states
const hoveredFeature = ref(null);

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                // Optional: unobserve after animation
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });

    // Observe all reveal elements
    document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));

    // Stagger children of feature grid
    const featureItems = document.querySelectorAll('.feature-item');
    featureItems.forEach((el, index) => {
        el.style.transitionDelay = `${index * 30}ms`;
    });
});
</script>

<template>
    <div class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-28 sm:pb-16 lg:pt-36 lg:pb-24">
        <!-- Animated background gradient -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:3rem_3rem] sm:bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

        <!-- Floating orbs for visual interest -->
        <div class="absolute top-20 left-10 w-64 h-64 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-64 h-64 bg-slate-50 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse animation-delay-2000"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Hero Section with staggered animation -->
            <div class="text-center max-w-4xl mx-auto mb-12 sm:mb-16 lg:mb-20">

                <h1 class="reveal text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 tracking-tight mb-4 animate-slide-up">
                    Built for Nigerian businesses —
                    <span class="text-blue-600 relative inline-block">
                        compliant, fast, reliable
                        <svg class="absolute -bottom-2 left-0 w-full h-2 text-blue-200" viewBox="0 0 100 10" preserveAspectRatio="none">
                            <path d="M0,5 Q25,0 50,5 T100,5" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                    </span>
                </h1>

                <p class="reveal text-base sm:text-lg lg:text-xl text-slate-500 leading-relaxed max-w-3xl mx-auto animate-slide-up animation-delay-200">
                    All the tax tools you need to automate filings, eliminate surprises, and keep your cashflow healthy — payroll, VAT, remittances, CIT and bank integrations in a single platform.
                </p>

                <div class="reveal flex flex-col sm:flex-row items-center justify-center gap-4 mt-8 animate-slide-up animation-delay-400">
                    <Link
                        :href="route('register')"
                        class="group inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 sm:px-8 py-3 sm:py-4 text-sm font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95 hover:scale-105"
                    >
                        Create your free account
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>

                    <Link
                        :href="route('contact')"
                        class="group inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-6 sm:px-8 py-3 sm:py-4 text-sm font-medium text-slate-600 transition-all hover:border-slate-300 hover:bg-slate-50 hover:scale-105"
                    >
                        Talk to sales
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </Link>
                </div>

                <!-- Trust badges -->
                <div class="reveal flex flex-wrap items-center justify-center gap-6 mt-8 text-xs text-slate-400 animate-fade-in animation-delay-600">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        No credit card required
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        NDPA Compliant
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        5,000+ businesses
                    </span>
                </div>
            </div>

            <!-- Feature Highlights Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-12 sm:mb-16">
                <div
                    v-for="(f, i) in firstTwoFeatures"
                    :key="i"
                    class="group bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 reveal hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)] transition-all duration-500 hover:border-slate-200"
                    :style="{ transitionDelay: `${i * 100}ms` }"
                    @mouseenter="hoveredFeature = i"
                    @mouseleave="hoveredFeature = null"
                >
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors duration-300">
                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="f.icon" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors">{{ f.title }}</h3>
                            <p class="text-sm text-slate-500 leading-relaxed">{{ f.description }}</p>
                        </div>
                    </div>

                    <!-- Animated indicator -->
                    <div class="mt-4 flex justify-end">
                        <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-blue-50 transition-all duration-300 group-hover:scale-110">
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition-colors" :class="{ 'translate-x-0.5': hoveredFeature === i }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Features Grid -->
            <section id="features" class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 mb-12 lg:mb-16">
                <!-- Left column - Main features -->
                <div class="lg:col-span-2 space-y-6">
                    <article class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 reveal">
                        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-3">Comprehensive tax and compliance automation</h2>
                        <p class="text-sm sm:text-base text-slate-500 leading-relaxed">From transaction capture to audit-ready filings — automate routine work, reduce errors, and keep regulators satisfied. Below is the full list of capabilities businesses get with TaxMaster.</p>

                        <!-- Feature grid with staggered animation -->
                        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div
                                v-for="(feature, idx) in remainingFeatures"
                                :key="idx"
                                class="feature-item p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-all duration-300 hover:shadow-md group cursor-default"
                                :class="{ 'is-visible': true }"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white group-hover:bg-blue-50 transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-500 group-hover:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-1 group-hover:text-blue-600 transition-colors">{{ feature.title }}</h4>
                                        <p class="text-xs text-slate-500 leading-relaxed">{{ feature.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <!-- Notifications Card with animation -->
                    <article class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 reveal hover:shadow-lg transition-all duration-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Notifications, approvals & controls</h3>
                        </div>
                        <p class="text-sm text-slate-500 leading-relaxed">Approval workflows, manager notifications, and role-based controls ensure filings are reviewed before submission. Admins can revoke invites and manage accountants centrally.</p>

                        <!-- Animated progress bar -->
                        <div class="mt-4 h-1 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full w-0 group-hover:w-2/3 transition-all duration-1000"></div>
                        </div>
                    </article>
                </div>

                <!-- Right column - Benefits & CTA -->
                <aside class="space-y-6">
                    <!-- Why Choose Us Card with floating animation -->
                    <div class="bg-slate-50 rounded-2xl sm:rounded-3xl border border-slate-200 p-6 sm:p-8 reveal hover:shadow-xl transition-all duration-500 hover:-translate-y-1">
                        <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Why businesses choose TaxMaster
                        </h4>

                        <ul class="space-y-3 text-sm text-slate-600">
                            <li v-for="(benefit, idx) in [
                                'Avoid penalties with automated deadline tracking',
                                'Save hours through bank sync and AI categorisation',
                                'Collaborate securely with invited accountants',
                                'Produce audit-ready exports in seconds',
                                'Scale with flexible plans and seat-based billing'
                            ]" :key="idx"
                                class="flex items-start gap-2 group/item"
                            >
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5 group-hover/item:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="group-hover/item:text-slate-900 transition-colors">{{ benefit }}</span>
                            </li>
                        </ul>

                        <div class="mt-6">
                            <Link
                                :href="route('contact')"
                                class="group w-full inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95 hover:scale-105"
                            >
                                Contact sales
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Security Card with pulse animation -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 reveal hover:shadow-lg transition-all duration-500">
                        <div class="relative">
                            <div class="absolute -top-2 -right-2">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                                </span>
                            </div>

                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>

                            <h4 class="text-lg font-semibold text-slate-900 mb-2">Security & compliance</h4>
                            <p class="text-sm text-slate-500 leading-relaxed">NDPA-aligned handling, encrypted storage, and audit trails. We retain detailed submission receipts for regulatory verification.</p>

                            <div class="flex flex-wrap gap-2 mt-4">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all">
                                    NDPA Compliant
                                </span>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all">
                                    End-to-end encryption
                                </span>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all">
                                    SOC2 Type II
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Card with counter animation (simulated) -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl sm:rounded-3xl p-6 sm:p-8 reveal transform hover:scale-105 transition-all duration-500">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white animate-pulse">5K+</div>
                                <div class="text-xs text-slate-400">Active businesses</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white animate-pulse animation-delay-1000">₦2B+</div>
                                <div class="text-xs text-slate-400">Tax processed</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white animate-pulse animation-delay-2000">98%</div>
                                <div class="text-xs text-slate-400">Time saved</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white animate-pulse animation-delay-3000">24/7</div>
                                <div class="text-xs text-slate-400">Support</div>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>

            <!-- Bottom CTA with floating animation -->
            <section class="text-center bg-gradient-to-br from-slate-50 to-white rounded-3xl sm:rounded-4xl p-8 sm:p-12 lg:p-16 reveal border border-slate-100 relative overflow-hidden group">
                <!-- Animated background effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-blue-50/0 via-blue-50/50 to-blue-50/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>

                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 mb-4 relative">
                    Ready to simplify your tax operations?
                </h2>
                <p class="text-base sm:text-lg text-slate-500 mb-8 max-w-2xl mx-auto relative">
                    Create a business account, invite your accountant, connect your bank, and start automating filings today.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative">
                    <Link
                        :href="route('register')"
                        class="group inline-flex items-center gap-2 rounded-full bg-slate-900 px-8 py-4 text-base font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95 hover:scale-105"
                    >
                        Get started for free
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>

                    <Link
                        :href="route('pricing')"
                        class="group inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-8 py-4 text-base font-medium text-slate-600 transition-all hover:border-slate-300 hover:bg-slate-50 hover:scale-105"
                    >
                        See pricing
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <!-- Bottom trust indicators -->
                <div class="flex flex-wrap items-center justify-center gap-6 mt-8 text-xs text-slate-400 relative">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Sign up for free
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Cancel anytime
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        No setup fees
                    </span>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
/* Animation keyframes */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Animation classes */
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.8s cubic-bezier(0.22, 1, 0.36, 1);
}

.reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}

.animate-slide-up {
    animation: slideUp 0.8s ease-out forwards;
}

.animate-pulse {
    animation: pulse 3s ease-in-out infinite;
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

/* Animation delays */
.animation-delay-200 {
    animation-delay: 200ms;
}

.animation-delay-400 {
    animation-delay: 400ms;
}

.animation-delay-600 {
    animation-delay: 600ms;
}

.animation-delay-1000 {
    animation-delay: 1000ms;
}

.animation-delay-2000 {
    animation-delay: 2000ms;
}

.animation-delay-3000 {
    animation-delay: 3000ms;
}

/* Feature item transitions */
.feature-item {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease, transform 0.5s ease, background-color 0.3s ease;
}

.feature-item.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Hover effects */
.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}

/* Custom border radius */
@media (min-width: 640px) {
    .sm\:rounded-4xl {
        border-radius: 2rem;
    }
}

/* Gradient animations */
.bg-gradient-shift {
    background-size: 200% 200%;
    animation: gradientShift 10s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}
</style>
