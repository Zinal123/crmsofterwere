<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-topbar="light">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Oracle Machine Tech</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Oracle Machine Tech client support portal" name="description" />
    <meta name="theme-color" content="#c2410c">
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

    @include('layouts.vendor-scripts')
</body>

</html>
