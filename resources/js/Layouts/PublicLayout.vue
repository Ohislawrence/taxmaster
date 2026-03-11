<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SeoMeta from '@/Components/SeoMeta.vue';
import VisitorChat from '@/Components/VisitorChat.vue';

defineProps({
    title: { type: String, default: '' },
    description: { type: String, default: '' },
    ogImage: { type: String, default: '' },
    keywords: { type: String, default: '' },
});

const page = usePage();
const scrolled = ref(false);
const mobileMenuOpen = ref(false);
const currentYear = new Date().getFullYear();

const handleScroll = () => {
    scrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const navLinks = [
    { label: 'Product', href: '/#product' },
    { label: 'Features', href: '/#features' },
    { label: 'Pricing', href: '/pricing' },
    { label: 'About', href: '/about' },
];

const footerColumns = [
    {
        title: 'Product',
        links: [
            { label: 'PAYE Filing', href: '/#features' },
            { label: 'VAT Returns', href: '/#features' },
            { label: 'WHT Remittance', href: '/#features' },
            { label: 'CIT Filing', href: '/#features' },
            { label: 'Pricing', href: '/pricing' },
        ],
    },
    {
        title: 'Company',
        links: [
            { label: 'About Us', href: '/about' },
            { label: 'Contact', href: '/contact' },
            { label: 'Blog', href: '/blog' },
            { label: 'Careers', href: '/careers' },
        ],
    },
    {
        title: 'Resources',
        links: [
            { label: 'Documentation', href: '/docs' },
            { label: 'API Reference', href: '/api-docs' },
            { label: 'Help Center', href: '/help' },
            { label: 'Status', href: '/status' },
        ],
    },
    {
        title: 'Legal',
        links: [
            { label: 'Privacy Policy', href: '/privacy' },
            { label: 'Cookie Policy', href: '/cookie-policy' },
            { label: 'Terms of Service', href: '/terms' },
            { label: 'Data Protection', href: '/data-protection' },
        ],
    },
];

</script>

<template>
    <SeoMeta
        :title="title"
        :description="description"
        :og-image="ogImage"
        :keywords="keywords"
    />

    <div class="min-h-screen bg-white text-slate-900 font-sans antialiased selection:bg-blue-100 selection:text-blue-900">
        <!-- Navigation - Mono.co style: floating pill when scrolled, transparent when top -->
        <nav
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-500"
            :class="[
                scrolled
                    ? 'py-3'
                    : 'py-5',
            ]"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    class="flex h-14 items-center justify-between transition-all duration-500"
                    :class="[
                        scrolled
                            ? 'bg-white/80 backdrop-blur-xl shadow-[0_2px_20px_rgba(0,0,0,0.08)] rounded-full px-6 border border-slate-200/50'
                            : 'bg-transparent px-2',
                    ]"
                >
                    <!-- Logo - Refined mark -->
                    <div class="flex items-center">
                        <Link href="/" class="flex items-center gap-2.5 group">
                            <!-- Mono-style geometric mark -->
                            <div
                                class="relative flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-300"
                                :class="scrolled ? 'bg-slate-900' : 'bg-slate-900'"
                            >
                                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <path d="M9 7v10"/>
                                    <path d="M15 7v10"/>
                                    <path d="M7 12h10"/>
                                </svg>
                            </div>
                            <span
                                class="text-lg font-semibold tracking-tight transition-colors"
                                :class="scrolled ? 'text-slate-900' : 'text-slate-900'"
                            >
                                TaxMaster
                            </span>
                        </Link>
                    </div>

                    <!-- Desktop Navigation - Mono minimal links -->
                    <div class="hidden items-center gap-8 lg:flex">
                        <a
                            v-for="link in navLinks"
                            :key="link.label"
                            :href="link.href"
                            class="text-sm font-medium transition-colors hover:text-slate-900"
                            :class="scrolled ? 'text-slate-600' : 'text-slate-600'"
                        >
                            {{ link.label }}
                        </a>
                    </div>

                    <!-- Desktop CTA - Mono pill button style -->
                    <div class="hidden items-center gap-4 lg:flex">
                        <Link
                            v-if="!page.props.auth?.user"
                            :href="route('login')"
                            class="text-sm font-medium transition-colors hover:text-slate-900"
                            :class="scrolled ? 'text-slate-600' : 'text-slate-600'"
                        >
                            Sign in
                        </Link>
                        <Link
                            :href="page.props.auth?.user ? route('dashboard') : route('register')"
                            class="group relative overflow-hidden rounded-full bg-slate-900 px-5 py-2 text-sm font-medium text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95"
                        >
                            <span class="relative z-10">
                                {{ page.props.auth?.user ? 'Dashboard' : 'Get started' }}
                            </span>
                            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-blue-600 to-blue-500 opacity-0 transition-opacity duration-300 group-hover:opacity-100"/>
                        </Link>
                    </div>

                    <!-- Mobile menu button - Refined -->
                    <button
                        class="inline-flex items-center justify-center rounded-full p-2 transition-colors lg:hidden"
                        :class="scrolled ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-600 hover:bg-slate-100/50'"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <svg v-if="!mobileMenuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6h16.5M3.75 12h16.5M3.75 18h16.5" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu - Mono sheet style -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-4"
            >
                <div
                    v-if="mobileMenuOpen"
                    class="absolute left-4 right-4 top-full mt-3 rounded-2xl bg-white p-6 shadow-[0_8px_40px_rgba(0,0,0,0.12)] ring-1 ring-slate-900/5 lg:hidden"
                >
                    <div class="space-y-1">
                        <a
                            v-for="link in navLinks"
                            :key="link.label"
                            :href="link.href"
                            class="block rounded-xl px-4 py-3 text-base font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900"
                            @click="mobileMenuOpen = false"
                        >
                            {{ link.label }}
                        </a>
                    </div>
                    <div class="mt-4 space-y-2 border-t border-slate-100 pt-4">
                        <Link
                            v-if="!page.props.auth?.user"
                            :href="route('login')"
                            class="block rounded-xl px-4 py-3 text-center text-base font-medium text-slate-600 transition-colors hover:bg-slate-50"
                        >
                            Sign in
                        </Link>
                        <Link
                            :href="page.props.auth?.user ? route('dashboard') : route('register')"
                            class="block rounded-xl bg-slate-900 px-4 py-3 text-center text-base font-semibold text-white transition-transform active:scale-[0.98]"
                        >
                            {{ page.props.auth?.user ? 'Dashboard' : 'Get started' }}
                        </Link>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer - Mono.co style: clean, spacious, subtle -->
        <footer class="bg-slate-50">
            <!-- Main footer -->
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
                <div class="grid gap-12 lg:grid-cols-6">
                    <!-- Brand column - Wider -->
                    <div class="lg:col-span-2">
                        <Link href="/" class="inline-flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <path d="M9 7v10"/>
                                    <path d="M15 7v10"/>
                                    <path d="M7 12h10"/>
                                </svg>
                            </div>
                            <span class="text-lg font-semibold text-slate-900">
                                TaxMaster
                            </span>
                        </Link>
                        <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-600">
                            Modern tax infrastructure for Nigerian businesses. Automate compliance, eliminate penalties, and focus on growth.
                        </p>
                        <!-- Social links - Minimal -->
                        <div class="mt-6 flex gap-4">
                            <a href="https://twitter.com/taxmaster_ng" target="_blank" rel="noopener" class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm ring-1 ring-slate-200 transition-all hover:text-slate-900 hover:shadow-md" aria-label="Twitter">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                </svg>
                            </a>
                            <a href="https://linkedin.com/company/taxmaster-ng" target="_blank" rel="noopener" class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm ring-1 ring-slate-200 transition-all hover:text-slate-900 hover:shadow-md" aria-label="LinkedIn">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.66z" />
                                </svg>
                            </a>
                            <a href="https://instagram.com/taxmaster_ng" target="_blank" rel="noopener" class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm ring-1 ring-slate-200 transition-all hover:text-slate-900 hover:shadow-md" aria-label="Instagram">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2c2.717 0 3.056.01 4.122.06 1.065.05 1.79.217 2.428.465.66.254 1.216.598 1.772 1.153a4.908 4.908 0 011.153 1.772c.247.637.415 1.363.465 2.428.047 1.066.06 1.405.06 4.122 0 2.717-.01 3.056-.06 4.122-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 01-1.153 1.772 4.915 4.915 0 01-1.772 1.153c-.637.247-1.363.415-2.428.465-1.066.047-1.405.06-4.122.06-2.717 0-3.056-.01-4.122-.06-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 01-1.772-1.153 4.904 4.904 0 01-1.153-1.772c-.248-.637-.415-1.363-.465-2.428C2.013 15.056 2 14.717 2 12c0-2.717.01-3.056.06-4.122.05-1.066.217-1.79.465-2.428a4.88 4.88 0 011.153-1.772A4.897 4.897 0 015.45 2.525c.638-.248 1.362-.415 2.428-.465C8.944 2.013 9.283 2 12 2zm0 5a5 5 0 100 10 5 5 0 000-10zm6.5-.25a1.25 1.25 0 10-2.5 0 1.25 1.25 0 002.5 0zM12 9a3 3 0 110 6 3 3 0 010-6z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Footer columns - Refined spacing -->
                    <div v-for="col in footerColumns" :key="col.title" class="lg:col-span-1">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-900">{{ col.title }}</h3>
                        <ul class="mt-4 space-y-3">
                            <li v-for="link in col.links" :key="link.label">
                                <a :href="link.href" class="text-sm text-slate-600 transition-colors hover:text-slate-900">
                                    {{ link.label }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Bottom bar - Clean separation -->
            <div class="border-t border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-6 sm:flex-row sm:px-6 lg:px-8">
                    <p class="text-sm text-slate-500">
                        &copy; {{ currentYear }} TaxMaster. All rights reserved.
                    </p>
                    <div class="flex items-center gap-6">
                        <span class="flex items-center gap-2 text-sm text-slate-500">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            </span>
                            Systems operational
                        </span>
                        <span class="text-sm text-slate-400">Lagos, NG</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>


    <!-- Floating Visitor AI Chat Widget (public/guest) -->
    <VisitorChat />

    <!-- Cookie Consent Banner -->
    <CookieConsent />
</template>
