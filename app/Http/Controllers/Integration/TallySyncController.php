<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\TallyConnection;
use App\Models\TallySyncQueue;

class TallySyncController extends Controller
{
    public function index()
    {
        $connection = TallyConnection::first();

        return view('integration.tally-sync.index', [
            'queue' => TallySyncQueue::orderByDesc('id')->paginate(25),
            'notConnected' => $connection === null || $connection->status !== 'connected',
        ]);
    }

    public function retry(TallySyncQueue $tallySyncQueue)
    {
        $tallySyncQueue->update(['status' => 'pending', 'last_error' => null]);

        return redirect()->route('tally-sync.index')->with('success', 'Queued for retry.');
    }
}
