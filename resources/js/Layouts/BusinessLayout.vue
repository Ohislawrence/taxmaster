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
                    'fixed bottom-4 sm:bottom-6 left-4 right-4 sm:left-1/2 sm:right-auto sm:-translate-x-1/2 z-[9999] sm:max-w-md w-auto px-4 py-3 rounded-lg shadow-2xl',
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
                        <p class="text-sm font-medium break-words">{{ flashMessage.message }}</p>
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
                    <!-- Mobile Menu Toggle -->
                    <button
                        v-if="auth?.user"
                        @click="showMobileMenu = !showMobileMenu"
                        class="md:hidden p-2 rounded-lg text-gray-700 hover:bg-gray-100"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Logo/Title -->
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <Link href="/business/dashboard" class="text-xl md:text-2xl font-bold text-blue-600">TaxMaster</Link>
                        <span class="hidden sm:inline text-gray-400">|</span>
                        <span class="hidden sm:inline text-gray-600 font-medium">Business Portal</span>
                    </div>

                    <!-- User Menu -->
                    <div v-if="auth?.user" class="flex items-center space-x-2 md:space-x-4">
                        <span class="text-gray-700">{{ auth.user.name }}</span>
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="relative w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center"
                        >
                            {{ auth.user.name.charAt(0).toUpperCase() }}
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-if="showUserMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-50">
                            <Link href="/business/settings" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Settings
                            </Link>
                            <form @submit.prevent="logout">
                                <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                    <div v-else class="flex items-center space-x-4">
                        <Link href="/login" class="text-gray-700 hover:text-blue-600">Login</Link>
                        <Link href="/register" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            Register
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex flex-col md:flex-row">
            <!-- Mobile Menu Drawer -->
            <div
                v-if="showMobileMenu && auth?.user"
                class="md:hidden fixed inset-0 z-40 bg-black bg-opacity-50"
                @click="showMobileMenu = false"
            ></div>

            <!-- Sidebar -->
            <aside
                v-if="auth?.user"
                :class="showMobileMenu ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
                class="fixed md:sticky left-0 top-16 w-64 bg-white shadow-sm transition-transform duration-300 z-40 md:z-auto md:shadow-sm max-h-[calc(100vh-4rem)] md:max-h-screen overflow-y-auto"
            >
                <nav class="px-4 py-6 space-y-2">
                    <!-- Get Started Guide -->
                    <Link
                        href="/business/get-started"
                        :class="isActive('/business/get-started') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        <span>Get Started</span>
                    </Link>

                    <!-- Dashboard -->
                    <Link
                        href="/business/dashboard"
                        :class="isActive('/business/dashboard') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4l4 4m0 0l4-4m-4 4V8" />
                        </svg>
                        <span>Dashboard</span>
                    </Link>

                    <!-- Tax Returns -->
                    <Link
                        href="/business/tax-returns"
                        :class="isActive('/business/tax-returns') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Tax Returns</span>
                    </Link>

                    <!-- Payments -->
                    <Link
                        href="/business/payments"
                        :class="isActive('/business/payments') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Payments</span>
                    </Link>

                    <!-- Staff Management -->
                    <Link
                        href="/business/staff"
                        :class="isActive('/business/staff') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                        class="flex items-center space-x-3 px-4 py-3 rounded transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.593M9 21h6" />
                        </svg>
                        <span>Staff</span>
                    </Link>

                        <!-- Bank Accounts -->
                        <Link
                            href="/business/banks"
                            :class="isActive('/business/banks') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span>Bank Accounts</span>
                        </Link>

                        <!-- Transactions -->
                        <Link
                            href="/business/transactions"
                            :class="isActive('/business/transactions') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Transactions</span>
                        </Link>

                        <!-- Compliance & Deadlines -->
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="text-xs font-semibold text-gray-500 uppercase px-4 py-2">Tax Compliance</p>
                            <Link
                                href="/business/compliance"
                                :class="isActive('/business/compliance') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                                class="flex items-center space-x-3 px-4 py-3 rounded transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Compliance Calendar</span>
                            </Link>
                            <Link
                                href="/business/paye"
                                :class="isActive('/business/paye') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                                class="flex items-center space-x-3 px-4 py-3 rounded transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>PAYE Returns</span>
                            </Link>
                            <Link
                                href="/business/cit"
                                :class="isActive('/business/cit') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                                class="flex items-center space-x-3 px-4 py-3 rounded transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>CIT Returns</span>
                            </Link>
                            <Link
                                href="/business/wht"
                                :class="isActive('/business/wht') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                                class="flex items-center space-x-3 px-4 py-3 rounded transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>WHT Transactions</span>
                            </Link>
                            <Link
                                href="/business/vat"
                                :class="isActive('/business/vat') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                                class="flex items-center space-x-3 px-4 py-3 rounded transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>VAT Returns</span>
                            </Link>
                            <Link
                                href="/business/reports/financial-statements"
                                :class="isActive('/business/reports/financial-statements') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                                class="flex items-center space-x-3 px-4 py-3 rounded transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v4H3V3zm0 6h18v4H3V9zm0 6h18v6H3v-6z" />
                                </svg>
                                <span>Financial Statements</span>
                            </Link>
                            <Link
                                href="/business/reports/cac-forms"
                                :class="isActive('/business/reports/cac-forms') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                                class="flex items-center space-x-3 px-4 py-3 rounded transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h6m-9 5h18a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>CAC Annual Return</span>
                            </Link>
                        </div>

                    <!-- AI & Insights -->
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase px-4 py-2">AI & Intelligence</p>
                        <Link
                            href="/business/ai/insights"
                            :class="isActive('/business/ai/insights') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5h.01M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Tax Insights</span>
                        </Link>
                        <Link
                            href="/business/ai/chat"
                            :class="isActive('/business/ai/chat') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span>AI Chat</span>
                        </Link>
                    </div>

                    <!-- Settings -->
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase px-4 py-2">Account</p>
                        <Link
                            href="/business/settings"
                            :class="isActive('/business/settings') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="flex items-center space-x-3 px-4 py-3 rounded transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5A2.25 2.25 0 008.25 22.5h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 5.25h3m-3 3h3m-3 3h3M9 15.75h.008v.008H9v-.008zm3 0h.008v.008h-.008v-.008zm3 0h.008v.008h-.008v-.008z" />
                            </svg>
                            <span>Settings</span>
                        </Link>
                    </div>
                </nav>
            </aside>

                        <!-- Main Content -->
            <main :class="auth?.user ? 'flex-1 w-full' : 'w-full'">
                <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
                    <div
                        v-if="auth?.user"
                        class="mb-4 sm:mb-6 flex flex-col sm:flex-row sm:items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 px-3 sm:px-4 py-3 text-xs sm:text-sm text-blue-900"
                    >
                        <span
                            class="mt-0.5 inline-flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                            title="AI Automation is active for your business. Transactions can be auto-categorized, compliance reminders are sent, and payment recovery runs in the background. Review suggestions anytime in AI Insights."
                        >
                            i
                        </span>
                        <div>
                            <p class="font-semibold">AI Automation is active</p>
                            <p class="text-blue-800">
                                Transactions can be auto-categorized, compliance reminders are sent, and payment recovery runs in the background.
                                <Link href="/business/ai/insights" class="underline">Review suggestions in AI Insights</Link>.
                            </p>
                        </div>
                    </div>
                    <slot />
                </div>
            </main>
        </div>

        <!-- Feature Upgrade Modal -->
        <div
            v-if="showUpgradeModal"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-[100] p-4"
            @click.self="closeUpgradeModal"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 sm:px-6 py-4 sm:py-6">
                    <h3 class="text-lg sm:text-2xl font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Feature Locked</span>
                    </h3>
                </div>

                <!-- Body -->
                <div class="px-4 sm:px-6 py-4">
                    <p class="text-sm sm:text-base text-gray-600 mb-4">
                        {{ upgradeModalData?.message || 'This feature is not available on your current plan. Please upgrade to access it.' }}
                    </p>
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded mb-4">
                        <p class="text-xs sm:text-sm text-blue-800">
                            <strong>Feature:</strong> {{ formatFeatureName(upgradeModalData?.feature || 'unknown') }}
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-4 sm:px-6 py-4 flex flex-col sm:flex-row gap-3">
                    <button
                        @click="closeUpgradeModal"
                        class="w-full order-2 sm:order-1 sm:flex-1 px-4 py-2 text-center text-sm bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium"
                    >
                        Go Back
                    </button>
                    <Link
                        :href="route('business.plans.index')"
                        class="w-full order-1 sm:order-2 sm:flex-1 px-4 py-2 text-center text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                    >
                        View Plans
                    </Link>
                </div>
            </div>
        </div>

        <!-- Floating AI Chat Widget -->
        <TaxMasterChat v-if="auth?.user" />

        <!-- Cookie Consent Banner -->
        <CookieConsent />
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watchEffect } from 'vue';
import { usePage } from '@inertiajs/vue3';
import TaxMasterChat from '@/Components/TaxMasterChat.vue';
import CookieConsent from '@/Components/CookieConsent.vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const showUserMenu = ref(false);
const showMobileMenu = ref(false);
const showUpgradeModal = ref(false);
const upgradeModalData = ref(null);
const flashMessage = ref(null);
let flashTimeout = null;

/**
 * Show flash message
 */
const showFlash = (type, message) => {
    console.log('Flash message triggered:', { type, message });

    if (flashTimeout) {
        clearTimeout(flashTimeout);
    }

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

// Watch for flash data changes
watchEffect(() => {
    console.log('Page props changed:', {
        flash: page.props.flash,
        allProps: Object.keys(page.props)
    });

    if (page.props.flash?.upgrade_modal?.show) {
        upgradeModalData.value = page.props.flash.upgrade_modal;
        showUpgradeModal.value = true;
    }

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

// Close mobile menu on route change
watchEffect(() => {
    showMobileMenu.value = false;
});

const isActive = (route) => {
    return window.location.pathname.startsWith(route);
};

const logout = () => {
    router.post(route('logout'));
};

/**
 * Format feature name for display
 */
const formatFeatureName = (feature) => {
    if (!feature) return 'Unknown Feature';
    return feature
        .replace(/_/g, ' ')
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

/**
 * Close upgrade modal
 */
const closeUpgradeModal = () => {
    showUpgradeModal.value = false;
};
</script>
