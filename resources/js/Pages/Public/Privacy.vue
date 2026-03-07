<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
defineOptions({ layout: PublicLayout });
</script>

<template>
    <section class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-32 sm:pb-16 lg:pt-40 lg:pb-20">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 relative">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl mb-6">Privacy Policy</h1>
            <div class="prose prose-gray max-w-none text-gray-700">
                <p>Your privacy is important to us. This Privacy Policy explains how TaxMaster collects, uses, and protects your information.</p>
                <h2>1. Information We Collect</h2>
                <p>We collect information you provide when you register, use our services, or contact us. This may include your name, email, business details, and tax information.</p>
                <h2>2. How We Use Information</h2>
                <p>We use your information to provide and improve our services, communicate with you, and comply with legal obligations.</p>
                <h2>3. Data Security</h2>
                <p>We implement industry-standard security measures to protect your data, including encryption and access controls. Your data is stored securely in Nigeria.</p>
                <h2>4. Sharing of Information</h2>
                <p>We do not sell your personal information. We may share data with trusted partners only as necessary to provide our services or comply with the law.</p>
                <h2>5. Your Rights</h2>
                <p>You may access, update, or delete your information at any time. Contact us for assistance with your data rights.</p>
                <h2>6. Changes to Policy</h2>
                <p>We may update this Privacy Policy from time to time. Continued use of TaxMaster means you accept the updated policy.</p>
                <h2>7. Contact</h2>
                <p>If you have questions about this Privacy Policy, please <a href="/contact">contact us</a>.</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.prose h2 {
    margin-top: 2rem;
    font-size: 1.25rem;
    font-weight: 600;
    color: #2563eb;
}
</style>
