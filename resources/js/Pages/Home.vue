<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

// Intersection Observer for scroll-triggered animations
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
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
        );

        document.querySelectorAll('.reveal').forEach((el) => {
            observer.observe(el);
        });
    });
});

const features = [
    {
        label: 'PAYE Filing',
        title: 'Payroll taxes, on autopilot',
        desc: 'Auto-calculate employee taxes using up-to-date PITA bands. Multi-state SIRS routing built in. Generate filing-ready schedules and export for submission in seconds — not hours.',
        items: ['PITA-compliant tax bands', 'Multi-state SIRS routing', 'Monthly schedule generation', 'Bulk staff import via CSV'],
    },
    {
        label: 'VAT Returns',
        title: 'VAT returns, calculated instantly',
        desc: 'Track input and output VAT across all transactions. Auto-generate FIRS-compliant returns with full breakdowns and export for submission or payment.',
        items: ['Input/output VAT tracking', 'FIRS-compliant return generation', 'Export for manual submission', 'Transaction categorisation'],
    },
    {
        label: 'WHT Remittance',
        title: 'Withholding tax, sorted',
        desc: 'Handle WHT on contracts, dividends, rents and more. Smart routing determines whether to remit to FIRS or State IRS based on beneficiary type.',
        items: ['Smart FIRS/SIRS routing', 'All WHT categories covered', 'Beneficiary type detection', 'Auto-rate application'],
    },
];

const stats = [
    { value: '1,200+', label: 'Businesses' },
    { value: '₦4.2B', label: 'Taxes filed' },
    { value: '37', label: 'States covered' },
    { value: '99.9%', label: 'Uptime SLA' },
];

const capabilities = [
    { icon: 'robot', title: 'AI Tax Automation', desc: 'Click once, AI does the rest. Automatically calculates VAT, PAYE, WHT & CIT from your transactions. No accountant needed for basic compliance.' },
    { icon: 'shield', title: 'NDPA Compliant', desc: 'Full data protection compliance with encrypted TIN storage and audit trails.' },
    { icon: 'clock', title: 'Real-time Alerts', desc: 'Never miss a deadline. Get notified before due dates with penalty calculators.' },
    { icon: 'bank', title: 'Bank & E-commerce Integrations', desc: 'Connect your bank via Mono, sync with QuickBooks, Zoho Books, Sage, or Xero, and import orders from Shopify. Transactions auto-categorised for tax purposes.' },
    { icon: 'chart', title: 'Smart Reports', desc: 'Export PDF/CSV reports. Annual returns prep with all supporting schedules.' },
    { icon: 'users', title: 'Multi-Entity', desc: 'Manage multiple businesses from one dashboard with role-based access.' },
];

const capabilityIcons = {
    robot: 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
    shield: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    clock: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    bank: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
    chart: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    users: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
};

const activeFeature = ref(0);
</script>

<template>
    <!-- Hero Section — Split layout like Mono.co -->
    <section class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-28 sm:pb-16 lg:pt-36 lg:pb-24">
        <!-- Background grid pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="grid items-center gap-8 sm:gap-12 lg:grid-cols-2 lg:gap-16">
                <!-- Left: Text content -->
                <div class="max-w-xl">

                    <!-- Headline -->
                    <h1 class="reveal reveal-up text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl lg:text-[3.5rem] lg:leading-[1.1]">
                        Tax compliance that works for modern Nigerian businesses
                    </h1>

                    <!-- Sub-headline -->
                    <p class="reveal reveal-up mt-4 text-base leading-relaxed text-gray-500 sm:mt-6 sm:text-lg lg:text-xl">
                        <strong class="text-gray-900">AI-powered tax automation at your fingertips.</strong> Click once and watch AI calculate your VAT, PAYE, WHT & CIT from your transactions. No accountant needed for basic compliance. Create FIRS-compliant e-invoices, connect your bank, and get filing-ready returns in seconds—not weeks.
                    </p>

                    <!-- CTA Buttons — Black -->
                    <div class="reveal reveal-up mt-8 flex flex-col gap-3 sm:mt-10 sm:flex-row sm:gap-4">
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-gray-900/20 transition-all sm:px-8 sm:py-4 sm:text-base hover:bg-black hover:shadow-xl hover:shadow-gray-900/30 active:scale-[0.98]"
                        >
                            Get started — it's free
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                        <a
                            href="#product"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-6 py-3.5 text-sm font-semibold text-gray-700 transition-all sm:px-8 sm:py-4 sm:text-base hover:border-gray-300 hover:bg-gray-50"
                        >
                            See how it works
                        </a>
                        <Link
                            :href="route('tax-calculator')"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-blue-100 bg-white px-4 py-2 text-sm font-semibold text-blue-600 transition-all hover:bg-blue-50"
                        >
                            Try the Tax Calculator
                        </Link>
                    </div>

                    <!-- Trust line -->
                    <p class="reveal reveal-up mt-8 text-sm text-gray-400">
                        Free for small businesses · No credit card · 2 min setup
                    </p>
                </div>

                <!-- Right: Device mockups -->
                <div class="reveal reveal-right relative flex items-center justify-center lg:justify-end">
                    <!-- Desktop mockup -->
                    <div class="relative z-10 w-full max-w-[340px] sm:max-w-[520px] animate-float">
                        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-950 shadow-2xl">
                            <!-- Window chrome -->
                            <div class="flex items-center gap-1.5 border-b border-gray-800 px-4 py-2.5">
                                <div class="h-2.5 w-2.5 rounded-full bg-red-500/70"></div>
                                <div class="h-2.5 w-2.5 rounded-full bg-yellow-500/70"></div>
                                <div class="h-2.5 w-2.5 rounded-full bg-green-500/70"></div>
                                <div class="ml-3 flex-1 rounded bg-gray-800 px-3 py-0.5 text-[10px] text-gray-500">
                                    app.taxmaster.ng/dashboard
                                </div>
                            </div>
                            <!-- Dashboard content -->
                            <div class="bg-gray-50 p-3 sm:p-4">
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3">
                                    <div class="rounded-xl bg-white p-3 shadow-sm border border-gray-100">
                                        <p class="text-[10px] text-gray-400">PAYE This Month</p>
                                        <p class="mt-0.5 text-lg font-bold text-gray-900">₦847K</p>
                                        <p class="text-[10px] text-green-600">↓ 3.2%</p>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 shadow-sm border border-gray-100">
                                        <p class="text-[10px] text-gray-400">VAT Collected</p>
                                        <p class="mt-0.5 text-lg font-bold text-gray-900">₦1.2M</p>
                                        <p class="text-[10px] text-green-600">↑ 12%</p>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 shadow-sm border border-gray-100">
                                        <p class="text-[10px] text-gray-400">Compliance</p>
                                        <p class="mt-0.5 text-lg font-bold text-blue-600">98%</p>
                                        <div class="mt-1 h-1 rounded-full bg-gray-100 hidden sm:block">
                                            <div class="h-full w-[98%] rounded-full bg-blue-500"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 rounded-xl bg-white p-3 shadow-sm border border-gray-100">
                                    <p class="text-[10px] font-medium text-gray-500 mb-2">Tax Obligations</p>
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between rounded-lg bg-green-50 px-2.5 py-1.5">
                                            <span class="text-[10px] text-gray-600">PAYE (Lagos SIRS)</span>
                                            <span class="rounded-full bg-green-100 px-1.5 py-0.5 text-[9px] font-medium text-green-700">Filed</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-lg bg-green-50 px-2.5 py-1.5">
                                            <span class="text-[10px] text-gray-600">VAT Return (FIRS)</span>
                                            <span class="rounded-full bg-green-100 px-1.5 py-0.5 text-[9px] font-medium text-green-700">Paid</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-lg bg-yellow-50 px-2.5 py-1.5">
                                            <span class="text-[10px] text-gray-600">WHT Remittance</span>
                                            <span class="rounded-full bg-yellow-100 px-1.5 py-0.5 text-[9px] font-medium text-yellow-700">Due Oct 21</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile mockup — overlapping bottom-right -->
                    <div class="absolute -bottom-6 -right-2 z-20 w-[140px] sm:w-[180px] lg:w-[200px] lg:-right-4 hidden sm:block animate-float-delayed">
                        <div class="overflow-hidden rounded-[24px] border-[3px] border-gray-900 bg-gray-900 shadow-2xl">
                            <!-- Phone notch -->
                            <div class="mx-auto h-5 w-20 rounded-b-xl bg-gray-900"></div>
                            <!-- Phone screen -->
                            <div class="bg-white px-3 pb-4 pt-2">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-[9px] font-bold text-gray-900">TaxMaster</p>
                                    <div class="h-4 w-4 rounded-full bg-gray-100"></div>
                                </div>
                                <div class="rounded-xl bg-gray-900 p-3 text-center mb-3">
                                    <p class="text-[8px] text-gray-400">Total Tax Due</p>
                                    <p class="text-base font-bold text-white mt-0.5">₦2.28M</p>
                                    <p class="text-[8px] text-green-400 mt-0.5">All filings up to date</p>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2">
                                        <div class="h-6 w-6 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <svg class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-medium text-gray-900">PAYE</p>
                                            <p class="text-[7px] text-gray-400">₦847,320</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2">
                                        <div class="h-6 w-6 rounded-lg bg-green-100 flex items-center justify-center">
                                            <svg class="h-3 w-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-medium text-gray-900">VAT</p>
                                            <p class="text-[7px] text-gray-400">₦1,200,000</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2">
                                        <div class="h-6 w-6 rounded-lg bg-amber-100 flex items-center justify-center">
                                            <svg class="h-3 w-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-medium text-gray-900">WHT</p>
                                            <p class="text-[7px] text-gray-400">₦234,500</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Background blur accents -->
                    <div class="absolute -top-12 -right-12 h-72 w-72 rounded-full bg-blue-200/40 blur-3xl"></div>
                    <div class="absolute -bottom-16 -left-8 h-56 w-56 rounded-full bg-indigo-100/50 blur-3xl"></div>
                    <div class="absolute top-1/2 left-1/3 h-40 w-40 rounded-full bg-cyan-100/30 blur-3xl"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trusted By — Marquee scroll -->
    <section class="border-y border-gray-100 bg-gray-50/50 py-10 overflow-hidden">
        <div class="reveal reveal-up">
            <p class="text-center text-[11px] font-semibold uppercase tracking-[0.25em] text-gray-400 mb-8">
                Trusted by leading businesses across Nigeria
            </p>
            <div class="relative">
                <!-- Fade edges -->
                <div class="pointer-events-none absolute left-0 top-0 z-10 h-full w-24 bg-gradient-to-r from-gray-50/80 to-transparent"></div>
                <div class="pointer-events-none absolute right-0 top-0 z-10 h-full w-24 bg-gradient-to-l from-gray-50/80 to-transparent"></div>
                <div class="flex animate-marquee gap-16 whitespace-nowrap">
                    <span v-for="n in 2" :key="n" class="flex items-center gap-16">
                        <span v-for="(logo, idx) in [
                            { name: 'Paystack', letters: 'Ps' },
                            { name: 'Flutterwave', letters: 'Fw' },
                            { name: 'Kuda Bank', letters: 'Kb' },
                            { name: 'PiggyVest', letters: 'Pv' },
                            { name: 'Cowrywise', letters: 'Cw' },
                            { name: 'Carbon', letters: 'Cb' },
                            { name: 'Mono', letters: 'Mn' },
                            { name: 'Risevest', letters: 'Rv' },
                            { name: 'TechCabal', letters: 'Tc' },
                            { name: 'Andela', letters: 'An' },
                        ]" :key="logo.name + n"
                            class="flex items-center gap-2 select-none">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-200/70 text-[10px] font-bold text-gray-500">{{ logo.letters }}</span>
                            <span class="text-[15px] font-semibold tracking-tight text-gray-300">{{ logo.name }}</span>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="py-14 sm:py-20 lg:py-24 bg-gray-950 relative overflow-hidden">
        <!-- Subtle grid bg -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up grid grid-cols-2 gap-8 lg:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="text-center">
                    <div class="text-3xl font-bold text-white sm:text-4xl lg:text-5xl tracking-tight">{{ stat.value }}</div>
                    <div class="mt-2 text-sm text-gray-500 font-medium">{{ stat.label }}</div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works — Alternating layout with phone mockups -->
    <section id="product" class="py-16 sm:py-20 lg:py-28 bg-gray-50 relative overflow-hidden">
        <!-- Decorative circles -->
        <div class="absolute top-20 left-10 h-72 w-72 rounded-full bg-blue-50 blur-3xl opacity-60"></div>
        <div class="absolute bottom-20 right-10 h-56 w-56 rounded-full bg-indigo-50 blur-3xl opacity-50"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up mx-auto max-w-2xl text-center mb-12 sm:mb-20">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">How it works</p>
                <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl lg:text-5xl">
                    Three steps to compliance
                </h2>
                <p class="mt-4 text-lg text-gray-500">
                    From sign-up to filing — TaxMaster automates the entire process.
                </p>
            </div>

            <!-- Step 1: Text left, Phone right -->
            <div class="reveal reveal-up grid items-center gap-8 sm:gap-12 lg:grid-cols-2 lg:gap-20 mb-16 lg:mb-24">
                <div>
                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-900 text-white text-sm font-bold mb-6">01</span>
                    <h3 class="text-xl font-bold text-gray-900 sm:text-2xl lg:text-3xl">Create your account</h3>
                    <p class="mt-3 text-base text-gray-500 leading-relaxed sm:mt-4 sm:text-lg">
                        Sign up for free in under 2 minutes. Add your business details, staff records, and TIN. We'll handle the rest.
                    </p>
                    <Link
                        :href="route('register')"
                        class="mt-6 inline-flex items-center gap-2 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition-all sm:mt-8 sm:px-6 sm:py-3 hover:bg-black active:scale-[0.98]"
                    >
                        Create free account
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </Link>
                </div>
                <div class="flex justify-center lg:justify-end">
                    <div class="relative">
                        <div class="w-[220px] sm:w-[260px] animate-float overflow-hidden rounded-[32px] border-[4px] border-gray-900 bg-gray-900 shadow-2xl">
                            <div class="mx-auto h-5 w-20 sm:h-6 sm:w-24 rounded-b-xl bg-gray-900"></div>
                            <div class="bg-white px-4 pb-5 pt-2 sm:px-5 sm:pb-6 sm:pt-3">
                                <p class="text-xs font-bold text-gray-900 mb-4">Create Account</p>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-[10px] text-gray-500 mb-1">Business Name</p>
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-[11px] text-gray-700">TechVentures Ltd</div>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-500 mb-1">Email</p>
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-[11px] text-gray-700">admin@techventures.ng</div>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-500 mb-1">TIN</p>
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-[11px] text-gray-400">Enter your TIN</div>
                                    </div>
                                    <div class="rounded-lg bg-gray-900 py-2.5 text-center text-[11px] font-semibold text-white">
                                        Get Started — Free
                                    </div>
                                </div>
                                <p class="mt-3 text-center text-[9px] text-gray-400">No credit card required</p>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 -right-4 -z-10 h-full w-full rounded-[32px] bg-blue-100/60"></div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Phone left, Text right -->
            <div class="reveal reveal-up grid items-center gap-8 sm:gap-12 lg:grid-cols-2 lg:gap-20 mb-16 lg:mb-24">
                <div class="flex justify-center lg:justify-start order-2 lg:order-1">
                    <div class="relative">
                        <div class="w-[220px] sm:w-[260px] animate-float-delayed overflow-hidden rounded-[32px] border-[4px] border-gray-900 bg-gray-900 shadow-2xl">
                            <div class="mx-auto h-5 w-20 sm:h-6 sm:w-24 rounded-b-xl bg-gray-900"></div>
                            <div class="bg-white px-4 pb-5 pt-2 sm:px-5 sm:pb-6 sm:pt-3">
                                <p class="text-xs font-bold text-gray-900 mb-1">Connect Bank</p>
                                <p class="text-[9px] text-gray-400 mb-4">Link via Mono for automatic import</p>
                                <div class="space-y-2.5">
                                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 p-3">
                                        <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center text-[10px] font-bold text-green-700">GT</div>
                                        <div class="flex-1">
                                            <p class="text-[11px] font-medium text-gray-900">GTBank</p>
                                            <p class="text-[9px] text-gray-400">****4521</p>
                                        </div>
                                        <div class="rounded-full bg-green-100 px-2 py-0.5 text-[8px] font-medium text-green-700">Connected</div>
                                    </div>
                                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 p-3">
                                        <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center text-[10px] font-bold text-blue-700">AC</div>
                                        <div class="flex-1">
                                            <p class="text-[11px] font-medium text-gray-900">Access Bank</p>
                                            <p class="text-[9px] text-gray-400">****7832</p>
                                        </div>
                                        <div class="rounded-full bg-green-100 px-2 py-0.5 text-[8px] font-medium text-green-700">Connected</div>
                                    </div>
                                    <div class="flex items-center gap-3 rounded-xl border border-dashed border-gray-300 p-3">
                                        <div class="h-8 w-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                        </div>
                                        <p class="text-[11px] text-gray-400">Add another bank</p>
                                    </div>
                                </div>
                                <p class="mt-3 text-center text-[9px] text-gray-400">12 transactions imported today</p>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 -left-4 -z-10 h-full w-full rounded-[32px] bg-gray-200/60"></div>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-900 text-white text-sm font-bold mb-6">02</span>
                    <h3 class="text-xl font-bold text-gray-900 sm:text-2xl lg:text-3xl">Connect & import</h3>
                    <p class="mt-3 text-base text-gray-500 leading-relaxed sm:mt-4 sm:text-lg">
                        Link your bank accounts via Mono in one click. Transactions are automatically imported and categorised for tax purposes. Upload staff data via CSV for instant PAYE setup.
                    </p>
                    <ul class="mt-6 space-y-3">
                        <li v-for="item in ['Instant bank connection via Mono', 'Auto-categorised transactions', 'CSV staff import']" :key="item" class="flex items-center gap-2.5 text-sm text-gray-600">
                            <svg class="h-4 w-4 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            {{ item }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Step 3: Text left, Phone right -->
            <div class="reveal reveal-up grid items-center gap-8 sm:gap-12 lg:grid-cols-2 lg:gap-20">
                <div>
                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-900 text-white text-sm font-bold mb-6">03</span>
                    <h3 class="text-xl font-bold text-gray-900 sm:text-2xl lg:text-3xl">Auto-calculate & file</h3>
                    <p class="mt-3 text-base text-gray-500 leading-relaxed sm:mt-4 sm:text-lg">
                        TaxMaster computes your taxes using current Nigerian tax law, generates compliant returns, and prepares payment instructions you can export for remittance.
                    </p>
                    <ul class="mt-6 space-y-3">
                        <li v-for="item in ['PITA-compliant calculations', 'Auto-routed to FIRS or State IRS', 'Export payment instructions for remittance']" :key="item" class="flex items-center gap-2.5 text-sm text-gray-600">
                            <svg class="h-4 w-4 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            {{ item }}
                        </li>
                    </ul>
                </div>
                <div class="flex justify-center lg:justify-end">
                    <div class="relative">
                        <div class="w-[220px] sm:w-[260px] animate-float-slow overflow-hidden rounded-[32px] border-[4px] border-gray-900 bg-gray-900 shadow-2xl">
                            <div class="mx-auto h-5 w-20 sm:h-6 sm:w-24 rounded-b-xl bg-gray-900"></div>
                            <div class="bg-white px-4 pb-5 pt-2 sm:px-5 sm:pb-6 sm:pt-3">
                                <p class="text-xs font-bold text-gray-900 mb-1">PAYE Return</p>
                                <p class="text-[9px] text-gray-400 mb-4">October 2025 · Lagos SIRS</p>
                                <div class="rounded-xl bg-green-50 border border-green-200 p-3 text-center mb-3">
                                    <svg class="mx-auto h-6 w-6 text-green-500 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-[10px] font-semibold text-green-700">Filed Successfully</p>
                                    <p class="text-[8px] text-green-600 mt-0.5">Payment reference: 310045672819</p>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-[10px]">
                                        <span class="text-gray-500">Staff Count</span>
                                        <span class="font-medium text-gray-900">47</span>
                                    </div>
                                    <div class="flex justify-between text-[10px]">
                                        <span class="text-gray-500">Gross Pay</span>
                                        <span class="font-medium text-gray-900">₦12,450,000</span>
                                    </div>
                                    <div class="flex justify-between text-[10px]">
                                        <span class="text-gray-500">Total PAYE</span>
                                        <span class="font-medium text-gray-900">₦847,320</span>
                                    </div>
                                    <div class="h-px bg-gray-100 my-1"></div>
                                    <div class="flex justify-between text-[10px]">
                                        <span class="text-gray-500">Status</span>
                                        <span class="font-semibold text-green-600">Paid ✓</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 -right-4 -z-10 h-full w-full rounded-[32px] bg-green-100/50"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Showcase — Tabbed with phone mockup -->
    <section id="features" class="py-16 sm:py-20 lg:py-28 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal reveal-up mx-auto max-w-2xl text-center mb-10 sm:mb-16">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Features</p>
                <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl lg:text-5xl">
                    Everything you need
                </h2>
                <p class="mt-3 text-base text-gray-500 sm:mt-4 sm:text-lg">
                    Built specifically for Nigerian tax law — FIRS, State IRS, and everything in between.
                </p>
            </div>

            <div class="reveal reveal-up grid items-start gap-8 sm:gap-12 lg:grid-cols-2 lg:gap-20">
                <!-- Left: Feature tabs -->
                <div>
                    <div class="space-y-3 sm:space-y-4">
                        <button
                            v-for="(feature, i) in features"
                            :key="feature.label"
                            @click="activeFeature = i"
                            class="w-full text-left rounded-2xl border p-4 sm:p-6 transition-all duration-300"
                            :class="activeFeature === i ? 'border-gray-900 bg-gray-50 shadow-sm' : 'border-gray-100 bg-white hover:border-gray-200'"
                        >
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-xs font-semibold uppercase tracking-wider" :class="activeFeature === i ? 'text-blue-600' : 'text-gray-400'">{{ feature.label }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ feature.title }}</h3>
                            <transition
                                enter-active-class="transition-all duration-300 ease-out"
                                enter-from-class="opacity-0 max-h-0"
                                enter-to-class="opacity-100 max-h-96"
                                leave-active-class="transition-all duration-200 ease-in"
                                leave-from-class="opacity-100 max-h-96"
                                leave-to-class="opacity-0 max-h-0"
                            >
                                <div v-show="activeFeature === i" class="overflow-hidden">
                                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ feature.desc }}</p>
                                    <ul class="mt-4 space-y-2">
                                        <li v-for="item in feature.items" :key="item" class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg class="h-3.5 w-3.5 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            {{ item }}
                                        </li>
                                    </ul>
                                </div>
                            </transition>
                        </button>
                    </div>
                </div>

                <!-- Right: Phone mockup -->
                <div class="flex justify-center lg:justify-end lg:sticky lg:top-32">
                    <div class="relative">
                        <!-- Background shape -->
                        <div class="absolute -inset-4 rounded-[40px] bg-gradient-to-b from-gray-100 to-gray-50 -z-10"></div>
                        <!-- PAYE Phone -->
                        <transition
                            enter-active-class="transition-all duration-500 ease-out"
                            enter-from-class="opacity-0 translate-y-4"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-300 ease-in absolute inset-0"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div v-if="activeFeature === 0" class="w-[240px] sm:w-[280px] overflow-hidden rounded-[32px] border-[4px] border-gray-900 bg-gray-900 shadow-2xl">
                                <div class="mx-auto h-5 w-20 sm:h-6 sm:w-24 rounded-b-xl bg-gray-900"></div>
                                <div class="bg-white px-5 pb-6 pt-3">
                                    <div class="flex items-center justify-between mb-4">
                                        <p class="text-xs font-bold text-gray-900">PAYE Schedule</p>
                                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[9px] font-medium text-blue-700">Oct 2025</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div v-for="emp in [{name:'Adebayo O.', amt:'₦45,200', state:'Lagos'}, {name:'Chioma N.', amt:'₦38,100', state:'Lagos'}, {name:'Ibrahim M.', amt:'₦52,800', state:'Abuja'}, {name:'Funke A.', amt:'₦29,400', state:'Lagos'}]" :key="emp.name"
                                            class="flex items-center justify-between rounded-lg bg-gray-50 p-2.5">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center text-[8px] font-bold text-gray-600">{{ emp.name.split(' ').map(n => n[0]).join('') }}</div>
                                                <div>
                                                    <p class="text-[10px] font-medium text-gray-900">{{ emp.name }}</p>
                                                    <p class="text-[8px] text-gray-400">{{ emp.state }} SIRS</p>
                                                </div>
                                            </div>
                                            <p class="text-[10px] font-semibold text-gray-900">{{ emp.amt }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 rounded-lg bg-gray-900 py-2.5 text-center text-[11px] font-semibold text-white">
                                        Generate Return →
                                    </div>
                                </div>
                            </div>
                        </transition>

                        <!-- VAT Phone -->
                        <transition
                            enter-active-class="transition-all duration-500 ease-out"
                            enter-from-class="opacity-0 translate-y-4"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-300 ease-in absolute inset-0"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div v-if="activeFeature === 1" class="w-[240px] sm:w-[280px] overflow-hidden rounded-[32px] border-[4px] border-gray-900 bg-gray-900 shadow-2xl">
                                <div class="mx-auto h-5 w-20 sm:h-6 sm:w-24 rounded-b-xl bg-gray-900"></div>
                                <div class="bg-white px-5 pb-6 pt-3">
                                    <div class="flex items-center justify-between mb-4">
                                        <p class="text-xs font-bold text-gray-900">VAT Return</p>
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-[9px] font-medium text-green-700">Ready</span>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 p-3 mb-3">
                                        <div class="flex justify-between text-[10px] mb-2">
                                            <span class="text-gray-500">Output VAT</span>
                                            <span class="font-medium text-gray-900">₦1,425,000</span>
                                        </div>
                                        <div class="flex justify-between text-[10px] mb-2">
                                            <span class="text-gray-500">Input VAT</span>
                                            <span class="font-medium text-gray-900">₦225,000</span>
                                        </div>
                                        <div class="h-px bg-gray-200 my-2"></div>
                                        <div class="flex justify-between text-[10px]">
                                            <span class="font-medium text-gray-700">Net Payable</span>
                                            <span class="font-bold text-gray-900">₦1,200,000</span>
                                        </div>
                                    </div>
                                    <div class="space-y-1.5 mb-4">
                                        <div class="flex items-center gap-2 text-[10px] text-gray-500">
                                            <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            42 transactions categorised
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] text-gray-500">
                                            <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            All invoices reconciled
                                        </div>
                                    </div>
                                    <div class="rounded-lg bg-gray-900 py-2.5 text-center text-[11px] font-semibold text-white">
                                        Generate payment instructions →
                                    </div>
                                </div>
                            </div>
                        </transition>

                        <!-- WHT Phone -->
                        <transition
                            enter-active-class="transition-all duration-500 ease-out"
                            enter-from-class="opacity-0 translate-y-4"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-300 ease-in absolute inset-0"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div v-if="activeFeature === 2" class="w-[240px] sm:w-[280px] overflow-hidden rounded-[32px] border-[4px] border-gray-900 bg-gray-900 shadow-2xl">
                                <div class="mx-auto h-5 w-20 sm:h-6 sm:w-24 rounded-b-xl bg-gray-900"></div>
                                <div class="bg-white px-5 pb-6 pt-3">
                                    <div class="flex items-center justify-between mb-4">
                                        <p class="text-xs font-bold text-gray-900">WHT Summary</p>
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-medium text-amber-700">3 pending</span>
                                    </div>
                                    <div class="space-y-2 mb-4">
                                        <div v-for="wht in [{vendor:'Adeola & Partners', type:'Consultancy', rate:'5%', amt:'₦62,500', route:'FIRS'}, {vendor:'BuildRight Ltd', type:'Construction', rate:'5%', amt:'₦75,000', route:'FIRS'}, {vendor:'John Doe', type:'Rent', rate:'10%', amt:'₦34,500', route:'Lagos SIRS'}]" :key="wht.vendor"
                                            class="rounded-lg bg-gray-50 p-2.5">
                                            <div class="flex items-center justify-between mb-1">
                                                <p class="text-[10px] font-medium text-gray-900">{{ wht.vendor }}</p>
                                                <p class="text-[10px] font-bold text-gray-900">{{ wht.amt }}</p>
                                            </div>
                                            <div class="flex items-center gap-2 text-[8px] text-gray-400">
                                                <span>{{ wht.type }}</span>
                                                <span>·</span>
                                                <span>{{ wht.rate }}</span>
                                                <span>→</span>
                                                <span class="font-medium text-blue-600">{{ wht.route }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg bg-gray-900 py-2.5 text-center text-[11px] font-semibold text-white">
                                        Export remittance files →
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Capabilities Grid -->
    <section class="py-16 sm:py-20 lg:py-28 bg-gray-50 relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] opacity-50"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up mx-auto max-w-2xl text-center mb-10 sm:mb-16">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Capabilities</p>
                <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl lg:text-4xl">
                    Built for scale & security
                </h2>
            </div>

            <div class="grid gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(cap, i) in capabilities"
                    :key="cap.title"
                    class="reveal reveal-up group rounded-2xl border border-gray-100 bg-white p-6 sm:p-8 transition-all duration-300 hover:border-gray-200 hover:shadow-lg"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-600 transition-colors group-hover:bg-gray-900 group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="capabilityIcons[cap.icon]" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-gray-900">{{ cap.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ cap.desc }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why TaxMaster — Desktop + notification mockup -->
    <section class="py-16 sm:py-20 lg:py-28 bg-white relative overflow-hidden">
        <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-blue-50/50 blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up grid items-center gap-8 sm:gap-12 lg:grid-cols-2 lg:gap-20">
                <!-- Left: Desktop + notification overlay -->
                <div class="relative flex items-end justify-center">
                    <div class="w-full max-w-[320px] sm:max-w-[420px] animate-float">
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-950 shadow-xl">
                            <div class="flex items-center gap-1.5 border-b border-gray-800 px-3 py-2">
                                <div class="h-2 w-2 rounded-full bg-red-500/70"></div>
                                <div class="h-2 w-2 rounded-full bg-yellow-500/70"></div>
                                <div class="h-2 w-2 rounded-full bg-green-500/70"></div>
                                <div class="ml-2 flex-1 rounded bg-gray-800 px-2 py-0.5 text-[8px] text-gray-500">app.taxmaster.ng/compliance</div>
                            </div>
                            <div class="bg-white p-3">
                                <p class="text-[10px] font-semibold text-gray-900 mb-2">Compliance Dashboard</p>
                                <div class="space-y-1.5">
                                    <div v-for="item in [
                                        { label: 'PAYE — Lagos', status: 'Filed & Paid', ok: true },
                                        { label: 'VAT — FIRS', status: 'Filed & Paid', ok: true },
                                        { label: 'WHT — FIRS', status: 'Filed & Paid', ok: true },
                                        { label: 'CIT — FIRS', status: 'Due Dec 31', ok: false },
                                    ]" :key="item.label" class="flex items-center justify-between rounded-lg px-2 py-1.5"
                                        :class="item.ok ? 'bg-green-50' : 'bg-amber-50'">
                                        <span class="text-[9px] text-gray-600">{{ item.label }}</span>
                                        <span class="flex items-center gap-1 text-[8px] font-medium" :class="item.ok ? 'text-green-600' : 'text-amber-600'">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path v-if="item.ok" stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ item.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Notification overlay -->
                    <div class="absolute -bottom-4 right-0 sm:right-4 z-10 animate-float-delayed">
                        <div class="w-[160px] sm:w-[180px] rounded-2xl border border-gray-200 bg-white p-2.5 sm:p-3 shadow-xl">
                            <div class="flex items-start gap-2">
                                <div class="h-6 w-6 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="h-3.5 w-3.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div>
                                    <p class="text-[9px] font-semibold text-gray-900">PAYE Filed!</p>
                                    <p class="text-[8px] text-gray-500 mt-0.5">Lagos SIRS · ₦847K payment recorded</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Content -->
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Why TaxMaster</p>
                    <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl lg:text-4xl">
                        Built for Nigeria, from the ground up
                    </h2>
                    <p class="mt-3 text-base leading-relaxed text-gray-500 sm:mt-4 sm:text-lg">
                        Most tax software is built for US or UK markets. TaxMaster understands PITA tax bands, FIRS regulations, State IRS differences, and local government filing/payment processes — and generates FIRS-compliant returns and payment instructions your team needs to complete filing and remittance.
                    </p>
                    <ul class="mt-6 space-y-3 sm:mt-8 sm:space-y-4">
                        <li v-for="item in [
                            'Multi-state PAYE — routes to the correct SIRS automatically',
                            'FIRS-compliant return generation with export capabilities',
                            'NDPA-compliant data protection and TIN encryption',
                            'Real-time compliance monitoring with penalty calculators',
                            'Direct FIRS API integration (coming Q3 2026)',
                        ]" :key="item" class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600">{{ item }}</span>
                        </li>
                    </ul>
                    <Link
                        :href="route('register')"
                        class="mt-8 inline-flex items-center gap-2 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition-all sm:mt-10 sm:px-6 sm:py-3 hover:bg-black active:scale-[0.98]"
                    >
                        Start for free
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </Link>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial — Dark -->
    <section class="bg-gray-950 py-16 sm:py-20 lg:py-28 relative overflow-hidden">
        <!-- Decorative gradient -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 h-px w-1/2 bg-gradient-to-r from-transparent via-gray-700 to-transparent"></div>
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <div class="reveal reveal-up">
                <svg class="mx-auto h-8 w-8 text-gray-700" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                </svg>
                <blockquote class="mt-6 sm:mt-8">
                    <p class="text-lg font-medium leading-relaxed text-white sm:text-2xl lg:text-3xl">
                        "TaxMaster saved us over 40 hours per month on tax computations. Filing PAYE for 150 staff across 3 states used to be a nightmare — now it takes minutes."
                    </p>
                </blockquote>
                <div class="mt-10 flex items-center justify-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-gray-800 flex items-center justify-center text-sm font-bold text-white">AJ</div>
                    <div class="text-left">
                        <p class="text-base font-semibold text-white">Adebayo Johnson</p>
                        <p class="text-sm text-gray-500">CFO, TechVentures Nigeria Ltd</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA — Clean, minimal -->
    <section class="py-16 sm:py-24 lg:py-32 bg-gradient-to-b from-white via-blue-50/30 to-white relative overflow-hidden">
        <!-- Decorative blurs -->
        <div class="absolute top-0 left-1/4 h-64 w-64 rounded-full bg-blue-100/40 blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 h-48 w-48 rounded-full bg-indigo-100/30 blur-3xl"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up mx-auto max-w-3xl text-center">
                <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl lg:text-5xl">
                    Ready to simplify your<br class="hidden sm:inline" /> tax compliance?
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-base text-gray-500 sm:mt-6 sm:text-lg">
                    Join 1,200+ Nigerian businesses who trust TaxMaster. Start for free — upgrade as you grow.
                </p>
                <div class="mt-8 flex flex-col items-center gap-3 sm:mt-10 sm:flex-row sm:justify-center sm:gap-4">
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-gray-900/20 transition-all sm:px-8 sm:py-4 sm:text-base hover:bg-black hover:shadow-xl hover:shadow-gray-900/25 active:scale-[0.98]"
                    >
                        Get started — it's free
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    <a
                        href="/contact"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-6 py-3.5 text-sm font-semibold text-gray-700 transition-all sm:px-8 sm:py-4 sm:text-base hover:border-gray-300 hover:bg-gray-50"
                    >
                        Talk to sales
                    </a>
                </div>
                <p class="mt-8 text-sm text-gray-400">
                    Free forever for small businesses · No credit card required · Cancel anytime
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Scroll-triggered reveal animations */
.reveal {
    opacity: 0;
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.reveal.reveal-up {
    transform: translateY(30px);
}
.reveal.reveal-left {
    transform: translateX(-40px);
}
.reveal.reveal-right {
    transform: translateX(40px);
}
.reveal.is-visible {
    opacity: 1;
    transform: translateY(0) translateX(0);
}
</style>
