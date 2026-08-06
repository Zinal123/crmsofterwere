@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('root')" label="Back to Dashboard" />

    <h4 class="mb-3">Reports</h4>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Job Throughput</h5>
            <div class="row g-3">
                @foreach($jobThroughput as $status => $count)
                    <div class="col-6 col-md-2">
                        <div class="border rounded p-3 text-center">
                            <div class="fs-22 fw-semibold">{{ $count }}</div>
                            <div class="text-muted small">{{ ucfirst(str_replace('_', ' ', $status)) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Technician Performance</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr><th>Worker</th><th>Completed Jobs</th><th>Avg. Completion Time</th></tr>
                    </thead>
                    <tbody>
                        @forelse($technicianPerformance as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['completed_count'] }}</td>
                                <td>{{ $row['avg_completion_hours'] !== null ? $row['avg_completion_hours'] . ' hrs' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-ui.empty-state icon="ri-user-line" message="No Workers yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Machine Service History</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr><th>Machine</th><th>Total Jobs</th><th>Last Service</th></tr>
                    </thead>
                    <tbody>
                        @forelse($machineHistory as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['job_count'] }}</td>
                                <td>{{ $row['last_service_at'] ? \Illuminate\Support\Carbon::parse($row['last_service_at'])->format('d M Y') : 'Never' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-ui.empty-state icon="ri-tools-line" message="No machines yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Inventory Levels</h5>
            <p class="text-muted small">Current stock on hand. This is not an inventory-turns report — that needs a stock-movement history this app doesn't track yet.</p>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr><th>Part</th><th>Quantity</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryLevels as $row)
                            <tr>
                                <td>{{ $row['product_name'] }}</td>
                                <td>{{ $row['quantity'] }}</td>
                                <td>
                                    @if($row['is_low_stock'])
                                        <x-ui.status-badge status="Low Stock" variant="danger" icon="ri-error-warning-line" />
                                    @else
                                        <x-ui.status-badge status="In Stock" variant="success" icon="ri-checkbox-circle-line" />
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-ui.empty-state icon="ri-archive-line" message="No inventory records yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
