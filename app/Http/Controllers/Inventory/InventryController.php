<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;

class InventryController extends Controller
{
    public function __construct(private InventoryService $service)
    {
    }

    public function inventry()
    {
        // The view (inventrylist.blade.php) re-queries both $Invetry and
        // $product itself via inline PHP, so this passed-in $product is
        // never actually used - pre-existing dead data, preserved as-is
        // rather than fixed here (same pattern as apps-invoices-list and
        // vender before their route-shadowing fix).
        $product = $this->service->getProductList();
        return view('inventrylist', compact('product'));
    }

    public function inventrystore(Request $request)
    {
        $this->service->create($request->all());
        return redirect()->route('invoice.inventrylist');
    }

    public function quantityupdate(Request $request)
    {
        $found = $this->service->reduceQuantity(
            $request->input('id'),
            $request->input('quantity')
        );

        if ($found) {
            return response()->json(['success' => true]);
        }
    }
}
