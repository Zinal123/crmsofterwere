@extends('layouts.master')
@section('title') My Profile @endsection

@section('content')
@php
    $avatarUrl = $user->avatar ? URL::asset('images/' . $user->avatar) : URL::asset('build/images/users/avatar-1.jpg');
@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box"><h4 class="mb-0">My Profile</h4></div>
    </div>
</div>

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

<div class="row">
    <!-- Profile summary -->
    <div class="col-xl-4">
        <div class="card dash-card">
            <div class="card-body text-center">
                <img src="{{ $avatarUrl }}" alt="Avatar" id="avatarPreview" class="rounded-circle avatar-xl img-thumbnail mb-3" style="width:120px;height:120px;object-fit:cover;">
                <h5 class="mb-1">{{ $user->name }}</h5>
                <span class="badge bg-primary-subtle text-primary mb-3">{{ $user->getRoleNames()->first() ?? 'User' }}</span>
                <div class="text-start mt-2">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted"><i class="ri-mail-line align-middle me-1"></i> Email</span>
                        <span class="fw-medium text-truncate ms-2">{{ $user->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted"><i class="ri-shield-check-line align-middle me-1"></i> Status</span>
                        <span>
                            @if($user->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted"><i class="ri-calendar-2-line align-middle me-1"></i> Member since</span>
                        <span class="fw-medium">{{ optional($user->created_at)->format('d M Y') ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings -->
    <div class="col-xl-8">
        <!-- Personal information -->
        <div class="card dash-card">
            <div class="card-header border-0"><h5 class="card-title mb-0">Personal Information</h5></div>
            <div class="card-body">
                <form action="{{ route('updateProfile', $user->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="avatar" class="form-label">Profile Photo</label>
                            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png" class="form-control @error('avatar') is-invalid @enderror" onchange="previewAvatar(event)">
                            <div class="form-text">JPG or PNG, up to 1 MB.</div>
                            @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="ri-save-line align-middle me-1"></i> Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change password -->
        <div class="card dash-card" id="change-password">
            <div class="card-header border-0"><h5 class="card-title mb-0">Change Password</h5></div>
            <div class="card-body">
                <div id="passwordAlert"></div>
                <form id="changePasswordForm" action="{{ route('updatePassword', $user->id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" id="password" name="password" class="form-control" minlength="6" required>
                            <div class="form-text">At least 6 characters.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="6" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="changePasswordBtn"><i class="ri-lock-2-line align-middle me-1"></i> Update Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function previewAvatar(e) {
        var file = e.target.files[0];
        if (file) document.getElementById('avatarPreview').src = URL.createObjectURL(file);
    }

    document.getElementById('changePasswordForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var form = e.target;
        var btn = document.getElementById('changePasswordBtn');
        var alertBox = document.getElementById('passwordAlert');
        var pwd = form.password.value;
        var confirm = form.password_confirmation.value;

        function show(type, msg) {
            alertBox.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + msg +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }

        if (pwd !== confirm) { show('danger', 'New password and confirmation do not match.'); return; }

        btn.disabled = true;
        var original = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': form._token.value, 'Accept': 'application/json' },
            body: new FormData(form)
        })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, status: r.status, data: d }; }); })
        .then(function (res) {
            if (res.status === 422) {
                var first = res.data.errors ? Object.values(res.data.errors)[0][0] : 'Please check your input.';
                show('danger', first);
            } else if (res.data.isSuccess) {
                show('success', res.data.Message || 'Password updated.');
                form.reset();
            } else {
                show('danger', res.data.Message || 'Could not update password.');
            }
        })
        .catch(function () { show('danger', 'Something went wrong. Please try again.'); })
        .finally(function () { btn.disabled = false; btn.innerHTML = original; });
    });
</script>
@endsection
