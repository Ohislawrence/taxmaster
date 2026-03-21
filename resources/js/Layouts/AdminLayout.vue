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
                    <!-- Logo/Title -->
                    <div class="flex items-center gap-2 lg:gap-4">
                        <Link href="/admin/dashboard" class="flex items-center gap-2">
                            <img src="/taxmaster-one.png" alt="TaxMaster" class="h-7 lg:h-8 w-auto" />
                            <span class="text-sm lg:text-base font-medium text-gray-600">Admin Dashboard</span>
                        </Link>
                    </div>

                    <!-- User Menu -->
                    <div v-if="auth?.user" class="flex items-center gap-2 lg:gap-4">
                        <!-- Mobile Menu Toggle for Sidebar -->
                        <button
                            @click="showMobileSidebar = !showMobileSidebar"
                            class="lg:hidden p-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100/50 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

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
                                    <Link href="/admin/profile" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Profile Settings
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
                </div>
            </div>
        </nav>

        <div class="flex">
            <!-- Mobile Sidebar Overlay -->
            <transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showMobileSidebar && auth?.user"
                    class="lg:hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-40"
                    @click="showMobileSidebar = false"
                ></div>
            </transition>

            <!-- Sidebar -->
            <aside
                v-if="auth?.user"
                :class="[
                    'fixed lg:sticky left-0 top-16 lg:top-20 h-[calc(100vh-4rem)] lg:h-[calc(100vh-5rem)] w-72 bg-white border-r border-gray-200/50 transition-transform duration-300 z-40 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-transparent',
                    showMobileSidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
                ]"
            >
                <nav class="px-3 py-6">
                    <!-- Main Navigation -->
                    <div class="space-y-1">
                        <!-- Dashboard -->
                        <Link
                            href="/admin/dashboard"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/admin/dashboard')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/admin/dashboard') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Dashboard</span>
                        </Link>

                        <!-- Users Management -->
                        <Link
                            href="/admin/users"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/admin/users')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/admin/users') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.593M9 21h6" />
                            </svg>
                            <span>Users</span>
                        </Link>

                        <!-- Accountants Management -->
                        <Link
                            href="/admin/accountants"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/admin/accountants')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/admin/accountants') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 10-8 0v4M5 21h14a2 2 0 002-2v-5a2 2 0 00-2-2H5a2 2 0 00-2 2v5a2 2 0 002 2z" />
                            </svg>
                            <span>Accountants</span>
                        </Link>

                        <!-- Businesses Management -->
                        <Link
                            href="/admin/businesses"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/admin/businesses')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/admin/businesses') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5.581m0 0H9m5.581 0a2 2 0 100-4H9m0 4a2 2 0 110-4m0 0H7a2 2 0 00-2 2v3m2-3V7a2 2 0 012-2h5.581a2 2 0 011.915 1.264m0 0H20" />
                            </svg>
                            <span>Businesses</span>
                        </Link>

                        <Link
                            href="/admin/broadcast"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/admin/broadcast')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/admin/broadcast') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                            <span>Broadcast</span>
                        </Link>

                        <!-- Subscriptions -->
                        <Link
                            href="/admin/subscriptions"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/admin/subscriptions')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/admin/subscriptions') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Subscriptions</span>
                        </Link>

                        <!-- Plans Management -->
                        <Link
                            href="/admin/plans"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/admin/plans')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/admin/plans') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span>Plans</span>
                        </Link>

                        <!-- Blog Management -->
                        <Link
                            href="/admin/blog"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/admin/blog')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/admin/blog') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h10M7 16h10M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Blog</span>
                        </Link>

                        <!-- AI Settings -->
                        <Link
                            href="/admin/ai-settings"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                isActive('/admin/ai-settings')
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" :class="isActive('/admin/ai-settings') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5h.01M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>AI Settings</span>
                        </Link>
                    </div>

                    <!-- Phase 1: Bank & Transaction Management -->
                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Business Management</p>
                        <div class="space-y-1">
                            <Link
                                href="/admin/bank-accounts"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/bank-accounts')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/bank-accounts') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span>Bank Accounts</span>
                            </Link>
                            <Link
                                href="/admin/transactions"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/transactions')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/transactions') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                                <span>Transactions</span>
                            </Link>
                            <Link
                                href="/admin/compliance"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/compliance')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/compliance') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Compliance</span>
                            </Link>
                            <Link
                                href="/admin/vat-returns"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/vat-returns')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/vat-returns') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>VAT Returns</span>
                            </Link>
                            <!-- Phase 2 PAYE Management -->
                            <Link
                                href="/admin/paye"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/paye')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/paye') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>PAYE Dashboard</span>
                            </Link>
                            <Link
                                href="/admin/wht-returns"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/wht-returns')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/wht-returns') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>WHT Transactions</span>
                            </Link>
                        </div>
                    </div>

                    <!-- System Management -->
                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">System Management</p>
                        <div class="space-y-1">
                            <Link
                                href="/admin/invoices"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/invoices')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/invoices') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Invoices</span>
                            </Link>
                            <Link
                                href="/admin/affiliate/payouts"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/affiliate/payouts')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/affiliate/payouts') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Affiliate Payouts</span>
                            </Link>
                            <Link
                                href="/admin/affiliate/settings"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/affiliate/settings')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/affiliate/settings') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M4 6h16M4 10h16M4 14h16" />
                                </svg>
                                <span>Affiliate Settings</span>
                            </Link>

                            <Link
                                href="/admin/accountant-settings"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/accountant-settings')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/accountant-settings') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v4m0 8v4m8-8h-4M4 12H8m9.657-6.657L15.657 8m-7.314 0L4.343 5.343M19.657 18.657L16 15m-8 0L4.343 18.657" />
                                </svg>
                                <span>Accountant Settings</span>
                            </Link>
                            <Link
                                href="/admin/sync-failures"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/sync-failures')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/sync-failures') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4v2m0 -12a9 9 0 110 18 9 9 0 010-18zm-1 15h2v2h-2v-2zm0 -10h2v4h-2v-4z" />
                                </svg>
                                <span>Sync Failures</span>
                            </Link>
                            <Link
                                href="/admin/ai-automation"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/ai-automation')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/ai-automation') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span>AI Automation</span>
                            </Link>
                            <Link
                                href="/admin/backups"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/backups')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/backups') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Backups</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Reports -->
                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Reports</p>
                        <div class="space-y-1">
                            <Link
                                href="/admin/compliance/reports/overdue"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/compliance/reports/overdue')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/compliance/reports/overdue') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Overdue Compliance</span>
                            </Link>
                            <Link
                                href="/admin/vat-returns/reports/revenue"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/vat-returns/reports/revenue')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/vat-returns/reports/revenue') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8L5.257 19.393A2 2 0 005 18.21V4a2 2 0 012-2h10a2 2 0 012 2v14.211a2 2 0 01-.757 1.563z" />
                                </svg>
                                <span>VAT Revenue Report</span>
                            </Link>
                            <Link
                                href="/admin/reports/tax"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/reports/tax')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/reports/tax') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m-6 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span>Tax Report</span>
                            </Link>
                            <Link
                                href="/admin/reports/payments"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/reports/payments')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/reports/payments') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Payment Report</span>
                            </Link>
                            <Link
                                href="/admin/reports/revenue"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/admin/reports/revenue')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/admin/reports/revenue') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8L5.257 19.393A2 2 0 005 18.21V4a2 2 0 012-2h10a2 2 0 012 2v14.211a2 2 0 01-.757 1.563z" />
                                </svg>
                                <span>Revenue Report</span>
                            </Link>
                        </div>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 min-w-0">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watchEffect, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const auth = computed(() => page.props.auth);
const showUserMenu = ref(false);
const showMobileSidebar = ref(false);
const flashMessage = ref(null);
let flashTimeout = null;

// Close user menu when clicking outside
const handleClickOutside = (e) => {
    if (showUserMenu.value && !e.target.closest('.user-menu-container')) {
        showUserMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    // Listen for programmatic flash events from pages
    window.addEventListener('admin:flash', handleAdminFlash);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('admin:flash', handleAdminFlash);
});

const handleAdminFlash = (e) => {
    try {
        const d = e?.detail || {};
        const type = d.type || 'info';
        const message = d.message || '';
        if (message) showFlash(type, message);
    } catch (ex) {
        // ignore
    }
};

// Watch for flash data changes
watchEffect(() => {
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

// Close mobile sidebar on route change
watchEffect(() => {
    showMobileSidebar.value = false;
    showUserMenu.value = false;
});

const isActive = (route) => {
    return window.location.pathname.startsWith(route);
};

const logout = () => {
    router.post(route('logout'));
};

const showFlash = (type, message) => {
    if (flashTimeout) clearTimeout(flashTimeout);
    flashMessage.value = { type, message };
    flashTimeout = setTimeout(() => (flashMessage.value = null), 5000);
};

const dismissFlash = () => {
    if (flashTimeout) clearTimeout(flashTimeout);
    flashMessage.value = null;
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
