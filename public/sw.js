// Minimaler Service Worker für PWA-Installierbarkeit auf Android & Samsung Tablets
const CACHE_NAME = 'rezeptschatz-pwa-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Standard Netzwerk-Anfrage
    event.respondWith(fetch(event.request).catch(() => caches.match(event.request)));
});
