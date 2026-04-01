const CACHE_VERSION = 'v1';
const CACHE_NAMES = {
    static: `static-${CACHE_VERSION}`,
    dynamic: `dynamic-${CACHE_VERSION}`,
    images: `images-${CACHE_VERSION}`,
};

const STATIC_ASSETS = [
    '/',
    '/dashboard',
    '/my-dashboard',
    '/offline.js',
];

// Install Service Worker
self.addEventListener('install', (event) => {
    console.log('Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAMES.static)
            .then((cache) => {
                console.log('Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate Service Worker
self.addEventListener('activate', (event) => {
    console.log('Service Worker activating...');
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => !Object.values(CACHE_NAMES).includes(name))
                        .map((name) => {
                            console.log(`Deleting old cache: ${name}`);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch Event Handler - Cache First for static, Network First for dynamic
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip external requests
    if (url.origin !== self.location.origin) {
        return;
    }

    // Static assets - cache first
    if (url.pathname.includes('/build/') ||
        url.pathname.includes('/images/') ||
        url.pathname.includes('/fonts/') ||
        url.pathname.match(/\.(css|js|woff2)$/)) {
        event.respondWith(cacheFirstStrategy(request, CACHE_NAMES.static));
        return;
    }

    // API calls - network first, fallback to cache
    if (url.pathname.includes('/api/')) {
        event.respondWith(networkFirstStrategy(request, CACHE_NAMES.dynamic));
        return;
    }

    // HTML pages - network first, fallback to cache
    if (request.headers.get('accept').includes('text/html')) {
        event.respondWith(networkFirstStrategy(request, CACHE_NAMES.dynamic));
        return;
    }

    // Default - network first
    event.respondWith(networkFirstStrategy(request, CACHE_NAMES.dynamic));
});

async function cacheFirstStrategy(request, cacheName) {
    try {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }

        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('Cache first strategy failed:', error);
        return new Response('Offline - Resource not found', {
            status: 503,
            statusText: 'Service Unavailable',
        });
    }
}

async function networkFirstStrategy(request, cacheName) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('Network first strategy failed:', error);
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }

        return new Response('Offline', {
            status: 503,
            statusText: 'Service Unavailable',
        });
    }
}

// Handle messages from clients
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.delete(CACHE_NAMES.dynamic);
        caches.delete(CACHE_NAMES.static);
    }
});
