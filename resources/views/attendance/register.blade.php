@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('employees.index')" label="Back to Employees" />
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $employee->name }} — Attendance Register ({{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }})</h5>
            <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>Date</th><th>Status</th><th>Overtime Hours</th><th></th></tr></thead>
                <tbody>
                    @forelse($attendanceRows as $row)
                        <tr>
                            <td>{{ $row->date->toDateString() }}</td>
                            <td>{{ str_replace('_', ' ', $row->status) }}</td>
                            <td>{{ $row->overtime_hours }}</td>
                            <td>
                                @can('attendance.view-audit')
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#auditTrailModal-attendance" data-audit-id="{{ $row->id }}">History</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-ui.empty-state icon="ri-calendar-line" message="No attendance marked for this month yet." /></td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@can('attendance.view-audit')
    <x-ui.audit-trail-modal type="attendance" />
@endcan
@endsection
