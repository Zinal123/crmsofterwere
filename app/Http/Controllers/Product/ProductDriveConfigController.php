<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductDriveConfigService;
use Illuminate\Http\Request;

/**
 * Split out of ProductConfigController (38 methods, over Sonar's 20-method
 * threshold) - this third covers the "standerconfiglist" view's 4 config
 * types: motor, gear, rack, nesting software.
 */
class ProductDriveConfigController extends Controller
{
    public function __construct(private ProductDriveConfigService $service)
    {
    }

    public function standerconfiglist($id)
    {
        return view('standerconfiglist', $this->service->getStanderconfigListViewData((int) $id));
    }

    public function motorstore(Request $request)
    {
        $this->requireProductId($request);
        $this->service->createMotor($request->all());
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function gearstore(Request $request)
    {
        $this->requireProductId($request);
        $this->service->createGear($request->all());
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function rackstore(Request $request)
    {
        $this->requireProductId($request);
        $this->service->createRack($request->all());
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function softwarestore(Request $request)
    {
        $this->requireProductId($request);
        $this->service->createSoftware1($request->all());
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function motorupdate(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->updateMotor($id, $request->only(['companyname', 'product_id']));
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function motordelete(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->deleteMotor($id);
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function gearupdate(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->updateGear($id, $request->only(['companyname', 'product_id']));
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function geardelete(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->deleteGear($id);
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function rackupdate(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->updateRack($id, $request->only(['companyname', 'product_id']));
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function rackdelete(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->deleteRack($id);
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function software1update(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->updateSoftware1($id, $request->only(['companyname', 'product_id']));
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
    }

    public function software1delete(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->deleteSoftware1($id);
        return view('standerconfiglist', $this->service->getStanderconfigListViewData($request->input('product_id')));
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
