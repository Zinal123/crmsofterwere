<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\TallyConnection;
use App\Services\Integration\TallyConnectionService;
use Illuminate\Http\Request;

class TallyConnectionController extends Controller
{
    public function edit()
    {
        return view('integration.tally-connection.edit', [
            'connection' => TallyConnection::first(),
        ]);
    }

    public function update(Request $request)
    {
        $existing = TallyConnection::first();

        $data = $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'company_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => ($existing ? 'nullable' : 'required').'|string|max:255',
        ]);

        $connection = $existing ?? new TallyConnection();
        $connection->host = $data['host'];
        $connection->port = $data['port'];
        $connection->company_name = $data['company_name'];
        $connection->username = $data['username'];

        if (! empty($data['password'])) {
            $connection->password = $data['password'];
        }

        $connection->status = 'disconnected';
        $connection->last_error = null;
        $connection->save();

        return redirect()->route('tally-connection.edit')->with('success', 'Tally connection details saved.');
    }

    public function connect(TallyConnectionService $service)
    {
        $connection = TallyConnection::first();

        if (! $connection) {
            return redirect()->route('tally-connection.edit')->with('error', 'Save connection details before testing the connection.');
        }

        $service->testConnection($connection);
        $connection->refresh();

        return redirect()->route('tally-connection.edit')->with(
            $connection->status === 'connected' ? 'success' : 'error',
            $connection->status === 'connected' ? 'Connected to Tally successfully.' : 'Connection failed: '.$connection->last_error
        );
    }
}
