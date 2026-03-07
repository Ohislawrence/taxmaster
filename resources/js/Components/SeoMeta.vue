<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'TaxMaster — Simplifying Tax Compliance for Nigerian Businesses',
    },
    description: {
        type: String,
        default: 'Automate PAYE, VAT, WHT & CIT filings. TaxMaster helps Nigerian businesses stay compliant with FIRS and State IRS — effortlessly.',
    },
    keywords: {
        type: String,
        default: 'tax compliance nigeria, PAYE filing, VAT returns, WHT remittance, CIT filing, FIRS, SIRS, tax software nigeria, taxmaster',
    },
    ogImage: {
        type: String,
        default: '/images/og-taxmaster.png',
    },
    ogType: {
        type: String,
        default: 'website',
    },
    url: {
        type: String,
        default: '',
    },
    twitterCard: {
        type: String,
        default: 'summary_large_image',
    },
    canonicalUrl: {
        type: String,
        default: '',
    },
    noIndex: {
        type: Boolean,
        default: false,
    },
    structuredData: {
        type: Object,
        default: null,
    },
});

const siteUrl = 'https://taxmaster.ng';

const pageUrl = computed(() => props.url || props.canonicalUrl || siteUrl);
const fullTitle = computed(() => {
    if (props.title === 'TaxMaster — Simplifying Tax Compliance for Nigerian Businesses') {
        return props.title;
    }
    return `${props.title} — TaxMaster`;
});

const jsonLd = computed(() => {
    if (props.structuredData) return JSON.stringify(props.structuredData);

    return JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'SoftwareApplication',
        name: 'TaxMaster',
        url: siteUrl,
        description: props.description,
        applicationCategory: 'FinanceApplication',
        operatingSystem: 'Web',
        offers: {
            '@type': 'Offer',
            price: '0',
            priceCurrency: 'NGN',
            description: 'Free tier available',
        },
        author: {
            '@type': 'Organization',
            name: 'TaxMaster Nigeria',
            url: siteUrl,
        },
    });
});

// Inject JSON-LD structured data into <head> via DOM (Inertia Head doesn't support <script> tags)
let jsonLdEl = null;

const injectJsonLd = () => {
    if (jsonLdEl) jsonLdEl.remove();
    jsonLdEl = document.createElement('script');
    jsonLdEl.type = 'application/ld+json';
    jsonLdEl.textContent = jsonLd.value;
    document.head.appendChild(jsonLdEl);
};

onMounted(injectJsonLd);
watch(jsonLd, injectJsonLd);
onUnmounted(() => { if (jsonLdEl) jsonLdEl.remove(); });
</script>

<template>
    <Head :title="fullTitle">
        <!-- Primary Meta -->
        <meta name="description" :content="description" head-key="description" />
        <meta name="keywords" :content="keywords" head-key="keywords" />
        <meta name="author" content="TaxMaster Nigeria" head-key="author" />

        <!-- Robots -->
        <meta v-if="noIndex" name="robots" content="noindex, nofollow" head-key="robots" />
        <meta v-else name="robots" content="index, follow" head-key="robots" />

        <!-- Canonical -->
        <link v-if="canonicalUrl" rel="canonical" :href="canonicalUrl" head-key="canonical" />

        <!-- Open Graph -->
        <meta property="og:type" :content="ogType" head-key="og:type" />
        <meta property="og:title" :content="fullTitle" head-key="og:title" />
        <meta property="og:description" :content="description" head-key="og:description" />
        <meta property="og:image" :content="ogImage" head-key="og:image" />
        <meta property="og:url" :content="pageUrl" head-key="og:url" />
        <meta property="og:site_name" content="TaxMaster" head-key="og:site_name" />
        <meta property="og:locale" content="en_NG" head-key="og:locale" />

        <!-- Twitter Card -->
        <meta name="twitter:card" :content="twitterCard" head-key="twitter:card" />
        <meta name="twitter:title" :content="fullTitle" head-key="twitter:title" />
        <meta name="twitter:description" :content="description" head-key="twitter:description" />
        <meta name="twitter:image" :content="ogImage" head-key="twitter:image" />
        <meta name="twitter:site" content="@taxmaster_ng" head-key="twitter:site" />

    </Head>
</template>
