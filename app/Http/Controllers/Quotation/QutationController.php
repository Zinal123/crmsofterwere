<?php

namespace App\Http\Controllers\Quotation;

use App\Http\Controllers\Controller;
use App\Services\Quotation\QuotationService;
use Barryvdh\DomPDF\Facade\Pdf;
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
        return view('qutation', array_merge($this->service->getQuotationFormViewData($id), ['defaultType' => 'fiber']));
    }

    public function generatequtationstore(Request $request)
    {
        return $this->storeQuotation($request);
    }

    public function print($id)
    {
        $data = $this->service->getQuotationPdfData($id);
        $pdf = Pdf::loadView('pdf.quotation', $data)->setPaper('a4');

        return $pdf->download('Quotation-' . $data['quotation']->id . '.pdf');
    }

    public function printInline($id)
    {
        $data = $this->service->getQuotationPdfData($id);
        $data['autoPrint'] = true;
        $pdf = Pdf::loadView('pdf.quotation', $data)->setPaper('a4');
        $pdf->getDomPDF()->getOptions()->set('isJavascriptEnabled', true);

        return $pdf->stream('Quotation-' . $data['quotation']->id . '.pdf');
    }

    public function delete($id)
    {
        $this->service->delete($id);
        return redirect()->route('listqutation')->with('success', 'Quotation deleted.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'clientname' => 'nullable|string|max:255',
            'companyname' => 'nullable|string|max:255',
            'gstno' => 'nullable|string|max:255',
            'companyaddress' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ]);

        $this->service->update($id, $data);

        return redirect()->route('listqutation')->with('success', 'Quotation updated.');
    }

    public function convertToInvoice(Request $request, $id)
    {
        $data = $request->validate([
            'state' => ['required', 'string', \Illuminate\Validation\Rule::in(\App\Support\IndianStates::LIST)],
        ]);

        try {
            $result = $this->service->convertToInvoice($id, $data['state']);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('listqutation')->with('error', $e->getMessage());
        }

        $message = 'Invoice ' . $result['invoice']->invoice_id . ' created. GST rates default to 0 - review and adjust on each line before finalizing.';
        if ($result['skipped_items'] > 0) {
            $message .= ' ' . $result['skipped_items'] . ' quotation line(s) with no linked product could not be auto-added - add them manually.';
        }

        return redirect()->route('invoice.details', $result['invoice']->id)->with('success', $message);
    }

    public function Co2quation($id)
    {
        return view('qutation', array_merge($this->service->getQuotationFormViewData($id), ['defaultType' => 'co2']));
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
        $data = $request->validate($this->validationRules());
        $items = $request->validate([
            'items' => 'nullable|array',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.amount' => 'nullable|string|max:255',
            'items.*.product_id' => 'nullable|integer|exists:product,id',
            'items.*.quantity' => 'nullable|integer|min:1',
        ])['items'] ?? [];
        $this->service->create($data, $items);
        return redirect()->route('listqutation')->with('success', 'Your message has been sent successfully!');
    }

    /**
     * Types/lengths mirror database/migrations/2026_07_10_000018_create_quationform_table.php
     * exactly - every column here is nullable in the schema, so this only rejects data that
     * would otherwise crash the insert (wrong type, too long), it doesn't newly require
     * anything that was previously optional.
     */
    private function validationRules(): array
    {
        return [
            'product_id' => 'nullable|integer',
            'clientname' => 'nullable|string|max:255',
            'companyname' => 'nullable|string|max:255',
            'gstno' => 'nullable|string|max:255',
            'companyaddress' => 'nullable|string|max:255',
            'bank' => 'nullable|integer',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'date' => 'nullable|date',
            'reminderdate' => 'nullable|date',
            'softweredetails' => 'nullable|integer',
            'lasercutting' => 'nullable|integer',
            'focus' => 'nullable|integer',
            'power' => 'nullable|integer',
            'inputpower' => 'nullable|string|max:255',
            'cuttingway' => 'nullable|integer',
            'cncspan' => 'nullable|string|max:255',
            'cnslenght' => 'nullable|string|max:255',
            'cuttingrang' => 'nullable|string|max:255',
            'liftingheight' => 'nullable|string|max:255',
            'headquantity' => 'nullable|string|max:255',
            'cuttingthickess' => 'nullable|integer',
            'strokespeed' => 'nullable|string|max:255',
            'cuttingspeed' => 'nullable|string|max:255',
            'drive' => 'nullable|string|max:255',
            'motor' => 'nullable|integer',
            'motortype' => 'nullable|integer',
            'gearbox' => 'nullable|integer',
            'rack' => 'nullable|integer',
            'software' => 'nullable|integer',
            'description' => 'nullable|string',
            'description1' => 'nullable|string',
            'description2' => 'nullable|string',
            'amount' => 'nullable|string|max:255',
            'amount1' => 'nullable|string|max:255',
            'amount2' => 'nullable|string|max:255',
            'optionparthyscope' => 'nullable|string',
            'note' => 'nullable|string',
        ];
    }
}
