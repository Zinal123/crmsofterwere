@extends('layouts.master-without-nav')
@section('title')
@lang('translation.password-reset')
@endsection
@section('css')
@include('auth.partials.styles')
@endsection
@section('content')
<div class="oms-auth">
    <a href="{{ route('root') }}" class="oms-auth-logo">
        <img src="{{ URL::asset('build/images/oracallogo.png') }}" alt="Oracle Machine Tech">
    </a>
    <p class="oms-auth-tagline">CNC &amp; Laser Cutting Management</p>

    <div class="oms-auth-card">
        <div class="oms-auth-badge"><i class="ri-shield-keyhole-line"></i></div>
        <div class="oms-auth-card-header mb-4">
            <h5 class="mb-1">Forgot password?</h5>
            <p class="text-muted mb-0">Enter your email and we&rsquo;ll send you reset instructions.</p>
        </div>

        @if(session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-3">
                <label for="useremail" class="form-label">Email <span class="text-danger">*</span></label>
                <div class="oms-input-group">
                    <i class="ri-mail-line oms-input-icon"></i>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="useremail" name="email" placeholder="Enter email" value="{{ old('email') }}">
                </div>
                @error('email')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mt-4">
                <button class="btn btn-success w-100" type="submit">Reset password</button>
            </div>
        </form>
    </div>

    <p class="oms-auth-below mb-0">Remember your password? <a href="{{ route('login') }}" class="fw-semibold">Sign in</a></p>

    <p class="oms-auth-footer mb-0">&copy; {{ date('Y') }} Oracle Machine Tech. All rights reserved.</p>
</div>
@endsection
