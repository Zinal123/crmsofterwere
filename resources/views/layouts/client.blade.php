<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-topbar="light">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Oracle Machine Tech</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Oracle Machine Tech client support portal" name="description" />
    <meta name="theme-color" content="#4361ee">
    <link rel="manifest" href="{{ URL::asset('manifest.json') }}">
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico')}}">
    @include('layouts.head-css')
    <style>
        .client-topbar { background: #fff; border-bottom: 1px solid #eee; padding: .75rem 1rem; display: flex; align-items: center; justify-content: space-between; }
        .client-topbar img { height: 32px; }
        .client-content { max-width: 720px; margin: 0 auto; padding: 1rem; }
    </style>
    @yield('css')
</head>

<body>
    @auth('client')
    <div id="push-subscribe-banner" data-vapid-key="{{ config('webpush.vapid.public_key') }}" data-endpoint="{{ route('client.push-subscriptions.store') }}" class="alert alert-info d-flex justify-content-between align-items-center m-0 rounded-0" style="display:none">
        <span><i class="ri-notification-3-line"></i> Enable notifications for ticket updates?</span>
        <span>
            <x-ui.button variant="primary" size="sm" data-push-enable type="button">Enable</x-ui.button>
            <x-ui.button variant="secondary" size="sm" data-push-dismiss type="button" icon="ri-close-line" ariaLabel="Dismiss">Not now</x-ui.button>
        </span>
    </div>
    <script>
        if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
            var banner = document.getElementById('push-subscribe-banner');
            if (banner) { banner.style.display = 'flex'; }
        }
    </script>
    @endauth

    <div class="client-topbar">
        <a href="{{ route('client.dashboard') }}"><img src="{{ URL::asset('build/images/oracallogo.png') }}" alt="Oracle Machine Tech"></a>
        @auth('client')
        <form action="{{ route('client.logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="ri-logout-box-line align-bottom"></i> Logout</button>
        </form>
        @endauth
    </div>

    <div class="client-content">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-checkbox-circle-line align-middle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line align-middle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @yield('content')
    </div>

    {{-- Deliberately not @include('layouts.vendor-scripts') here: that now
         loads app.js globally (see the sidebar-scroll fix), which assumes
         staff-layout elements (#scrollbar, .navbar-menu, ...) that don't
         exist on this simpler client-only layout and throws on load. The
         portal only needs Bootstrap's JS (dropdowns/modals/alert dismiss),
         not the rest of the admin template's machinery. --}}
    <script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @yield('script')
    <script>
        // Registered unconditionally (not just on push opt-in) so the
        // browser sees an active service worker + manifest as soon as the
        // portal loads - both are required before "Add to Home Screen"
        // becomes available.
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
    @auth('client')
    <script src="{{ asset('build/js/push-subscribe.js') }}"></script>
    @endauth
</body>

</html>
