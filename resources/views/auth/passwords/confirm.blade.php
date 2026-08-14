@extends('layouts.master-without-nav')
@section('title')
Confirm Password
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
        <div class="oms-auth-badge"><i class="ri-shield-keyhole-line"></i></div>
        <div class="oms-auth-card-header mb-4">
            <h5 class="mb-1">Confirm password</h5>
            <p class="text-muted mb-0">For your security, please confirm your password to continue.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="password-input">Password <span class="text-danger">*</span></label>
                <div class="position-relative auth-pass-inputgroup oms-input-group mt-1">
                    <i class="ri-lock-2-line oms-input-icon"></i>
                    <input type="password" class="form-control password-input pe-5 @error('password') is-invalid @enderror" name="password" placeholder="Enter password" id="password-input" required autofocus>
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon" title="Show password" aria-label="Show password"><i class="ri-eye-fill align-middle"></i></button>
                    @error('password')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-success w-100" type="submit">Confirm Password</button>
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
