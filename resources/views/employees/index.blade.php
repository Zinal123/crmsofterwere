@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.data-table-card title="Employees" :create-route="route('employees.create')" create-label="Add Employee">
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
                            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            <a href="{{ route('attendance.register', $employee->id) }}" class="btn btn-sm btn-secondary">Attendance</a>
                            <a href="{{ route('employees.payroll', $employee->id) }}" class="btn btn-sm btn-success">Payroll</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state icon="ri-team-line" message="No employees added yet." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.data-table-card>
</div>
@endsection
