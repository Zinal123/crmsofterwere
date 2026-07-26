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
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|in:electrical,mechanical',
            'name' => 'required|string|max:255',
        ]);

        $this->service->create($request->only(['category', 'name']));

        return redirect()->route('admin.ticket-problem-types.index')->with('success', 'Problem type added.');
    }

    public function toggle($id)
    {
        $this->service->toggleActive((int) $id);

        return redirect()->route('admin.ticket-problem-types.index')->with('success', 'Problem type updated.');
    }
}
