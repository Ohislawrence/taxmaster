<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Flash Messages -->
        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-[50px]"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="flashMessage"
                :class="[
                    'fixed bottom-6 left-1/2 transform -translate-x-1/2 z-[9999] max-w-md w-full mx-4 px-4 py-3 rounded-lg shadow-2xl',
                    flashMessage.type === 'success' ? 'bg-green-50 text-green-900 border-2 border-green-300' : '',
                    flashMessage.type === 'error' ? 'bg-red-50 text-red-900 border-2 border-red-300' : '',
                    flashMessage.type === 'warning' ? 'bg-yellow-50 text-yellow-900 border-2 border-yellow-300' : '',
                    flashMessage.type === 'info' ? 'bg-blue-50 text-blue-900 border-2 border-blue-300' : ''
                ]"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-2 flex-1">
                        <svg v-if="flashMessage.type === 'success'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <svg v-if="flashMessage.type === 'error'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <svg v-if="flashMessage.type === 'warning'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <svg v-if="flashMessage.type === 'info'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium">{{ flashMessage.message }}</p>
                    </div>
                    <button
                        @click="dismissFlash"
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </transition>

        <!-- Navigation Header -->
        <nav class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo/Title -->
                    <div class="flex items-center space-x-4">
                        <Link href="/admin/dashboard" class="text-2xl font-bold text-blue-600">TaxMaster</Link>
                        <span class="text-gray-400">|</span>
                        <span class="text-gray-600 font-medium">Admin Dashboard</span>
                    </div>

                                        <!-- User Menu -->
                    <div v-if="auth?.user" class="flex items-center space-x-4">
                        <span class="text-gray-700">{{ auth.user.name }}</span>
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="relative w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center"
                        >
                            {{ auth.user.name.charAt(0).toUpperCase() }}
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-if="showUserMenu" class="absolute right-0 mt-48 w-48 bg-white rounded-lg shadow-lg z-50">
                            <Link href="/admin/profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Profile Settings
                            </Link>
                            <form @submit.prevent="logout">
                                <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex">
            <!-- Sidebar -->
            <aside class="w-64 bg-white shadow-sm min-h-screen sticky top-16">
                <nav class="px-4 py-6 space-y-2">
                    <!-- Dashboard -->
                    <Link
                        href="/admin/dashboard"
                        :class="isActive('/admin/dashboard') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4l4 4m0 0l4-4m-4 4V8" />
                        </svg>
                        <span>Dashboard</span>
                    </Link>

                    <!-- Users Management -->
                    <Link
                        href="/admin/users"
                        :class="isActive('/admin/users') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.593M9 21h6" />
                        </svg>
                        <span>Users</span>
                    </Link>

                    <!-- Businesses Management -->
                    <Link
                        href="/admin/businesses"
                        :class="isActive('/admin/businesses') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5.581m0 0H9m5.581 0a2 2 0 100-4H9m0 4a2 2 0 110-4m0 0H7a2 2 0 00-2 2v3m2-3V7a2 2 0 012-2h5.581a2 2 0 011.915 1.264m0 0H20" />
                        </svg>
                        <span>Businesses</span>
                    </Link>

                    <!-- Subscriptions -->
                    <Link
                        href="/admin/subscriptions"
                        :class="isActive('/admin/subscriptions') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Subscriptions</span>
                    </Link>

                    <!-- Plans Management -->
                    <Link
                        href="/admin/plans"
                        :class="isActive('/admin/plans') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span>Plans</span>
                    </Link>

                    <!-- Blog Management -->
                    <Link
                        href="/admin/blog"
                        :class="isActive('/admin/blog') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Blog</span>
                    </Link>

                    <!-- AI Settings -->
                    <Link
                        href="/admin/ai-settings"
                        :class="isActive('/admin/ai-settings') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5h.01M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>AI Settings</span>
                    </Link>

                    <!-- Phase 1: Bank & Transaction Management -->
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase px-4 py-2">Business Management</p>
                        <Link
                            href="/admin/bank-accounts"
                            :class="isActive('/admin/bank-accounts') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span>Bank Accounts</span>
                        </Link>
                        <Link
                            href="/admin/transactions"
                            :class="isActive('/admin/transactions') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                            <span>Transactions</span>
                        </Link>
                        <Link
                            href="/admin/compliance"
                            :class="isActive('/admin/compliance') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Compliance</span>
                        </Link>
                        <Link
                            href="/admin/vat-returns"
                            :class="isActive('/admin/vat-returns') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>VAT Returns</span>
                        </Link>
                        <!-- Phase 2 PAYE Management -->
                        <Link
                            href="/admin/paye"
                            :class="isActive('/admin/paye') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>PAYE Dashboard</span>
                        </Link>

                        <Link
                            href="/admin/wht-returns"
                            :class="isActive('/admin/wht-returns') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>WHT Transactions</span>
                        </Link>
                    </div>

                    <!-- System Management -->
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase px-4 py-2">System Management</p>
                        <Link
                            href="/admin/invoices"
                            :class="isActive('/admin/invoices') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Invoices</span>
                        </Link>
                        <Link
                            href="/admin/sync-failures"
                            :class="isActive('/admin/sync-failures') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 -12a9 9 0 110 18 9 9 0 010-18zm-1 15h2v2h-2v-2zm0 -10h2v4h-2v-4z" />
                            </svg>
                            <span>Sync Failures</span>
                        </Link>
                        <Link
                            href="/admin/ai-automation"
                            :class="isActive('/admin/ai-automation') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>AI Automation</span>
                        </Link>
                        <Link
                            href="/admin/backups"
                            :class="isActive('/admin/backups') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Backups</span>
                        </Link>
                    </div>

                    <!-- Reports -->
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase px-4 py-2">Reports</p>
                        <Link
                            href="/admin/compliance/reports/overdue"
                            :class="isActive('/admin/compliance/reports/overdue') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Overdue Compliance</span>
                        </Link>
                        <Link
                            href="/admin/vat-returns/reports/revenue"
                            :class="isActive('/admin/vat-returns/reports/revenue') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8L5.257 19.393A2 2 0 005 18.21V4a2 2 0 012-2h10a2 2 0 012 2v14.211a2 2 0 01-.757 1.563z" />
                            </svg>
                            <span>VAT Revenue Report</span>
                        </Link>
                        <Link
                            href="/admin/reports/tax"
                            :class="isActive('/admin/reports/tax') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m-6 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Tax Report</span>
                        </Link>
                        <Link
                            href="/admin/reports/payments"
                            :class="isActive('/admin/reports/payments') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Payment Report</span>
                        </Link>
                        <Link
                            href="/admin/reports/revenue"
                            :class="isActive('/admin/reports/revenue') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8L5.257 19.393A2 2 0 005 18.21V4a2 2 0 012-2h10a2 2 0 012 2v14.211a2 2 0 01-.757 1.563z" />
                            </svg>
                            <span>Revenue Report</span>
                        </Link>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watchEffect } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const auth = computed(() => page.props.auth);
const showUserMenu = ref(false);
const flashMessage = ref(null);
let flashTimeout = null;




// Watch for flash data changes
watchEffect(() => {
    // Handle flash messages
    if (page.props.flash?.success) {
        showFlash('success', page.props.flash.success);
    } else if (page.props.flash?.error) {
        showFlash('error', page.props.flash.error);
    } else if (page.props.flash?.warning) {
        showFlash('warning', page.props.flash.warning);
    } else if (page.props.flash?.info) {
        showFlash('info', page.props.flash.info);
    } else if (page.props.flash?.message) {
        showFlash('info', page.props.flash.message);
    }
});

const isActive = (route) => {
    return window.location.pathname.startsWith(route);
};

const logout = () => {
    router.post(route('logout'));
};

/**
 * Show flash message
 */
const showFlash = (type, message) => {
    console.log('Flash message triggered:', { type, message });

    flashMessage.value = { type, message };

    // Auto-dismiss after 5 seconds
    flashTimeout = setTimeout(() => {
        flashMessage.value = null;
    }, 5000);
};

/**
 * Dismiss flash message manually
 */
const dismissFlash = () => {
    if (flashTimeout) {
        clearTimeout(flashTimeout);
    }
    flashMessage.value = null;
};
</script>
