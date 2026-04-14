import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// ─── Service Worker Registration ─────────────────────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .catch((err) => console.warn('[TaxMaster SW] Registration failed:', err));
    });
}

// ─── Marketing routes that should open in the browser when in standalone mode ─
const MARKETING_PATHS = [
    '/', '/pricing', '/features', '/tax-calculator',
    '/for-accountants', '/for-businesses', '/e-invoicing',
    '/about', '/contact', '/help', '/privacy', '/terms',
    '/blog',
];

function isMarketingPath(url) {
    try {
        const pathname = (typeof url === 'string') ? new URL(url, window.location.origin).pathname : url.pathname;
        return MARKETING_PATHS.some((route) =>
            route === '/' ? pathname === '/' : pathname === route || pathname.startsWith(route + '/')
        );
    } catch {
        return false;
    }
}

function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// ─── Intercept Inertia navigation to marketing pages when in standalone ───────
router.on('before', (event) => {
    if (!isStandalone()) return;
    const url = event.detail.visit.url;
    if (isMarketingPath(url)) {
        event.preventDefault();
        window.open(typeof url === 'string' ? url : url.href, '_blank', 'noopener,noreferrer');
    }
});
