<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\TallySyncQueue;

class TallySyncController extends Controller
{
    private const STALE_AFTER_MINUTES = 10;

    public function index()
    {
        $lastCheckin = cache('tally_agent_last_checkin');

        return view('integration.tally-sync.index', [
            'queue' => TallySyncQueue::orderByDesc('id')->paginate(25),
            'lastCheckin' => $lastCheckin,
            'agentIsStale' => $lastCheckin === null || $lastCheckin->diffInMinutes(now()) > self::STALE_AFTER_MINUTES,
        ]);
    }

    public function retry(TallySyncQueue $tallySyncQueue)
    {
        $tallySyncQueue->update(['status' => 'pending', 'last_error' => null]);

        return redirect()->route('tally-sync.index')->with('success', 'Queued for retry.');
    }
}
