<?php

namespace App\Http\Controllers\Ticketing;

use App\Http\Controllers\Controller;
use App\Services\Ticketing\SparePartRequestService;
use Illuminate\Http\Request;

class SparePartRequestController extends Controller
{
    public function __construct(private SparePartRequestService $service)
    {
    }

    public function index()
    {
        return view('ticketing.spare-part-requests.index', [
            'requests' => $this->service->listAll(),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,fulfilled,rejected',
        ]);

        try {
            $this->service->updateStatus($this->service->find((int) $id), $request->input('status'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.spare-part-requests.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.spare-part-requests.index')->with('success', 'Request updated.');
    }
}
