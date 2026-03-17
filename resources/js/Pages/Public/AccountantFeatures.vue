<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineOptions({ layout: PublicLayout });

// Feature highlights for animation
const features = ref([
    {
        title: 'Multi-Entity Management',
        description: 'Create and switch between multiple client businesses, manage filings, and assign roles to staff or other advisors.',
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'
    },
    {
        title: 'Automated Filing & Schedules',
        description: 'Generate PAYE, VAT, WHT and CIT returns with prefilled schedules and downloadable reports all Ai assisted.',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
    }
]);

onMounted(() => {
    // Intersection Observer for reveal animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
});
</script>

<template>
    <div class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-28 sm:pb-16 lg:pt-36 lg:pb-24">
        <!-- Subtle grid background - Mono.co style -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:3rem_3rem] sm:bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="text-center max-w-4xl mx-auto mb-12 sm:mb-16 lg:mb-20 reveal">

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 tracking-tight mb-4">
                    TaxMaster for Accountants
                </h1>

                <p class="text-base sm:text-lg lg:text-xl text-slate-500 leading-relaxed max-w-3xl mx-auto">
                    Manage multiple clients, automate filings, get paid, and grow your practice with TaxMaster's accountant tools — built for Nigerian tax workflows.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
                    <Link
                        :href="route('contact')"
                        class="group inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 sm:px-8 py-3 sm:py-4 text-sm font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95"
                    >
                        Contact us to request an accountant account
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>

                    <Link
                        href="#features"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-6 sm:px-8 py-3 sm:py-4 text-sm font-medium text-slate-600 transition-all hover:border-slate-300 hover:bg-slate-50"
                    >
                        View features
                    </Link>
                </div>

                <p class="text-xs text-slate-500 mt-4">Please contact us to request an accountant account.</p>
            </div>

            <!-- Quick summary cards - Enhanced -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-12 sm:mb-16">
                <div
                    v-for="(feature, index) in features"
                    :key="index"
                    class="group bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 transition-all duration-300 hover:border-slate-200 hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)] reveal"
                    :style="{ transitionDelay: `${index * 100}ms` }"
                >
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-slate-900 mb-2">{{ feature.title }}</h3>
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Features Grid -->
            <section id="features" class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 mb-12 lg:mb-16">
                <!-- Left column - Main features -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Overview Card -->
                    <article class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 reveal">
                        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-3">Designed for accounting practices</h2>
                        <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
                            TaxMaster provides a single pane of glass for accountants managing multiple clients across Nigeria. Below are the core capabilities that help you save time, reduce errors, and scale your practice.
                        </p>

                        <!-- Feature List with Icons -->
                        <div class="mt-8 space-y-6">
                            <!-- Client Onboarding -->
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-base sm:text-lg font-semibold text-slate-900 mb-1">Client Onboarding & Business Creation</h4>
                                    <p class="text-sm text-slate-500 leading-relaxed">Quickly create client businesses, capture TINs, contact information, and optional bank connections. Businesses created by accountants are clearly marked in the admin console for governance and reporting.</p>
                                </div>
                            </div>

                            <!-- Role-Based Access -->
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-base sm:text-lg font-semibold text-slate-900 mb-1">Role-Based Access</h4>
                                    <p class="text-sm text-slate-500 leading-relaxed">Invite team members with granular roles (viewer, accountant, manager). Use per-business access controls so junior staff can prepare returns while seniors review and approve.</p>
                                </div>
                            </div>

                            <!-- Automated Tax Computations -->
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-base sm:text-lg font-semibold text-slate-900 mb-1">Automated PAYE, VAT, WHT & CIT</h4>
                                    <p class="text-sm text-slate-500 leading-relaxed">TaxMaster computes PAYE using statutory bands and allowances, prepares VAT returns with input/output breakdowns, tracks WHT credits, and computes CIT from your ledgers. Export filing-ready schedules and supporting documents.</p>
                                </div>
                            </div>

                            <!-- Bank Integration -->
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4m-9 4v10" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-base sm:text-lg font-semibold text-slate-900 mb-1">Bank Integration & Transaction Categorisation</h4>
                                    <p class="text-sm text-slate-500 leading-relaxed">Connect client banks via Mono to import transactions. Auto-categorise receipts and expenses for accurate VAT and profit computations, and flag potential tax adjustments.</p>
                                </div>
                            </div>

                            <!-- Bulk Operations -->
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-base sm:text-lg font-semibold text-slate-900 mb-1">Bulk Operations & CSV Import</h4>
                                    <p class="text-sm text-slate-500 leading-relaxed">Import staff payroll, transactions, and supplier lists via CSV. Run bulk filings across selected clients to save time during month-end.</p>
                                </div>
                            </div>

                            <!-- Audit Trail -->
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-base sm:text-lg font-semibold text-slate-900 mb-1">Audit Trail & Compliance</h4>
                                    <p class="text-sm text-slate-500 leading-relaxed">Every change is logged. Maintain audit trails for TIN updates, return edits, and approvals to help during client audits and regulatory reviews.</p>
                                </div>
                            </div>
                        </div>
                    </article>

                    <!-- Affiliate / Monetization Card -->
                    <article class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 reveal">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold text-slate-900">Affiliate & Referral Features</h3>
                        </div>

                        <p class="text-sm text-slate-500 mb-6">Grow revenue by referring clients to TaxMaster. Our affiliate system is built for accountants who introduce businesses to the platform.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div class="p-4 bg-slate-50 rounded-xl">
                                <h4 class="font-semibold text-slate-900 mb-2">Referral Links</h4>
                                <p class="text-xs text-slate-500">Generate a persistent referral link. When a client signs up using your link, TaxMaster records an affiliate referral.</p>
                            </div>

                            <div class="p-4 bg-slate-50 rounded-xl">
                                <h4 class="font-semibold text-slate-900 mb-2">Commission Rules</h4>
                                <p class="text-xs text-slate-500">Commissions configured per plan — one-off, recurring, or fixed amounts. Admins manage rules and payouts.</p>
                            </div>

                            <div class="p-4 bg-slate-50 rounded-xl">
                                <h4 class="font-semibold text-slate-900 mb-2">Payouts & Bank Details</h4>
                                <p class="text-xs text-slate-500">Track accrued commissions, request payouts, and save bank details for receiving transfers.</p>
                            </div>

                            <div class="p-4 bg-slate-50 rounded-xl">
                                <h4 class="font-semibold text-slate-900 mb-2">Visibility & Reporting</h4>
                                <p class="text-xs text-slate-500">View referrals, payouts, and which clients were created on your behalf.</p>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Right column - Benefits & CTA -->
                <aside class="space-y-6">
                    <!-- Benefits Card -->
                    <div class="bg-slate-50 rounded-2xl sm:rounded-3xl border border-slate-200 p-6 sm:p-8 reveal">
                        <h4 class="text-lg font-semibold text-slate-900 mb-4">Benefits for your practice</h4>

                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Faster client onboarding
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Reduced manual reconciliation
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                New revenue via referrals
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Centralised client tax dashboards
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Multi-entity management
                            </li>
                        </ul>

                        <div class="mt-6">
                            <Link
                                :href="route('contact')"
                                class="group w-full inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95"
                            >
                                Contact us to request an accountant account
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Security Card -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 reveal">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-semibold text-slate-900 mb-2">Security & Compliance</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            NDPA-aligned data handling, encrypted storage for sensitive identifiers, and role-based access to protect client information.
                        </p>

                        <!-- Compliance badges -->
                        <div class="flex flex-wrap gap-2 mt-4">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                NDPA Compliant
                            </span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                End-to-end encryption
                            </span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                SOC2 Type II
                            </span>
                        </div>
                    </div>

                    <!-- Stats Card -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl sm:rounded-3xl p-6 sm:p-8 reveal">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-2xl font-bold text-white">500+</div>
                                <div class="text-xs text-slate-400">Accounting firms</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">5,000+</div>
                                <div class="text-xs text-slate-400">Clients managed</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">₦2B+</div>
                                <div class="text-xs text-slate-400">Tax filed</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">98%</div>
                                <div class="text-xs text-slate-400">Time saved</div>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>

            <!-- Testimonials Section -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-12">
                <!-- Testimonial 1 -->
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 reveal">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold">
                            A
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-900">Adewale & Co.</h4>
                            <p class="text-xs text-slate-400">Accounting Firm, Lagos</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 italic">"TaxMaster reduced our month-end filing time from 3 days to 3 hours. The multi-client dashboard is a game-changer for our practice."</p>

                    <!-- Rating -->
                    <div class="flex items-center gap-1 mt-4">
                        <svg v-for="n in 5" :key="n" class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 reveal">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-slate-500 to-slate-600 flex items-center justify-center text-white font-bold">
                            P
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-900">Prime Tax Consultants</h4>
                            <p class="text-xs text-slate-400">Abuja</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 italic">"The affiliate program alone has created a new revenue stream for us. We're now recommending TaxMaster to all our clients."</p>

                    <!-- Rating -->
                    <div class="flex items-center gap-1 mt-4">
                        <svg v-for="n in 5" :key="n" class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>
            </section>

            <!-- Bottom CTA -->
            <section class="text-center bg-slate-50 rounded-3xl sm:rounded-4xl p-8 sm:p-12 lg:p-16 reveal">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                    Ready to transform your practice?
                </h2>
                <p class="text-base sm:text-lg text-slate-500 mb-8 max-w-2xl mx-auto">
                    Join hundreds of accounting firms already saving time and growing revenue with TaxMaster.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        :href="route('contact')"
                        class="group inline-flex items-center gap-2 rounded-full bg-slate-900 px-8 py-4 text-base font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95"
                    >
                        Contact us to request an accountant account
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>

                    <Link
                        href="/contact"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-8 py-4 text-base font-medium text-slate-600 transition-all hover:border-slate-300 hover:bg-slate-50"
                    >
                        Talk to sales
                    </Link>
                </div>

                <!-- Trust indicators -->
                <div class="flex flex-wrap items-center justify-center gap-6 mt-8 text-xs text-slate-400">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        No credit card required
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Enterprise-grade security
                    </span>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s cubic-bezier(0.22, 1, 0.36, 1), transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

.reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Custom border radius for larger screens */
@media (min-width: 640px) {
    .sm\:rounded-4xl {
        border-radius: 2rem;
    }
}

/* Smooth hover effects */
.group:hover .group-hover\:translate-x-1 {
    transform: translateX(0.25rem);
}
</style>
