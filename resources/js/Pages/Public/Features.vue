<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const activeTab = ref('ai');

const features = {
  ai: {
    label: 'AI Automation',
    title: 'Your AI tax team—no accountant needed',
    description: 'Click once, AI analyzes your transactions and generates complete tax returns. VAT, PAYE, WHT, and CIT calculated automatically with 95%+ accuracy. Perfect for basic compliance without hiring expensive accountants.',
    icon: 'robot',
    color: 'indigo',
    capabilities: [
      { title: 'One-click automation', desc: 'AI does all calculations instantly' },
      { title: 'Transaction analysis', desc: 'Scans bank data automatically' },
      { title: 'Multi-tax support', desc: 'VAT, PAYE, WHT, CIT in one click' },
      { title: 'Confidence scoring', desc: 'Shows calculation certainty' },
      { title: 'Draft returns', desc: 'Review before submission' },
      { title: 'No accountant fees', desc: 'Save thousands monthly' },
    ],
    stat: '95%+',
    statLabel: 'Accuracy rate'
  },
  paye: {
    label: 'PAYE',
    title: 'Payroll taxes, automated',
    description: 'Calculate employee PAYE using current PITA bands, route to correct State SIRS, and generate filing-ready schedules.',
    icon: 'users',
    color: 'blue',
    capabilities: [
      { title: 'Bulk staff import', desc: 'CSV upload with validation' },
      { title: 'Multi-state routing', desc: 'Auto-detect SIRS jurisdiction' },
      { title: 'PITA band updates', desc: 'Always current tax rates' },
      { title: 'Schedule export', desc: 'CSV/PDF for any platform' },
    ],
    stat: '97%',
    statLabel: 'Calculation accuracy'
  },
  vat: {
    label: 'VAT',
    title: 'Track every naira of VAT',
    description: 'Input and output VAT reconciliation with auto-generated returns and audit-ready supporting schedules.',
    icon: 'calculator',
    color: 'green',
    capabilities: [
      { title: 'Transaction mapping', desc: 'Auto-link invoices to VAT' },
      { title: 'Input/output tracking', desc: 'Real-time position view' },
      { title: 'Return generation', desc: 'Filing-ready schedules' },
      { title: 'Audit support', desc: 'Complete documentation' },
    ],
    stat: '12hrs',
    statLabel: 'Saved monthly'
  },
  wht: {
    label: 'WHT',
    title: 'Withholding tax, sorted',
    description: 'Calculate WHT across all categories, identify correct authority, and prepare bulk remittance files.',
    icon: 'document',
    color: 'amber',
    capabilities: [
      { title: 'Category detection', desc: 'Contracts, rent, dividends' },
      { title: 'Authority routing', desc: 'FIRS vs State IRS logic' },
      { title: 'Bulk exports', desc: 'Ready for remittance' },
      { title: 'Beneficiary tracking', desc: 'TIN validation' },
    ],
    stat: '500+',
    statLabel: 'WHT categories'
  },
  cit: {
    label: 'CIT',
    title: 'Corporate tax preparation',
    description: 'Generate CIT computations, capital allowance schedules, and supporting documents for filing.',
    icon: 'building',
    color: 'purple',
    capabilities: [
      { title: 'Tax computation', desc: 'Assessable profit calc' },
      { title: 'Capital allowances', desc: 'Automated schedule' },
      { title: 'Document export', desc: 'Advisor-ready files' },
      { title: 'Year-on-year', desc: 'Comparative analysis' },
    ],
    stat: '3hrs',
    statLabel: 'Vs 2 days manual'
  },
  einvoice: {
    label: 'E-Invoicing',
    title: 'FIRS-compliant invoicing',
    description: 'Create UBL 2.1 invoices with digital signatures. Export for manual submission now; automated FIRS API submission coming Q3 2026.',
    icon: 'document-text',
    color: 'indigo',
    capabilities: [
      { title: 'UBL 2.1 format', desc: 'FIRS-compliant invoices' },
      { title: 'TIN validation', desc: 'Live buyer TIN check' },
      { title: 'Digital signatures', desc: 'ECDSA cryptographic signing' },
      { title: 'Export ready', desc: 'UBL XML/JSON download' },
      { title: 'Professional PDFs', desc: 'Client-ready invoices' },
      { title: 'API submission', desc: 'Coming Q3 2026' },
    ],
    stat: '100%',
    statLabel: 'FIRS compliance'
  }
};

const integrations = [
  { name: 'Mono', category: 'Banking', status: 'Live', description: 'Transaction import & categorization' },
  { name: 'QuickBooks Online', category: 'Accounting', status: 'Live', description: 'Sync invoices, bills, customers & vendors' },
  { name: 'Zoho Books', category: 'Accounting', status: 'Live', description: 'Multi-datacenter accounting integration' },
  { name: 'Shopify', category: 'E-commerce', status: 'Live', description: 'Sync orders and products for Nigerian tax compliance' },
  { name: 'Sage Business Cloud', category: 'Accounting', status: 'Coming Soon', description: 'Full accounting data sync' },
  { name: 'Xero', category: 'Accounting', status: 'Coming Soon', description: 'Complete accounting integration' },
  { name: 'FIRS E-Invoicing', category: 'Invoicing', status: 'Q3 2026', description: 'UBL 2.1 invoice API submission (export available now)' },
  { name: 'FIRS TaxPro-Max', category: 'Filing', status: 'Export', description: 'Generate schedules for manual upload' },
  { name: 'State IRS', category: 'Filing', status: 'Export', description: 'LIRS, OIRS, multi-state support' },
];

const securityFeatures = [
  { icon: 'shield', title: 'NDPA Compliant', desc: 'Full data protection regulation adherence' },
  { icon: 'lock', title: 'Encrypted TINs', desc: 'AES-256 encryption for sensitive fields' },
  { icon: 'eye', title: 'Audit Trails', desc: 'Complete history of every calculation' },
  { icon: 'users', title: 'Role-Based Access', desc: 'Granular permissions per entity' },
];

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
    document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
  });
});
</script>

<template>
  <!-- Hero Section - UNCHANGED -->
  <section class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-32 sm:pb-16 lg:pt-36 lg:pb-24">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
      <div class="mx-auto max-w-2xl text-center">
        <h1 class="reveal reveal-up text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl lg:text-[3.5rem] lg:leading-[1.1]">Features</h1>
        <p class="reveal reveal-up mt-4 text-base leading-relaxed text-gray-500 sm:mt-6 sm:text-lg">AI-powered tax automation plus practical tools for Nigerian businesses. Click once to calculate taxes, or use individual features for full control. Your choice—automated or manual.</p>
        <p class="reveal reveal-up mt-4 text-sm text-gray-400">Note: AI generates filing-ready returns automatically. Manual remittance is required; programmatic remittance integrations are planned.</p>
      </div>
    </div>
  </section>

  <!-- Interactive Feature Tabs - Modern Mono Style -->
  <section class="py-16 sm:py-20 lg:py-28 bg-gray-50/50 relative overflow-hidden">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#f8f8f8_1px,transparent_1px),linear-gradient(to_bottom,#f8f8f8_1px,transparent_1px)] bg-[size:8rem_8rem]"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">

      <!-- Tab Navigation - Floating Pill -->
      <div class="reveal reveal-up flex justify-center mb-12">
        <div class="inline-flex p-1 bg-white rounded-full shadow-sm border border-gray-200">
          <button
            v-for="(feature, key) in features"
            :key="key"
            @click="activeTab = key"
            class="px-5 py-2.5 rounded-full text-sm font-medium transition-all duration-300"
            :class="activeTab === key ? 'bg-gray-900 text-white shadow-md' : 'text-gray-600 hover:text-gray-900'"
          >
            {{ feature.label }}
          </button>
        </div>
      </div>

      <!-- Active Feature Content -->
      <div class="reveal reveal-up">
        <transition
          enter-active-class="transition-all duration-500 ease-out"
          enter-from-class="opacity-0 translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-300 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
          mode="out-in"
        >
          <div :key="activeTab" class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left: Content -->
            <div>
              <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider mb-4"
                :class="`bg-${features[activeTab].color}-100 text-${features[activeTab].color}-700`"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path v-if="features[activeTab].icon === 'robot'" stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                  <path v-else-if="features[activeTab].icon === 'users'" stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path v-else-if="features[activeTab].icon === 'calculator'" stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  <path v-else-if="features[activeTab].icon === 'document'" stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                {{ features[activeTab].label }}
              </div>

              <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">{{ features[activeTab].title }}</h2>
              <p class="mt-4 text-lg text-gray-600 leading-relaxed">{{ features[activeTab].description }}</p>

              <!-- Capabilities Grid -->
              <div class="mt-8 grid sm:grid-cols-2 gap-4">
                <div
                  v-for="cap in features[activeTab].capabilities"
                  :key="cap.title"
                  class="group p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-gray-200 transition-all"
                >
                  <h4 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ cap.title }}</h4>
                  <p class="mt-1 text-sm text-gray-500">{{ cap.desc }}</p>
                </div>
              </div>

              <!-- Stat Highlight -->
              <div class="mt-8 flex items-center gap-4">
                <div
                  class="h-16 w-16 rounded-2xl flex items-center justify-center text-2xl font-bold"
                  :class="`bg-${features[activeTab].color}-100 text-${features[activeTab].color}-700`"
                >
                  {{ features[activeTab].stat }}
                </div>
                <div>
                  <p class="text-sm text-gray-500">{{ features[activeTab].statLabel }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">Based on customer data</p>
                </div>
              </div>
            </div>

            <!-- Right: Visual/Illustration -->
            <div class="relative">
              <div
                class="absolute -inset-4 rounded-3xl opacity-30 blur-2xl"
                :class="`bg-${features[activeTab].color}-200`"
              ></div>
              <div class="relative bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                <!-- Mock UI based on feature -->
                <div v-if="activeTab === 'paye'" class="space-y-3">
                  <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-semibold text-gray-900">October 2025 PAYE</span>
                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">Ready</span>
                  </div>
                  <div v-for="emp in [{name:'Adebayo O.', tax:'₦45,200'}, {name:'Chioma N.', tax:'₦38,100'}, {name:'Ibrahim M.', tax:'₦52,800'}]" :key="emp.name" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                      <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">{{ emp.name.split(' ').map(n=>n[0]).join('') }}</div>
                      <span class="text-sm font-medium text-gray-900">{{ emp.name }}</span>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">{{ emp.tax }}</span>
                  </div>
                  <div class="mt-4 p-3 bg-gray-900 rounded-lg text-white text-center text-sm font-medium">
                    Download PAYE Schedule →
                  </div>
                </div>

                <div v-else-if="activeTab === 'vat'" class="space-y-3">
                  <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="p-4 bg-blue-50 rounded-xl">
                      <p class="text-xs text-blue-600 font-medium uppercase">Output VAT</p>
                      <p class="text-xl font-bold text-blue-900 mt-1">₦1.2M</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                      <p class="text-xs text-gray-500 font-medium uppercase">Input VAT</p>
                      <p class="text-xl font-bold text-gray-900 mt-1">₦225K</p>
                    </div>
                  </div>
                  <div class="p-4 bg-green-50 rounded-xl border border-green-100">
                    <div class="flex justify-between items-center">
                      <span class="text-sm font-medium text-green-900">Net VAT Payable</span>
                      <span class="text-lg font-bold text-green-900">₦975K</span>
                    </div>
                  </div>
                  <div class="mt-3 flex gap-2">
                    <button class="flex-1 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium">Export Return</button>
                    <button class="flex-1 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700">View Details</button>
                  </div>
                </div>

                <div v-else-if="activeTab === 'wht'" class="space-y-3">
                  <div v-for="wht in [{type:'Contract', rate:'5%', amt:'₦62.5K'}, {type:'Rent', rate:'10%', amt:'₦34.5K'}, {type:'Dividend', rate:'10%', amt:'₦50K'}]" :key="wht.type" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ wht.type }}</p>
                      <p class="text-xs text-gray-500">{{ wht.rate }} rate</p>
                    </div>
                    <span class="text-sm font-bold text-gray-900">{{ wht.amt }}</span>
                  </div>
                  <div class="p-3 bg-amber-50 rounded-lg border border-amber-100">
                    <p class="text-xs text-amber-800 font-medium">3 beneficiaries pending TIN verification</p>
                  </div>
                </div>

                <div v-else class="space-y-4">
                  <div class="p-4 bg-purple-50 rounded-xl">
                    <p class="text-xs text-purple-600 font-medium uppercase">Assessable Profit</p>
                    <p class="text-2xl font-bold text-purple-900 mt-1">₦12.4M</p>
                  </div>
                  <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600">Capital Allowances</span>
                      <span class="font-medium text-gray-900">-₦2.1M</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600">Taxable Profit</span>
                      <span class="font-medium text-gray-900">₦10.3M</span>
                    </div>
                    <div class="h-px bg-gray-200 my-2"></div>
                    <div class="flex justify-between text-sm font-semibold">
                      <span class="text-gray-900">Tax Payable (30%)</span>
                      <span class="text-purple-900">₦3.09M</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </section>

  <!-- Integration Ecosystem - Clean Grid -->
  <section class="py-16 sm:py-20 lg:py-28 bg-white relative">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="reveal reveal-up max-w-2xl mb-12">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Integrations</p>
        <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl">Connected to your workflow</h2>
        <p class="mt-4 text-gray-600">Export to government platforms and import from your financial stack.</p>
      </div>

      <div class="reveal reveal-up grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="integration in integrations"
          :key="integration.name"
          class="group p-6 bg-gray-50 rounded-2xl border border-gray-100 hover:bg-white hover:shadow-lg hover:border-gray-200 transition-all duration-300"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="h-10 w-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-sm font-bold text-gray-400 group-hover:text-gray-900 transition-colors">
              {{ integration.name.slice(0,2) }}
            </div>
            <span
              class="px-2 py-1 rounded-full text-xs font-medium"
              :class="integration.status === 'Live' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
            >
              {{ integration.status }}
            </span>
          </div>
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">{{ integration.category }}</p>
          <h4 class="font-semibold text-gray-900 mb-2">{{ integration.name }}</h4>
          <p class="text-sm text-gray-600">{{ integration.description }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Security & Compliance - Dark Section -->
  <section class="py-16 sm:py-20 lg:py-28 bg-gray-950 relative overflow-hidden">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
      <div class="reveal reveal-up max-w-2xl mb-12">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-400">Security</p>
        <h2 class="mt-3 text-2xl font-bold text-white sm:text-3xl">Built for trust</h2>
        <p class="mt-4 text-gray-400">NDPA-compliant infrastructure with enterprise-grade security.</p>
      </div>

      <div class="reveal reveal-up grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="feature in securityFeatures"
          :key="feature.title"
          class="p-6 bg-gray-900/50 rounded-2xl border border-gray-800 hover:border-gray-700 transition-colors"
        >
          <div class="h-12 w-12 rounded-xl bg-gray-800 flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path v-if="feature.icon === 'shield'" stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              <path v-else-if="feature.icon === 'lock'" stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              <path v-else-if="feature.icon === 'eye'" stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <h4 class="font-semibold text-white mb-2">{{ feature.title }}</h4>
          <p class="text-sm text-gray-400">{{ feature.desc }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works - Timeline -->
  <section class="py-16 sm:py-20 lg:py-28 bg-gray-50/50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="reveal reveal-up max-w-2xl mx-auto text-center mb-16">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Process</p>
        <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl">From data to filing</h2>
      </div>

      <div class="reveal reveal-up relative">
        <!-- Connection line -->
        <div class="absolute left-8 top-0 bottom-0 w-px bg-gray-200 hidden lg:block"></div>

        <div class="space-y-12 lg:space-y-0 lg:grid lg:grid-cols-3 lg:gap-8">
          <div v-for="(step, i) in [
            {num: '01', title: 'Import', desc: 'Connect bank via Mono or upload transactions. Bulk import staff via CSV.'},
            {num: '02', title: 'Calculate', desc: 'Auto-compute PAYE, VAT, WHT using current Nigerian tax laws and rates.'},
            {num: '03', title: 'Export', desc: 'Download filing-ready schedules. Upload to TaxPro-Max or State IRS. Track payment.'}
          ]" :key="i" class="relative lg:pl-16">
            <div class="hidden lg:flex absolute left-0 top-0 h-16 w-16 rounded-full bg-white border-2 border-gray-200 items-center justify-center text-lg font-bold text-gray-900 shadow-sm">
              {{ step.num }}
            </div>
            <div class="lg:hidden mb-4">
              <span class="inline-flex h-10 w-10 rounded-full bg-gray-900 text-white items-center justify-center text-sm font-bold">{{ step.num }}</span>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ step.title }}</h3>
            <p class="text-gray-600 leading-relaxed">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Roadmap - Minimal -->
  <section class="py-16 sm:py-20 bg-white border-t border-gray-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="reveal reveal-up flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
        <div class="max-w-xl">
          <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Roadmap</p>
          <h2 class="mt-3 text-2xl font-bold text-gray-900">Toward full automation</h2>
          <p class="mt-4 text-gray-600">Programmatic filing and payment integrations are in development. Today, TaxMaster eliminates 90% of manual preparation work.</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <span class="px-4 py-2 bg-gray-100 rounded-full text-sm font-medium text-gray-700">Programmatic FIRS API</span>
          <span class="px-4 py-2 bg-gray-100 rounded-full text-sm font-medium text-gray-700">Auto-RRR Generation</span>
          <span class="px-4 py-2 bg-gray-100 rounded-full text-sm font-medium text-gray-700">Background Filing</span>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="py-16 sm:py-20 bg-gray-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
      <div class="reveal reveal-up">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Ready to simplify compliance?</h2>
        <p class="mt-4 text-gray-600 max-w-xl mx-auto">Join 1,200+ Nigerian businesses using TaxMaster to prepare and file taxes faster.</p>
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
          <Link :href="route('register')" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-6 py-3 text-sm font-semibold text-white hover:bg-black transition-colors">
            Get started free
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
          </Link>
          <Link href="/contact" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:border-gray-300 transition-colors">
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
