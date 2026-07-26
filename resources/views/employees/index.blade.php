@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.data-table-card title="Employees" :create-route="route('employees.create')" create-label="Add Employee">
        <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Pay Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->phone }}</td>
                        <td>{{ $employee->department }}</td>
                        <td>{{ ucfirst($employee->pay_type) }}</td>
                        <td>
                            <x-ui.status-badge
                                :status="$employee->is_active ? 'Active' : 'Inactive'"
                                :variant="$employee->is_active ? 'success' : 'secondary'"
                                icon="{{ $employee->is_active ? 'ri-checkbox-circle-line' : 'ri-close-circle-line' }}" />
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" title="Edit" aria-label="Edit"><i class="ri-edit-line align-bottom"></i></a>
                                @can('attendance.view')
                                <a href="{{ route('attendance.register', $employee->id) }}" class="btn btn-soft-secondary btn-sm" data-bs-toggle="tooltip" title="Attendance" aria-label="Attendance"><i class="ri-calendar-check-line align-bottom"></i></a>
                                @endcan
                                @can('payroll.view')
                                <a href="{{ route('employees.payroll', $employee->id) }}" class="btn btn-soft-success btn-sm" data-bs-toggle="tooltip" title="Payroll" aria-label="Payroll"><i class="ri-wallet-3-line align-bottom"></i></a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state icon="ri-team-line" message="No employees added yet." /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-ui.data-table-card>
</div>
@endsection
