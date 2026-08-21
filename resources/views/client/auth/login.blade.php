@extends('layouts.master-without-nav')
@section('title')
Client Login
@endsection
@section('css')
@include('auth.partials.styles')
@endsection
@section('content')
<div class="oms-auth-shell">
    @include('auth.partials.hero')
    <div class="oms-auth-panel">
    <a href="{{ route('client.login') }}" class="oms-auth-logo">
        <img src="{{ URL::asset('build/images/oracallogo.png') }}" alt="Oracle Machine Tech">
    </a>
    <p class="oms-auth-tagline">Client Support Portal</p>

    <div class="oms-auth-card">
        @error('login')
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line align-middle me-1"></i>{{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @enderror

        <div class="oms-auth-badge"><i class="ri-customer-service-2-line"></i></div>
        <div class="oms-auth-card-header mb-4">
            <h5 class="mb-1">Welcome back</h5>
            <p class="text-muted mb-0">Sign in to view your machine and raise a ticket.</p>
        </div>

        <form action="{{ route('client.login.attempt') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="login" class="form-label">Email or Phone <span class="text-danger">*</span></label>
                <div class="oms-input-group">
                    <i class="ri-user-line oms-input-icon"></i>
                    <input type="text" class="form-control" value="{{ old('login') }}" id="login" name="login" placeholder="Enter email or phone" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password-input">Password <span class="text-danger">*</span></label>
                <div class="position-relative auth-pass-inputgroup oms-input-group mt-1">
                    <i class="ri-lock-2-line oms-input-icon"></i>
                    <input type="password" class="form-control password-input pe-5" name="password" placeholder="Enter password" id="password-input" required>
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon" title="Show password" aria-label="Show password"><i class="ri-eye-fill align-middle"></i></button>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary w-100" type="submit">Sign In</button>
            </div>
        </form>
    </div>

    <p class="oms-auth-footer mb-0">&copy; {{ date('Y') }} Oracle Machine Tech. All rights reserved.</p>
    </div>
</div>
@endsection
@section('script')
<script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
@endsection
