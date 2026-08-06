<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-topbar="light" data-sidebar-image="none">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Oracle Machine Tech</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta content="Oracle Machine Tech CRM" name="description" />
    <meta content="Oracle Machine Tech" name="author" />
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico')}}">
    @include('layouts.head-css')
    <style>
        /*
         * Worker Kiosk shell - deliberately not the admin theme. High-contrast,
         * large-text, no soft/pastel button variants (those are low-contrast by
         * design, which fights glare on a shop floor). Works for both a shared
         * tablet and a personal phone; see EnforceIdleTimeout for the
         * device-trust distinction between the two.
         */
        body.worker-kiosk {
            font-size: 16px;
            background-color: #f4f6f9;
        }
        .worker-kiosk .worker-topbar {
            background-color: #1d2939;
            color: #fff;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .worker-kiosk .worker-topbar img {
            height: 32px;
        }
        .worker-kiosk .worker-topbar .worker-user {
            font-size: 1rem;
            font-weight: 600;
        }
        .worker-kiosk main {
            max-width: 640px;
            margin: 0 auto;
            padding: 20px 16px 40px;
        }
        .worker-kiosk h1, .worker-kiosk h4, .worker-kiosk h5, .worker-kiosk h6 {
            font-weight: 700;
        }
        .worker-kiosk label, .worker-kiosk .form-label {
            font-size: 1.125rem;
            font-weight: 600;
        }
        .worker-kiosk .form-control, .worker-kiosk .form-select, .worker-kiosk textarea {
            font-size: 1.125rem;
            padding: 14px 16px;
            min-height: 56px;
        }

        /*
         * High-contrast mode (Fiix precedent: "increase readability... in
         * low visibility environments"). Pure black-on-white, thicker
         * borders/outlines, no soft/pastel badge backgrounds - readable in
         * shop-floor glare. Toggled via [data-contrast="high"] on <html>,
         * applied pre-paint (see the inline script below) so there's no
         * flash of normal-contrast content on load.
         */
        html[data-contrast="high"] body.worker-kiosk {
            background-color: #fff;
            color: #000;
        }
        html[data-contrast="high"] .worker-kiosk .worker-topbar {
            background-color: #000;
        }
        html[data-contrast="high"] .worker-kiosk .card {
            border: 2px solid #000;
        }
        html[data-contrast="high"] .worker-kiosk .text-muted {
            color: #000 !important;
        }
        html[data-contrast="high"] .worker-kiosk .btn-shopfloor {
            border: 2px solid #000;
        }
        html[data-contrast="high"] .worker-kiosk .badge {
            border: 1px solid #000;
        }
    </style>
    <script>
        // Applied before <body> paints, so a returning Worker never sees a
        // flash of normal contrast before this flips.
        if (localStorage.getItem('worker-high-contrast') === '1') {
            document.documentElement.setAttribute('data-contrast', 'high');
        }
    </script>
</head>

<body class="worker-kiosk">
    <div class="worker-topbar">
        <img src="{{ URL::asset('build/images/oracallogo.png') }}" alt="Oracle Machine Tech">
        <div class="worker-user">
            {{ auth()->user()->name }}
            <button type="button" id="high-contrast-toggle" class="btn btn-outline-light btn-shopfloor ms-2" style="min-height: 44px; padding: 8px 16px; font-size: 1rem;" title="Toggle high-contrast mode" aria-label="Toggle high-contrast mode">
                <i class="ri-contrast-2-line align-bottom"></i>
            </button>
            <form action="{{ route('logout') }}" method="POST" class="d-inline ms-2">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-shopfloor" style="min-height: 44px; padding: 8px 16px; font-size: 1rem;">Log Out</button>
            </form>
        </div>
    </div>

    <main>
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                <i class="ri-checkbox-circle-line align-middle me-1"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" role="alert">
                <i class="ri-error-warning-line align-middle me-1"></i>{{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        document.getElementById('high-contrast-toggle').addEventListener('click', function () {
            var isHighContrast = document.documentElement.getAttribute('data-contrast') === 'high';
            if (isHighContrast) {
                document.documentElement.removeAttribute('data-contrast');
                localStorage.removeItem('worker-high-contrast');
            } else {
                document.documentElement.setAttribute('data-contrast', 'high');
                localStorage.setItem('worker-high-contrast', '1');
            }
        });
    </script>

    @include('layouts.vendor-scripts')
</body>
</html>
