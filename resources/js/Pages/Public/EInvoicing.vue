<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineOptions({ layout: PublicLayout });

// Key benefits - Mono style: minimal, monochrome with subtle accents
const benefits = ref([
    {
        title: 'FIRS-Compliant Generation',
        description: 'Generate UBL 2.1 standard invoices with digital signatures that meet Nigerian Revenue Service requirements. Export ready for manual submission.',
        icon: 'shield-check',
        code: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
    },
    {
        title: 'Export & Submit',
        description: 'Export UBL XML or JSON files for manual submission to FIRS portal. Direct API submission coming Q3 2026.',
        icon: 'zap',
        code: 'M13 10V3L4 14h7v7l9-11h-7z'
    },
    {
        title: 'Digital Signatures',
        description: 'ECDSA digital signatures ensure authenticity and non-repudiation for every invoice generated.',
        icon: 'file-signature',
        code: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
    },
    {
        title: 'Manual Export',
        description: 'Export UBL XML or JSON files for manual submission when API credentials are not available.',
        icon: 'download',
        code: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'
    },
    {
        title: 'TIN Validation',
        description: 'Validate buyer Tax Identification Numbers automatically before invoice creation.',
        icon: 'check-circle',
        code: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    {
        title: 'Professional PDFs',
        description: 'Generate branded PDF invoices with FIRS compliance badges and payment details.',
        icon: 'file-text',
        code: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'
    },
]);

// Features - Mono style: clean grid with subtle borders
const features = ref([
    { title: 'UBL 2.1 Standard', description: 'Universal Business Language format accepted by FIRS internationally.' },
    { title: 'Buyer Management', description: 'Store and manage buyer details with automatic TIN validation.' },
    { title: 'Item-Level Details', description: 'Multiple line items with quantities, prices, and tax categories.' },
    { title: 'Tax Calculations', description: 'Automatic VAT at 7.5%, zero-rated and exempt item support.' },
    { title: 'Status Tracking', description: 'Track pending, submitted, validated, or failed invoice statuses.' },
    { title: 'Batch Export', description: 'Export multiple invoices in UBL XML or JSON for bulk submission.' },
    { title: 'Payment Terms', description: 'Specify terms, due dates, methods, and bank details in NGN.' },
    { title: 'Document References', description: 'Link to purchase orders, delivery notes, and contracts.' },
    { title: 'Amendments', description: 'Create credit and debit notes with full audit trail compliance.' },
    { title: 'Search & Filters', description: 'Find invoices by number, buyer, date range, amount, or status.' },
    { title: 'Role-Based Access', description: 'Granular permissions for creating, viewing, editing, and submitting.' },
    { title: 'Audit Trail', description: 'Complete history with timestamps and user attribution.' },
]);

// How it works - Mono style: numbered steps with connecting line
const steps = ref([
    { number: '01', title: 'Create Invoice', description: 'Fill buyer details and line items. System validates TIN and calculates totals.' },
    { number: '02', title: 'Sign & Secure', description: 'Invoice is digitally signed with ECDSA for authenticity and security.' },
    { number: '03', title: 'Generate UBL', description: 'System creates UBL 2.1 XML/JSON with FIRS compliance metadata.' },
    { number: '04', title: 'Submit', description: 'Automatic submission to FIRS portal or export for manual upload.' },
]);

// Use cases - Mono style: minimal cards with icons
const useCases = ref([
    { title: 'SMEs & Startups', description: 'FIRS-compliant invoicing from day one without hiring accountants.' },
    { title: 'Accountants', description: 'Manage e-invoicing for multiple clients from one dashboard.' },
    { title: 'E-commerce', description: 'Auto-generate compliant invoices for every order with VAT handling.' },
    { title: 'Service Providers', description: 'Professional PDFs with tracking and payment acknowledgments.' },
]);

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
});
</script>

<template>
    <div class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-32 sm:pb-16 lg:pt-40 lg:pb-20">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <!-- Subtle grid background - Mono signature pattern -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#f1f5f9_1px,transparent_1px),linear-gradient(to_bottom,#f1f5f9_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] opacity-60"></div>

            <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">

                    <!-- Mono style: Large, bold, tight typography -->
                    <h1 class="reveal text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-slate-900 mb-6 leading-[1.1]">
                        Nigerian E-Invoicing<br>
                        <span class="text-slate-400">Made Simple</span>
                    </h1>

                    <p class="reveal text-lg sm:text-xl text-slate-600 leading-relaxed max-w-2xl mx-auto mb-10">
                        Generate FIRS-compliant e-invoices with digital signatures, automated submission, and TIN validation. UBL 2.1 standard with manual export options.
                    </p>

                    <!-- Mono style: Clean buttons, minimal styling -->
                    <div class="reveal flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                        <Link
                            :href="route('register')"
                            class="group inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-8 py-4 text-base font-semibold text-white transition-all hover:bg-slate-800 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            Start Creating Invoices
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>

                        <Link
                            :href="route('contact')"
                            class="group inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-8 py-4 text-base font-semibold text-slate-700 transition-all hover:bg-slate-50 hover:border-slate-300"
                        >
                            Talk to Sales
                        </Link>
                    </div>

                    <!-- Mono style: Clean trust indicators -->
                    <div class="reveal flex flex-wrap items-center justify-center gap-8 text-sm text-slate-500 border-t border-slate-100 pt-8">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            UBL 2.1 Standard
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            ECDSA Signatures
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            FIRS Portal Ready
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Benefits Section - Mono style: Clean grid with subtle borders -->
        <div class="relative py-24 bg-slate-50/50">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="reveal text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 mb-4">
                        Everything you need for compliance
                    </h2>
                    <p class="reveal text-lg text-slate-600 max-w-2xl mx-auto">
                        Built specifically for Nigerian businesses with FIRS requirements at its core
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-px bg-slate-200 rounded-2xl overflow-hidden border border-slate-200">
                    <div
                        v-for="(benefit, index) in benefits"
                        :key="index"
                        class="reveal bg-white p-8 hover:bg-slate-50 transition-colors group"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700 group-hover:bg-slate-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="benefit.code" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ benefit.title }}</h3>
                                <p class="text-slate-600 text-sm leading-relaxed">{{ benefit.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works - Mono style: Numbered steps with connecting line -->
        <div class="relative py-24 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="reveal text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 mb-4">
                        How it works
                    </h2>
                    <p class="reveal text-lg text-slate-600">
                        From creation to submission in four steps
                    </p>
                </div>

                <div class="relative">
                    <!-- Connecting line -->
                    <div class="hidden lg:block absolute top-12 left-[12.5%] right-[12.5%] h-px bg-slate-200"></div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                        <div
                            v-for="(step, index) in steps"
                            :key="index"
                            class="reveal relative text-center"
                        >
                            <div class="relative z-10 inline-flex items-center justify-center w-24 h-24 rounded-full bg-white border-2 border-slate-200 mb-6 shadow-sm">
                                <span class="text-3xl font-bold text-slate-900">{{ step.number }}</span>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-900 mb-3">{{ step.title }}</h3>
                            <p class="text-slate-600 leading-relaxed">{{ step.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Grid - Mono style: Dense, clean grid -->
        <div class="relative py-24 bg-slate-50/50">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="reveal text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 mb-4">
                        Comprehensive features
                    </h2>
                    <p class="reveal text-lg text-slate-600">
                        Everything for compliant, efficient electronic invoicing
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-px bg-slate-200 rounded-2xl overflow-hidden border border-slate-200">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="reveal bg-white p-6 hover:bg-slate-50 transition-colors"
                    >
                        <h3 class="text-base font-semibold text-slate-900 mb-2 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            {{ feature.title }}
                        </h3>
                        <p class="text-slate-600 text-sm leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Use Cases - Mono style: Minimal cards -->
        <div class="relative py-24 bg-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="reveal text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 mb-4">
                        Built for every business
                    </h2>
                    <p class="reveal text-lg text-slate-600">
                        Solutions tailored to your specific needs
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="(useCase, index) in useCases"
                        :key="index"
                        class="reveal group rounded-xl border border-slate-200 p-6 hover:border-slate-300 hover:shadow-sm transition-all bg-white"
                    >
                        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center mb-4 group-hover:bg-slate-200 transition-colors">
                            <span class="text-lg font-bold text-slate-700">{{ index + 1 }}</span>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ useCase.title }}</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">{{ useCase.description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section - Mono style: Dark, minimal -->
        <div class="relative py-24 bg-slate-900">
            <div class="mx-auto max-w-4xl px-6 lg:px-8 text-center">
                <h2 class="reveal text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6 tracking-tight">
                    Ready to get compliant?
                </h2>
                <p class="reveal text-lg text-slate-400 mb-10 max-w-2xl mx-auto">
                    Join thousands of Nigerian businesses using TaxMaster for FIRS-compliant e-invoicing
                </p>

                <div class="reveal flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        :href="route('register')"
                        class="group inline-flex items-center justify-center gap-2 rounded-lg bg-white px-8 py-4 text-base font-semibold text-slate-900 transition-all hover:bg-slate-100 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        Create free account
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>

                    <Link
                        :href="route('features')"
                        class="group inline-flex items-center justify-center gap-2 rounded-lg border border-slate-700 bg-transparent px-8 py-4 text-base font-semibold text-white transition-all hover:bg-slate-800"
                    >
                        View documentation
                    </Link>
                </div>

                <p class="reveal mt-8 text-sm text-slate-500">
                    No credit card required • 14-day free trial • Cancel anytime
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease-out, transform 0.5s ease-out;
}

.reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Mono-style subtle animations */
@media (prefers-reduced-motion: no-preference) {
    .reveal {
        transition-duration: 0.6s;
    }
}
</style>
