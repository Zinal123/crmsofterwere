<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;
use App\Services\Quotation\QuotationService;
use Illuminate\Http\Request;

class QutationController extends Controller
{
    public function __construct(private QuotationService $service)
    {
    }

    public function index(Request $request)
    {
        $product = $this->service->list();
        return view('listquation', compact('product'));
    }

    public function generatequtation($id)
    {
        return view('qutation', $this->service->getQuotationFormViewData($id));
    }

    public function generatequtationstore(Request $request)
    {
        return $this->storeQuotation($request);
    }

    public function print()
    {
        return view('queationpdf');
    }

    public function Co2quation($id)
    {
        return view('co2qutation', $this->service->getQuotationFormViewData($id));
    }

    public function Co2quationstore(Request $request)
    {
        // co2qutation.blade.php posts to the same `quationform` table (via
        // the same Quation model/fillable list) as the fiber quotation
        // form, just with a different subset of fields filled in
        // (description/amount/description1/... instead of the
        // fiber-specific config selects) - no CO2-specific persistence
        // logic is needed, so this shares storeQuotation() with the
        // fiber form's store action.
        return $this->storeQuotation($request);
    }

    private function storeQuotation(Request $request)
    {
        $this->service->create($request->all());
        return redirect()->route('listqutation')->with('success', 'Your message has been sent successfully!');
    }
}
