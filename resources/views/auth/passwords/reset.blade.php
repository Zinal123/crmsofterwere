@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.password-reset')
@endsection
@section('css')
@include('auth.partials.styles')
@endsection
@section('content')
<div class="oms-auth-shell">
    @include('auth.partials.hero')
    <div class="oms-auth-panel">
    <a href="{{ route('login') }}" class="oms-auth-logo">
        <img src="{{ URL::asset('build/images/oracallogo.png') }}" alt="Oracle Machine Tech">
    </a>
    <p class="oms-auth-tagline">CNC &amp; Laser Cutting Management</p>

    <div class="oms-auth-card">
        <div class="oms-auth-badge"><i class="ri-lock-password-line"></i></div>
        <div class="oms-auth-card-header mb-4">
            <h5 class="mb-1">Reset password</h5>
            <p class="text-muted mb-0">Choose a new password for your account.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3">
                <label for="useremail" class="form-label">Email <span class="text-danger">*</span></label>
                <div class="oms-input-group">
                    <i class="ri-mail-line oms-input-icon"></i>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="useremail" name="email" placeholder="Enter email" value="{{ $email ?? old('email') }}">
                </div>
                @error('email')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="userpassword">Password <span class="text-danger">*</span></label>
                <div class="position-relative auth-pass-inputgroup oms-input-group">
                    <i class="ri-lock-2-line oms-input-icon"></i>
                    <input type="password" class="form-control password-input pe-5 @error('password') is-invalid @enderror" name="password" id="userpassword" placeholder="Enter password">
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" title="Show password" aria-label="Show password"><i class="ri-eye-fill align-middle"></i></button>
                    @error('password')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password-confirm">Confirm Password <span class="text-danger">*</span></label>
                <div class="position-relative auth-pass-inputgroup oms-input-group">
                    <i class="ri-lock-2-line oms-input-icon"></i>
                    <input id="password-confirm" type="password" name="password_confirmation" class="form-control password-input pe-5" placeholder="Enter confirm password">
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" title="Show password" aria-label="Show password"><i class="ri-eye-fill align-middle"></i></button>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-success w-100" type="submit">Reset password</button>
            </div>
        </form>
    </div>

    <p class="oms-auth-below mb-0">Wait, I remember my password... <a href="{{ route('login') }}" class="fw-semibold">Sign in</a></p>

    <p class="oms-auth-footer mb-0">&copy; {{ date('Y') }} Oracle Machine Tech. All rights reserved.</p>
    </div>
</div>
@endsection
@section('script')
<script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
@endsection
