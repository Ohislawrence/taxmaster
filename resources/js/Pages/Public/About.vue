
<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const team = [
    { name: 'Chinedu Okafor', role: 'Founder & CEO', bio: 'Former Big 4 tax consultant. 10+ years in Nigerian tax advisory.' },
    { name: 'Amina Ibrahim', role: 'CTO', bio: 'Full-stack engineer. Built fintech platforms serving 500K+ users.' },
    { name: 'Tunde Adeyemi', role: 'Head of Compliance', bio: 'Chartered Tax Practitioner (CITN). Ex-FIRS senior officer.' },
    { name: 'Ngozi Eze', role: 'Head of Product', bio: 'Product leader with 8 years building SaaS for African markets.' },
];

const values = [
    { title: 'Compliance First', desc: 'Every feature we build is validated against current Nigerian tax law and FIRS/SIRS regulations.' },
    { title: 'Simplicity', desc: 'Tax is complex enough. Our software should be intuitive for anyone — not just accountants.' },
    { title: 'Security', desc: 'NDPA-compliant encryption, audit trails, and role-based access. Your data is always protected.' },
    { title: 'Nigerian-Built', desc: "We don't adapt foreign software. We build from scratch for Nigeria's unique tax landscape." },
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
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-32 sm:pb-16 lg:pt-40 lg:pb-20">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="mx-auto max-w-2xl text-center">

                <h1 class="reveal reveal-up text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl lg:text-[3.5rem] lg:leading-[1.1]">
                    Making tax simple for every Nigerian business
                </h1>
                <p class="reveal reveal-up mt-4 text-base leading-relaxed text-gray-500 sm:mt-6 sm:text-lg">
                    TaxMaster was born from the frustration of dealing with Nigeria's complex, multi-layered tax system. We believe no business should face penalties because tax compliance is too hard. Our mission is to automate tax calculations, filings, and payments — so you can focus on growing your business.
                </p>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="py-16 sm:py-20 lg:py-28 bg-gray-50 relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] opacity-40"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up mx-auto max-w-2xl text-center mb-10 sm:mb-16">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Our Values</p>
                <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl lg:text-4xl">What drives us</h2>
            </div>
            <div class="grid gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="v in values" :key="v.title" class="reveal reveal-up rounded-2xl border border-gray-100 bg-white p-6 sm:p-8 transition-all duration-300 hover:border-gray-200 hover:shadow-lg">
                    <h3 class="text-lg font-semibold text-gray-900">{{ v.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ v.desc }}</p>
                </div>
            </div>
        </div>
    </section>


    <!-- ...existing code... -->

    <!-- CTA -->
    <section class="py-16 sm:py-20 lg:py-24 bg-gray-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 h-px w-1/2 bg-gradient-to-r from-transparent via-gray-700 to-transparent"></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up mx-auto max-w-2xl text-center">
                <h2 class="text-2xl font-bold text-white sm:text-3xl lg:text-4xl">Want to join our mission?</h2>
                <p class="mt-2 text-gray-400">We're always looking for talented people who care about making tax easier.</p>
                <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center sm:gap-4">
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3.5 text-sm font-semibold text-gray-900 shadow-lg transition-all sm:px-8 sm:py-4 sm:text-base hover:bg-gray-100 active:scale-[0.98]"
                    >
                        Get started
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    <a href="/contact" class="inline-flex items-center gap-2 rounded-lg border border-gray-700 px-6 py-3.5 text-sm font-semibold text-gray-300 transition-all sm:px-8 sm:py-4 sm:text-base hover:border-gray-500 hover:text-white">
                        Contact us
                    </a>
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
