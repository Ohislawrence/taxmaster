<template>
    <BusinessLayout>
        <Head title="Integrations" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Integrations</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Connect TaxMaster with your favorite accounting software
                    </p>
                </div>

                <!-- Integrations Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="integration in integrations"
                        :key="integration.slug"
                        class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
                    >
                        <div class="p-6">
                            <!-- Integration Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <!-- Icon -->
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" :class="getIconBgClass(integration.slug)">
                                        <component :is="getIcon(integration.slug)" class="w-8 h-8" :class="getIconColorClass(integration.slug)" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ integration.name }}</h3>
                                        <span :class="getStatusBadgeClass(integration.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ getStatusText(integration.status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <p class="text-sm text-gray-600 mb-4">
                                {{ integration.description }}
                            </p>

                            <!-- Connection Status -->
                            <div v-if="integration.status === 'connected'" class="mb-4 p-3 bg-green-50 rounded-lg">
                                <div class="flex items-center text-xs text-green-800">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="font-medium">Connected</p>
                                        <p v-if="integration.last_synced_at" class="text-green-700">
                                            Last synced {{ formatDate(integration.last_synced_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="mt-4">
                                <Link
                                    v-if="integration.available && integration.route"
                                    :href="integration.route"
                                    class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors"
                                >
                                    {{ integration.status === 'connected' ? 'Manage' : 'Connect' }}
                                </Link>
                                <button
                                    v-else
                                    disabled
                                    class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-400 bg-gray-50 cursor-not-allowed"
                                >
                                    Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-blue-800">Need help with integrations?</h3>
                            <p class="mt-2 text-sm text-blue-700">
                                Our integrations automatically sync your transactions, invoices, and bills. Each integration can be configured with your own API credentials for maximum security.
                            </p>
                            <a href="/help/integrations" target="_blank" class="mt-3 inline-flex items-center text-sm font-medium text-blue-800 hover:text-blue-900">
                                Learn more about integrations
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { format, parseISO } from 'date-fns';
import { h } from 'vue';

const props = defineProps({
    integrations: Array,
    businessName: String,
});

// Helper functions
const formatDate = (date) => {
    if (!date) return '';
    return format(parseISO(date), 'MMM d, yyyy');
};

const getStatusText = (status) => {
    const statusMap = {
        'connected': 'Connected',
        'available': 'Available',
        'coming_soon': 'Coming Soon',
    };
    return statusMap[status] || 'Unknown';
};

const getStatusBadgeClass = (status) => {
    const classMap = {
        'connected': 'bg-green-100 text-green-800',
        'available': 'bg-blue-100 text-blue-800',
        'coming_soon': 'bg-gray-100 text-gray-600',
    };
    return classMap[status] || 'bg-gray-100 text-gray-600';
};

const getIconBgClass = (slug) => {
    const classMap = {
        'quickbooks': 'bg-green-100',
        'zoho': 'bg-orange-100',
        'sage': 'bg-emerald-100',
        'xero': 'bg-sky-100',
    };
    return classMap[slug] || 'bg-gray-100';
};

const getIconColorClass = (slug) => {
    const classMap = {
        'quickbooks': 'text-green-600',
        'zoho': 'text-orange-600',
        'sage': 'text-emerald-600',
        'xero': 'text-sky-600',
    };
    return classMap[slug] || 'text-gray-600';
};

const getIcon = (slug) => {
    // QuickBooks icon
    if (slug === 'quickbooks') {
        return () => h('svg', {
            fill: 'currentColor',
            viewBox: '0 0 24 24',
        }, [
            h('path', {
                d: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2v-4h2v4z'
            })
        ]);
    }

    // Zoho icon
    if (slug === 'zoho') {
        return () => h('svg', {
            fill: 'currentColor',
            viewBox: '0 0 24 24',
        }, [
            h('path', {
                d: 'M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-9 12H7v-2h4v2zm0-4H7v-2h4v2zm0-4H7V6h4v2zm6 8h-4V6h4v10z'
            })
        ]);
    }

    // Sage icon
    if (slug === 'sage') {
        return () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24',
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
            })
        ]);
    }

    // Xero icon
    if (slug === 'xero') {
        return () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24',
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'
            })
        ]);
    }

    // Default icon
    return () => h('svg', {
        fill: 'none',
        stroke: 'currentColor',
        viewBox: '0 0 24 24',
    }, [
        h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'
        })
    ]);
};
</script>
