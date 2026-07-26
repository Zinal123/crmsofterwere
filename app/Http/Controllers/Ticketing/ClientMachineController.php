<?php

namespace App\Http\Controllers\Ticketing;

use App\Http\Controllers\Controller;
use App\Services\Ticketing\ClientMachineService;
use Illuminate\Http\Request;

class ClientMachineController extends Controller
{
    public function __construct(private ClientMachineService $service)
    {
    }

    public function index()
    {
        return view('ticketing.client-machines.index', array_merge(
            ['clientMachines' => $this->service->listAll()],
            $this->service->getCreateViewData()
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_account_id' => 'required|integer|exists:client_accounts,id',
            'product_id' => 'required|integer|exists:product,id',
            'invoice_id' => 'nullable|integer|exists:invoice,id',
            'serial_number' => 'required|string|max:255',
            'installed_at' => 'nullable|date',
        ]);

        $this->service->createMachine($request->only(['client_account_id', 'product_id', 'invoice_id', 'serial_number', 'installed_at']));

        return redirect()->route('admin.client-machines.index')->with('success', 'Machine registered.');
    }
}
