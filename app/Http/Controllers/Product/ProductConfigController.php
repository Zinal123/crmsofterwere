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
        return view('standerconfig', $this->service->getConfigViewData((int) $id));
    }

    public function standerconfiglist($id)
    {
        return view('standerconfiglist', $this->service->getStanderconfigListViewData((int) $id));
    }

    public function technicalparameters($id)
    {
        return view('technicalparameters', $this->service->getTechnicalParamsViewData((int) $id));
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
        $this->requireProductId($request);
        $this->service->createSoftware($request->all());
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function cuttingstore(Request $request)
    {
        $this->requireProductId($request);
        $this->service->createLaserCutting($request->all());
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function focusingstore(Request $request)
    {
        $this->requireProductId($request);
        $this->service->createFocusing($request->all());
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function powerstore(Request $request)
    {
        $this->requireProductId($request);
        $this->service->createPower($request->all());
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
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

    public function softerwereupdate(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->updateSoftware($id, $request->only(['company', 'product_id', 'modal', 'description']));
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function softereweredelete(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->deleteSoftware($id);
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function cuttingupdate(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->updateLaserCutting($id, $request->only(['company', 'product_id', 'modal', 'decription']));
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function cuttingdelete(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->deleteLaserCutting($id);
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function focusingupdate(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->updateFocusing($id, $request->only(['company', 'product_id', 'modal', 'description']));
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function focusingdelete(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->deleteFocusing($id);
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function powerupdate(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->updatePower($id, $request->only(['company', 'product_id', 'modal', 'description']));
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
    }

    public function powerdelete(Request $request, $id)
    {
        $this->requireProductId($request);
        $this->service->deletePower($id);
        return view('standerconfig', $this->service->getConfigViewData($request->input('product_id')));
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
     * A fully empty POST to any of these 10 store endpoints previously created
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
