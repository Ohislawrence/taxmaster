<script setup>
import { useForm } from '@inertiajs/vue3';
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
    // For now, just a placeholder. Wire up to a real endpoint later.
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
</script>

<template>
    <section class="pt-32 pb-20 lg:pt-40 lg:pb-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Contact</p>
                <h1 class="mt-3 text-4xl font-bold text-gray-900 sm:text-5xl">Get in touch</h1>
                <p class="mt-4 text-lg text-gray-500">
                    Have a question, need a demo, or want to discuss enterprise pricing? We'd love to hear from you.
                </p>
            </div>

            <div class="mt-16 grid gap-12 lg:grid-cols-5">
                <!-- Contact Info -->
                <div class="lg:col-span-2">
                    <h2 class="text-xl font-semibold text-gray-900">Contact Information</h2>
                    <p class="mt-2 text-sm text-gray-500">Reach out via any channel and we'll respond within 24 hours.</p>

                    <div class="mt-8 space-y-6">
                        <div v-for="info in contactInfo" :key="info.label" class="flex items-start gap-4">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="info.icon" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ info.label }}</p>
                                <a
                                    v-if="info.href"
                                    :href="info.href"
                                    class="text-sm text-blue-600 hover:text-blue-700"
                                >
                                    {{ info.value }}
                                </a>
                                <p v-else class="text-sm text-gray-500">{{ info.value }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social -->
                    <div class="mt-10">
                        <p class="text-sm font-medium text-gray-900">Follow us</p>
                        <div class="mt-3 flex gap-4">
                            <a href="https://twitter.com/taxmaster_ng" target="_blank" rel="noopener" class="text-gray-400 hover:text-gray-600" aria-label="Twitter">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                            </a>
                            <a href="https://linkedin.com/company/taxmaster-ng" target="_blank" rel="noopener" class="text-gray-400 hover:text-gray-600" aria-label="LinkedIn">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.66z" /></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="lg:col-span-3">
                    <form @submit.prevent="submit" class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Your name"
                                />
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="you@company.com"
                                />
                            </div>
                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-700">Company</label>
                                <input
                                    id="company"
                                    v-model="form.company"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Company name"
                                />
                            </div>
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                                <select
                                    id="subject"
                                    v-model="form.subject"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
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
                        <div class="mt-6">
                            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="4"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="Tell us how we can help..."
                            ></textarea>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="mt-6 w-full rounded-lg bg-blue-600 py-3 text-sm font-semibold text-white transition-all hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Sending...' : 'Send Message' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>
