<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TallySyncQueue;
use Illuminate\Http\Request;

class TallySyncApiController extends Controller
{
    public function pending()
    {
        // Read by the status screen (Task 6) to warn when the agent hasn't
        // polled recently - the only signal the CRM has that it's still
        // running, since it never receives an inbound connection from it.
        cache()->put('tally_agent_last_checkin', now(), now()->addDay());

        return response()->json([
            'data' => TallySyncQueue::pending()->orderBy('id')->get(),
        ]);
    }

    public function acknowledge(Request $request, TallySyncQueue $tallySyncQueue)
    {
        $data = $request->validate([
            'status' => 'required|in:synced,failed',
            'tally_voucher_id' => 'nullable|string|max:255',
            'error' => 'nullable|string|max:2000',
        ]);

        $tallySyncQueue->update([
            'status' => $data['status'],
            'tally_voucher_id' => $data['tally_voucher_id'] ?? null,
            'last_error' => $data['status'] === 'failed' ? ($data['error'] ?? null) : null,
            'attempts' => $tallySyncQueue->attempts + 1,
        ]);

        return response()->json(['isSuccess' => true]);
    }
}
