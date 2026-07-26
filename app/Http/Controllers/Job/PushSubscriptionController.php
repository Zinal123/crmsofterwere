<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        return $this->subscribe($request, $request->user());
    }

    public function storeForClient(Request $request)
    {
        return $this->subscribe($request, $request->user('client'));
    }

    private function subscribe(Request $request, $subscriber)
    {
        $data = $request->validate([
            'endpoint' => 'required|string',
            'publicKey' => 'nullable|string',
            'authToken' => 'nullable|string',
            'contentEncoding' => 'nullable|string',
        ]);

        $subscriber->updatePushSubscription(
            $data['endpoint'],
            $data['publicKey'] ?? null,
            $data['authToken'] ?? null,
            $data['contentEncoding'] ?? null,
        );

        return response()->json(['success' => true]);
    }
}
