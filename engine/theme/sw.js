// Service Worker для PWA: офлайн-кеш + stale-while-revalidate для статики
const CACHE_VERSION = 'v1';
const STATIC_CACHE = 'static-' + CACHE_VERSION;
const RUNTIME_CACHE = 'runtime-' + CACHE_VERSION;

const PRECACHE = [
    './',
    './theme/style.css',
];

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(STATIC_CACHE)
            .then((c) => c.addAll(PRECACHE).catch(() => {}))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter(k => k !== STATIC_CACHE && k !== RUNTIME_CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (e) => {
    const req = e.request;
    if (req.method !== 'GET') return;
    const url = new URL(req.url);

    // Не кешируем админку, API подписки, лайки и автосохранение
    if (
        url.pathname.includes('/admin') ||
        url.search.includes('route=admin') ||
        url.search.includes('route=like') ||
        url.search.includes('route=subscribe') ||
        url.search.includes('route=suggest') ||
        url.search.includes('route=autosave')
    ) {
        return;
    }

    // Картинки и шрифты — cache-first
    if (req.destination === 'image' || req.destination === 'font') {
        e.respondWith(
            caches.match(req).then(cached => {
                if (cached) return cached;
                return fetch(req).then(resp => {
                    if (resp.ok) {
                        const clone = resp.clone();
                        caches.open(RUNTIME_CACHE).then(c => c.put(req, clone));
                    }
                    return resp;
                }).catch(() => cached);
            })
        );
        return;
    }

    // CSS/JS — stale-while-revalidate
    if (req.destination === 'style' || req.destination === 'script') {
        e.respondWith(
            caches.match(req).then(cached => {
                const network = fetch(req).then(resp => {
                    if (resp.ok) {
                        const clone = resp.clone();
                        caches.open(RUNTIME_CACHE).then(c => c.put(req, clone));
                    }
                    return resp;
                }).catch(() => cached);
                return cached || network;
            })
        );
        return;
    }

    // HTML страницы — network-first с фолбэком на кеш
    if (req.mode === 'navigate' || (req.headers.get('Accept') || '').includes('text/html')) {
        e.respondWith(
            fetch(req).then(resp => {
                if (resp.ok) {
                    const clone = resp.clone();
                    caches.open(RUNTIME_CACHE).then(c => c.put(req, clone));
                }
                return resp;
            }).catch(() => caches.match(req).then(c => c || caches.match('./')))
        );
    }
});
