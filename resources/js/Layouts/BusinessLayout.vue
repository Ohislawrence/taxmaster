<template>
    <div class="min-h-screen bg-[#f9fafc]">
        <!-- Flash Messages - Modern Toast Style -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="flashMessage"
                :class="[
                    'fixed bottom-4 right-4 z-[9999] w-auto max-w-md',
                    'px-4 py-3 rounded-xl shadow-lg border backdrop-blur-sm',
                    flashMessage.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : '',
                    flashMessage.type === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : '',
                    flashMessage.type === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' : '',
                    flashMessage.type === 'info' ? 'bg-sky-50 border-sky-200 text-sky-800' : ''
                ]"
            >
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <svg v-if="flashMessage.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else-if="flashMessage.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else-if="flashMessage.type === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium flex-1">{{ flashMessage.message }}</p>
                    <button @click="dismissFlash" class="flex-shrink-0 hover:opacity-75">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </transition>

        <!-- Navigation Header -->
        <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200/50 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16 lg:h-20">
                    <!-- Mobile Menu Toggle -->
                    <button
                        v-if="auth?.user"
                        @click="showMobileMenu = !showMobileMenu"
                        class="lg:hidden p-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100/50 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Logo/Title -->
                    <div class="flex items-center gap-2 lg:gap-4">
                        <Link href="/business/dashboard" class="flex items-center gap-2">
                            <img src="/taxmaster-one.png" alt="TaxMaster" class="h-7 lg:h-8 w-auto" />
                            <span class="text-sm lg:text-base font-medium text-gray-600">Business Portal</span>
                        </Link>
                    </div>

                    <!-- User Menu -->
                    <div v-if="auth?.user" class="flex items-center gap-2 lg:gap-4">
                        <!-- User Menu -->
                        <div class="relative user-menu-container">
                            <button
                                @click.stop="showUserMenu = !showUserMenu"
                                class="flex items-center gap-3 px-2 py-1.5 rounded-xl hover:bg-gray-50 transition-colors"
                            >
                                <div class="hidden sm:block text-right">
                                    <p class="text-sm font-medium text-gray-700">{{ auth.user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth.user.email }}</p>
                                </div>
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-semibold flex items-center justify-center shadow-sm">
                                    {{ auth.user.name.charAt(0).toUpperCase() }}
                                </div>
                            </button>

                            <!-- Dropdown Menu -->
                            <transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-1"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 translate-y-1"
                            >
                                <div v-if="showUserMenu" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50">
                                    <Link :href="route('profile.show')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.341A8 8 0 116.659 6.572" />
                                        </svg>
                                        Profile
                                    </Link>
                                    <Link href="/business/settings" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Settings
                                    </Link>
                                    <form @submit.prevent="logout">
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </transition>
                        </div>
                    </div>
                    <div v-else class="flex items-center gap-3">
                        <Link href="/login" class="text-sm font-medium text-gray-600 hover:text-gray-900 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                            Sign in
                        </Link>
                        <Link href="/register" class="text-sm font-medium bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                            Register
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex flex-col lg:flex-row">
            <!-- Mobile Menu Overlay -->
            <transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showMobileMenu && auth?.user"
                    class="lg:hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-40"
                    @click="showMobileMenu = false"
                ></div>
            </transition>

            <!-- Sidebar -->
            <aside
                v-if="auth?.user"
                :class="[
                    'fixed lg:sticky left-0 top-16 lg:top-20 h-[calc(100vh-4rem)] lg:h-[calc(100vh-5rem)] w-72 bg-white border-r border-gray-200/50 transition-transform duration-300 z-40 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-transparent',
                    showMobileMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
                ]"
            >
                <nav class="px-3 py-6">
                    <!-- Main Navigation -->
                    <div class="space-y-1">
                        <!-- Get Started Guide -->
                        <Link
                            href="/business/get-started"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/business/get-started')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/business/get-started') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            <span>Get Started</span>
                        </Link>

                        <!-- Dashboard -->
                        <Link
                            href="/business/dashboard"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/business/dashboard')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/business/dashboard') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Dashboard</span>
                        </Link>

                        <!-- Tax Returns -->
                        <Link
                            href="/business/tax-returns"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/business/tax-returns')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/business/tax-returns') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Tax Returns</span>
                        </Link>

                        <!-- Payments -->
                        <Link
                            href="/business/payments"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/business/payments')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/business/payments') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Payments</span>
                        </Link>

                        <!-- Staff Management -->
                        <Link
                            href="/business/staff"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/business/staff')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/business/staff') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.593M9 21h6" />
                            </svg>
                            <span>Staff</span>
                        </Link>

                        <!-- Bank Accounts -->
                        <Link
                            href="/business/banks"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/business/banks')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/business/banks') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span>Bank Accounts</span>
                        </Link>

                        <!-- Transactions -->
                        <Link
                            href="/business/transactions"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/business/transactions')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/business/transactions') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Transactions</span>
                        </Link>
                    </div>

                    <!-- Tax Compliance -->
                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Tax Compliance</p>
                        <div class="space-y-1">
                            <Link
                                href="/business/compliance"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/business/compliance')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/business/compliance') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Compliance Calendar</span>
                            </Link>
                            <Link
                                href="/business/paye"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/business/paye')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/business/paye') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>PAYE Returns</span>
                            </Link>
                            <Link
                                href="/business/cit"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/business/cit')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/business/cit') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>CIT Returns</span>
                            </Link>
                            <Link
                                href="/business/wht"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/business/wht')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/business/wht') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>WHT Transactions</span>
                            </Link>
                            <Link
                                href="/business/vat"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/business/vat')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/business/vat') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>VAT Returns</span>
                            </Link>
                            <Link
                                href="/business/reports/financial-statements"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/business/reports/financial-statements')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/business/reports/financial-statements') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h18v4H3V3zm0 6h18v4H3V9zm0 6h18v6H3v-6z" />
                                </svg>
                                <span>Financial Statements</span>
                            </Link>
                            <Link
                                href="/business/reports/cac-forms"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/business/reports/cac-forms')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/business/reports/cac-forms') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h10M7 11h10M7 15h6m-9 5h18a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>CAC Annual Return</span>
                            </Link>
                        </div>
                    </div>

                    <!-- AI & Insights -->
                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">AI & Intelligence</p>
                        <div class="space-y-1">
                            <Link
                                href="/business/ai/insights"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/business/ai/insights')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/business/ai/insights') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5h.01M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Tax Insights</span>
                            </Link>
                            <Link
                                href="/business/ai/chat"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/business/ai/chat')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/business/ai/chat') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>AI Chat</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Account -->
                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Account</p>
                        <div class="space-y-1">
                            <Link
                                href="/business/settings"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/business/settings')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/business/settings') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5A2.25 2.25 0 008.25 22.5h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 5.25h3m-3 3h3m-3 3h3M9 15.75h.008v.008H9v-.008zm3 0h.008v.008h-.008v-.008zm3 0h.008v.008h-.008v-.008z" />
                                </svg>
                                <span>Settings</span>
                            </Link>
                        </div>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main :class="auth?.user ? 'flex-1 min-w-0' : 'w-full'">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                    <!-- AI Automation Banner -->
                    <div
                        v-if="auth?.user"
                        class="mb-6 rounded-xl border border-blue-200 bg-blue-50/50 backdrop-blur-sm px-4 py-3"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                            <div class="flex items-start gap-2 flex-1">
                                <span class="inline-flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                                    i
                                </span>
                                <div class="text-sm text-blue-900">
                                    <p class="font-semibold">AI Automation is active</p>
                                    <p class="text-blue-800">
                                        Transactions can be auto-categorized, compliance reminders are sent, and payment recovery runs in the background.
                                        <Link href="/business/ai/insights" class="font-medium underline hover:text-blue-700">
                                            Review suggestions in AI Insights
                                        </Link>.
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 sm:ml-auto">
                                <button
                                    v-if="page.props.current_business"
                                    @click.prevent="showLeaveConfirm = true"
                                    class="px-3 py-1.5 text-xs sm:text-sm bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    Return to accountant
                                </button>
                            </div>
                        </div>
                    </div>

                    <slot />
                </div>
            </main>
        </div>

        <!-- Feature Upgrade Modal -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showUpgradeModal"
                class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-[100] p-4"
                @click.self="closeUpgradeModal"
            >
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Feature Locked</span>
                        </h3>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4">
                        <p class="text-sm text-gray-600 mb-4">
                            {{ upgradeModalData?.message || 'This feature is not available on your current plan. Please upgrade to access it.' }}
                        </p>
                        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3">
                            <p class="text-xs sm:text-sm text-blue-800">
                                <span class="font-semibold">Feature:</span> {{ formatFeatureName(upgradeModalData?.feature || 'unknown') }}
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50/50 px-6 py-4 flex flex-col sm:flex-row gap-3">
                        <button
                            @click="closeUpgradeModal"
                            class="w-full order-2 sm:order-1 sm:flex-1 px-4 py-2 text-center text-sm bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition font-medium"
                        >
                            Go Back
                        </button>
                        <Link
                            :href="route('business.plans.index')"
                            class="w-full order-1 sm:order-2 sm:flex-1 px-4 py-2 text-center text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium shadow-sm"
                        >
                            View Plans
                        </Link>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Leave Business Confirmation Modal -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showLeaveConfirm"
                class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-[110] p-4"
                @click.self="cancelLeave"
            >
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                    <div class="px-6 py-5">
                        <h3 class="text-lg font-semibold text-gray-900">Return to accountant account</h3>
                        <p class="text-sm text-gray-600 mt-2">Are you sure you want to leave the current business and return to your accountant account? You can re-open the business anytime.</p>
                    </div>
                    <div class="bg-gray-50/50 px-6 py-4 flex gap-3">
                        <button @click="cancelLeave" class="flex-1 px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium">
                            Cancel
                        </button>
                        <button @click="confirmLeave" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors text-sm font-medium shadow-sm">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Floating AI Chat Widget -->
        <div class="fixed bottom-6 right-6 z-40">
            <TaxMasterChat v-if="auth?.user" />
        </div>

        <!-- Cookie Consent Banner -->
        <CookieConsent />
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watchEffect, onMounted, onUnmounted } from 'vue';
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
const showLeaveConfirm = ref(false);
let flashTimeout = null;

// Close user menu when clicking outside
const handleClickOutside = (e) => {
    if (showUserMenu.value && !e.target.closest('.user-menu-container')) {
        showUserMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

/**
 * Show flash message
 */
const showFlash = (type, message) => {
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
    showUserMenu.value = false;
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

/**
 * Leave business context and return to accountant or dashboard
 */
const leaveBusiness = () => {
    router.post(route('business.leave'));
};

const confirmLeave = () => {
    showLeaveConfirm.value = false;

    // Post to leave; show client-side toast on success and handle failures with a redirect fallback
    router.post(route('business.leave'))
        .then(() => {
            showFlash('success', 'Returned to accountant account.');
        })
        .catch(() => {
            showFlash('error', 'Could not leave business context. Redirecting to accountant dashboard.');
            // Fallback redirect in case the POST fails (non-Inertia or network issue)
            setTimeout(() => {
                window.location.href = route('accountant.dashboard');
            }, 1200);
        });
};

const cancelLeave = () => {
    showLeaveConfirm.value = false;
};
</script>

<style>
/* Custom scrollbar for sidebar */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 20px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
</style>
