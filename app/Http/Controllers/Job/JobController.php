<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Models\JobChecklistItem;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Job\JobChecklistService;
use App\Services\Job\JobService;
use App\Services\Job\MachineService;
use App\Services\Ticketing\TicketService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(
        private JobService $service,
        private MachineService $machineService,
        private JobRepositoryInterface $repository,
        private \App\Services\Job\JobPhotoService $photoService,
        private UserRepositoryInterface $userRepository,
        private TicketService $ticketService,
        private JobChecklistService $checklistService,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $canViewAll = $user->can('jobs.view-all');
        $jobs = $canViewAll ? $this->repository->all() : $this->repository->allForUser($user);

        // A plain Worker (no jobs.view-all) gets the Worker Kiosk: a distinct,
        // large-touch-target surface for shop-floor use, not the admin theme.
        if ($canViewAll) {
            $jobs->loadMissing('materials');
            $materialStatus = $jobs->filter(fn ($j) => $j->materials->isNotEmpty())
                ->mapWithKeys(fn ($j) => [$j->id => app(\App\Services\Job\JobMaterialService::class)->availabilityFor($j)['all_available']]);

            return view('jobs.index', compact('jobs', 'materialStatus'));
        }

        $machines = $this->machineService->list();

        $today = now()->toDateString();
        $summary = [
            'todo' => $jobs->whereIn('status', ['assigned', 'on_hold'])->count(),
            'in_progress' => $jobs->where('status', 'in_progress')->count(),
            'overdue' => $jobs->filter(fn ($j) => $j->overdue_flagged_at && $j->status !== 'completed')->count(),
            'completed_today' => $jobs->filter(fn ($j) => $j->status === 'completed' && optional($j->updated_at)->toDateString() === $today)->count(),
        ];

        return view('worker.jobs.index', compact('jobs', 'machines', 'summary'));
    }

    public function create(Request $request)
    {
        return view('jobs.create', [
            'machines' => $this->machineService->list(),
            'canAssign' => $request->user()->can('jobs.assign'),
            'workers' => $this->userRepository->byRole('Worker'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'machine_id' => 'nullable|exists:machines,id',
            'site_name' => 'nullable|string|max:255',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        try {
            if ($request->user()->can('jobs.assign') && ! empty($data['assigned_to'])) {
                $job = $this->service->createAssigned($data, $request->user());
            } else {
                $job = $this->service->createRequest($data, $request->user());
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job created.');
    }

    public function show(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);
        abort_unless(
            $request->user()->can('jobs.view-all') || $job->created_by === $request->user()->id || $job->assigned_to === $request->user()->id,
            403
        );

        $workers = $this->userRepository->byRole('Worker');

        $checklistTemplates = \App\Models\ChecklistTemplate::where('is_active', true)->orderBy('name')->get();

        if (! $request->user()->can('jobs.view-all')) {
            return view('worker.jobs.show', compact('job', 'workers', 'checklistTemplates'));
        }

        $materialAvailability = app(\App\Services\Job\JobMaterialService::class)->availabilityFor($job);
        $products = \App\Models\Product::orderBy('name')->get();

        return view('jobs.show', compact('job', 'workers', 'materialAvailability', 'products', 'checklistTemplates'));
    }

    public function applyTemplate(Request $request, $job, \App\Services\Job\ChecklistTemplateService $svc)
    {
        $data = $request->validate(['checklist_template_id' => 'required|integer|exists:checklist_templates,id']);
        $jobModel = $this->repository->find($job);
        abort_if(! $jobModel, 404);
        abort_unless($jobModel->created_by === $request->user()->id || $request->user()->can('jobs.assign'), 403);
        $template = \App\Models\ChecklistTemplate::where('is_active', true)->findOrFail($data['checklist_template_id']);
        $svc->applyToJob($template, $jobModel);

        return redirect()->route('jobs.show', $jobModel->id)->with('success', 'Template applied to checklist.');
    }

    public function addMaterial(Request $request, $job, \App\Services\Job\JobMaterialService $svc)
    {
        $data = $request->validate(['product_id' => 'required|integer|exists:product,id', 'quantity' => 'required|integer|min:1']);
        $jobModel = $this->repository->find($job);
        abort_if(! $jobModel, 404);
        $svc->add($jobModel, (int) $data['product_id'], (int) $data['quantity']);

        return redirect()->route('jobs.show', $jobModel->id)->with('success', 'Material added to job.');
    }

    public function removeMaterial($material, \App\Services\Job\JobMaterialService $svc)
    {
        $svc->remove((int) $material);

        return back()->with('success', 'Material removed.');
    }

    public function substituteMaterial(Request $request, $material, \App\Services\Job\JobMaterialService $svc)
    {
        $data = $request->validate(['new_product_id' => 'required|integer|exists:product,id']);
        $svc->substitute((int) $material, (int) $data['new_product_id']);

        return back()->with('success', 'Material substituted.');
    }

    public function addChecklistItem(Request $request, $id)
    {
        $data = $request->validate(['description' => 'required|string|max:255']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);
        abort_unless(
            $job->created_by === $request->user()->id || $request->user()->can('jobs.assign'),
            403
        );

        $this->checklistService->addItem($job, $data['description']);

        return redirect()->route('jobs.show', $job->id)->with('success', 'Checklist item added.');
    }

    public function toggleChecklistItem(Request $request, $id, $itemId)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);
        abort_unless(
            $request->user()->can('jobs.view-all') || $job->created_by === $request->user()->id || $job->assigned_to === $request->user()->id,
            403
        );

        $item = JobChecklistItem::where('job_id', $job->id)->findOrFail($itemId);
        $this->checklistService->toggleItem($item, $request->user());

        return redirect()->route('jobs.show', $job->id);
    }

    public function pdf(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);
        abort_unless(
            $request->user()->can('jobs.view-all') || $job->created_by === $request->user()->id || $job->assigned_to === $request->user()->id,
            403
        );

        if ($job->status !== 'completed') {
            return redirect()->route('jobs.show', $job->id)->with('error', 'The completion report is only available once the job is completed.');
        }

        $job->load(['machine', 'assignee', 'photos']);
        $pdf = Pdf::loadView('pdf.job', ['job' => $job])->setPaper('a4');

        return $pdf->download('Job-' . $job->id . '-completion-report.pdf');
    }

    public function approve(Request $request, $id)
    {
        $data = $request->validate(['assigned_to' => 'nullable|exists:users,id']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->approve($job, $request->user(), $data['assigned_to'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.pending-approval')->with('success', 'Job approved.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->reject($job, $request->user(), $data['rejection_reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.pending-approval')->with('success', 'Job rejected.');
    }

    public function pendingApproval()
    {
        return view('jobs.pending-approval', ['jobs' => $this->repository->pendingApproval()]);
    }

    public function start(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->start($job, $request->user());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        $this->ticketService->syncStatusFromJob($job);

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job started.');
    }

    public function hold(Request $request, $id)
    {
        $data = $request->validate(['on_hold_reason' => 'required|string|max:1000']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->hold($job, $request->user(), $data['on_hold_reason']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        $this->ticketService->syncStatusFromJob($job);

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job put on hold.');
    }

    public function resume(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->resume($job, $request->user());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        $this->ticketService->syncStatusFromJob($job);

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job resumed.');
    }

    public function storePhoto(Request $request, $id)
    {
        $data = $request->validate([
            'photo' => 'required|image|max:10240',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'stage' => 'nullable|in:before,after,general',
        ]);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);
        abort_unless($job->assigned_to === $request->user()->id || $request->user()->can('jobs.assign'), 403);

        try {
            $this->photoService->upload(
                $job,
                $request->user(),
                $request->file('photo'),
                (float) $data['latitude'],
                (float) $data['longitude'],
                $data['stage'] ?? 'general',
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true], 201);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Photo uploaded.');
    }

    public function complete(Request $request, $id)
    {
        $data = $request->validate(['completion_notes' => 'required|string|max:2000']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->complete($job, $request->user(), $data['completion_notes']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $this->ticketService->syncStatusFromJob($job);

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job marked complete.');
    }

    public function reassign(Request $request, $id)
    {
        $data = $request->validate(['assigned_to' => 'required|exists:users,id']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->reassign($job, $request->user(), (int) $data['assigned_to']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job reassigned.');
    }
}
