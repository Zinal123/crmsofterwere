@extends('layouts.master')
@section('title')
Users
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Admin
@endslot
@slot('title')
Users
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Create User</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Temporary Password</label>
                        <input type="text" class="form-control" name="password" minlength="8" required>
                        <div class="form-text">Share this with the user directly. They can change it after logging in via their profile page.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Create User</x-ui.button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">All Users</h5></div>
            <div class="card-body table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->roles->pluck('name')->first() ?? '-' }}</td>
                            <td>
                                <x-ui.status-badge
                                    :status="$user->is_active ? 'Active' : 'Inactive'"
                                    :variant="$user->is_active ? 'success' : 'danger'"
                                    :icon="$user->is_active ? 'ri-checkbox-circle-line' : 'ri-close-circle-line'"
                                />
                            </td>
                            <td>
                                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" class="form-select form-select-sm">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ $user->roles->pluck('name')->first() === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
