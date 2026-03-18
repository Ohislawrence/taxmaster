<template>
    <div class="min-h-screen bg-gradient-to-br from-[#f9fafc] to-[#f3f6fd]">
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
                    'fixed bottom-6 right-6 z-[9999] w-auto max-w-md',
                    'px-4 py-3 rounded-2xl shadow-lg border backdrop-blur-md',
                    flashMessage.type === 'success' ? 'bg-emerald-50/90 border-emerald-200/50 text-emerald-800' : '',
                    flashMessage.type === 'error' ? 'bg-rose-50/90 border-rose-200/50 text-rose-800' : '',
                    flashMessage.type === 'warning' ? 'bg-amber-50/90 border-amber-200/50 text-amber-800' : '',
                    flashMessage.type === 'info' ? 'bg-sky-50/90 border-sky-200/50 text-sky-800' : ''
                ]"
            >
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
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
                    <p class="text-sm font-medium flex-1 leading-5">{{ flashMessage.message }}</p>
                    <button @click="dismissFlash" class="flex-shrink-0 hover:opacity-75 transition-opacity mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </transition>

        <!-- Navigation Header - Enhanced -->
        <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200/50 sticky top-0 z-50 supports-backdrop-blur:bg-white/95">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16 lg:h-20">
                    <!-- Mobile Menu Toggle -->
                    <button
                        v-if="auth?.user"
                        @click="showMobileMenu = !showMobileMenu"
                        class="lg:hidden p-2.5 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100/80 transition-all active:scale-95"
                        :class="{ 'bg-gray-100/80': showMobileMenu }"
                    >
                        <svg v-if="!showMobileMenu" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Logo & Brand -->
                    <div class="flex items-center gap-2 lg:gap-4">
                        <Link href="/accountant/dashboard" class="flex items-center gap-2 group">
                            <img src="/taxmaster-one.png" alt="TaxMaster" class="h-7 lg:h-8 w-auto transition-transform group-hover:scale-105" />
                            <span class="text-sm lg:text-base font-medium text-gray-600 group-hover:text-gray-900 transition-colors">Accountant Portal</span>
                        </Link>
                    </div>

                    <!-- Right Section -->
                    <div v-if="auth?.user" class="flex items-center gap-2 lg:gap-4">
                        <!-- Affiliate Badge - Enhanced -->
                        <button
                            v-if="auth.user.affiliate_code"
                            @click="copyAffiliateLink"
                            class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm bg-gradient-to-r from-gray-50 to-gray-100/50 text-gray-600 hover:from-gray-100 hover:to-gray-200/50 transition-all active:scale-95 border border-gray-200/50 shadow-sm"
                        >
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 14.828a4 4 0 01-5.656 0 4 4 0 010-5.656m5.656 5.656a4 4 0 105.656-5.656m-5.656 5.656L15.172 7m-5.656 5.656L7 15.172" />
                            </svg>
                            <span class="text-xs font-medium">Affiliate: {{ auth.user.affiliate_code }}</span>
                        </button>

                        <!-- User Menu -->
                        <div class="relative user-menu-container">
                            <button
                                @click.stop="showUserMenu = !showUserMenu"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-xl hover:bg-gray-100/80 transition-all active:scale-95"
                                :class="{ 'bg-gray-100/80': showUserMenu }"
                            >
                                <div class="hidden sm:block text-right">
                                    <p class="text-sm font-semibold text-gray-700">{{ auth.user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ truncateEmail(auth.user.email) }}</p>
                                </div>
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-semibold flex items-center justify-center shadow-sm ring-2 ring-white/50">
                                    {{ auth.user.name.charAt(0).toUpperCase() }}
                                </div>
                            </button>

                            <!-- Dropdown Menu - Enhanced -->
                            <transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 -translate-y-2"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 -translate-y-2"
                            >
                                <div v-if="showUserMenu" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50 overflow-hidden">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-semibold text-gray-900">{{ auth.user.name }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ auth.user.email }}</p>
                                    </div>
                                    <Link :href="route('profile.show')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.341A8 8 0 116.659 6.572" />
                                        </svg>
                                        Profile
                                    </Link>
                                    <form @submit.prevent="logout">
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
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
                            Get started
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex flex-col lg:flex-row">
            <!-- Mobile Menu Overlay -->
            <transition
                enter-active-class="transition-opacity duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showMobileMenu && auth?.user"
                    class="lg:hidden fixed inset-0 bg-gradient-to-b from-black/30 to-black/20 backdrop-blur-sm z-40"
                    @click="showMobileMenu = false"
                ></div>
            </transition>

            <!-- Sidebar - Modern & Clean -->
            <aside
                v-if="auth?.user"
                :class="[
                    'fixed lg:sticky left-0 top-16 lg:top-20 h-[calc(100vh-4rem)] lg:h-[calc(100vh-5rem)] w-72 bg-white/95 backdrop-blur-sm border-r border-gray-200/50 transition-transform duration-300 ease-out z-40 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-transparent sidebar-smooth-scroll',
                    showMobileMenu ? 'translate-x-0 shadow-xl' : '-translate-x-full lg:translate-x-0 lg:shadow-none'
                ]"
            >
                <div class="h-full overflow-y-auto">
                    <nav class="px-4 py-6">
                        <!-- Welcome Section -->
                        <div class="mb-6 px-3">
                            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Welcome back,</p>
                            <p class="text-sm font-semibold text-gray-800">{{ auth?.user?.name?.split(' ')[0] || 'Accountant' }}</p>
                        </div>

                        <!-- Main Navigation -->
                        <div class="space-y-0.5">
                            <!-- Dashboard -->
                            <Link
                                href="/accountant/dashboard"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group',
                                    isActive('/accountant/dashboard')
                                        ? 'bg-gradient-to-r from-blue-50 to-indigo-50/50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-100/80'
                                ]"
                            >
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" :class="isActive('/accountant/dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                <span class="flex-1">Dashboard</span>
                                <span v-if="isActive('/accountant/dashboard')" class="w-1 h-1 bg-blue-600 rounded-full"></span>
                            </Link>

                            <!-- My Businesses -->
                            <Link
                                href="/accountant/businesses"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group',
                                    isActive('/accountant/businesses')
                                        ? 'bg-gradient-to-r from-blue-50 to-indigo-50/50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-100/80'
                                ]"
                            >
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" :class="isActive('/accountant/businesses') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="flex-1">My Businesses</span>
                                <span v-if="isActive('/accountant/businesses')" class="w-1 h-1 bg-blue-600 rounded-full"></span>
                            </Link>

                            <!-- Affiliate -->
                            <Link
                                href="/accountant/affiliate"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group',
                                    isActive('/accountant/affiliate')
                                        ? 'bg-gradient-to-r from-blue-50 to-indigo-50/50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-100/80'
                                ]"
                            >
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" :class="isActive('/accountant/affiliate') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 14.828a4 4 0 01-5.656 0 4 4 0 010-5.656m5.656 5.656a4 4 0 105.656-5.656m-5.656 5.656L15.172 7m-5.656 5.656L7 15.172" />
                                </svg>
                                <span class="flex-1">Affiliate Program</span>
                                <span v-if="isActive('/accountant/affiliate')" class="w-1 h-1 bg-blue-600 rounded-full"></span>
                            </Link>
                        </div>

                        <!-- Account Section -->
                        <div class="mt-8 pt-6 border-t border-gray-200/70">
                            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                Account
                            </p>
                            <div class="space-y-0.5">
                                <Link
                                    href="/accountant/settings"
                                    :class="[
                                        'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group',
                                        isActive('/accountant/settings')
                                            ? 'bg-gradient-to-r from-blue-50 to-indigo-50/50 text-blue-600'
                                            : 'text-gray-600 hover:bg-gray-100/80'
                                    ]"
                                >
                                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" :class="isActive('/accountant/settings') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="flex-1">Settings</span>
                                    <span v-if="isActive('/accountant/settings')" class="w-1 h-1 bg-blue-600 rounded-full"></span>
                                </Link>
                            </div>
                        </div>


                    </nav>
                </div>
            </aside>

            <!-- Main Content -->
            <main :class="auth?.user ? 'flex-1 min-w-0' : 'w-full'">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                    <!-- Welcome Banner -->
                    <div
                        v-if="auth?.user && isActive('/accountant/dashboard')"
                        class="mb-8 rounded-2xl border border-blue-200/50 bg-gradient-to-r from-blue-50/80 to-indigo-50/80 backdrop-blur-sm px-5 py-4 shadow-sm"
                    >
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <div class="text-sm text-blue-900">
                                <p class="font-semibold flex items-center gap-2">
                                    Accountant Dashboard
                                    <span class="px-1.5 py-0.5 text-xs bg-blue-200/50 text-blue-700 rounded-full">Live</span>
                                </p>
                                <p class="text-blue-700/90 mt-0.5">
                                    Manage your client businesses, track compliance, and monitor tax filings all in one place.
                                </p>
                            </div>
                        </div>
                    </div>

                    <slot />
                </div>
            </main>
        </div>

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
const flashMessage = ref(null);
let flashTimeout = null;

// Helper function to truncate email
const truncateEmail = (email) => {
    if (!email) return '';
    const [localPart, domain] = email.split('@');
    if (localPart.length > 10) {
        return `${localPart.substring(0, 8)}...@${domain}`;
    }
    return email;
};

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

const showFlash = (type, message) => {
    if (flashTimeout) clearTimeout(flashTimeout);
    flashMessage.value = { type, message };
    flashTimeout = setTimeout(() => (flashMessage.value = null), 5000);
};

const dismissFlash = () => {
    if (flashTimeout) clearTimeout(flashTimeout);
    flashMessage.value = null;
};

watchEffect(() => {
    if (page.props.flash?.success) showFlash('success', page.props.flash.success);
    else if (page.props.flash?.error) showFlash('error', page.props.flash.error);
    else if (page.props.flash?.warning) showFlash('warning', page.props.flash.warning);
    else if (page.props.flash?.info) showFlash('info', page.props.flash.info);
    else if (page.props.flash?.message) showFlash('info', page.props.flash.message);
});

watchEffect(() => {
    showMobileMenu.value = false;
    showUserMenu.value = false;
});

const isActive = (route) => window.location.pathname.startsWith(route);

const logout = () => {
    router.post(route('logout'));
};

const copyAffiliateLink = async () => {
    try {
        const code = auth.value?.user?.affiliate_code;
        if (!code) return showFlash('info', 'No affiliate code available');
        const url = `${window.location.origin}/affiliate/${code}`;
        await navigator.clipboard.writeText(url);
        showFlash('success', 'Affiliate link copied to clipboard');
    } catch (e) {
        showFlash('error', 'Unable to copy link');
    }
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

/* Support for backdrop blur in different browsers */
@supports (backdrop-filter: blur(0)) {
    .supports-backdrop-blur\:bg-white\/95 {
        background-color: rgba(255, 255, 255, 0.95);
    }
}

/* Active navigation indicator animation */
.router-link-active {
    position: relative;
}

.router-link-active::after {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 60%;
    background: linear-gradient(to bottom, #3b82f6, #6366f1);
    border-radius: 0 3px 3px 0;
    opacity: 0;
    animation: slideIn 0.2s ease-out forwards;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-50%) translateX(-5px);
    }
    to {
        opacity: 1;
        transform: translateY(-50%) translateX(0);
    }
}

/* Smooth scrolling for sidebar */
.sidebar-smooth-scroll {
    scroll-behavior: smooth;
}

/* Glassmorphism effects */
.glass-effect {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Hover scale effect */
.hover-scale {
    transition: transform 0.2s ease;
}

.hover-scale:hover {
    transform: scale(1.02);
}

/* Focus outline for accessibility */
*:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Mobile menu animations */
.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: transform 0.3s ease-out, opacity 0.2s ease;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}

.mobile-menu-enter-to,
.mobile-menu-leave-from {
    transform: translateX(0);
    opacity: 1;
}

/* Welcome banner gradient */
.welcome-gradient {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(99, 102, 241, 0.1) 100%);
}
</style>
