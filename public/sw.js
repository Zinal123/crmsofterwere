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
    if (jobId) {
        event.waitUntil(clients.openWindow('/jobs/' + jobId));
    }
});
