const OMT_CACHE = 'omt-client-portal-v1';
const OMT_OFFLINE_URL = '/portal/login';

// Worker Kiosk pages get their own runtime cache: unlike the client portal
// (which only needs a not-a-browser-error fallback), a Worker genuinely
// needs to reach a job they've already opened while offline - to view its
// checklist, or resume a capture-then-queue-offline flow (see the
// IndexedDB queue in jobs/_detail.blade.php, which handles the photo data
// itself; this only covers the page shell around it). Jobs list/detail
// pages are cached the first time they're successfully loaded online, then
// served from that cache on a later offline visit - not pre-warmed on
// install, since we don't know which jobs a Worker will actually open.
const OMT_WORKER_CACHE = 'omt-worker-kiosk-v1';
const OMT_WORKER_PATH = '/jobs';

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(OMT_CACHE).then(function (cache) {
            return cache.add(OMT_OFFLINE_URL);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

// Network-first for navigations under /portal, falling back to the cached
// login page when offline - this app needs a live connection to raise or
// view tickets, so this is just enough to avoid a bare browser error when
// the client opens the installed app with no signal, not full offline mode.
self.addEventListener('fetch', function (event) {
    if (event.request.mode !== 'navigate') {
        return;
    }

    if (event.request.url.includes('/portal')) {
        event.respondWith(
            fetch(event.request).catch(function () {
                return caches.match(OMT_OFFLINE_URL);
            })
        );
        return;
    }

    if (event.request.url.includes(OMT_WORKER_PATH)) {
        event.respondWith(
            fetch(event.request).then(function (response) {
                var responseCopy = response.clone();
                caches.open(OMT_WORKER_CACHE).then(function (cache) {
                    cache.put(event.request, responseCopy);
                });
                return response;
            }).catch(function () {
                return caches.match(event.request, { cacheName: OMT_WORKER_CACHE });
            })
        );
    }
});

self.addEventListener('push', function (event) {
    const data = event.data ? event.data.json() : {};
    event.waitUntil(
        self.registration.showNotification(data.title || 'Notification', {
            body: data.body || '',
            icon: data.icon || '/favicon.ico',
        })
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const jobId = event.notification.data && event.notification.data.job_id;
    const ticketId = event.notification.data && event.notification.data.ticket_id;
    if (jobId) {
        event.waitUntil(clients.openWindow('/jobs/' + jobId));
    } else if (ticketId) {
        event.waitUntil(clients.openWindow('/portal/tickets/' + ticketId));
    }
});
