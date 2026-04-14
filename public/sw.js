/**
 * TaxMaster Service Worker
 * Handles asset caching, offline fallback, and push notifications.
 */

const CACHE_VERSION = 'v1';
const ASSET_CACHE = `taxmaster-assets-${CACHE_VERSION}`;
const PAGE_CACHE = `taxmaster-pages-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/taxmaster-icon.png',
];

// ─── Install ─────────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(ASSET_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

// ─── Activate ────────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key !== ASSET_CACHE && key !== PAGE_CACHE)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

// ─── Fetch ────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle same-origin requests
    if (url.origin !== self.location.origin) return;

    // Never cache Inertia AJAX requests (they have X-Inertia header)
    if (request.headers.get('X-Inertia')) {
        event.respondWith(
            fetch(request).catch(() =>
                new Response(JSON.stringify({ error: 'You are offline. Please reconnect.' }), {
                    status: 503,
                    headers: { 'Content-Type': 'application/json' },
                })
            )
        );
        return;
    }

    // Build assets (Vite hashed files) — cache-first, safe because filenames are hashed
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(ASSET_CACHE).then((cache) => cache.put(request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Static files (icons, images, fonts) — cache-first
    if (/\.(png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|eot)$/i.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(ASSET_CACHE).then((cache) => cache.put(request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Navigation (full page loads) — network-first, offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(PAGE_CACHE).then((cache) => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() =>
                    caches.match(request).then((cached) => cached || caches.match(OFFLINE_URL))
                )
        );
        return;
    }

    // Everything else (API calls etc.) — network-first, cache fallback
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});

// ─── Push Notifications ───────────────────────────────────────────────────────
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data = {};
    try {
        data = event.data.json();
    } catch {
        data = { title: 'TaxMaster', body: event.data.text() };
    }

    event.waitUntil(
        self.registration.showNotification(data.title || 'TaxMaster', {
            body: data.body || '',
            icon: '/taxmaster-icon.png',
            badge: '/taxmaster-icon.png',
            data: { url: data.url || '/business/dashboard' },
            tag: data.tag || 'taxmaster-notification',
            requireInteraction: data.requireInteraction || false,
            vibrate: [100, 50, 100],
        })
    );
});

// ─── Notification Click ───────────────────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data?.url || '/business/dashboard';

    event.waitUntil(
        self.clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then((clients) => {
                // Focus an existing window if one is open
                for (const client of clients) {
                    if ('focus' in client) {
                        client.focus();
                        return client.navigate(targetUrl);
                    }
                }
                // Otherwise open a new window
                if (self.clients.openWindow) {
                    return self.clients.openWindow(targetUrl);
                }
            })
    );
});
