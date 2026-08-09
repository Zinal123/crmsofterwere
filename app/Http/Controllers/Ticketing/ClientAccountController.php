<?php

namespace App\Http\Controllers\Ticketing;

use App\Http\Controllers\Controller;
use App\Services\Ticketing\ClientAccountService;
use Illuminate\Http\Request;

class ClientAccountController extends Controller
{
    public function __construct(private ClientAccountService $service)
    {
    }

    public function index()
    {
        return view('ticketing.client-accounts.index', [
            'clientAccounts' => $this->service->listAll(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:client_accounts,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        $this->service->createAccount($request->only(['name', 'email', 'phone', 'password']));

        return redirect()->route('admin.client-accounts.index')->with('success', 'Client account created.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:client_accounts,email,' . $id,
            'phone' => 'nullable|string|max:20',
        ]);

        $this->service->update($id, $data);

        return redirect()->route('admin.client-accounts.index')->with('success', 'Client account updated.');
    }

    public function toggle($id)
    {
        $this->service->toggleActive($id);

        return redirect()->route('admin.client-accounts.index')->with('success', 'Client account status updated.');
    }
}
