@extends('layouts.master-without-nav')

@section('title')
Access Denied
@endsection

@section('body')
<body>
@endsection
@section('content')
        <div class="auth-page-wrapper py-5 d-flex justify-content-center align-items-center min-vh-100">
            <div class="auth-page-content overflow-hidden p-0">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-xl-4 text-center">
                            <div class="mt-n4">
                                <h1 class="display-1 fw-medium">403</h1>
                                <h3 class="text-uppercase">Access Denied</h3>
                                <p class="text-muted mb-4">You don't have permission to view this page. If you believe this is a mistake, contact an administrator.</p>
                                <a href="{{ route('root') }}" class="btn btn-success"><i class="ri-home-4-line me-1"></i>Back to home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
