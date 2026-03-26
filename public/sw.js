const CACHE_NAME = 'piktoflow-v1';

self.addEventListener('install', (event) => {
    console.log('[Service Worker] Zainstalowany');
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Aktywowany');
    return self.clients.claim();
});

self.addEventListener('fetch', (event) => {
});
