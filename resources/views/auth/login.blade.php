@extends('layouts.master-without-nav')
@section('title')
@lang('translation.signin')
@endsection
@section('css')
@include('auth.partials.styles')
@endsection
@section('content')
<div class="oms-auth-shell">
    @include('auth.partials.hero')
    <div class="oms-auth-panel">
    <a href="{{ route('root') }}" class="oms-auth-logo">
        <img src="{{ URL::asset('build/images/oracallogo.png') }}" alt="Oracle Machine Tech">
    </a>
    <p class="oms-auth-tagline">CNC &amp; Laser Cutting Management</p>

    <div class="oms-auth-card">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line align-middle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="oms-auth-badge"><i class="ri-shield-check-line"></i></div>
        <div class="oms-auth-card-header mb-4">
            <h5 class="mb-1">Welcome back</h5>
            <p class="text-muted mb-0">Sign in to continue.</p>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="username" class="form-label">Email <span class="text-danger">*</span></label>
                <div class="oms-input-group">
                    <i class="ri-mail-line oms-input-icon"></i>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" id="username" name="email" placeholder="Enter your email">
                </div>
                @error('email')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-1">
                    <label class="form-label mb-0" for="password-input">Password <span class="text-danger">*</span></label>
                    <a href="{{ route('password.update') }}" class="text-muted small">Forgot password?</a>
                </div>
                <div class="position-relative auth-pass-inputgroup oms-input-group mt-1">
                    <i class="ri-lock-2-line oms-input-icon"></i>
                    <input type="password" class="form-control password-input pe-5 @error('password') is-invalid @enderror" name="password" placeholder="Enter password" id="password-input">
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon" title="Show password" aria-label="Show password"><i class="ri-eye-fill align-middle"></i></button>
                    @error('password')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    Remember this device
                </label>
                <div class="form-text">Only check this on your own phone — leave unchecked on a shared shop tablet, which will sign out automatically after 15 minutes idle.</div>
            </div>

            <div class="mt-4">
                <button class="btn btn-success w-100" type="submit">Sign In</button>
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
