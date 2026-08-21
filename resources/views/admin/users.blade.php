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
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-xs flex-shrink-0">
                                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-13">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php $roleName = $user->roles->pluck('name')->first(); @endphp
                                @if($roleName)
                                    <span class="badge bg-{{ $roleName === 'Owner' ? 'primary' : 'secondary' }}-subtle text-{{ $roleName === 'Owner' ? 'primary' : 'secondary' }}">{{ $roleName }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <x-ui.toggle :checked="$user->is_active" name="is_active" :ariaLabel="$user->name . ' active status'" :formId="'user-role-form-' . $user->id" />
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap mb-1">
                                    <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal-{{ $user->id }}" title="Edit" aria-label="Edit user">
                                        <i class="ri-edit-line align-bottom"></i>
                                    </button>
                                    @can('admin.view-audit')
                                    <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#auditTrailModal-user" data-audit-id="{{ $user->id }}" title="History" aria-label="View history">
                                        <i class="ri-history-line align-bottom"></i>
                                    </button>
                                    @endcan
                                </div>
                                <form id="user-role-form-{{ $user->id }}" action="{{ route('admin.users.update', $user->id) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <label class="visually-hidden" for="user-role-select-{{ $user->id }}">Role for {{ $user->name }}</label>
                                    <select id="user-role-select-{{ $user->id }}" name="role" class="form-select form-select-sm">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ $user->roles->pluck('name')->first() === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Save Role</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        @foreach($users as $user)
        <div class="modal fade" id="editUserModal-{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.users.update-profile', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <label class="form-label" for="edit-user-name-{{ $user->id }}">Name <span class="text-danger">*</span></label>
                                <input id="edit-user-name-{{ $user->id }}" type="text" class="form-control" name="name" value="{{ $user->name }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="edit-user-email-{{ $user->id }}">Email <span class="text-danger">*</span></label>
                                <input id="edit-user-email-{{ $user->id }}" type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="edit-user-password-{{ $user->id }}">New Password</label>
                                <input id="edit-user-password-{{ $user->id }}" type="text" class="form-control" name="password" minlength="8">
                                <div class="form-text">Leave blank to keep the current password.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <x-ui.button type="submit" variant="success" icon="ri-save-line">Save Changes</x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
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
