<?php

namespace App\Services\Reporting;

use App\Models\Invetry;
use App\Models\Job;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Job counts by status. Always includes every known status, zero-filled,
     * so the report has a stable shape regardless of what data exists.
     */
    public function jobThroughput(): array
    {
        $counts = Job::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return collect(['pending_approval', 'assigned', 'in_progress', 'on_hold', 'completed', 'rejected'])
            ->mapWithKeys(fn ($status) => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    /**
     * Per-Worker completed-job count and average hours from creation to
     * completion, across every Worker regardless of whether they have any
     * jobs yet (a technician with zero completed jobs is itself a signal).
     */
    public function technicianPerformance(): Collection
    {
        return User::role('Worker')->get()->map(function (User $worker) {
            $completed = Job::where('assigned_to', $worker->id)->where('status', 'completed')->get();

            $avgHours = $completed->isEmpty()
                ? null
                : round($completed->avg(fn (Job $job) => $job->created_at->diffInMinutes($job->completed_at) / 60), 1);

            return [
                'id' => $worker->id,
                'name' => $worker->name,
                'completed_count' => $completed->count(),
                'avg_completion_hours' => $avgHours,
            ];
        });
    }

    /**
     * Per-machine job count and most recent service (completed job) date.
     */
    public function machineServiceHistory(): Collection
    {
        return Machine::withCount('jobs')->get()->map(function (Machine $machine) {
            $lastCompleted = Job::where('machine_id', $machine->id)
                ->where('status', 'completed')
                ->latest('completed_at')
                ->value('completed_at');

            return [
                'id' => $machine->id,
                'name' => $machine->name,
                'job_count' => $machine->jobs_count,
                'last_service_at' => $lastCompleted,
            ];
        });
    }

    /**
     * Current stock level per inventory item. Deliberately not "inventory
     * turns" - that needs a stock-movement ledger (COGS / average inventory
     * value over a period) which this app doesn't track yet; this is an
     * honest current-levels view, not a fabricated turnover metric.
     */
    public function inventoryLevels(): Collection
    {
        return Invetry::with('product')->get()->map(fn (Invetry $item) => [
            'id' => $item->id,
            'product_name' => $item->product->name ?? 'Unknown part',
            'quantity' => $item->quantity,
            'is_low_stock' => $item->quantity < $item->effectiveLowStockThreshold(),
        ]);
    }

    /**
     * One of 5 states per machine, derived entirely from job data we already
     * have (this app has no real machine telemetry - not the MachineMetrics
     * "actual spindle signal" kind of status, an honest proxy built from
     * open-job state). Precedence when a machine matches more than one:
     * overdue > not reporting > active > setup > idle.
     */
    public function fleetStatus(): Collection
    {
        return Machine::all()->map(function (Machine $machine) {
            $jobs = Job::where('machine_id', $machine->id)->get();

            $status = match (true) {
                $jobs->contains(fn (Job $job) => $job->overdue_flagged_at !== null) => 'overdue',
                ! $machine->is_active => 'inactive',
                $jobs->contains(fn (Job $job) => $job->status === 'in_progress') => 'active',
                $jobs->contains(fn (Job $job) => in_array($job->status, ['pending_approval', 'assigned', 'on_hold'], true)) => 'setup',
                default => 'idle',
            };

            return [
                'id' => $machine->id,
                'name' => $machine->name,
                'status' => $status,
            ];
        });
    }
}
