/**
 * Dot.Docs Service Worker
 * - Caches static assets for offline shell
 * - Intercepts document save requests and queues them in IndexedDB when offline
 * - Background sync replays queued saves when connectivity is restored
 */

const CACHE_NAME = 'dotdocs-v1';
const OFFLINE_DB  = 'dotdocs-offline';
const SYNC_TAG    = 'dotdocs-sync-saves';

// Assets to pre-cache (shell). Vite build output filenames change; we cache
// dynamically on first fetch instead (network-first with offline fallback).
const PRECACHE_URLS = ['/'];

// ── Install: pre-cache shell ─────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS))
    );
    self.skipWaiting();
});

// ── Activate: claim clients immediately ──────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// ── Fetch interception ────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle same-origin requests
    if (url.origin !== self.location.origin) return;

    // Livewire AJAX / document save POSTs — queue offline
    if (request.method === 'POST' && url.pathname.includes('/livewire/')) {
        event.respondWith(handleLivewirePost(request));
        return;
    }

    // Navigation requests — network first, fall back to cache
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() =>
                caches.match(request).then((cached) => cached || caches.match('/'))
            )
        );
        return;
    }

    // Static assets — cache first, network fallback + cache update
    event.respondWith(
        caches.match(request).then((cached) => {
            if (cached) return cached;
            return fetch(request).then((networkRes) => {
                if (networkRes && networkRes.status === 200 && networkRes.type === 'basic') {
                    const clone = networkRes.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                }
                return networkRes;
            });
        })
    );
});

// ── Handle Livewire POSTs offline ─────────────────────────────────────────────
async function handleLivewirePost(request) {
    try {
        const response = await fetch(request.clone());
        return response;
    } catch (_) {
        // Offline: queue the request body for later replay
        try {
            const body = await request.clone().text();
            await enqueueOfflineSave({ url: request.url, body, timestamp: Date.now() });

            // Request background sync if supported
            if ('sync' in self.registration) {
                await self.registration.sync.register(SYNC_TAG);
            }
        } catch (_) { /* ignore storage errors */ }

        // Return a synthetic "offline queued" response so Livewire doesn't crash
        return new Response(
            JSON.stringify({ effects: [], components: [] }),
            { status: 200, headers: { 'Content-Type': 'application/json' } }
        );
    }
}

// ── Background sync: replay queued saves ──────────────────────────────────────
self.addEventListener('sync', (event) => {
    if (event.tag === SYNC_TAG) {
        event.waitUntil(replayQueuedSaves());
    }
});

async function replayQueuedSaves() {
    const queue = await dequeueAllOfflineSaves();
    for (const item of queue) {
        try {
            await fetch(item.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: item.body,
            });
        } catch (_) {
            // Still offline — re-enqueue and stop
            await enqueueOfflineSave(item);
            break;
        }
    }
}

// ── IndexedDB helpers ─────────────────────────────────────────────────────────
function openDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(OFFLINE_DB, 1);
        req.onupgradeneeded = (e) => {
            e.target.result.createObjectStore('saves', { keyPath: 'id', autoIncrement: true });
            e.target.result.createObjectStore('drafts', { keyPath: 'docUuid' });
        };
        req.onsuccess = (e) => resolve(e.target.result);
        req.onerror = () => reject(req.error);
    });
}

async function enqueueOfflineSave(item) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('saves', 'readwrite');
        tx.objectStore('saves').add(item);
        tx.oncomplete = resolve;
        tx.onerror = () => reject(tx.error);
    });
}

async function dequeueAllOfflineSaves() {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('saves', 'readwrite');
        const store = tx.objectStore('saves');
        const items = [];
        const cursor = store.openCursor();
        cursor.onsuccess = (e) => {
            const c = e.target.result;
            if (c) { items.push(c.value); store.delete(c.primaryKey); c.continue(); }
            else resolve(items);
        };
        cursor.onerror = () => reject(cursor.error);
    });
}
