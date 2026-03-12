<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const form = useForm({
    name: '',
    email: '',
    company: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const contactInfo = [
    {
        icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        label: 'Email',
        value: 'hello@taxmaster.ng',
        href: 'mailto:hello@taxmaster.ng',
    },
    {
        icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
        label: 'Phone',
        value: '+234 (0) 812 345 6789',
        href: 'tel:+2348123456789',
    },
    {
        icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
        label: 'Office',
        value: 'Victoria Island, Lagos, Nigeria',
        href: null,
    },
];

const faqs = [
    {
        q: "How quickly do you respond to inquiries?",
        a: "We typically respond within 24 hours during business days. For enterprise inquiries, our sales team reaches out within 4 hours."
    },
    {
        q: "Do you offer on-site demos in Lagos?",
        a: "Yes, for teams of 10+ users, we offer on-site demos across Lagos, Abuja, and Port Harcourt."
    },
    {
        q: "Can I speak with a tax expert directly?",
        a: "Absolutely. Our compliance team includes chartered tax practitioners who can advise on complex Nigerian tax scenarios."
    }
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
    <section class="relative overflow-hidden bg-white pt-24 pb-16 sm:pt-32 sm:pb-20 lg:pt-40 lg:pb-28">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f8f8f8_1px,transparent_1px),linear-gradient(to_bottom,#f8f8f8_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

        <!-- Subtle gradient orb -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-blue-50/50 to-transparent rounded-full blur-3xl"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="mx-auto max-w-3xl text-center">

                <h1 class="reveal reveal-up text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl lg:text-5xl lg:leading-[1.15]">
                    Get in <span class="text-blue-600">touch</span>
                </h1>

                <p class="reveal reveal-up mt-6 text-base leading-relaxed text-gray-600 sm:text-lg max-w-2xl mx-auto">
                    Have a question about TaxMaster? Need a demo or want to discuss enterprise pricing?
                    We'd love to hear from you.
                </p>

                <div class="reveal reveal-up mt-8 flex flex-wrap items-center justify-center gap-4 text-sm text-gray-500">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        24h response time
                    </span>
                    <span class="hidden sm:inline text-gray-300">|</span>
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Tax experts on staff
                    </span>
                    <span class="hidden sm:inline text-gray-300">|</span>
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Lagos & Abuja offices
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Grid -->
    <section class="py-16 sm:py-20 lg:py-28 bg-gray-50/50 relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:8rem_8rem] opacity-40"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="grid gap-8 lg:grid-cols-12 lg:gap-12">
                <!-- Contact Info Cards -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="reveal reveal-up">
                        <p class="text-sm font-semibold uppercase tracking-wider text-blue-600 mb-3">Contact Information</p>
                        <h2 class="text-2xl font-bold text-gray-900">Reach out any way you prefer</h2>
                        <p class="mt-4 text-gray-600">Our team is based in Lagos with support coverage across all time zones.</p>
                    </div>

                    <div class="space-y-4 mt-8">
                        <div
                            v-for="(info, i) in contactInfo"
                            :key="info.label"
                            class="reveal reveal-up group relative bg-white rounded-2xl p-6 border border-gray-100 hover:border-gray-200 hover:shadow-lg transition-all duration-300"
                            :style="{ transitionDelay: `${i * 75}ms` }"
                        >
                            <div class="flex items-start gap-4">
                                <div class="h-12 w-12 rounded-xl bg-gray-100 group-hover:bg-gray-900 flex items-center justify-center flex-shrink-0 transition-colors duration-300">
                                    <svg class="h-5 w-5 text-gray-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="info.icon" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-500 mb-1">{{ info.label }}</p>
                                    <a
                                        v-if="info.href"
                                        :href="info.href"
                                        class="text-base font-semibold text-gray-900 hover:text-blue-600 transition-colors break-all"
                                    >
                                        {{ info.value }}
                                    </a>
                                    <p v-else class="text-base font-semibold text-gray-900">{{ info.value }}</p>
                                </div>
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="reveal reveal-up pt-6">
                        <p class="text-sm font-medium text-gray-900 mb-4">Follow us</p>
                        <div class="flex gap-3">
                            <a
                                href="https://twitter.com/taxmaster_ng"
                                target="_blank"
                                rel="noopener"
                                class="flex items-center justify-center w-11 h-11 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:border-blue-200 hover:shadow-md transition-all duration-300"
                                aria-label="Twitter"
                            >
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                </svg>
                            </a>
                            <a
                                href="https://linkedin.com/company/taxmaster-ng"
                                target="_blank"
                                rel="noopener"
                                class="flex items-center justify-center w-11 h-11 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:border-blue-200 hover:shadow-md transition-all duration-300"
                                aria-label="LinkedIn"
                            >
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.66z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="lg:col-span-7">
                    <form
                        @submit.prevent="submit"
                        class="reveal reveal-up bg-white rounded-2xl p-6 sm:p-8 lg:p-10 border border-gray-100 shadow-sm"
                    >
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Send us a message</h3>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="block w-full rounded-lg border-gray-200 px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors"
                                    placeholder="John Doe"
                                />
                            </div>
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="block w-full rounded-lg border-gray-200 px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors"
                                    placeholder="john@company.com"
                                />
                            </div>
                            <div class="space-y-2">
                                <label for="company" class="block text-sm font-medium text-gray-700">Company</label>
                                <input
                                    id="company"
                                    v-model="form.company"
                                    type="text"
                                    class="block w-full rounded-lg border-gray-200 px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors"
                                    placeholder="Company Ltd"
                                />
                            </div>
                            <div class="space-y-2">
                                <label for="subject" class="block text-sm font-medium text-gray-700">Topic</label>
                                <select
                                    id="subject"
                                    v-model="form.subject"
                                    required
                                    class="block w-full rounded-lg border-gray-200 px-4 py-3 text-gray-900 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors bg-white"
                                >
                                    <option value="" disabled>Select a topic</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="demo">Request a Demo</option>
                                    <option value="enterprise">Enterprise Pricing</option>
                                    <option value="support">Technical Support</option>
                                    <option value="partnership">Partnership</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 space-y-2">
                            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="4"
                                required
                                class="block w-full rounded-lg border-gray-200 px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors resize-none"
                                placeholder="Tell us how we can help..."
                            ></textarea>
                        </div>

                        <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <p class="text-xs text-gray-500">
                                By submitting, you agree to our
                                <Link href="/privacy" class="text-blue-600 hover:underline">Privacy Policy</Link>
                            </p>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="group inline-flex items-center justify-center gap-2 rounded-full bg-gray-900 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-gray-900/10 transition-all hover:bg-gray-800 hover:shadow-xl hover:shadow-gray-900/20 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ form.processing ? 'Sending...' : 'Send Message' }}
                                <svg v-if="!form.processing" class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 sm:py-20 lg:py-28 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal reveal-up max-w-2xl mb-12">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Common Questions</p>
                <h2 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl">Quick answers</h2>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div
                    v-for="(faq, i) in faqs"
                    :key="i"
                    class="reveal reveal-up"
                    :style="{ transitionDelay: `${i * 100}ms` }"
                >
                    <div class="bg-gray-50 rounded-2xl p-6 h-full">
                        <h3 class="font-semibold text-gray-900 mb-3">{{ faq.q }}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ faq.a }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 sm:py-20 lg:py-28 bg-gray-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 h-px w-1/2 bg-gradient-to-r from-transparent via-gray-700 to-transparent"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
            <div class="reveal reveal-up mx-auto max-w-2xl text-center">
                <h2 class="text-2xl font-bold text-white sm:text-3xl lg:text-4xl">Prefer to explore first?</h2>
                <p class="mt-4 text-gray-400">Start your free trial and see how TaxMaster works for your business.</p>

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
                        href="#"
                        class="inline-flex items-center gap-2 rounded-full border border-gray-700 px-8 py-4 text-sm font-semibold text-gray-300 transition-all hover:border-gray-500 hover:text-white"
                    >
                        View documentation
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
