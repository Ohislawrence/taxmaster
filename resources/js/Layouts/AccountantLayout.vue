<template>
    <div class="min-h-screen bg-[#f9fafc]">
                            <transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-1"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 translate-y-1"
                            >
                                <div v-if="showUserMenu" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50">
                                    <!-- Profile link for accountants -->
                                    <Link :href="route('profile.show')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.341A8 8 0 116.659 6.572" />
                                        </svg>
                                        Profile
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

                            <!-- Flash Messages - Modern Toast Style -->
                            <transition
                                enter-active-class="transform ease-out duration-300 transition"
                                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                                leave-active-class="transition ease-in duration-200"
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

        <!-- Navigation Header - Clean & Minimal -->
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

                    <!-- Logo & Brand -->
                    <div class="flex items-center gap-2 lg:gap-4">
                        <Link href="/accountant/dashboard" class="flex items-center gap-2">
                            <img src="/taxmaster-one.png" alt="TaxMaster" class="h-7 lg:h-8 w-auto" />
                            <span class="text-sm lg:text-base font-medium text-gray-600">Accountant Portal</span>
                        </Link>
                    </div>

                    <!-- Right Section -->
                    <div v-if="auth?.user" class="flex items-center gap-2 lg:gap-4">
                        <!-- Affiliate Badge -->
                        <button
                            v-if="auth.user.affiliate_code"
                            @click="copyAffiliateLink"
                            class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm bg-gray-50 text-gray-600 hover:bg-gray-100 transition-colors border border-gray-200/50"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 14.828a4 4 0 01-5.656 0 4 4 0 010-5.656m5.656 5.656a4 4 0 105.656-5.656m-5.656 5.656L15.172 7m-5.656 5.656L7 15.172" />
                            </svg>
                            <span class="text-xs font-medium">Affiliate</span>
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
                                    <Link :href="route('profile.show')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.341A8 8 0 116.659 6.572" />
                                        </svg>
                                        Profile
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
                            Get started
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex">
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

            <!-- Sidebar - Modern & Clean -->
            <aside
                v-if="auth?.user"
                :class="[
                    'fixed lg:sticky left-0 top-16 lg:top-20 h-[calc(100vh-4rem)] lg:h-[calc(100vh-5rem)] w-64 bg-white border-r border-gray-200/50 transition-transform duration-300 z-40',
                    showMobileMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
                ]"
            >
                <div class="h-full overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-transparent">
                    <nav class="px-3 py-6">
                        <!-- Main Navigation -->
                        <div class="space-y-1">
                            <Link
                                href="/accountant/dashboard"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/accountant/dashboard')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/accountant/dashboard') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Dashboard
                            </Link>

                            <Link
                                href="/accountant/businesses"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/accountant/businesses')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/accountant/businesses') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                My Businesses
                            </Link>

                            <Link
                                href="/accountant/affiliate"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                                    isActive('/accountant/affiliate')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'text-gray-600 hover:bg-gray-50'
                                ]"
                            >
                                <svg class="w-5 h-5" :class="isActive('/accountant/affiliate') ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 14.828a4 4 0 01-5.656 0 4 4 0 010-5.656m5.656 5.656a4 4 0 105.656-5.656m-5.656 5.656L15.172 7m-5.656 5.656L7 15.172" />
                                </svg>
                                Affiliate
                            </Link>
                        </div>

                        <!-- Account Section -->
                        <div class="mt-8 pt-8 border-t border-gray-100">
                            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Account</p>
                            <div class="space-y-1">
                                <!-- Account settings link removed for accountants (admin-only feature) -->
                            </div>
                        </div>

                        <!-- Bottom Section with User Info (Mobile) -->
                        <div class="lg:hidden absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100 bg-white">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-semibold flex items-center justify-center text-sm">
                                    {{ auth?.user?.name?.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-700 truncate">{{ auth?.user?.name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ auth?.user?.email }}</p>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </aside>

            <!-- Main Content -->
            <main :class="auth?.user ? 'flex-1 min-w-0' : 'w-full'">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Floating AI Chat Widget - Position adjusted -->
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

// Close user menu when clicking outside
const handleClickOutside = (e) => {
    // Only close if clicking outside the user menu container AND the menu is open
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
    // Close mobile menu on route change
    showMobileMenu.value = false;
    // Close user menu on route change
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
</style>
