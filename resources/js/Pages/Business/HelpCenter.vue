<template>
    <BusinessLayout :title="`Help Center`">
        <div class="max-w-7xl mx-auto py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Help Center</h1>
                <p class="text-gray-600">Everything you need to know about using TaxMaster</p>
            </div>

            <!-- Search Bar -->
            <div class="mb-8">
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search for help articles..."
                        class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <button
                    v-for="link in quickLinks"
                    :key="link.id"
                    @click="scrollToSection(link.id)"
                    class="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-xl hover:border-blue-500 hover:shadow-md transition-all text-left"
                >
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="link.icon" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ link.title }}</h3>
                        <p class="text-sm text-gray-500">{{ link.description }}</p>
                    </div>
                </button>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Table of Contents -->
                <div class="lg:col-span-1">
                    <div class="sticky top-6 bg-white border border-gray-200 rounded-xl p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Contents</h3>
                        <nav class="space-y-2">
                            <a
                                v-for="section in filteredSections"
                                :key="section.id"
                                @click.prevent="scrollToSection(section.id)"
                                :href="`#${section.id}`"
                                class="block text-sm text-gray-600 hover:text-blue-600 py-1 hover:pl-2 transition-all cursor-pointer"
                            >
                                {{ section.title }}
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Help Articles -->
                <div class="lg:col-span-3 space-y-8">
                    <!-- Getting Started -->
                    <section v-if="shouldShowSection('getting-started')" :id="'getting-started'" class="bg-white border border-gray-200 rounded-xl p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">🚀 Getting Started</h2>

                        <div class="space-y-4">
                            <div class="border-l-4 border-blue-500 pl-4">
                                <h3 class="font-semibold text-gray-900 mb-2">Welcome to TaxMaster</h3>
                                <p class="text-gray-600 mb-3">
                                    TaxMaster is your complete Nigerian tax compliance platform. Follow these steps to get started:
                                </p>
                                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                                    <li><strong>Complete Your Business Profile</strong> - Add your company details, TIN, and tax registration info</li>
                                    <li><strong>Link Your Bank Account</strong> - Connect via Mono for automatic transaction sync</li>
                                    <li><strong>Choose Your Subscription Plan</strong> - Select the plan that fits your needs</li>
                                    <li><strong>Add Your Team Members</strong> - Invite staff and accountants to collaborate</li>
                                    <li><strong>File Your First Tax Return</strong> - Start with a simple VAT or PAYE return</li>
                                </ol>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium text-blue-900">Pro Tip</p>
                                        <p class="text-sm text-blue-700">Visit the "Get Started" page in your sidebar for a guided checklist with progress tracking.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Tax Modules -->
                    <section v-if="shouldShowSection('tax-modules')" :id="'tax-modules'" class="bg-white border border-gray-200 rounded-xl p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">💰 Tax Modules</h2>

                        <div class="space-y-6">
                            <!-- VAT -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                    VAT (Value Added Tax)
                                </h3>
                                <p class="text-gray-700 mb-2"><strong>Rate:</strong> 7.5% (Finance Act 2019)</p>
                                <p class="text-gray-600 mb-3">
                                    Track VAT on sales and expenses, file monthly returns, and manage exempt categories.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Features:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• Automatic VAT calculation at 7.5%</li>
                                        <li>• VAT exempt status toggle (for businesses dealing in exempt goods/services)</li>
                                        <li>• 18 FIRS-approved exempt categories (11 goods + 7 services)</li>
                                        <li>• ₦25M turnover exemption threshold</li>
                                        <li>• VAT Form 002 generation</li>
                                        <li>• Monthly return filing</li>
                                    </ul>
                                    <p class="text-sm text-gray-500 mt-3">
                                        <strong>Exempt Categories:</strong> Medical/pharmaceutical, basic food items, books, baby products, agricultural inputs, exported goods, medical services, microfinance services, tuition, and more.
                                    </p>
                                </div>
                            </div>

                            <!-- PAYE -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                    PAYE (Personal Income Tax)
                                </h3>
                                <p class="text-gray-700 mb-2"><strong>Rates:</strong> 7% - 24% (progressive)</p>
                                <p class="text-gray-600 mb-3">
                                    Calculate employee income tax automatically with reliefs and allowances.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Features:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• Progressive tax calculation (7%, 11%, 15%, 19%, 21%, 24%)</li>
                                        <li>• Consolidated Relief Allowance: ₦200,000 + 20% of gross</li>
                                        <li>• Automatic pension/NHF/NSITF deductions</li>
                                        <li>• Employee management</li>
                                        <li>• Monthly remittance tracking</li>
                                        <li>• Bulk payroll processing</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- WHT -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                    WHT (Withholding Tax)
                                </h3>
                                <p class="text-gray-700 mb-2"><strong>Rates:</strong> 2% - 15% (varies by service)</p>
                                <p class="text-gray-600 mb-3">
                                    Record WHT deductions on supplier payments, with automatic double rate for invalid TINs.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Features:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• Rate calculation by transaction type (professional services 5%, dividends 10%, etc.)</li>
                                        <li>• <strong class="text-orange-600">Double Rate Enforcement (WHT Regulations 2024):</strong> If supplier has no TIN or invalid TIN, rate is automatically doubled</li>
                                        <li>• Real-time TIN validation (11-14 digits)</li>
                                        <li>• Orange warning indicators for double rate transactions</li>
                                        <li>• WHT credit tracking</li>
                                        <li>• Monthly return filing</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- CIT -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                                    CIT (Companies Income Tax)
                                </h3>
                                <p class="text-gray-700 mb-2"><strong>Rates:</strong> 0% - 30% (by revenue tier)</p>
                                <p class="text-gray-600 mb-3">
                                    Calculate annual corporate income tax with Finance Act 2019 rates.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Finance Act 2019 Rates:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• <strong>Small companies</strong> (turnover &lt; ₦25M): <strong>0%</strong></li>
                                        <li>• <strong>Medium companies</strong> (₦25M - ₦100M): <strong>20%</strong></li>
                                        <li>• <strong>Large companies</strong> (> ₦100M): <strong>30%</strong></li>
                                    </ul>
                                    <p class="text-sm text-gray-500 mt-3">
                                        Annual filing due 6 months after year-end.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Financial Management -->
                    <section v-if="shouldShowSection('financial-management')" :id="'financial-management'" class="bg-white border border-gray-200 rounded-xl p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">🏦 Financial Management</h2>

                        <div class="space-y-6">
                            <!-- Bank Accounts -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Bank Account Connections</h3>
                                <p class="text-gray-600 mb-3">
                                    Connect your bank accounts via Mono integration for automatic transaction sync.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">How to connect:</p>
                                    <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                                        <li>Go to <strong>Bank Accounts</strong> in the sidebar</li>
                                        <li>Click <strong>"Connect Bank Account"</strong></li>
                                        <li>Select your bank from the list</li>
                                        <li>Enter your internet banking credentials (Mono secure portal)</li>
                                        <li>Authorize TaxMaster to read transactions</li>
                                    </ol>
                                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700">
                                        <strong>🔒 Security:</strong> Your banking credentials are never stored by TaxMaster. All connections are handled securely by Mono.
                                    </div>
                                </div>
                            </div>

                            <!-- Transactions -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Transactions</h3>
                                <p class="text-gray-600 mb-3">
                                    View, categorize, and manage all your business transactions in one place.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Features:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• Automatic sync from connected bank accounts</li>
                                        <li>• Manual transaction entry</li>
                                        <li>• Tax type assignment (VAT_INPUT, VAT_OUTPUT, PAYE, WHT, etc.)</li>
                                        <li>• CSV export for accounting software</li>
                                        <li>• Search and filter by date, amount, category</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Invoicing -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Invoicing</h3>
                                <p class="text-gray-600 mb-3">
                                    Create professional invoices with automatic VAT calculation and tax compliance.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Features:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• Professional invoice templates</li>
                                        <li>• Automatic VAT calculation at 7.5%</li>
                                        <li>• WHT deduction tracking</li>
                                        <li>• PDF generation</li>
                                        <li>• Email delivery to clients</li>
                                        <li>• Payment status tracking</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Reports -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Reports & Exports</h3>
                                <p class="text-gray-600 mb-3">
                                    Generate comprehensive reports for tax filing, audits, and financial analysis.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Available Reports:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• Tax liability summary (all tax types)</li>
                                        <li>• Transaction history by tax type</li>
                                        <li>• VAT Form 002 (PDF/CSV)</li>
                                        <li>• PAYE remittance schedule</li>
                                        <li>• WHT certificate templates</li>
                                        <li>• Financial statements (P&L, Balance Sheet)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Compliance & Filing -->
                    <section v-if="shouldShowSection('compliance-filing')" :id="'compliance-filing'" class="bg-white border border-gray-200 rounded-xl p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">📋 Compliance & Filing</h2>

                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tax Returns</h3>
                                <p class="text-gray-600 mb-3">
                                    File VAT, PAYE, and WHT returns directly from TaxMaster with pre-filled data.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Filing deadlines:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• <strong>VAT:</strong> 21st of following month</li>
                                        <li>• <strong>PAYE:</strong> 10th of following month (remittance)</li>
                                        <li>• <strong>WHT:</strong> 21st of following month</li>
                                        <li>• <strong>CIT:</strong> 6 months after year-end</li>
                                    </ul>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Compliance Calendar</h3>
                                <p class="text-gray-600 mb-3">
                                    Never miss a deadline with automated reminders and calendar view of all tax obligations.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Features:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• Calendar view of all deadlines</li>
                                        <li>• Email reminders 7 days before due date</li>
                                        <li>• Filing status tracking</li>
                                        <li>• Historical compliance records</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium text-amber-900">Penalties</p>
                                        <p class="text-sm text-amber-700">Late filing attracts ₦25,000 (first month) + ₦5,000 each subsequent month for CIT. PAYE late remittance = 10% penalty + interest.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Subscription Plans -->
                    <section v-if="shouldShowSection('subscription-plans')" :id="'subscription-plans'" class="bg-white border border-gray-200 rounded-xl p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">💎 Subscription Plans</h2>

                        <div class="space-y-4">
                            <p class="text-gray-600">
                                TaxMaster offers 4 subscription tiers to suit businesses of all sizes:
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Free -->
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-900 mb-1">Free Plan</h3>
                                    <p class="text-2xl font-bold text-gray-900 mb-3">₦0 <span class="text-sm font-normal text-gray-500">/month</span></p>
                                    <ul class="text-sm text-gray-600 space-y-1">
                                        <li>✓ 1 staff member</li>
                                        <li>✓ 12 tax returns/year</li>
                                        <li>✓ 1GB storage</li>
                                        <li>✓ Basic tax features</li>
                                        <li>✗ No AI analysis</li>
                                        <li>✗ No priority support</li>
                                    </ul>
                                </div>

                                <!-- Basic -->
                                <div class="border border-blue-300 rounded-lg p-4 bg-blue-50">
                                    <h3 class="font-semibold text-blue-900 mb-1">Basic Plan</h3>
                                    <p class="text-2xl font-bold text-blue-900 mb-3">₦5,000 <span class="text-sm font-normal text-blue-600">/month</span></p>
                                    <ul class="text-sm text-blue-700 space-y-1">
                                        <li>✓ 3 staff members</li>
                                        <li>✓ 50 tax returns/year</li>
                                        <li>✓ 10GB storage</li>
                                        <li>✓ All tax features</li>
                                        <li>✓ AI chat assistant</li>
                                        <li>✗ No payment automation</li>
                                    </ul>
                                </div>

                                <!-- Professional -->
                                <div class="border border-purple-300 rounded-lg p-4 bg-purple-50">
                                    <h3 class="font-semibold text-purple-900 mb-1">Professional Plan</h3>
                                    <p class="text-2xl font-bold text-purple-900 mb-3">₦15,000 <span class="text-sm font-normal text-purple-600">/month</span></p>
                                    <ul class="text-sm text-purple-700 space-y-1">
                                        <li>✓ 10 staff members</li>
                                        <li>✓ Unlimited tax returns</li>
                                        <li>✓ 50GB storage</li>
                                        <li>✓ Advanced AI analysis</li>
                                        <li>✓ Payment automation</li>
                                        <li>✓ Priority email support</li>
                                    </ul>
                                </div>

                                <!-- Enterprise -->
                                <div class="border border-gray-800 rounded-lg p-4 bg-gray-900 text-white">
                                    <h3 class="font-semibold mb-1">Enterprise Plan</h3>
                                    <p class="text-2xl font-bold mb-3">₦50,000 <span class="text-sm font-normal text-gray-400">/month</span></p>
                                    <ul class="text-sm text-gray-300 space-y-1">
                                        <li>✓ Unlimited staff</li>
                                        <li>✓ Unlimited tax returns</li>
                                        <li>✓ 500GB storage</li>
                                        <li>✓ Full AI suite</li>
                                        <li>✓ Custom branding</li>
                                        <li>✓ 24/7 priority support</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-sm text-blue-700">
                                    <strong>💡 Tip:</strong> Annual billing saves 20%! Visit <strong>Plans & Billing</strong> in the sidebar to upgrade.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Team & Settings -->
                    <section v-if="shouldShowSection('team-settings')" :id="'team-settings'" class="bg-white border border-gray-200 rounded-xl p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">👥 Team & Settings</h2>

                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Staff Management</h3>
                                <p class="text-gray-600 mb-3">
                                    Invite team members and assign roles for collaboration on tax compliance.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Available roles:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• <strong>Owner:</strong> Full control over business and settings</li>
                                        <li>• <strong>Manager:</strong> Can file returns and manage transactions</li>
                                        <li>• <strong>Staff:</strong> View-only access to tax data</li>
                                        <li>• <strong>Accountant:</strong> External accountant with full access</li>
                                    </ul>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Business Settings</h3>
                                <p class="text-gray-600 mb-3">
                                    Configure your business profile, tax settings, and compliance preferences.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Key settings:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• <strong>Business Profile:</strong> Company name, TIN, registration details</li>
                                        <li>• <strong>VAT Exempt Status:</strong> Toggle if dealing in exempt goods/services (18 FIRS categories)</li>
                                        <li>• <strong>Tax Registrations:</strong> VAT, PAYE, WHT, CIT registration numbers</li>
                                        <li>• <strong>Notification Preferences:</strong> Email alerts for deadlines</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium text-green-900">VAT Exempt Status</p>
                                        <p class="text-sm text-green-700">If your annual turnover is below ₦25M or you deal exclusively in exempt goods/services, enable VAT exempt status in Settings to exclude VAT calculations.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- AI Features -->
                    <section v-if="shouldShowSection('ai-features')" :id="'ai-features'" class="bg-white border border-gray-200 rounded-xl p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">🤖 AI Features</h2>

                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">AI Chat Assistant</h3>
                                <p class="text-gray-600 mb-3">
                                    Ask TaxMaster AI any question about Nigerian tax law, regulations, or your tax obligations.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Example questions:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• "What is the VAT rate in Nigeria?"</li>
                                        <li>• "When is my next PAYE deadline?"</li>
                                        <li>• "How do I calculate WHT on professional services?"</li>
                                        <li>• "What goods are exempt from VAT?"</li>
                                        <li>• "Explain CIT rates for small companies"</li>
                                    </ul>
                                    <p class="text-sm text-gray-500 mt-3">
                                        The AI is trained on CITA, PITA, VAT Act, WHT Regulations 2024, and Finance Acts 2019/2020.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tax Insights</h3>
                                <p class="text-gray-600 mb-3">
                                    Get AI-powered analysis of your tax trends, liabilities, and optimization opportunities.
                                </p>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Available insights:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• Tax liability trends over time</li>
                                        <li>• Compliance score and recommendations</li>
                                        <li>• VAT refund opportunities</li>
                                        <li>• PAYE optimization suggestions</li>
                                        <li>• CIT planning for year-end</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <p class="text-sm text-purple-700">
                                    <strong>🌟 Premium Feature:</strong> AI analysis and insights are available on Basic, Professional, and Enterprise plans.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- FAQs -->
                    <section v-if="shouldShowSection('faqs')" :id="'faqs'" class="bg-white border border-gray-200 rounded-xl p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">❓ Frequently Asked Questions</h2>

                        <div class="space-y-4">
                            <div v-for="(faq, index) in faqs" :key="index" class="border-b border-gray-200 pb-4 last:border-b-0">
                                <button
                                    @click="toggleFaq(index)"
                                    class="flex items-center justify-between w-full text-left"
                                >
                                    <h3 class="font-semibold text-gray-900 pr-4">{{ faq.question }}</h3>
                                    <svg
                                        class="w-5 h-5 text-gray-400 flex-shrink-0 transition-transform"
                                        :class="{ 'rotate-180': faq.open }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div v-if="faq.open" class="mt-3 text-gray-600 text-sm">
                                    {{ faq.answer }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Contact Support -->
                    <section v-if="shouldShowSection('contact-support')" :id="'contact-support'" class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">📞 Need More Help?</h2>

                        <div class="space-y-4">
                            <p class="text-gray-700">
                                Can't find what you're looking for? Our support team is here to help.
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 text-center">
                                    <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <h3 class="font-semibold text-gray-900 mb-1">Email Support</h3>
                                    <p class="text-sm text-gray-600 mb-2">support@taxmaster.ng</p>
                                    <p class="text-xs text-gray-500">Response within 24 hours</p>
                                </div>

                                <div class="bg-white rounded-lg p-4 text-center">
                                    <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                    <h3 class="font-semibold text-gray-900 mb-1">Live Chat</h3>
                                    <p class="text-sm text-gray-600 mb-2">Chat with AI or agent</p>
                                    <p class="text-xs text-gray-500">Available 9am - 5pm WAT</p>
                                </div>

                                <div class="bg-white rounded-lg p-4 text-center">
                                    <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <h3 class="font-semibold text-gray-900 mb-1">Documentation</h3>
                                    <p class="text-sm text-gray-600 mb-2">Detailed guides & tutorials</p>
                                    <p class="text-xs text-gray-500">Updated weekly</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { Link } from '@inertiajs/vue3';

const searchQuery = ref('');

const quickLinks = [
    {
        id: 'getting-started',
        title: 'Getting Started',
        description: 'New to TaxMaster?',
        icon: 'M13 10V3L4 14h7v7l9-11h-7z'
    },
    {
        id: 'tax-modules',
        title: 'Tax Modules',
        description: 'VAT, PAYE, CIT, WHT',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    {
        id: 'subscription-plans',
        title: 'Subscription Plans',
        description: 'Features & pricing',
        icon: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'
    }
];

const sections = [
    { id: 'getting-started', title: '🚀 Getting Started' },
    { id: 'tax-modules', title: '💰 Tax Modules' },
    { id: 'financial-management', title: '🏦 Financial Management' },
    { id: 'compliance-filing', title: '📋 Compliance & Filing' },
    { id: 'subscription-plans', title: '💎 Subscription Plans' },
    { id: 'team-settings', title: '👥 Team & Settings' },
    { id: 'ai-features', title: '🤖 AI Features' },
    { id: 'faqs', title: '❓ FAQs' },
    { id: 'contact-support', title: '📞 Contact Support' }
];

const faqs = ref([
    {
        question: 'How do I enable VAT exempt status?',
        answer: 'Go to Settings in the sidebar, scroll to the VAT Exempt Status section, toggle it on, select your category (e.g., medical services, basic food items), and provide a reason. Your business must either have turnover below ₦25M or deal exclusively in FIRS-approved exempt goods/services.',
        open: false
    },
    {
        question: 'Why is my WHT rate doubled?',
        answer: 'Per WHT Regulations 2024, if a supplier\'s TIN (Tax Identification Number) is missing or invalid (not 11-14 digits), the WHT rate is automatically doubled. For example, professional services normally at 5% become 10%. Ensure your vendors provide valid TINs to avoid double rates.',
        open: false
    },
    {
        question: 'Can I connect multiple bank accounts?',
        answer: 'Yes! You can connect multiple bank accounts from different banks. Each connection is handled securely via Mono. Go to Bank Accounts → Connect Bank Account and repeat for each account you want to link.',
        open: false
    },
    {
        question: 'How do I upgrade my subscription?',
        answer: 'Go to Plans & Billing in the sidebar, choose your desired plan, select monthly or annual billing (annual saves 20%), and complete payment via Paystack. Your upgrade takes effect immediately.',
        open: false
    },
    {
        question: 'What happens if I miss a filing deadline?',
        answer: 'Late filing attracts penalties: CIT = ₦25,000 (first month) + ₦5,000/month thereafter. PAYE late remittance = 10% penalty + interest. TaxMaster sends email reminders 7 days before each deadline to help you avoid penalties.',
        open: false
    },
    {
        question: 'Can my accountant access my TaxMaster account?',
        answer: 'Yes! Go to Staff in the sidebar, click "Invite Team Member", enter your accountant\'s email, and assign the "Accountant" role. They\'ll receive an invitation and get full access to manage your tax compliance.',
        open: false
    },
    {
        question: 'How do I export my transaction data?',
        answer: 'Go to Transactions, use the filters if needed, then click "Export" in the top right. You can export as CSV for use in Excel, QuickBooks, or other accounting software.',
        open: false
    },
    {
        question: 'Is my financial data secure?',
        answer: 'Absolutely. TaxMaster uses bank-level security: SSL encryption for all data, bank connections via Mono (your credentials never touch our servers), SOC 2 compliant infrastructure, and regular security audits. Your data is backed up daily.',
        open: false
    }
]);

const filteredSections = computed(() => {
    if (!searchQuery.value) return sections;

    const query = searchQuery.value.toLowerCase();
    return sections.filter(section =>
        section.title.toLowerCase().includes(query)
    );
});

const shouldShowSection = (sectionId) => {
    if (!searchQuery.value) return true;

    const query = searchQuery.value.toLowerCase();
    const section = sections.find(s => s.id === sectionId);
    return section && section.title.toLowerCase().includes(query);
};

const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

const toggleFaq = (index) => {
    faqs.value[index].open = !faqs.value[index].open;
};
</script>
