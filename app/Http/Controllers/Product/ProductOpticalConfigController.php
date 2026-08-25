<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductOpticalConfigService;
use Illuminate\Http\Request;

/**
 * Split out of ProductConfigController (38 methods, over Sonar's 20-method
 * threshold) - this third covers the "standerconfig" view's 4 config types:
 * software, laser cutting head, focusing, power source.
 */
class ProductOpticalConfigController extends Controller
{
    public function __construct(private ProductOpticalConfigService $service)
    {
    }

    public function standerconfig($id)
    {
        return view('standerconfig', $this->service->getConfigViewData((int) $id));
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
