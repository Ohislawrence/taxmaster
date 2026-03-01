<template>
    <BusinessLayout>
        <Head title="No Business" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-8 text-center text-gray-500">
                                        <p>You don't have a business yet.</p>
                    <Link href="/business/setup" class="text-blue-600 hover:underline">Create your first business</Link>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

</script>
