{{-- resources/views/worker/jobs/_summary.blade.php --}}
<div id="worker-summary" class="mb-4">
    <h4 class="mb-3">{{ now()->format('H') < 12 ? 'Good morning' : (now()->format('H') < 17 ? 'Good afternoon' : 'Good evening') }}, {{ auth()->user()->name }}</h4>
    <div class="row g-2 text-center">
        @foreach([
            ['label' => 'To Do', 'value' => $summary['todo'], 'variant' => 'secondary', 'icon' => 'ri-inbox-line'],
            ['label' => 'In Progress', 'value' => $summary['in_progress'], 'variant' => 'primary', 'icon' => 'ri-loader-4-line'],
            ['label' => 'Overdue', 'value' => $summary['overdue'], 'variant' => 'danger', 'icon' => 'ri-alarm-warning-line'],
            ['label' => 'Completed Today', 'value' => $summary['completed_today'], 'variant' => 'success', 'icon' => 'ri-checkbox-circle-line'],
        ] as $tile)
        <div class="col-6 col-md-3">
            <div class="card border h-100 mb-0">
                <div class="card-body p-3">
                    <i class="{{ $tile['icon'] }} fs-3 text-{{ $tile['variant'] }}"></i>
                    <div class="fs-1 fw-bold lh-1 my-1">{{ $tile['value'] }}</div>
                    <div class="text-muted">{{ $tile['label'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
