@extends('layouts.master')
@section('title')
Roles & Permissions
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Admin
@endslot
@slot('title')
Roles & Permissions
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Create Role</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <input type="text" class="form-control" name="name" placeholder="Role name" required>
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Create Role</x-ui.button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <x-ui.data-table-card title="Roles & Permissions">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle" id="roles-matrix-table">
                    <thead>
                        <tr>
                            <th>Permission</th>
                            @foreach($roles as $role)
                                <th class="text-center">
                                    <span class="badge bg-primary-subtle text-primary fs-12">{{ $role->name }}</span>
                                    @can('admin.view-audit')
                                    <button type="button" class="btn btn-soft-secondary btn-sm p-1 ms-1" data-bs-toggle="modal" data-bs-target="#auditTrailModal-role" data-audit-id="{{ $role->id }}" title="History" aria-label="History">
                                        <i class="ri-history-line align-bottom"></i>
                                    </button>
                                    @endcan
                                    @if($role->name !== 'Owner')
                                        <button type="button" class="btn btn-soft-primary btn-sm p-1 ms-1" data-bs-toggle="modal" data-bs-target="#renameRole-{{ $role->id }}" title="Rename Role" aria-label="Rename Role">
                                            <i class="ri-edit-line align-bottom"></i>
                                        </button>
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-sm p-1 ms-1" data-confirm-delete data-bs-toggle="tooltip" title="Delete Role" aria-label="Delete Role">
                                                <i class="ri-delete-bin-fill align-bottom"></i>
                                            </button>
                                        </form>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $permission)
                        <tr>
                            <td>{{ $permission }}</td>
                            @foreach($roles as $role)
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center mb-0">
                                        <input type="checkbox"
                                            class="form-check-input permission-toggle"
                                            data-role-id="{{ $role->id }}"
                                            data-permission="{{ $permission }}"
                                            aria-label="{{ $permission }} for {{ $role->name }}"
                                            {{ $role->permissions->pluck('name')->contains($permission) ? 'checked' : '' }}>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>

@foreach($roles as $role)
    @if($role->name !== 'Owner')
    <div class="modal fade" id="renameRole-{{ $role->id }}" tabindex="-1" aria-labelledby="renameRole-{{ $role->id }}-label" aria-modal="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="renameRole-{{ $role->id }}-label">Rename Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-2">
                            <label class="form-label" for="rename-role-name-{{ $role->id }}">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="rename-role-name-{{ $role->id }}" name="name" value="{{ $role->name }}" required>
                        </div>
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@can('admin.view-audit')
    <x-ui.audit-trail-modal type="role" />
@endcan
<x-ui.confirm-modal recordType="role" />
@endsection

@section('script')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" integrity="sha384-Dv1j0mqPOKbG6R+/4/adHCn5JaMBLG3iu8uTXFBM2MjEZuKwtsyLedRcRMR0cq7P" crossorigin="anonymous" rel="stylesheet" type="text/css" />
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" integrity="sha384-ficRBwtap/VLzILv81vIvgp30PoJYnlCm96tPpNYHXAf+h9SIThOZxxIzRUzbpAh" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js" integrity="sha384-jIAE3P7Re8BgMkT0XOtfQ6lzZgbDw/02WeRMJvXK3WMHBNynEx5xofqia1OHuGh0" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new DataTable('#roles-matrix-table', {
        paging: false,
        searching: false,
        info: false,
        ordering: false,
    });
});
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.permission-toggle').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            var roleId = this.dataset.roleId;
            var permission = this.dataset.permission;
            var checkboxEl = this;

            fetch('/admin/roles/' + roleId + '/toggle-permission', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ permission: permission }),
            })
            .then(function (res) { return res.json(); })
            .then(function (data) { checkboxEl.checked = data.granted; })
            .catch(function () { checkboxEl.checked = !checkboxEl.checked; });
        });
    });
});
</script>
@endsection
