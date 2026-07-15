<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductConfigService;
use Illuminate\Http\Request;

class ProductConfigController extends Controller
{
    public function __construct(private ProductConfigService $service)
    {
    }

    public function standerconfig($id)
    {
        // Pre-existing bug, intentionally preserved: this route has always
        // passed the literal string "id" instead of the real route
        // parameter, so this page never actually filters by the URL's
        // product id. Not fixed here per the no-behavior-change constraint;
        // tracked as a known issue for a future dedicated bug-fix pass.
        return view('standerconfig', $this->service->getConfigViewData('id'));
    }

    public function standerconfiglist($id)
    {
        // Pre-existing bug, intentionally preserved: this page has always
        // hardcoded product_id=1 regardless of the URL parameter.
        return view('standerconfiglist', $this->service->getStanderconfigListViewData(1));
    }

    public function technicalparameters($id)
    {
        // Pre-existing bug, intentionally preserved: same hardcoded-1
        // behavior as standerconfiglist() above.
        return view('technicalparameters', $this->service->getTechnicalParamsViewData(1));
    }

    public function softerwere(Request $request, $id)
    {
        return view('standerconfig', $this->service->getConfigViewData($id));
    }

    public function softerwereshow(Request $request)
    {
        // Route "softerwere.show" is registered but not yet wired to any view/logic; kept as a no-op stub.
    }

    public function cuttingway(Request $request, $id)
    {
        return view('standerconfig', $this->service->getConfigViewData($id));
    }

    public function softerwerestore(Request $request)
    {
        $this->service->createSoftware($request->all());
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function cuttingstore(Request $request)
    {
        $this->service->createLaserCutting($request->all());
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function focusingstore(Request $request)
    {
        $this->service->createFocusing($request->all());
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function powerstore(Request $request)
    {
        $this->service->createPower($request->all());
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function motorstore(Request $request)
    {
        $this->service->createMotor($request->all());
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function gearstore(Request $request)
    {
        $this->service->createGear($request->all());
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function rackstore(Request $request)
    {
        $this->service->createRack($request->all());
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function softwarestore(Request $request)
    {
        $this->service->createSoftware1($request->all());
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function cuttingwaystore(Request $request)
    {
        $this->service->createCuttingWay($request->all());
        return view('technicalparameters', $this->service->getTechnicalParamsViewData($request->input('product_id')));
    }

    public function cncthinknessstore(Request $request)
    {
        $this->service->createCncThickness($request->all());
        return view('technicalparameters', $this->service->getTechnicalParamsViewData($request->input('product_id')));
    }
}
