<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ClientMachineRepositoryInterface;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private ClientMachineRepositoryInterface $machineRepository)
    {
    }

    public function index(Request $request)
    {
        return view('client.dashboard', [
            'machines' => $this->machineRepository->forClientAccount($request->user('client')->id),
        ]);
    }
}
