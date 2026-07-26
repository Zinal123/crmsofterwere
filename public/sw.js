const OMT_CACHE = 'omt-client-portal-v1';
const OMT_OFFLINE_URL = '/portal/login';

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
    if (event.request.mode !== 'navigate' || !event.request.url.includes('/portal')) {
        return;
    }

    event.respondWith(
        fetch(event.request).catch(function () {
            return caches.match(OMT_OFFLINE_URL);
        })
    );
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
