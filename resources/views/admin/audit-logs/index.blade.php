@extends('layouts.master')

@section('title', 'Audit Log')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('root')" label="Back to Dashboard" />
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Audit Log</h5>
            <p class="text-muted small">Every change across the app you have history access to. Individual records also show their own history via the History button on that page.</p>

            @if(empty($viewableTypes))
                <x-ui.empty-state icon="ri-shield-line" message="You don't have audit access to any record type yet." />
            @else
                <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-2 align-items-end mb-3">
                    <div class="col-auto">
                        <label for="filter-type" class="form-label small mb-1">Record Type</label>
                        <select id="filter-type" name="type" class="form-select">
                            <option value="">All</option>
                            @foreach($viewableTypes as $type)
                                <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="ri-filter-3-line align-middle me-1"></i> Apply</button>
                    </div>
                    @if(request('type'))
                        <div class="col-auto">
                            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    @endif
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-sm table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Type</th>
                                <th>Record #</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Field</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d M Y, H:i') }}</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $log->auditable_type)) }}</td>
                                    <td>{{ $log->auditable_id }}</td>
                                    <td>{{ $log->user->name ?? 'System' }}</td>
                                    <td>{{ $log->action }}</td>
                                    <td>{{ $log->field_name ? str_replace('_', ' ', $log->field_name) : '—' }}</td>
                                    <td>{{ $log->old_value ?? '—' }}</td>
                                    <td>{{ $log->new_value ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8"><x-ui.empty-state icon="ri-history-line" message="No history recorded yet." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
