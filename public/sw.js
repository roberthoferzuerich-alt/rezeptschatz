// Service Worker für PWA-Installierbarkeit auf Android & Samsung Tablets
const CACHE_NAME = 'rezeptschatz-pwa-v2';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// WICHTIG: Nur GET-Anfragen behandeln und Livewire / POST niemals stören
self.addEventListener('fetch', (event) => {
    // POST, PUT, DELETE und Livewire-Aktionen dürfen NIEMALS abgefangen werden
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    if (url.pathname.startsWith('/livewire') || url.pathname.startsWith('/api')) {
        return;
    }

    event.respondWith(
        fetch(event.request).catch(async () => {
            const cached = await caches.match(event.request);
            if (cached) {
                return cached;
            }
            return new Response('Offline', {
                status: 503,
                statusText: 'Service Unavailable',
                headers: { 'Content-Type': 'text/plain' }
            });
        })
    );
});
