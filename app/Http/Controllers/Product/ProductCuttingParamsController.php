<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductCuttingParamsService;
use Illuminate\Http\Request;

/**
 * Split out of ProductConfigController (38 methods, over Sonar's 20-method
 * threshold) - this third covers the "technicalparameters" view's 2 config
 * types: cutting way, CNC sheet thickness.
 */
class ProductCuttingParamsController extends Controller
{
    public function __construct(private ProductCuttingParamsService $service)
    {
    }

    public function technicalparameters($id)
    {
        return view('technicalparameters', $this->service->getTechnicalParamsViewData((int) $id));
    }

    public function cuttingwaystore(Request $request)
    {
        $this->requireProductId($request);
        $this->service->createCuttingWay($request->all());
        return view('technicalparameters', $this->service->getTechnicalParamsViewData($request->input('product_id')));
    }

    public function cncthinknessstore(Request $request)
    {
        $this->requireProductId($request);
        $this->service->createCncThickness($request->all());
        return view('technicalparameters', $this->service->getTechnicalParamsViewData($request->input('product_id')));
    }

    public function cuttingwayupdate(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->updateCuttingWay($id, $request->only(['cuttingway', 'product_id']));
        return view('technicalparameters', $this->service->getTechnicalParamsViewData($request->input('product_id')));
    }

    public function cuttingwaydelete(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->deleteCuttingWay($id);
        return view('technicalparameters', $this->service->getTechnicalParamsViewData($request->input('product_id')));
    }

    public function cncthinknessupdate(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->updateCncThickness($id, $request->only(['cuttingthinks', 'product_id']));
        return view('technicalparameters', $this->service->getTechnicalParamsViewData($request->input('product_id')));
    }

    public function cncthinknessdelete(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->deleteCncThickness($id);
        return view('technicalparameters', $this->service->getTechnicalParamsViewData($request->input('product_id')));
    }

    /**
     * A fully empty POST to any of these store endpoints previously created
     * an all-NULL row, invisible even under the product it was meant to belong
     * to (product_id itself was null). This is the one field common to and
     * required by every one of them.
     */
    private function requireProductId(Request $request): void
    {
        $request->validate([
            'product_id' => 'required|integer|exists:product,id',
        ]);
    }
}
