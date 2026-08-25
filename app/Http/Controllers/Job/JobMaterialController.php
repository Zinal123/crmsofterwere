<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Services\Job\JobMaterialService;
use Illuminate\Http\Request;

/**
 * Split out of JobController (which had grown to 21 methods, over Sonar's
 * 20-method-per-class threshold) - these 3 already shared a route-injected
 * JobMaterialService instead of constructor DI, a sign they were a distinct
 * concern bolted onto the job controller rather than genuinely part of it.
 */
class JobMaterialController extends Controller
{
    public function __construct(
        private JobRepositoryInterface $repository,
    ) {
    }

    public function addMaterial(Request $request, $job, JobMaterialService $svc)
    {
        $data = $request->validate(['product_id' => 'required|integer|exists:product,id', 'quantity' => 'required|integer|min:1']);
        $jobModel = $this->repository->find($job);
        abort_if(! $jobModel, 404);
        $svc->add($jobModel, (int) $data['product_id'], (int) $data['quantity']);

        return redirect()->route('jobs.show', $jobModel->id)->with('success', 'Material added to job.');
    }

    public function removeMaterial($material, JobMaterialService $svc)
    {
        $svc->remove((int) $material);

        return back()->with('success', 'Material removed.');
    }

    public function substituteMaterial(Request $request, $material, JobMaterialService $svc)
    {
        $data = $request->validate(['new_product_id' => 'required|integer|exists:product,id']);
        $svc->substitute((int) $material, (int) $data['new_product_id']);

        return back()->with('success', 'Material substituted.');
    }
}
