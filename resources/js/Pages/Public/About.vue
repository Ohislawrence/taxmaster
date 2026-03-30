<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const team = [
    {
        name: 'Chinedu Okafor',
        role: 'Founder & CEO',
        bio: 'Former Big 4 tax consultant. 10+ years in Nigerian tax advisory.',
        initials: 'CO'
    },
    {
        name: 'Amina Ibrahim',
        role: 'CTO',
        bio: 'Full-stack engineer. Built fintech platforms serving 500K+ users.',
        initials: 'AI'
    },
    {
        name: 'Tunde Adeyemi',
        role: 'Head of Compliance',
        bio: 'Chartered Tax Practitioner (CITN). Ex-FIRS senior officer.',
        initials: 'TA'
    },
    {
        name: 'Ngozi Eze',
        role: 'Head of Product',
        bio: 'Product leader with 8 years building SaaS for African markets.',
        initials: 'NE'
    },
];

const values = [
    {
        title: 'Compliance First',
        desc: 'Every feature is validated against current Nigerian tax law and FIRS/SIRS regulations.',
        icon: 'shield'
    },
    {
        title: 'Simplicity',
        desc: 'Tax is complex enough. Our software is intuitive for anyone — not just accountants.',
        icon: 'sparkles'
    },
    {
        title: 'Security',
        desc: 'NDPA-compliant encryption, audit trails, and role-based access. Always protected.',
        icon: 'lock'
    },
    {
        title: 'Nigerian-Built',
        desc: "We don't adapt foreign software. We build from scratch for Nigeria's unique tax landscape.",
        icon: 'flag'
    },
];

const milestones = [
    { year: '2022', title: 'Founded', desc: 'Started with a simple mission: make Nigerian tax compliance effortless' },
    { year: '2023', title: 'First 100 Customers', desc: 'Lagos SMEs adopt TaxMaster for PAYE automation' },
    { year: '2024', title: 'Multi-State Launch', desc: 'Expanded to Abuja, Ogun, Oyo, and Rivers State IRS integrations' },
    { year: '2025', title: '1,200+ Businesses', desc: '₦4.2B+ in taxes calculated and filed through the platform' },
];

// Intersection Observer for reveal animations
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
    <!-- Hero Section - Refined -->
    <section class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-32 sm:pb-16 lg:pt-40 lg:pb-20">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>


        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="mx-auto max-w-3xl text-center">

                <h1 class="reveal reveal-up text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl lg:text-5xl lg:leading-[1.15]">
                    Make compliance simple, reliable, and fast for <span class="text-blue-600">Nigerian businesses</span>
                </h1>

                <p class="reveal reveal-up mt-6 text-base leading-relaxed text-gray-600 sm:text-lg max-w-2xl mx-auto">
                    Built by Nigerian tax practitioners and engineers, TaxMaster turns complex rules into automated workflows so you file on time, avoid penalties, and free your team to focus on growth.
                </p>

                <div class="reveal reveal-up mt-8 flex flex-wrap items-center justify-center gap-4 text-sm text-gray-500">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        FIRS-ready exports
                    </span>
                    <span class="hidden sm:inline text-gray-300">|</span>
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        State IRS compatible
                    </span>
                    <span class="hidden sm:inline text-gray-300">|</span>
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        NDPA compliant
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Statement - Dark Section -->
    <section class="py-16 sm:py-20 lg:py-28 bg-gray-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-400 mb-4">Our Mission</p>
                <h2 class="text-2xl font-bold text-white sm:text-3xl lg:text-4xl leading-tight">
                    Automate calculations. Deliver filing-ready outputs. Avoid penalties.
                </h2>
                <p class="mt-6 text-gray-400 text-lg leading-relaxed">
                    Nigerian businesses spend 40+ hours monthly on tax compliance. TaxMaster reduces that to under 2 hours with accurate computations, validated exports for FIRS and state portals, and automated deadline tracking.
                </p>
                <div class="mt-8 flex items-center gap-6">
                    <div class="flex -space-x-3">
                        <div v-for="i in 4" :key="i" class="w-10 h-10 rounded-full bg-gray-800 border-2 border-gray-950 flex items-center justify-center text-xs font-medium text-gray-400">
                            {{ ['CO', 'AI', 'TA', 'NE'][i-1] }}
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Built by Nigerians, for Nigeria</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Values - Interactive Cards -->
    <section class="py-16 sm:py-20 lg:py-28 bg-gray-50/50 relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:8rem_8rem] opacity-40"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up max-w-2xl mb-12 sm:mb-16">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">What We Believe</p>
                <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl">Values that drive every decision</h2>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(v, i) in values"
                    :key="v.title"
                    class="reveal reveal-up group relative bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 hover:border-gray-200 hover:shadow-xl transition-all duration-300"
                    :style="{ transitionDelay: `${i * 50}ms` }"
                >
                    <div class="h-12 w-12 rounded-xl bg-gray-100 group-hover:bg-gray-900 flex items-center justify-center mb-5 transition-colors duration-300">
                        <svg class="w-6 h-6 text-gray-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path v-if="v.icon === 'shield'" stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            <path v-else-if="v.icon === 'sparkles'" stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            <path v-else-if="v.icon === 'lock'" stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M3 21v-8a2 2 0 012-2h14a2 2 0 012 2v8M3 21h18M5 21v-8a2 2 0 012-2h14a2 2 0 012 2v8M9 10a2 2 0 012-2h2a2 2 0 012 2v2H9v-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ v.title }}</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ v.desc }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works - Timeline -->
    <section class="py-16 sm:py-20 lg:py-28 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal reveal-up max-w-2xl mb-12 sm:mb-16">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">How We Help</p>
                <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl">From chaos to compliance</h2>
            </div>

            <div class="reveal reveal-up relative">
                <!-- Timeline line -->
                <div class="hidden lg:block absolute left-8 top-0 bottom-0 w-px bg-gray-200"></div>

                <div class="space-y-8 lg:space-y-12">
                    <div v-for="(step, i) in [
                        {num: '01', title: 'Connect Your Data', desc: 'Link bank accounts via Mono or upload transactions. Import staff via CSV. We handle the messy data.'},
                        {num: '02', title: 'Auto-Calculate', desc: 'Our engine applies current PITA bands, VAT rules, and WHT rates. Real-time tax liability updates.'},
                        {num: '03', title: 'Generate & Export', desc: 'Download filing-ready schedules for TaxPro-Max or State IRS. Formatted, validated, ready to upload.'},
                        {num: '04', title: 'Track & Confirm', desc: 'Upload your payment receipt. We verify and maintain your complete compliance history.'}
                    ]" :key="i" class="relative lg:pl-20">
                        <!-- Timeline dot -->
                        <div class="hidden lg:flex absolute left-0 top-0 h-16 w-16 rounded-full bg-gray-900 text-white items-center justify-center text-lg font-bold shadow-lg">
                            {{ step.num }}
                        </div>
                        <div class="lg:hidden mb-4">
                            <span class="inline-flex h-10 w-10 rounded-full bg-gray-900 text-white items-center justify-center text-sm font-bold">{{ step.num }}</span>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-6 sm:p-8 lg:bg-transparent lg:p-0">
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ step.title }}</h3>
                            <p class="text-gray-600 leading-relaxed max-w-xl">{{ step.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section
    <section class="py-16 sm:py-20 lg:py-28 bg-gray-50 relative overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal reveal-up max-w-2xl mb-12 sm:mb-16">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">The Team</p>
                <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl">Built by experts</h2>
                <p class="mt-4 text-gray-600">Tax practitioners, engineers, and product leaders who understand Nigerian business.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(member, i) in team"
                    :key="member.name"
                    class="reveal reveal-up group"
                    :style="{ transitionDelay: `${i * 75}ms` }"
                >
                    <div class="relative bg-white rounded-2xl p-6 border border-gray-100 hover:border-gray-200 hover:shadow-lg transition-all duration-300">

                        <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-lg font-bold text-gray-600 mb-4 group-hover:from-blue-50 group-hover:to-blue-100 group-hover:text-blue-600 transition-all duration-300">
                            {{ member.initials }}
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ member.name }}</h3>
                        <p class="text-sm text-blue-600 font-medium mt-1">{{ member.role }}</p>
                        <p class="text-sm text-gray-500 mt-3 leading-relaxed">{{ member.bio }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

     Milestones
    <section class="py-16 sm:py-20 bg-white border-t border-gray-100">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal reveal-up">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="m in milestones" :key="m.year" class="relative">
                        <p class="text-3xl font-bold text-gray-900">{{ m.year }}</p>
                        <p class="text-sm font-semibold text-blue-600 mt-1">{{ m.title }}</p>
                        <p class="text-sm text-gray-500 mt-2">{{ m.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>-->

    <!-- CTA - Refined -->
    <section class="py-16 sm:py-20 lg:py-28 bg-gray-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 h-px w-1/2 bg-gradient-to-r from-transparent via-gray-700 to-transparent"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up mx-auto max-w-2xl text-center">
                <h2 class="text-2xl font-bold text-white sm:text-3xl lg:text-4xl">Ready to simplify compliance?</h2>
                <p class="mt-4 text-gray-400">Join 1,200+ Nigerian businesses using TaxMaster.</p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        :href="route('register')"
                        class="group inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 text-sm font-semibold text-gray-900 shadow-lg shadow-white/10 transition-all hover:bg-gray-100 hover:shadow-xl hover:shadow-white/20 active:scale-[0.98]"
                    >
                        Start free today
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    <a
                        href="/contact"
                        class="inline-flex items-center gap-2 rounded-full border border-gray-700 px-8 py-4 text-sm font-semibold text-gray-300 transition-all hover:border-gray-500 hover:text-white"
                    >
                        Contact sales
                    </a>
                </div>

                <p class="mt-6 text-xs text-gray-600">
                    Free for small businesses · No credit card required · 2-minute setup
                </p>
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
