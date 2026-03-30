<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const searchQuery = ref('');
const activeSection = ref('');

const quickLinks = [
    { id: 'getting-started', title: 'Getting Started', description: 'New to TaxMaster?' },
    { id: 'tax-modules', title: 'Tax Modules', description: 'VAT, PAYE, CIT, WHT' },
    { id: 'subscription-plans', title: 'Pricing', description: 'Plans & features' },
];

const sections = [
    { id: 'getting-started', title: 'Getting Started' },
    { id: 'tax-modules', title: 'Tax Modules' },
    { id: 'financial-management', title: 'Financial Management' },
    { id: 'compliance-filing', title: 'Compliance & Filing' },
    { id: 'subscription-plans', title: 'Subscription Plans' },
    { id: 'team-settings', title: 'Team & Settings' },
    { id: 'ai-features', title: 'AI Features' },
    { id: 'faqs', title: 'FAQs' },
    { id: 'contact-support', title: 'Contact Support' }
];

const faqs = ref([
    {
        question: 'How do I enable VAT exempt status?',
        answer: 'Go to Settings in the sidebar, scroll to the VAT Exempt Status section, toggle it on, select your category, and provide a reason. Your business must either have turnover below ₦25M or deal exclusively in FIRS-approved exempt goods/services.',
        open: false
    },
    {
        question: 'Why is my WHT rate doubled?',
        answer: 'Per WHT Regulations 2024, if a supplier\'s TIN is missing or invalid (not 11-14 digits), the WHT rate is automatically doubled. Ensure your vendors provide valid TINs to avoid double rates.',
        open: false
    },
    {
        question: 'Can I connect multiple bank accounts?',
        answer: 'Yes. You can connect multiple bank accounts from different banks. Each connection is handled securely via Mono. Go to Bank Accounts → Connect Bank Account and repeat for each account.',
        open: false
    },
    {
        question: 'How do I upgrade my subscription?',
        answer: 'Go to Plans & Billing in the sidebar, choose your desired plan, select monthly or annual billing (annual saves 20%), and complete payment via Paystack. Your upgrade takes effect immediately.',
        open: false
    },
    {
        question: 'What happens if I miss a filing deadline?',
        answer: 'Late filing attracts penalties: CIT = ₦25,000 (first month) + ₦5,000/month thereafter. PAYE late remittance = 10% penalty + interest. TaxMaster sends email reminders 7 days before each deadline.',
        open: false
    },
    {
        question: 'Can my accountant access my TaxMaster account?',
        answer: 'Yes. Go to Staff in the sidebar, click "Invite Team Member", enter your accountant\'s email, and assign the "Accountant" role. They\'ll receive an invitation and get full access to manage your tax compliance.',
        open: false
    },
    {
        question: 'How do I export my transaction data?',
        answer: 'Go to Transactions, use the filters if needed, then click "Export" in the top right. You can export as CSV for use in Excel, QuickBooks, or other accounting software.',
        open: false
    },
    {
        question: 'Is my financial data secure?',
        answer: 'Absolutely. TaxMaster uses bank-level security: SSL encryption for all data, bank connections via Mono (your credentials never touch our servers), SOC 2 compliant infrastructure, and regular security audits.',
        open: false
    }
]);

const filteredSections = computed(() => {
    if (!searchQuery.value) return sections;
    return sections.filter(s => s.title.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const shouldShowSection = (id) => {
    if (!searchQuery.value) return true;
    return sections.find(s => s.id === id)?.title.toLowerCase().includes(searchQuery.value.toLowerCase());
};

const scrollToSection = (id) => {
    const el = document.getElementById(id);
    if (el) {
        // Update URL hash without triggering page jump
        history.pushState(null, '', `#${id}`);
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        activeSection.value = id;
    }
};

const toggleFaq = (index) => {
    faqs.value[index].open = !faqs.value[index].open;
};

// Intersection Observer to highlight active section
let observer = null;

onMounted(() => {
    // Check if there's a hash in the URL and scroll to it
    const hash = window.location.hash.slice(1); // Remove the '#'
    if (hash) {
        setTimeout(() => {
            const el = document.getElementById(hash);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                activeSection.value = hash;
            }
        }, 100);
    }

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activeSection.value = entry.target.id;
                    // Update URL hash as user scrolls
                    history.replaceState(null, '', `#${entry.target.id}`);
                }
            });
        },
        {
            rootMargin: '-20% 0px -70% 0px',
            threshold: 0
        }
    );

    // Observe all section elements
    sections.forEach((section) => {
        const el = document.getElementById(section.id);
        if (el) observer.observe(el);
    });
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});
</script>

<template>
    <div class="relative bg-white pt-24 pb-12 sm:pt-32 sm:pb-16 lg:pt-40 lg:pb-20">

        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] -z-10"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <!-- Header - Mono style: Clean, bold, minimal -->
            <div class="text-center mb-16">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight text-slate-900 mb-4">
                    Help Center
                </h1>
                <p class="text-lg text-slate-600 max-w-xl mx-auto">
                    Everything you need to know about using TaxMaster
                </p>
            </div>

            <!-- Search - Mono style: Subtle, clean input -->
            <div class="max-w-2xl mx-auto mb-16">
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search help articles..."
                        class="w-full px-5 py-4 pl-12 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-slate-900 text-base transition-all"
                    />
                    <svg class="absolute left-4 top-4 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Quick Links - Mono style: Minimal cards with subtle borders -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-20">
                <button
                    v-for="link in quickLinks"
                    :key="link.id"
                    @click="scrollToSection(link.id)"
                    class="group flex items-center gap-4 p-6 bg-white border border-slate-200 rounded-xl hover:border-slate-400 hover:bg-slate-50 transition-all text-left"
                >
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                        <span class="text-sm font-semibold text-slate-700">{{ link.title.charAt(0) }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ link.title }}</h3>
                        <p class="text-sm text-slate-500">{{ link.description }}</p>
                    </div>
                    <svg class="ml-auto w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Sidebar - Mono style: Clean sticky nav -->
                <div class="lg:col-span-3">
                    <div class="sticky top-24 max-h-[calc(100vh-8rem)] overflow-y-auto">
                        <div class="border border-slate-200 rounded-xl p-5 bg-white shadow-sm">
                            <h3 class="font-semibold text-slate-900 mb-4 text-sm uppercase tracking-wide">Contents</h3>
                            <nav class="space-y-1">
                                <a
                                    v-for="section in filteredSections"
                                    :key="section.id"
                                    @click.prevent="scrollToSection(section.id)"
                                    :href="`#${section.id}`"
                                    :class="[
                                        'block px-3 py-2 text-sm rounded-lg transition-colors cursor-pointer',
                                        activeSection === section.id
                                            ? 'bg-slate-900 text-white font-medium'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                                    ]"
                                >
                                    {{ section.title }}
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Content - Mono style: Clean sections with subtle dividers -->
                <div class="lg:col-span-9 space-y-16">

                    <!-- Getting Started -->
                    <section v-if="shouldShowSection('getting-started')" id="getting-started" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Getting Started</h2>
                        </div>

                        <div class="space-y-6">
                            <p class="text-slate-600 text-lg">
                                TaxMaster is your complete Nigerian tax compliance platform. Follow these steps to get started:
                            </p>

                            <ol class="space-y-4">
                                <li v-for="(step, i) in [
                                    'Complete Your Business Profile - Add your company details, TIN, and tax registration info',
                                    'Link Your Bank Account - Connect via Mono for automatic transaction sync',
                                    'Choose Your Subscription Plan - Select the plan that fits your needs',
                                    'Add Your Team Members - Invite staff and accountants to collaborate',
                                    'File Your First Tax Return - Start with a simple VAT or PAYE return'
                                ]" :key="i" class="flex gap-4">
                                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold flex items-center justify-center mt-0.5">
                                        {{ i + 1 }}
                                    </span>
                                    <span class="text-slate-700">{{ step }}</span>
                                </li>
                            </ol>

                            <div class="mt-8 p-5 bg-slate-50 border border-slate-200 rounded-lg">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-slate-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-slate-900 text-sm mb-1">Pro Tip</p>
                                        <p class="text-slate-600 text-sm">Once logged in, visit the "Get Started" page in your sidebar for a guided checklist with progress tracking.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Tax Modules -->
                    <section v-if="shouldShowSection('tax-modules')" id="tax-modules" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Tax Modules</h2>
                        </div>

                        <div class="space-y-10">
                            <!-- VAT -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-1">
                                    <h3 class="text-lg font-semibold text-slate-900 mb-1">VAT</h3>
                                    <p class="text-sm text-slate-500">Value Added Tax</p>
                                    <p class="text-2xl font-bold text-slate-900 mt-2">7.5%</p>
                                </div>
                                <div class="md:col-span-2 space-y-3">
                                    <p class="text-slate-600">Track VAT on sales and expenses, file monthly returns, and manage exempt categories.</p>
                                    <ul class="space-y-2 text-sm text-slate-600">
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                                            <span>Automatic VAT calculation at 7.5%</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                                            <span>VAT exempt status toggle (18 FIRS-approved categories)</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                                            <span>₦25M turnover exemption threshold</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                                            <span>VAT Form 002 generation</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <!-- PAYE -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-1">
                                    <h3 class="text-lg font-semibold text-slate-900 mb-1">PAYE</h3>
                                    <p class="text-sm text-slate-500">Personal Income Tax</p>
                                    <p class="text-2xl font-bold text-slate-900 mt-2">7-24%</p>
                                </div>
                                <div class="md:col-span-2 space-y-3">
                                    <p class="text-slate-600">Calculate employee income tax automatically with reliefs and allowances.</p>
                                    <ul class="space-y-2 text-sm text-slate-600">
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                                            <span>Progressive tax calculation (7%, 11%, 15%, 19%, 21%, 24%)</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                                            <span>Consolidated Relief Allowance: ₦200,000 + 20% of gross</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                                            <span>Automatic pension/NHF/NSITF deductions</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <!-- WHT -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-1">
                                    <h3 class="text-lg font-semibold text-slate-900 mb-1">WHT</h3>
                                    <p class="text-sm text-slate-500">Withholding Tax</p>
                                    <p class="text-2xl font-bold text-slate-900 mt-2">2-15%</p>
                                </div>
                                <div class="md:col-span-2 space-y-3">
                                    <p class="text-slate-600">Record WHT deductions on supplier payments, with automatic double rate for invalid TINs.</p>
                                    <ul class="space-y-2 text-sm text-slate-600">
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                                            <span>Rate calculation by transaction type</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                                            <span>Double Rate Enforcement (WHT Regulations 2024): Invalid TIN = doubled rate</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                                            <span>Real-time TIN validation (11-14 digits)</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <!-- CIT -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-1">
                                    <h3 class="text-lg font-semibold text-slate-900 mb-1">CIT</h3>
                                    <p class="text-sm text-slate-500">Companies Income Tax</p>
                                    <p class="text-2xl font-bold text-slate-900 mt-2">0-30%</p>
                                </div>
                                <div class="md:col-span-2 space-y-3">
                                    <p class="text-slate-600">Calculate annual corporate income tax with Finance Act 2019 rates.</p>
                                    <div class="grid grid-cols-3 gap-4 text-sm">
                                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                            <p class="font-semibold text-slate-900">0%</p>
                                            <p class="text-slate-500 text-xs mt-1">&lt; ₦25M turnover</p>
                                        </div>
                                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                            <p class="font-semibold text-slate-900">20%</p>
                                            <p class="text-slate-500 text-xs mt-1">₦25M - ₦100M</p>
                                        </div>
                                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                            <p class="font-semibold text-slate-900">30%</p>
                                            <p class="text-slate-500 text-xs mt-1">&gt; ₦100M</p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-500">Annual filing due 6 months after year-end.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Financial Management -->
                    <section v-if="shouldShowSection('financial-management')" id="financial-management" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Financial Management</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-slate-900">Bank Account Connections</h3>
                                <p class="text-slate-600 text-sm">Connect your bank accounts via Mono integration for automatic transaction sync.</p>
                                <ol class="space-y-2 text-sm text-slate-600 list-decimal list-inside">
                                    <li>Go to Bank Accounts in the sidebar</li>
                                    <li>Click "Connect Bank Account"</li>
                                    <li>Select your bank from the list</li>
                                    <li>Enter your internet banking credentials</li>
                                    <li>Authorize TaxMaster to read transactions</li>
                                </ol>
                                <div class="mt-4 p-3 bg-slate-50 border border-slate-200 rounded text-xs text-slate-600">
                                    <strong>Security:</strong> Your banking credentials are never stored by TaxMaster. All connections are handled securely by Mono.
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-slate-900">Transactions</h3>
                                <p class="text-slate-600 text-sm">View, categorize, and manage all your business transactions in one place.</p>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span>Automatic sync from connected bank accounts</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span>Manual transaction entry</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span>Tax type assignment (VAT_INPUT, VAT_OUTPUT, PAYE, WHT)</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span>CSV export for accounting software</span>
                                    </li>
                                </ul>

                                <h3 class="text-lg font-semibold text-slate-900 pt-4">Invoicing</h3>
                                <p class="text-slate-600 text-sm">Create professional invoices with automatic VAT calculation and tax compliance.</p>
                            </div>
                        </div>
                    </section>

                    <!-- Compliance & Filing -->
                    <section v-if="shouldShowSection('compliance-filing')" id="compliance-filing" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Compliance & Filing</h2>
                        </div>

                        <div class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Filing Deadlines</h3>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                            <span class="text-slate-600">VAT</span>
                                            <span class="font-medium text-slate-900">21st of following month</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                            <span class="text-slate-600">PAYE</span>
                                            <span class="font-medium text-slate-900">10th of following month</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                            <span class="text-slate-600">WHT</span>
                                            <span class="font-medium text-slate-900">21st of following month</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-slate-600">CIT</span>
                                            <span class="font-medium text-slate-900">6 months after year-end</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Compliance Calendar</h3>
                                    <p class="text-slate-600 text-sm mb-4">Never miss a deadline with automated reminders and calendar view of all tax obligations.</p>
                                    <ul class="space-y-2 text-sm text-slate-600">
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                            <span>Calendar view of all deadlines</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                            <span>Email reminders 7 days before due date</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                            <span>Filing status tracking</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="p-5 bg-amber-50 border border-amber-200 rounded-lg">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-amber-900 text-sm mb-1">Penalties</p>
                                        <p class="text-amber-700 text-sm">Late filing attracts ₦25,000 (first month) + ₦5,000 each subsequent month for CIT. PAYE late remittance = 10% penalty + interest.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Subscription Plans -->
                    <section v-if="shouldShowSection('subscription-plans')" id="subscription-plans" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Subscription Plans</h2>
                        </div>

                        <p class="text-slate-600 mb-8">TaxMaster offers 4 subscription tiers to suit businesses of all sizes.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div v-for="(plan, index) in [
                                { name: 'Free', price: '₦0', period: '/month', features: ['1 staff member', '12 tax returns/year', '1GB storage', 'Basic tax features'], highlight: false },
                                { name: 'Basic', price: '₦5,000', period: '/month', features: ['3 staff members', '50 tax returns/year', '10GB storage', 'AI chat assistant'], highlight: true },
                                { name: 'Professional', price: '₦15,000', period: '/month', features: ['10 staff members', 'Unlimited returns', '50GB storage', 'Priority support'], highlight: false },
                                { name: 'Enterprise', price: '₦50,000', period: '/month', features: ['Unlimited staff', 'Unlimited returns', '500GB storage', '24/7 support'], highlight: false, dark: true }
                            ]" :key="index"
                            :class="[
                                'rounded-xl p-6 border',
                                plan.dark ? 'bg-slate-900 border-slate-900 text-white' :
                                plan.highlight ? 'bg-blue-50 border-blue-200' : 'bg-white border-slate-200'
                            ]">
                                <h3 :class="['font-semibold mb-1', plan.dark ? 'text-white' : 'text-slate-900']">{{ plan.name }}</h3>
                                <div class="flex items-baseline gap-1 mb-4">
                                    <span :class="['text-2xl font-bold', plan.dark ? 'text-white' : 'text-slate-900']">{{ plan.price }}</span>
                                    <span :class="['text-sm', plan.dark ? 'text-slate-400' : 'text-slate-500']">{{ plan.period }}</span>
                                </div>
                                <ul class="space-y-2 text-sm">
                                    <li v-for="feature in plan.features" :key="feature" :class="['flex items-center gap-2', plan.dark ? 'text-slate-300' : 'text-slate-600']">
                                        <svg class="w-4 h-4 flex-shrink-0" :class="plan.dark ? 'text-slate-400' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ feature }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600">
                            <strong>Tip:</strong> Annual billing saves 20%. <Link href="/pricing" class="text-slate-900 underline font-medium hover:no-underline">View full pricing details</Link>
                        </div>
                    </section>

                    <!-- Team & Settings -->
                    <section v-if="shouldShowSection('team-settings')" id="team-settings" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Team & Settings</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-4">Staff Management</h3>
                                <p class="text-slate-600 text-sm mb-4">Invite team members and assign roles for collaboration on tax compliance.</p>

                                <div class="space-y-3">
                                    <div v-for="role in [
                                        { name: 'Owner', desc: 'Full control over business and settings' },
                                        { name: 'Manager', desc: 'Can file returns and manage transactions' },
                                        { name: 'Staff', desc: 'View-only access to tax data' },
                                        { name: 'Accountant', desc: 'External accountant with full access' }
                                    ]" :key="role.name" class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-slate-400 mt-2 flex-shrink-0"></div>
                                        <div>
                                            <p class="font-medium text-slate-900 text-sm">{{ role.name }}</p>
                                            <p class="text-slate-600 text-xs">{{ role.desc }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-4">Business Settings</h3>
                                <p class="text-slate-600 text-sm mb-4">Configure your business profile, tax settings, and compliance preferences.</p>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span><strong>Business Profile:</strong> Company name, TIN, registration details</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span><strong>VAT Exempt Status:</strong> Toggle if dealing in exempt goods/services</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span><strong>Tax Registrations:</strong> VAT, PAYE, WHT, CIT registration numbers</span>
                                    </li>
                                </ul>

                                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex gap-3">
                                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <p class="font-semibold text-green-900 text-sm mb-1">VAT Exempt Status</p>
                                            <p class="text-green-700 text-xs">If your annual turnover is below ₦25M or you deal exclusively in exempt goods/services, enable VAT exempt status in Settings.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- AI Features -->
                    <section v-if="shouldShowSection('ai-features')" id="ai-features" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">AI Features</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-4">AI Chat Assistant</h3>
                                <p class="text-slate-600 text-sm mb-4">Ask TaxMaster AI any question about Nigerian tax law, regulations, or your tax obligations.</p>

                                <div class="space-y-2">
                                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Example questions</p>
                                    <div v-for="q in [
                                        'What is the VAT rate in Nigeria?',
                                        'When is my next PAYE deadline?',
                                        'How do I calculate WHT on professional services?',
                                        'What goods are exempt from VAT?'
                                    ]" :key="q" class="p-3 bg-slate-50 rounded-lg text-sm text-slate-700 border border-slate-100">
                                        {{ q }}
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mt-4">The AI is trained on CITA, PITA, VAT Act, WHT Regulations 2024, and Finance Acts 2019/2020.</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-4">Tax Insights</h3>
                                <p class="text-slate-600 text-sm mb-4">Get AI-powered analysis of your tax trends, liabilities, and optimization opportunities.</p>
                                <ul class="space-y-3 text-sm text-slate-600">
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span>Tax liability trends over time</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span>Compliance score and recommendations</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span>VAT refund opportunities</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span>PAYE optimization suggestions</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-1 h-1 rounded-full bg-slate-400 mt-2"></span>
                                        <span>CIT planning for year-end</span>
                                    </li>
                                </ul>

                                <div class="mt-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                                    <p class="text-purple-700 text-sm">
                                        <strong>Premium Feature:</strong> AI analysis available on Basic, Professional, and Enterprise plans.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- FAQs -->
                    <section v-if="shouldShowSection('faqs')" id="faqs" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">FAQs</h2>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(faq, index) in faqs" :key="index" class="border border-slate-200 rounded-lg overflow-hidden">
                                <button
                                    @click="toggleFaq(index)"
                                    class="flex items-center justify-between w-full p-5 text-left bg-white hover:bg-slate-50 transition-colors"
                                >
                                    <span class="font-medium text-slate-900 pr-4">{{ faq.question }}</span>
                                    <svg
                                        class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform"
                                        :class="{ 'rotate-180': faq.open }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    v-show="faq.open"
                                    class="px-5 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-100 bg-slate-50/50"
                                >
                                    <p class="pt-4">{{ faq.answer }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Contact Support -->
                    <section v-if="shouldShowSection('contact-support')" id="contact-support" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Contact Support</h2>
                        </div>

                        <div class="space-y-8">
                            <p class="text-slate-600">Can't find what you're looking for? Our support team is here to help.</p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div v-for="(contact, i) in [
                                    { title: 'Email Support', detail: 'support@taxmaster.ng', note: 'Response within 24 hours', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
                                    { title: 'Live Chat', detail: 'Chat with AI or agent', note: 'Available 9am - 5pm WAT', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z' },
                                    { title: 'Contact Us', detail: 'Visit our contact page', note: 'Get in touch', icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z' }
                                ]" :key="i" class="p-6 border border-slate-200 rounded-xl text-center hover:border-slate-300 transition-colors">
                                    <svg class="w-8 h-8 text-slate-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="contact.icon" />
                                    </svg>
                                    <h3 class="font-semibold text-slate-900 mb-1">{{ contact.title }}</h3>
                                    <p class="text-slate-600 text-sm mb-1">{{ contact.detail }}</p>
                                    <p class="text-slate-400 text-xs">{{ contact.note }}</p>
                                </div>
                            </div>

                            <div class="text-center pt-8 border-t border-slate-200">
                                <p class="text-slate-600 mb-4">Ready to get started?</p>
                                <Link
                                    :href="route('register')"
                                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition-colors"
                                >
                                    Create free account
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </div>
    </div>
</template>
