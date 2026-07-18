// Requests notification permission and registers the service worker + push
// subscription. Loaded only on pages behind auth (topbar include), guarded
// so it degrades silently on unsupported browsers instead of throwing.
(function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return;
    }

    const banner = document.getElementById('push-subscribe-banner');

    function subscribe(vapidPublicKey) {
        navigator.serviceWorker.register('/sw.js').then(function (registration) {
            return registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: vapidPublicKey,
            });
        }).then(function (subscription) {
            const key = subscription.getKey('p256dh');
            const token = subscription.getKey('auth');
            return fetch('/push-subscriptions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    publicKey: key ? btoa(String.fromCharCode(...new Uint8Array(key))) : null,
                    authToken: token ? btoa(String.fromCharCode(...new Uint8Array(token))) : null,
                    contentEncoding: 'aesgcm',
                }),
            });
        }).then(function () {
            if (banner) banner.remove();
        }).catch(function () {
            // Silently degrade — the in-app bell remains the reliable channel.
        });
    }

    if (banner) {
        banner.querySelector('[data-push-enable]').addEventListener('click', function () {
            Notification.requestPermission().then(function (permission) {
                if (permission === 'granted') {
                    subscribe(banner.dataset.vapidKey);
                } else {
                    banner.remove();
                }
            });
        });
        banner.querySelector('[data-push-dismiss]').addEventListener('click', function () {
            banner.remove();
        });
    }
})();
