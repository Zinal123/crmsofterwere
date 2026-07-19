@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $employee->name }} — Attendance Register ({{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }})</h5>
            <table class="table table-bordered">
                <thead><tr><th>Date</th><th>Status</th><th>Overtime Hours</th></tr></thead>
                <tbody>
                    @forelse($attendanceRows as $row)
                        <tr>
                            <td>{{ $row->date->toDateString() }}</td>
                            <td>{{ str_replace('_', ' ', $row->status) }}</td>
                            <td>{{ $row->overtime_hours }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-ui.empty-state icon="ri-calendar-line" message="No attendance marked for this month yet." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
