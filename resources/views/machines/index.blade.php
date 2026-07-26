@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('root')" label="Back to Dashboard" />
    <x-ui.data-table-card title="Machines">
        <table class="table table-bordered align-middle" id="machinesTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($machines as $machine)
                    <tr>
                        <td>{{ $machine->name }}</td>
                        <td>
                            <x-ui.status-badge
                                :status="$machine->is_active ? 'Active' : 'Inactive'"
                                :variant="$machine->is_active ? 'success' : 'secondary'"
                                icon="{{ $machine->is_active ? 'ri-checkbox-circle-line' : 'ri-close-circle-line' }}" />
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <form action="{{ route('machines.toggle', $machine->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <x-ui.button variant="secondary" :soft="true" size="sm" type="submit" :icon="$machine->is_active ? 'ri-forbid-line' : 'ri-toggle-line'" :ariaLabel="$machine->is_active ? 'Disable' : 'Enable'" data-bs-toggle="tooltip" :title="$machine->is_active ? 'Disable' : 'Enable'" />
                                </form>
                                @can('machines.view-audit')
                                <x-ui.button variant="secondary" :soft="true" size="sm" type="button" icon="ri-history-line" data-bs-toggle="modal" data-bs-target="#auditTrailModal-machine" data-audit-id="{{ $machine->id }}" ariaLabel="History" title="History" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-ui.empty-state icon="ri-tools-line" message="No machines added yet." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.data-table-card>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Add Machine</h5>
            <form action="{{ route('machines.store') }}" method="POST" class="d-flex gap-2">
                @csrf
                <input type="text" name="name" class="form-control" placeholder="Machine name" required>
                <x-ui.button variant="success" type="submit" icon="ri-add-line" ariaLabel="Add machine">Add</x-ui.button>
            </form>
        </div>
    </div>
</div>
@can('machines.view-audit')
    <x-ui.audit-trail-modal type="machine" />
@endcan
@endsection
