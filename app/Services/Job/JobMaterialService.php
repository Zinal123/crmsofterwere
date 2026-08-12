<?php
namespace App\Services\Job;

use App\Models\Job;
use App\Models\JobMaterial;
use App\Services\Inventory\AvailabilityService;

class JobMaterialService
{
    public function __construct(private AvailabilityService $availability)
    {
    }

    public function add(Job $job, int $productId, int $quantity): JobMaterial
    {
        return $job->materials()->create(['product_id' => $productId, 'quantity' => max(1, $quantity)]);
    }

    public function remove(int $materialId): void
    {
        JobMaterial::where('id', $materialId)->delete();
    }

    public function substitute(int $materialId, int $newProductId): JobMaterial
    {
        $material = JobMaterial::findOrFail($materialId);
        $material->update(['product_id' => $newProductId]);

        return $material;
    }

    /**
     * Per-material availability for a job. Availability is computed EXCLUDING
     * this job's own demand (via the service's $excludingJobId), so a job is
     * judged against the stock other requests/jobs have not already claimed.
     */
    public function availabilityFor(Job $job): array
    {
        $lines = $job->materials()->with('product')->get()->map(function (JobMaterial $m) use ($job) {
            $free = $this->availability->available($m->product_id, null, $job->id);

            return [
                'product_name' => $m->product->name ?? 'Unknown part',
                'required' => $m->quantity,
                'available' => $free,
                'ok' => $m->quantity <= $free,
                'material_id' => $m->id,
            ];
        });

        return ['all_available' => $lines->every(fn ($l) => $l['ok']), 'lines' => $lines];
    }
}
