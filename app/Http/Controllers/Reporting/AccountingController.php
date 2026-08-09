<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Services\Reporting\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AccountingController extends Controller
{
    public function __construct(private AccountingService $service)
    {
    }

    public function chartOfAccounts()
    {
        return view('accounting.chart', [
            'accounts' => $this->service->chartOfAccounts(),
        ]);
    }

    public function profitAndLoss(Request $request)
    {
        [$from, $to] = $this->range($request, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());

        return view('accounting.profit-loss', [
            'report' => $this->service->profitAndLoss($from, $to),
        ]);
    }

    public function trialBalance(Request $request)
    {
        $asOf = $this->parseDate($request->query('as_of'), Carbon::now());

        return view('accounting.trial-balance', [
            'report' => $this->service->trialBalance($asOf),
        ]);
    }

    /** Parse from/to query params, falling back to the given defaults. */
    private function range(Request $request, Carbon $defaultFrom, Carbon $defaultTo): array
    {
        $from = $this->parseDate($request->query('from'), $defaultFrom)->startOfDay();
        $to = $this->parseDate($request->query('to'), $defaultTo)->endOfDay();

        // Guard against an inverted range rather than returning nonsense.
        return $from->lte($to) ? [$from, $to] : [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
    }

    private function parseDate($value, Carbon $default): Carbon
    {
        if (! is_string($value) || $value === '') {
            return $default->copy();
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception) {
            return $default->copy();
        }
    }
}
