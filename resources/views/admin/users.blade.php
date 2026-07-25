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
                        <label class="form-label" for="create-user-name">Name <span class="text-danger">*</span></label>
                        <input id="create-user-name" type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="create-user-email">Email <span class="text-danger">*</span></label>
                        <input id="create-user-email" type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="create-user-password">Temporary Password <span class="text-danger">*</span></label>
                        <input id="create-user-password" type="text" class="form-control" name="password" minlength="8" required>
                        <div class="form-text">Share this with the user directly. They can change it after logging in via their profile page.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="create-user-role">Role <span class="text-danger">*</span></label>
                        <select id="create-user-role" class="form-select" name="role" required>
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
        <x-ui.data-table-card title="Users">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="users-table">
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
                                @can('admin.view-audit')
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" data-bs-toggle="modal" data-bs-target="#auditTrailModal-user" data-audit-id="{{ $user->id }}">History</button>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>
@can('admin.view-audit')
    <x-ui.audit-trail-modal type="user" />
@endcan
@endsection

@section('script')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" integrity="sha384-Dv1j0mqPOKbG6R+/4/adHCn5JaMBLG3iu8uTXFBM2MjEZuKwtsyLedRcRMR0cq7P" crossorigin="anonymous" rel="stylesheet" type="text/css" />
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" integrity="sha384-ficRBwtap/VLzILv81vIvgp30PoJYnlCm96tPpNYHXAf+h9SIThOZxxIzRUzbpAh" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js" integrity="sha384-jIAE3P7Re8BgMkT0XOtfQ6lzZgbDw/02WeRMJvXK3WMHBNynEx5xofqia1OHuGh0" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new DataTable('#users-table', {
        language: {
            emptyTable: 'No users yet.',
            zeroRecords: 'No matching users found.',
            search: 'Search users:',
        }
    });
});
</script>
@endsection
