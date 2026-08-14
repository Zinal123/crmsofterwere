<?php

namespace App\Http\Controllers\Ticketing;

use App\Http\Controllers\Controller;
use App\Services\Ticketing\TicketProblemTypeService;
use Illuminate\Http\Request;

class TicketProblemTypeController extends Controller
{
    public function __construct(private TicketProblemTypeService $service)
    {
    }

    public function index()
    {
        return view('ticketing.problem-types.index', [
            'problemTypes' => $this->service->listAll(),
            'templates' => \App\Models\ChecklistTemplate::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', \Illuminate\Validation\Rule::in(\App\Models\TicketProblemType::CATEGORIES)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'default_priority' => ['required', \Illuminate\Validation\Rule::in(\App\Models\TicketProblemType::PRIORITIES)],
            'estimated_resolution_hours' => 'nullable|integer|min:0|max:8760',
            'checklist_template_id' => 'nullable|exists:checklist_templates,id',
        ]);

        $this->service->create($data);

        return redirect()->route('admin.ticket-problem-types.index')->with('success', 'Problem type added.');
    }

    public function toggle($id)
    {
        $this->service->toggleActive((int) $id);

        return redirect()->route('admin.ticket-problem-types.index')->with('success', 'Problem type updated.');
    }
}
