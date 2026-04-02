<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const searchQuery = ref('');
const activeSection = ref('');

const quickLinks = [
    { id: 'prerequisites', title: 'Prerequisites & Setup', description: 'What you need to start' },
    { id: 'how-to-file', title: 'How to File Returns', description: 'Step-by-step filing guides' },
    { id: 'government-portals', title: 'Government Portals', description: 'Where to submit & pay' },
];

const sections = [
    { id: 'prerequisites', title: 'Prerequisites & Setup' },
    { id: 'getting-started', title: 'Getting Started' },
    { id: 'tax-modules', title: 'Tax Modules' },
    { id: 'financial-management', title: 'Financial Management' },
    { id: 'compliance-filing', title: 'Compliance & Filing' },
    { id: 'ai-features', title: 'AI Features' },
    { id: 'how-to-file', title: 'How to File Returns' },
    { id: 'government-portals', title: 'Government Portals & Payment' },
    { id: 'subscription-plans', title: 'Subscription Plans' },
    { id: 'team-settings', title: 'Team & Settings' },
    { id: 'faqs', title: 'FAQs' },
    { id: 'contact-support', title: 'Contact Support' }
];

const faqs = ref([
    {
        question: 'How do I get my TIN if I don\'t have one?',
        answer: 'Visit https://taxpromax.firs.gov.ng and click "Register". Select "Corporate Body" for companies or "Individual" for sole proprietors. Complete registration with your CAC certificate (for companies) or valid ID (for individuals). Your TIN will be generated immediately (11-14 digits). You can also register at any FIRS office nationwide.',
        open: false
    },
    {
        question: 'Where do I submit my VAT return after generating it in TaxMaster?',
        answer: 'After generating your VAT return in TaxMaster, export the VAT Form 002 schedule, then login to FIRS TaxPro-Max portal (https://taxpromax.firs.gov.ng), navigate to "File Returns" → "VAT", upload your schedule, generate an assessment, get your Remita RRR, and make payment via online banking, bank branch, or Remita website.',
        open: false
    },
    {
        question: 'How do I pay my taxes after filing?',
        answer: 'After filing on FIRS TaxPro-Max or your State IRS portal, the system generates a Remita RRR (payment reference). Use this RRR to pay via: 1) Online banking (Pay Bills → Remita → Enter RRR), 2) Any bank branch (provide RRR to teller), or 3) Remita website (remita.net → Make Payment). Payment confirmation usually takes 24-48 hours to reflect.',
        open: false
    },
    {
        question: 'What\'s the difference between AI workflow and manual filing?',
        answer: 'AI Workflow: Click once, AI analyzes all your transactions and generates complete returns automatically in 5-15 seconds with 95%+ accuracy. Best for standard businesses with regular transactions. Manual Filing: You enter all figures yourself with full control over every detail. Best for complex businesses or when you need specific categorization. Both methods produce the same export files for government submission.',
        open: false
    },
    {
        question: 'Which portal do I use for PAYE - FIRS or State IRS?',
        answer: 'PAYE is filed with your State IRS, NOT FIRS. Lagos businesses use LIRS (etax.lirs.net), Oyo uses OIRS, Rivers uses RIRS, etc. Each state has its own portal. Check the "Government Portals & Payment" section in this help center for links to major state portals.',
        open: false
    },
    {
        question: 'Do I need Remita for all tax payments?',
        answer: 'For FIRS taxes (VAT, WHT, CIT), you use Remita RRR generated from TaxPro-Max. For State IRS taxes (PAYE), it depends on your state - most use Remita, but some states accept direct bank transfers to designated accounts. Check your state\'s IRS portal for specific payment methods.',
        open: false
    },
    {
        question: 'What happens after I export my return from TaxMaster?',
        answer: 'TaxMaster generates your return calculations and exports a schedule file (CSV/Excel). You then: 1) Login to the appropriate government portal (FIRS TaxPro-Max for VAT/WHT/CIT, or your State IRS for PAYE), 2) Upload the exported schedule, 3) Portal generates assessment, 4) Get Remita RRR or payment reference, 5) Make payment. TaxMaster prepares the return; government portals handle submission and payment.',
        open: false
    },
    {
        question: 'Can I file my returns directly from TaxMaster without visiting government portals?',
        answer: 'No. TaxMaster generates and calculates your tax returns, but you must submit them to FIRS TaxPro-Max (for VAT/WHT/CIT) or your State IRS portal (for PAYE) yourself. This is because FIRS and State IRS systems require direct submission and payment through their official portals for compliance and record-keeping.',
        open: false
    },
    {
        question: 'How long does the complete tax filing process take?',
        answer: 'In TaxMaster: AI workflow takes 1-2 minutes, manual takes 10-15 minutes. On government portals: Upload and RRR generation takes 5-10 minutes. Payment via online banking takes 2-5 minutes. Total: 10-30 minutes end-to-end depending on your method. Payment confirmation appears on the portal within 24-48 hours.',
        open: false
    },
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

                    <!-- Prerequisites & Setup -->
                    <section v-if="shouldShowSection('prerequisites')" id="prerequisites" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Prerequisites & Setup</h2>
                            <p class="text-slate-500 text-sm mt-2">What you need before using TaxMaster</p>
                        </div>

                        <div class="space-y-8">
                            <!-- TIN Registration -->
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-slate-900 text-white text-sm font-bold flex items-center justify-center">1</span>
                                    Tax Identification Number (TIN)
                                </h3>
                                <p class="text-slate-600 mb-4">Your TIN is required to file any tax returns in Nigeria. If you don't have one yet:</p>

                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-5 space-y-3">
                                    <h4 class="font-semibold text-slate-900 text-sm">How to get your TIN:</h4>
                                    <ol class="space-y-2 text-sm text-slate-700">
                                        <li class="flex gap-3"><span class="font-medium text-slate-500">a.</span> <span>Visit <a href="https://taxpromax.firs.gov.ng" target="_blank" class="text-slate-900 underline hover:no-underline font-medium">FIRS TaxPro-Max portal</a></span></li>
                                        <li class="flex gap-3"><span class="font-medium text-slate-500">b.</span> <span>Click "Register" → Select "Corporate Body" or "Individual"</span></li>
                                        <li class="flex gap-3"><span class="font-medium text-slate-500">c.</span> <span>Complete registration with CAC documents (for companies) or valid ID (for individuals)</span></li>
                                        <li class="flex gap-3"><span class="font-medium text-slate-500">d.</span> <span>Your TIN will be generated immediately (11-14 digits)</span></li>
                                        <li class="flex gap-3"><span class="font-medium text-slate-500">e.</span> <span>Download your TIN certificate for reference</span></li>
                                    </ol>
                                    <p class="text-xs text-slate-500 pt-2 border-t border-slate-200 mt-3">
                                        <strong>Note:</strong> You can also register at any FIRS office nationwide with your CAC certificate and valid means of ID.
                                    </p>
                                </div>
                            </div>

                            <!-- Business Registration -->
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-slate-900 text-white text-sm font-bold flex items-center justify-center">2</span>
                                    Business Registration Documents
                                </h3>
                                <p class="text-slate-600 mb-4">Have these documents ready:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="doc in [
                                        { name: 'CAC Certificate', desc: 'Certificate of Incorporation from CAC', required: true },
                                        { name: 'TIN Certificate', desc: 'From FIRS registration', required: true },
                                        { name: 'Business Address', desc: 'Registered office address', required: true },
                                        { name: 'Bank Account Details', desc: 'For connecting transactions', required: true },
                                        { name: 'VAT Registration', desc: 'If turnover > ₦25M/year', required: false },
                                        { name: 'Director/Shareholder Info', desc: 'Names, emails, phone numbers', required: true }
                                    ]" :key="doc.name" class="flex items-start gap-3 p-3 bg-white border border-slate-200 rounded-lg">
                                        <svg v-if="doc.required" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z"/>
                                        </svg>
                                        <svg v-else class="w-5 h-5 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <p class="font-medium text-slate-900 text-sm">{{ doc.name }}</p>
                                            <p class="text-slate-600 text-xs">{{ doc.desc }}</p>
                                            <span v-if="doc.required" class="text-xs text-red-600 font-medium">Required</span>
                                            <span v-else class="text-xs text-slate-500">Optional</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Account -->
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-slate-900 text-white text-sm font-bold flex items-center justify-center">3</span>
                                    Bank Account Setup
                                </h3>
                                <p class="text-slate-600 mb-4">For automatic transaction syncing via Mono, you need:</p>
                                <ul class="space-y-3">
                                    <li v-for="item in [
                                        'A business bank account with any Nigerian bank',
                                        'Internet banking enabled on your account',
                                        'Your internet banking username and password',
                                        'Phone number registered with your bank (for OTP verification)'
                                    ]" :key="item" class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-slate-700">{{ item }}</span>
                                    </li>
                                </ul>
                                <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <p class="text-green-700 text-sm">
                                        <strong>Supported Banks:</strong> Access, GTBank, Zenith, First Bank, UBA, Fidelity, Union Bank, Sterling, Wema, Stanbic IBTC, and 30+ more.
                                    </p>
                                </div>
                            </div>

                            <!-- Tax Registrations -->
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-slate-900 text-white text-sm font-bold flex items-center justify-center">4</span>
                                    Specific Tax Registrations
                                </h3>
                                <p class="text-slate-600 mb-4">Depending on your business activities, register for:</p>
                                <div class="space-y-4">
                                    <div class="border-l-4 border-slate-300 pl-4">
                                        <h4 class="font-semibold text-slate-900 text-sm mb-1">VAT Registration</h4>
                                        <p class="text-slate-600 text-sm mb-2">Required if annual turnover exceeds ₦25 million</p>
                                        <p class="text-xs text-slate-500">Register at FIRS TaxPro-Max or any FIRS office</p>
                                    </div>
                                    <div class="border-l-4 border-slate-300 pl-4">
                                        <h4 class="font-semibold text-slate-900 text-sm mb-1">PAYE Registration</h4>
                                        <p class="text-slate-600 text-sm mb-2">Required if you have employees</p>
                                        <p class="text-xs text-slate-500">Register with your State Internal Revenue Service (e.g., LIRS for Lagos)</p>
                                    </div>
                                    <div class="border-l-4 border-slate-300 pl-4">
                                        <h4 class="font-semibold text-slate-900 text-sm mb-1">WHT Registration</h4>
                                        <p class="text-slate-600 text-sm mb-2">Required if you make payments to contractors/suppliers</p>
                                        <p class="text-xs text-slate-500">Automatically covered under your TIN - no separate registration needed</p>
                                    </div>
                                    <div class="border-l-4 border-slate-300 pl-4">
                                        <h4 class="font-semibold text-slate-900 text-sm mb-1">CIT Registration</h4>
                                        <p class="text-slate-600 text-sm mb-2">Required for all incorporated companies</p>
                                        <p class="text-xs text-slate-500">Automatically covered under your TIN - file annually</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex gap-3">
                                    <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-blue-900 mb-2">Ready to Start?</p>
                                        <p class="text-blue-800 text-sm">Once you have your TIN and business documents, you can create your TaxMaster account and start managing your tax compliance in minutes.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

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

                    <!-- Government Portals & Payment -->
                    <section v-if="shouldShowSection('government-portals')" id="government-portals" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Government Portals & Payment</h2>
                            <p class="text-slate-500 text-sm mt-2">Where to submit returns and pay taxes after TaxMaster</p>
                        </div>

                        <div class="space-y-12">

                            <!-- FIRS TaxPro-Max -->
                            <div>
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-green-600 text-white font-bold flex items-center justify-center shadow-lg">
                                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">FIRS TaxPro-Max Portal</h3>
                                        <p class="text-sm text-slate-500">For VAT, WHT, and CIT returns</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div class="p-5 border-2 border-green-200 rounded-xl bg-green-50">
                                            <div class="flex items-center gap-2 mb-3">
                                                <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                </svg>
                                                <span class="font-semibold text-green-900">Portal Link</span>
                                            </div>
                                            <a href="https://taxpromax.firs.gov.ng" target="_blank" class="text-green-700 hover:text-green-900 underline font-medium text-sm break-all">
                                                https://taxpromax.firs.gov.ng
                                            </a>
                                        </div>

                                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                            <p class="font-semibold text-slate-900 text-sm mb-3">What to file here:</p>
                                            <ul class="space-y-2 text-sm text-slate-700">
                                                <li class="flex items-start gap-2">
                                                    <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span>VAT Returns (monthly)</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span>WHT Returns (monthly)</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span>CIT Returns (annual)</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span>TIN Registration</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-3 text-sm">How to submit your return:</h4>
                                        <ol class="space-y-3 text-sm text-slate-700">
                                            <li class="flex gap-3">
                                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold flex items-center justify-center">1</span>
                                                <div>
                                                    <p class="font-medium">Login to TaxPro-Max</p>
                                                    <p class="text-slate-600 text-xs mt-1">Use your TIN as username and your registered password</p>
                                                </div>
                                            </li>
                                            <li class="flex gap-3">
                                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold flex items-center justify-center">2</span>
                                                <div>
                                                    <p class="font-medium">Navigate to Returns</p>
                                                    <p class="text-slate-600 text-xs mt-1">Click "File Returns" → Select tax type (VAT/WHT/CIT)</p>
                                                </div>
                                            </li>
                                            <li class="flex gap-3">
                                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold flex items-center justify-center">3</span>
                                                <div>
                                                    <p class="font-medium">Upload schedule from TaxMaster</p>
                                                    <p class="text-slate-600 text-xs mt-1">Upload the CSV/Excel file exported from TaxMaster</p>
                                                </div>
                                            </li>
                                            <li class="flex gap-3">
                                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold flex items-center justify-center">4</span>
                                                <div>
                                                    <p class="font-medium">Generate Assessment</p>
                                                    <p class="text-slate-600 text-xs mt-1">System generates assessment notice with amount due</p>
                                                </div>
                                            </li>
                                            <li class="flex gap-3">
                                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold flex items-center justify-center">5</span>
                                                <div>
                                                    <p class="font-medium">Generate Remita RRR</p>
                                                    <p class="text-slate-600 text-xs mt-1">Click "Generate RRR" to get payment reference</p>
                                                </div>
                                            </li>
                                            <li class="flex gap-3">
                                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold flex items-center justify-center">6</span>
                                                <div>
                                                    <p class="font-medium">Make payment (see Payment section below)</p>
                                                    <p class="text-slate-600 text-xs mt-1">Use RRR to pay via Remita</p>
                                                </div>
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <!-- State IRS Portals (PAYE) -->
                            <div>
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-blue-600 text-white font-bold flex items-center justify-center shadow-lg">
                                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">State IRS Portals (PAYE)</h3>
                                        <p class="text-sm text-slate-500">File PAYE with your State Internal Revenue Service</p>
                                    </div>
                                </div>

                                <div class="p-5 bg-blue-50 border-2 border-blue-200 rounded-xl mb-6">
                                    <div class="flex gap-3">
                                        <svg class="w-6 h-6 text-blue-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <p class="font-semibold text-blue-900 mb-2">Important: PAYE is State-specific</p>
                                            <p class="text-blue-800 text-sm">PAYE is filed with your <strong>State IRS</strong>, not FIRS. Use the portal for the state where your business is registered or where your employees reside.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="statePortal in [
                                        { state: 'Lagos', agency: 'LIRS', url: 'https://etax.lirs.net', color: 'rose' },
                                        { state: 'Oyo', agency: 'OIRS', url: 'https://oirs.oyo.gov.ng', color: 'amber' },
                                        { state: 'Rivers', agency: 'RIRS', url: 'https://rirs.rg.gov.ng', color: 'emerald' },
                                        { state: 'Kano', agency: 'KIRS', url: 'https://kirs.kg.gov.ng', color: 'blue' },
                                        { state: 'Kaduna', agency: 'KDIRS', url: 'https://kdirs.kdsg.gov.ng', color: 'purple' },
                                        { state: 'Federal Capital', agency: 'FCT-IRS', url: 'https://fctirs.gov.ng', color: 'slate' }
                                    ]" :key="statePortal.state"
                                    class="p-4 bg-white border-2 border-slate-200 rounded-xl hover:border-slate-300 transition-colors">
                                        <div class="flex items-start justify-between mb-2">
                                            <div>
                                                <h4 class="font-semibold text-slate-900 text-sm">{{ statePortal.state }} State</h4>
                                                <p class="text-xs text-slate-500">{{ statePortal.agency }}</p>
                                            </div>
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </div>
                                        <a :href="statePortal.url" target="_blank" class="text-xs text-slate-600 hover:text-slate-900 underline hover:no-underline break-all">
                                            {{ statePortal.url }}
                                        </a>
                                    </div>
                                </div>

                                <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                    <p class="text-slate-700 text-sm">
                                        <strong>Other States:</strong> If your state is not listed, search for "[Your State] IRS portal" or visit your state's official IRS office. Most states now have online portals for PAYE filing.
                                    </p>
                                </div>

                                <div class="mt-6">
                                    <h4 class="font-semibold text-slate-900 mb-3 text-sm">General steps for State IRS PAYE filing:</h4>
                                    <ol class="space-y-2 text-sm text-slate-700 list-decimal list-inside">
                                        <li>Login to your state's IRS portal with your state TIN</li>
                                        <li>Navigate to PAYE → File Monthly Return</li>
                                        <li>Upload PAYE schedule exported from TaxMaster (employee breakdown)</li>
                                        <li>System generates assessment with total PAYE due</li>
                                        <li>Generate payment reference (varies by state - some use Remita, others bank transfer)</li>
                                        <li>Make payment using provided reference</li>
                                        <li>Download receipt for your records</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <!-- Remita Payment -->
                            <div>
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-yellow-500 text-white font-bold flex items-center justify-center shadow-lg">
                                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">Making Tax Payments (Remita)</h3>
                                        <p class="text-sm text-slate-500">How to pay your tax liabilities</p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div class="p-5 bg-yellow-50 border-2 border-yellow-200 rounded-xl">
                                        <p class="font-semibold text-yellow-900 mb-2">What is Remita RRR?</p>
                                        <p class="text-yellow-800 text-sm">
                                            <strong>RRR (Remita Retrieval Reference)</strong> is a unique payment reference number generated by FIRS TaxPro-Max or State IRS portals. You use this RRR to make payments through various channels.
                                        </p>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-4">Payment Methods with RRR:</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div class="p-4 border-2 border-slate-200 rounded-xl bg-white">
                                                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center mb-3">
                                                    <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <h5 class="font-semibold text-slate-900 text-sm mb-2">Online Banking</h5>
                                                <ol class="space-y-1 text-xs text-slate-600">
                                                    <li>1. Login to internet banking</li>
                                                    <li>2. Select "Pay Bills" → "Remita"</li>
                                                    <li>3. Enter your RRR</li>
                                                    <li>4. Confirm amount and pay</li>
                                                </ol>
                                            </div>

                                            <div class="p-4 border-2 border-slate-200 rounded-xl bg-white">
                                                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center mb-3">
                                                    <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </div>
                                                <h5 class="font-semibold text-slate-900 text-sm mb-2">Bank Branch</h5>
                                                <ol class="space-y-1 text-xs text-slate-600">
                                                    <li>1. Visit any bank branch</li>
                                                    <li>2. Request Remita payment</li>
                                                    <li>3. Provide your RRR</li>
                                                    <li>4. Make cash/transfer payment</li>
                                                </ol>
                                            </div>

                                            <div class="p-4 border-2 border-slate-200 rounded-xl bg-white">
                                                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center mb-3">
                                                    <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                                    </svg>
                                                </div>
                                                <h5 class="font-semibold text-slate-900 text-sm mb-2">Remita Website</h5>
                                                <ol class="space-y-1 text-xs text-slate-600">
                                                    <li>1. Visit <a href="https://remita.net" class="underline" target="_blank">remita.net</a></li>
                                                    <li>2. Click "Make Payment"</li>
                                                    <li>3. Enter your RRR</li>
                                                    <li>4. Pay with card/bank transfer</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-5 bg-red-50 border border-red-200 rounded-lg">
                                        <div class="flex gap-3">
                                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            <div>
                                                <p class="font-semibold text-red-900 text-sm mb-1">Important Payment Notes</p>
                                                <ul class="space-y-1 text-red-800 text-sm">
                                                    <li>• Always pay before the deadline to avoid penalties</li>
                                                    <li>• Payment confirmation may take 24-48 hours to reflect on the portal</li>
                                                    <li>• Keep your payment receipt/confirmation for your records</li>
                                                    <li>• Some State IRS portals may use direct bank transfers instead of Remita</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <h4 class="font-semibold text-green-900 text-sm mb-2">Complete Tax Filing Workflow Summary:</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mt-3">
                                            <div class="text-center">
                                                <div class="w-8 h-8 rounded-full bg-green-600 text-white text-xs font-bold flex items-center justify-center mx-auto mb-2">1</div>
                                                <p class="text-xs font-medium text-green-900">Generate return in TaxMaster</p>
                                            </div>
                                            <div class="text-center">
                                                <div class="w-8 h-8 rounded-full bg-green-600 text-white text-xs font-bold flex items-center justify-center mx-auto mb-2">2</div>
                                                <p class="text-xs font-medium text-green-900">Export schedule/CSV</p>
                                            </div>
                                            <div class="text-center">
                                                <div class="w-8 h-8 rounded-full bg-green-600 text-white text-xs font-bold flex items-center justify-center mx-auto mb-2">3</div>
                                                <p class="text-xs font-medium text-green-900">Login to FIRS/State portal</p>
                                            </div>
                                            <div class="text-center">
                                                <div class="w-8 h-8 rounded-full bg-green-600 text-white text-xs font-bold flex items-center justify-center mx-auto mb-2">4</div>
                                                <p class="text-xs font-medium text-green-900">Upload & generate RRR</p>
                                            </div>
                                            <div class="text-center">
                                                <div class="w-8 h-8 rounded-full bg-green-600 text-white text-xs font-bold flex items-center justify-center mx-auto mb-2">5</div>
                                                <p class="text-xs font-medium text-green-900">Pay via Remita/Bank</p>
                                            </div>
                                        </div>
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

                        <div class="space-y-10">
                            <!-- AI One-Click Tax Returns -->
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    AI One-Click Tax Returns
                                </h3>
                                <p class="text-slate-600 mb-4">Let AI analyze your transactions and generate complete tax returns automatically—no manual calculations needed.</p>

                                <div class="bg-gradient-to-br from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-6 space-y-4">
                                    <h4 class="font-semibold text-slate-900 text-sm mb-3">How the AI Workflow works:</h4>
                                    <ol class="space-y-3">
                                        <li v-for="(step, i) in [
                                            'Connect your bank account or add transactions manually',
                                            'Click &quot;Start AI Workflow&quot; for VAT, PAYE, WHT, or CIT',
                                            'AI analyzes all transactions and categorizes them by tax type',
                                            'Complete draft return is generated in seconds with 95%+ accuracy',
                                            'Review the AI-generated return and confidence scores',
                                            'Approve and export—ready to file with FIRS or State IRS'
                                        ]" :key="i" class="flex gap-3">
                                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-purple-600 text-white text-xs font-semibold flex items-center justify-center">
                                                {{ i + 1 }}
                                            </span>
                                            <span class="text-slate-700 text-sm">{{ step }}</span>
                                        </li>
                                    </ol>

                                    <div class="pt-3 border-t border-purple-200">
                                        <p class="text-xs text-purple-800">
                                            <strong>Available on:</strong> Basic, Professional, and Enterprise plans | <strong>Processing time:</strong> 5-15 seconds | <strong>Accuracy:</strong> 95%+
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="p-4 bg-white border border-slate-200 rounded-lg">
                                        <h5 class="font-semibold text-slate-900 text-sm mb-2">When to use AI</h5>
                                        <ul class="space-y-1 text-xs text-slate-600">
                                            <li class="flex items-start gap-2">
                                                <span class="text-green-600 mt-1">✓</span>
                                                <span>Standard business with regular transactions</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-green-600 mt-1">✓</span>
                                                <span>Monthly VAT, PAYE, WHT filings</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-green-600 mt-1">✓</span>
                                                <span>Want to save time and reduce errors</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-green-600 mt-1">✓</span>
                                                <span>Bank account connected for auto-sync</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="p-4 bg-white border border-slate-200 rounded-lg">
                                        <h5 class="font-semibold text-slate-900 text-sm mb-2">When to use Manual</h5>
                                        <ul class="space-y-1 text-xs text-slate-600">
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-600 mt-1">→</span>
                                                <span>Complex business with special tax treatments</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-600 mt-1">→</span>
                                                <span>Unusual transactions requiring specific categorization</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-600 mt-1">→</span>
                                                <span>Need complete control over every figure</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-600 mt-1">→</span>
                                                <span>Accountant preference for manual entry</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <!-- AI Chat Assistant -->
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
                        </div>
                    </section>

                    <!-- How to File Tax Returns -->
                    <section v-if="shouldShowSection('how-to-file')" id="how-to-file" class="scroll-mt-24">
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">How to File Tax Returns</h2>
                            <p class="text-slate-500 text-sm mt-2">Step-by-step guide to filing each tax type</p>
                        </div>

                        <div class="space-y-12">

                            <!-- VAT Filing -->
                            <div>
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 font-bold text-lg flex items-center justify-center">
                                        VAT
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">Filing VAT Returns (Monthly)</h3>
                                        <p class="text-sm text-slate-500">Due by 21st of following month | File with FIRS</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- AI Method -->
                                    <div class="border-2 border-purple-200 rounded-xl p-6 bg-purple-50/30">
                                        <div class="flex items-center gap-2 mb-4">
                                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <h4 class="font-semibold text-slate-900">AI Method (Recommended)</h4>
                                        </div>
                                        <ol class="space-y-3 text-sm text-slate-700">
                                            <li class="flex gap-2"><span class="font-semibold text-purple-600">1.</span> <span>Go to <strong>AI Workflows</strong> in sidebar</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-purple-600">2.</span> <span>Click <strong>"Start AI Workflow"</strong></span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-purple-600">3.</span> <span>Select <strong>"Monthly VAT"</strong></span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-purple-600">4.</span> <span>Choose tax period (e.g., Jan 2025)</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-purple-600">5.</span> <span>Click <strong>"Start"</strong> → AI analyzes transactions</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-purple-600">6.</span> <span>Review generated VAT return (5-15 seconds)</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-purple-600">7.</span> <span>Check confidence scores and adjust if needed</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-purple-600">8.</span> <span>Click <strong>"Approve"</strong> → Return saved to VAT module</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-purple-600">9.</span> <span>Go to <strong>VAT → Returns</strong> to view/export</span></li>
                                        </ol>
                                        <p class="text-xs text-purple-700 mt-4 font-medium">⏱️ Takes 1-2 minutes total</p>
                                    </div>

                                    <!-- Manual Method -->
                                    <div class="border-2 border-slate-200 rounded-xl p-6">
                                        <h4 class="font-semibold text-slate-900 mb-4">Manual Method</h4>
                                        <ol class="space-y-3 text-sm text-slate-700">
                                            <li class="flex gap-2"><span class="font-semibold text-slate-500">1.</span> <span>Go to <strong>VAT → Returns</strong></span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-slate-500">2.</span> <span>Click <strong>"Create New Return"</strong></span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-slate-500">3.</span> <span>Select tax period</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-slate-500">4.</span> <span>Enter Input VAT (VAT paid on purchases)</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-slate-500">5.</span> <span>Enter Output VAT (VAT collected from sales)</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-slate-500">6.</span> <span>System calculates VAT payable/refundable</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-slate-500">7.</span> <span>Add notes if needed</span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-slate-500">8.</span> <span>Click <strong>"Save Return"</strong></span></li>
                                            <li class="flex gap-2"><span class="font-semibold text-slate-500">9.</span> <span>Export VAT Form 002 schedule</span></li>
                                        </ol>
                                        <p class="text-xs text-slate-500 mt-4">⏱️ Takes 10-15 minutes</p>
                                    </div>
                                </div>

                                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                    <p class="text-amber-800 text-sm">
                                        <strong>Next Step:</strong> After generating your VAT return in TaxMaster, see the <a href="#government-portals" class="underline font-semibold hover:no-underline">Government Portals & Payment section</a> to learn how to submit to FIRS and make payment.
                                    </p>
                                </div>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <!-- PAYE Filing -->
                            <div>
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-green-100 text-green-700 font-bold text-lg flex items-center justify-center">
                                        PAYE
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">Filing PAYE Returns (Monthly)</h3>
                                        <p class="text-sm text-slate-500">Due by 10th of following month | File with State IRS</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-5">
                                        <h4 class="font-semibold text-slate-900 mb-3 text-sm">Steps in TaxMaster:</h4>
                                        <ol class="space-y-2 text-sm text-slate-700">
                                            <li class="flex gap-2"><span class="font-semibold">1.</span> <span>Go to <strong>PAYE → Staff</strong> and ensure all employees are added with accurate salary details</span></li>
                                            <li class="flex gap-2"><span class="font-semibold">2.</span> <span>Go to <strong>PAYE → Returns</strong> → Click <strong>"Create New Return"</strong></span></li>
                                            <li class="flex gap-2"><span class="font-semibold">3.</span> <span>Or use <strong>AI Workflow → Monthly PAYE</strong> for automatic calculation</span></li>
                                            <li class="flex gap-2"><span class="font-semibold">4.</span> <span>System calculates tax for each employee using progressive rates (7-24%)</span></li>
                                            <li class="flex gap-2"><span class="font-semibold">5.</span> <span>Consolidated Relief Allowance applied automatically (₦200k + 20% of gross)</span></li>
                                            <li class="flex gap-2"><span class="font-semibold">6.</span> <span>Review total PAYE liability</span></li>
                                            <li class="flex gap-2"><span class="font-semibold">7.</span> <span>Export PAYE schedule (shows breakdown per employee)</span></li>
                                        </ol>
                                    </div>

                                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                        <p class="text-amber-800 text-sm">
                                            <strong>Important:</strong> PAYE is filed with your <strong>State IRS</strong>, not FIRS. Lagos businesses file with LIRS, Oyo with OIRS, etc. See <a href="#government-portals" class="underline font-semibold hover:no-underline">Government Portals section</a> for your state's portal link.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <!-- WHT Filing -->
                            <div>
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-700 font-bold text-lg flex items-center justify-center">
                                        WHT
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">Filing WHT Returns (Monthly)</h3>
                                        <p class="text-sm text-slate-500">Due by 21st of following month | File with FIRS</p>
                                    </div>
                                </div>

                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-5">
                                    <h4 class="font-semibold text-slate-900 mb-3 text-sm">Steps in TaxMaster:</h4>
                                    <ol class="space-y-2 text-sm text-slate-700">
                                        <li class="flex gap-2"><span class="font-semibold">1.</span> <span>Go to <strong>WHT → Returns</strong> → Click <strong>"Create New Return"</strong></span></li>
                                        <li class="flex gap-2"><span class="font-semibold">2.</span> <span>Or use <strong>AI Workflow → Monthly WHT</strong></span></li>
                                        <li class="flex gap-2"><span class="font-semibold">3.</span> <span>Select tax period and add WHT deductions</span></li>
                                        <li class="flex gap-2"><span class="font-semibold">4.</span> <span>For each payment, enter: Supplier name, TIN, Amount, Transaction type</span></li>
                                        <li class="flex gap-2"><span class="font-semibold">5.</span> <span>System validates TIN format (11-14 digits)</span></li>
                                        <li class="flex gap-2"><span class="font-semibold">6.</span> <span><strong>Invalid/missing TIN = Double Rate</strong> applied automatically (WHT Regulations 2024)</span></li>
                                        <li class="flex gap-2"><span class="font-semibold">7.</span> <span>WHT calculated based on transaction type (2-10%)</span></li>
                                        <li class="flex gap-2"><span class="font-semibold">8.</span> <span>Export WHT schedule with supplier details</span></li>
                                    </ol>
                                </div>

                                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex gap-3">
                                        <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="font-semibold text-red-900 text-sm mb-1">Double Rate Warning</p>
                                            <p class="text-red-800 text-sm">Always collect valid TINs from suppliers. If a supplier's TIN is invalid or missing, you'll pay <strong>double the WHT rate</strong> (e.g., 5% becomes 10%). This is enforced by FIRS as of 2024.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <!-- CIT Filing -->
                            <div>
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 font-bold text-lg flex items-center justify-center">
                                        CIT
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">Filing CIT Returns (Annual)</h3>
                                        <p class="text-sm text-slate-500">Due 6 months after year-end | File with FIRS</p>
                                    </div>
                                </div>

                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-5">
                                    <h4 class="font-semibold text-slate-900 mb-3 text-sm">Steps in TaxMaster:</h4>
                                    <ol class="space-y-2 text-sm text-slate-700">
                                        <li class="flex gap-2"><span class="font-semibold">1.</span> <span>Prepare your annual financial statements (Profit & Loss, Balance Sheet)</span></li>
                                        <li class="flex gap-2"><span class="font-semibold">2.</span> <span>Go to <strong>CIT → Returns</strong> → Click <strong>"Create New Return"</strong></span></li>
                                        <li class="flex gap-2"><span class="font-semibold">3.</span> <span>Or use <strong>AI Workflow → Annual CIT</strong> for automatic calculation</span></li>
                                        <li class="flex gap-2"><span class="font-semibold">4.</span> <span>Enter: Revenue, Expenses, Taxable Profit</span></li>
                                        <li class="flex gap-2"><span class="font-semibold">5.</span> <span>System applies Finance Act 2019 rates:</span>
                                            <ul class="ml-6 mt-1 space-y-1 text-xs">
                                                <li>→ 0% if turnover &lt; ₦25M</li>
                                                <li>→ 20% if turnover ₦25M - ₦100M</li>
                                                <li>→ 30% if turnover &gt; ₦100M</li>
                                            </ul>
                                        </li>
                                        <li class="flex gap-2"><span class="font-semibold">6.</span> <span>Minimum tax calculated (0.5% of gross revenue if in loss)</span></li>
                                        <li class="flex gap-2"><span class="font-semibold">7.</span> <span>Review total CIT liability</span></li>
                                        <li class="flex gap-2"><span class="font-semibold">8.</span> <span>Export CIT computation and attach financial statements</span></li>
                                    </ol>
                                </div>

                                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-blue-800 text-sm">
                                        <strong>Pro Tip:</strong> For CIT, you'll need to attach audited financial statements (if turnover &gt; ₦25M). After generating your return in TaxMaster, file via FIRS TaxPro-Max portal. See <a href="#government-portals" class="underline font-semibold hover:no-underline">Government Portals section</a>.
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
