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
                    <button type="submit" class="btn btn-success">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Permission Matrix</h5></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Permission</th>
                            @foreach($roles as $role)
                                <th class="text-center">
                                    {{ $role->name }}
                                    @if($role->name !== 'Owner')
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 ms-1">&times;</button>
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
                                    <input type="checkbox"
                                        class="form-check-input permission-toggle"
                                        data-role-id="{{ $role->id }}"
                                        data-permission="{{ $permission }}"
                                        {{ $role->permissions->pluck('name')->contains($permission) ? 'checked' : '' }}>
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
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
