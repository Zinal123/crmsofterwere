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
        // $id is accepted but unused, matching pre-existing behavior - see
        // QuotationService::getQuotationFormViewData().
        return view('qutation', $this->service->getQuotationFormViewData());
    }

    public function generatequtationstore(Request $request)
    {
        $this->service->create($request->all());
        return redirect()->route('listqutation')->with('success', 'Your message has been sent successfully!');
    }

    public function print()
    {
        return view('queationpdf');
    }

    public function Co2quation($id)
    {
        // $id is accepted but unused, matching pre-existing behavior - see
        // QuotationService::getQuotationFormViewData().
        return view('co2qutation', $this->service->getQuotationFormViewData());
    }
}
