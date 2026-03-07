<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
defineOptions({ layout: PublicLayout });
</script>

<template>
    <section class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-32 sm:pb-16 lg:pt-40 lg:pb-20">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 relative">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl mb-6">Data Protection Policy</h1>
            <div class="prose prose-gray max-w-none text-gray-700">
                <p>TaxMaster is committed to protecting your data in compliance with the Nigeria Data Protection Act (NDPA).</p>
                <h2>1. Compliance</h2>
                <p>We comply with all NDPA requirements, including data minimization, purpose limitation, and user consent.</p>
                <h2>2. Data Security</h2>
                <p>All sensitive data is encrypted at rest and in transit. Access is restricted to authorized personnel only.</p>
                <h2>3. Data Storage</h2>
                <p>Your data is stored securely on servers located in Nigeria. We do not transfer your data outside Nigeria without your consent.</p>
                <h2>4. User Rights</h2>
                <p>You have the right to access, correct, or delete your data. Contact us to exercise your rights under the NDPA.</p>
                <h2>5. Breach Notification</h2>
                <p>In the event of a data breach, we will notify affected users and the NDPC as required by law.</p>
                <h2>6. Contact</h2>
                <p>For questions about our data protection practices, please <a href="/contact">contact us</a>.</p>
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
