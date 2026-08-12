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
const OMT_WORKER_CACHE = 'omt-worker-kiosk-v2';
const OMT_WORKER_PATH = '/jobs';

// The IndexedDB the job page queues offline photos into (see jobs/_detail).
// The service worker drains it via Background Sync so photos upload even
// after the tab that captured them is closed.
const OMT_OFFLINE_DB = 'oracle-crm-offline-photos';
const OMT_OFFLINE_STORE = 'pending_photos';

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(OMT_CACHE).then(function (cache) {
            return cache.add(OMT_OFFLINE_URL);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    const keep = [OMT_CACHE, OMT_WORKER_CACHE];
    event.waitUntil(
        caches.keys().then(function (names) {
            return Promise.all(names.map(function (name) {
                if (keep.indexOf(name) === -1) { return caches.delete(name); }
            }));
        }).then(function () { return self.clients.claim(); })
    );
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

// ---- Offline photo outbox drain (Background Sync) -------------------------
function omtOpenDb() {
    return new Promise(function (resolve, reject) {
        var req = indexedDB.open(OMT_OFFLINE_DB, 1);
        req.onsuccess = function () { resolve(req.result); };
        req.onerror = function () { reject(req.error); };
    });
}

function omtAllPending(db) {
    return new Promise(function (resolve, reject) {
        var req = db.transaction(OMT_OFFLINE_STORE, 'readonly').objectStore(OMT_OFFLINE_STORE).getAll();
        req.onsuccess = function () { resolve(req.result || []); };
        req.onerror = function () { reject(req.error); };
    });
}

function omtDelete(db, id) {
    return new Promise(function (resolve) {
        var tx = db.transaction(OMT_OFFLINE_STORE, 'readwrite');
        tx.objectStore(OMT_OFFLINE_STORE).delete(id);
        tx.oncomplete = function () { resolve(); };
        tx.onerror = function () { resolve(); };
    });
}

// Upload each queued photo. Same-origin fetch carries the session cookie;
// CSRF is sent as the header Laravel accepts, using the token captured when
// the photo was queued. A confirmed upload (ok/redirect) removes the record;
// a stale session (419/401) stops the loop WITHOUT deleting (nothing lost),
// and a network error rejects so the browser retries the sync later.
async function omtDrainOutbox() {
    var db = await omtOpenDb();
    var records = await omtAllPending(db);
    for (var i = 0; i < records.length; i++) {
        var r = records[i];
        var form = new FormData();
        // CSRF as a _token field, exactly like the page's uploadPhotoBlob.
        form.append('_token', r.csrfToken || '');
        form.append('photo', r.blob, 'proof-' + (r.queuedAt || Date.now()) + '.jpg');
        form.append('stage', r.stage || 'general');
        if (r.latitude) { form.append('latitude', r.latitude); }
        if (r.longitude) { form.append('longitude', r.longitude); }
        var resp = await fetch(r.url, {
            method: 'POST',
            body: form,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (resp.ok || resp.redirected) {
            await omtDelete(db, r.id);
        } else if (resp.status === 419 || resp.status === 401) {
            break;
        }
    }
    var clientsList = await self.clients.matchAll();
    clientsList.forEach(function (c) { c.postMessage({ type: 'omt-outbox-drained' }); });
}

self.addEventListener('sync', function (event) {
    if (event.tag === 'omt-photo-outbox') {
        event.waitUntil(omtDrainOutbox());
    }
});

// Shared-tablet isolation: the page asks the SW to drop its cached worker
// pages on logout so the next worker never sees the previous one's jobs.
self.addEventListener('message', function (event) {
    if (event.data && event.data.type === 'omt-clear-worker-cache') {
        event.waitUntil(caches.delete(OMT_WORKER_CACHE));
    }
});
